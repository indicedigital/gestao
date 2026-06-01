<?php

namespace App\Http\Controllers\Company;

use App\Exports\TasksExport;
use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Rules\BelongsToCompany;
use App\Services\MentionParserService;
use App\Services\SlaService;
use App\Services\TaskHistoryService;
use App\Services\TaskWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskController extends Controller
{
    use InteractsWithCompany;

    public function __construct(
        protected SlaService $slaService,
        protected TaskHistoryService $historyService,
        protected TaskWorkflowService $workflowService,
        protected MentionParserService $mentionParser
    ) {}

    protected function filteredQuery(Request $request, $company)
    {
        $authz = $this->authz();
        $query = Task::where('company_id', $company->id)->with(['project', 'assignee']);

        if (! $authz->hasFullDataScope('tasks')) {
            $authz->applyTaskScope($query);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }
        if ($request->filled('sla') && $request->sla === 'late') {
            $query->where('status', '!=', 'completed')
                ->whereNotNull('sla_deadline')
                ->where('sla_deadline', '<', now());
        }
        if ($request->filled('sla') && $request->sla === 'on_time') {
            $query->where(function ($q) {
                $q->whereNull('sla_deadline')
                    ->orWhere('sla_deadline', '>=', now())
                    ->orWhere('status', 'completed');
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();
        $authz = $this->authz();

        $tasks = $this->filteredQuery($request, $company)->latest()->paginate(20)->withQueryString();
        $projects = Project::where('company_id', $company->id)->orderBy('name')->get(['id', 'name']);
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('company.tasks.index', compact('company', 'tasks', 'projects', 'employees', 'authz'));
    }

    public function exportExcel(Request $request)
    {
        $company = $this->getCurrentCompany();
        abort_unless($this->authz()->canManage(), 403);

        $tasks = $this->filteredQuery($request, $company)->latest()->get();

        return Excel::download(new TasksExport($tasks), 'tasks-'.now()->format('Y-m-d').'.xlsx');
    }

    public function create(Request $request)
    {
        abort_unless($this->authz()->canCreateTask(), 403);

        $company = $this->getCurrentCompany();
        $projects = Project::where('company_id', $company->id)->orderBy('name')->get(['id', 'name']);
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $selectedProject = $request->query('project_id');

        return view('company.tasks.create', compact('company', 'projects', 'employees', 'selectedProject'));
    }

    public function store(Request $request)
    {
        abort_unless($this->authz()->canCreateTask(), 403);

        $company = $this->getCurrentCompany();
        $validated = $this->validateTask($request, $company);

        $validated['company_id'] = $company->id;
        $validated['created_by'] = Auth::id();
        $validated['creation_channel'] = 'system';

        $task = Task::create($validated);
        $this->slaService->applyToTask($task);
        $this->historyService->log($task, 'created');

        $redirect = $request->input('redirect_to') === 'kanban'
            ? route('company.projects.kanban', $validated['project_id'])
            : route('company.tasks.show', $task);

        return redirect($redirect)->with('success', 'Task criada com sucesso!');
    }

    public function show(Task $task)
    {
        $company = $this->getCurrentCompany();
        abort_unless($this->authz()->canViewTask($task), 403);

        $task->load([
            'project.client',
            'assignee',
            'subtasks.assignee',
            'comments.user',
            'attachments.user',
            'histories.user',
        ]);

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $canEdit = $this->authz()->canUpdateTask($task);
        $canDelete = $this->authz()->canDeleteTask($task);
        $canManageSubtasks = $this->authz()->canManageSubtasks($task);

        return view('company.tasks.show', compact('company', 'task', 'employees', 'canEdit', 'canDelete', 'canManageSubtasks'));
    }

    public function edit(Task $task)
    {
        abort_unless($this->authz()->canUpdateTask($task), 403);

        $company = $this->getCurrentCompany();
        $projects = Project::where('company_id', $company->id)->orderBy('name')->get(['id', 'name']);
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $isManager = $this->authz()->canManage();

        return view('company.tasks.edit', compact('company', 'task', 'projects', 'employees', 'isManager'));
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($this->authz()->canUpdateTask($task), 403);

        $company = $this->getCurrentCompany();
        $original = $task->getOriginal();
        $validated = $this->validateTask($request, $company, $task);
        $isManager = $this->authz()->canManage();

        if (! $isManager) {
            $validated = array_intersect_key($validated, array_flip([
                'title', 'description', 'status', 'estimated_hours',
            ]));
        }

        if (isset($validated['status']) && ! $this->authz()->canMoveTaskStatus($task)) {
            unset($validated['status']);
        }

        if (isset($validated['status'])) {
            try {
                $this->workflowService->transition($task, $validated['status']);
                unset($validated['status']);
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['status' => $e->getMessage()]);
            }
        }

        if ($validated) {
            $priorityChanged = isset($validated['priority']) && $validated['priority'] !== $task->priority;
            $task->update($validated);
            $this->historyService->logChanges($task, $original, $validated);

            if ($priorityChanged) {
                $this->slaService->applyToTask($task->fresh());
            }
        }

        return redirect()->route('company.tasks.show', $task)->with('success', 'Task atualizada com sucesso!');
    }

    public function destroy(Task $task)
    {
        abort_unless($this->authz()->canDeleteTask($task), 403);

        $projectId = $task->project_id;

        foreach ($task->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->path);
        }

        $task->delete();

        return redirect()->route('company.projects.kanban', $projectId)
            ->with('success', 'Task removida com sucesso!');
    }

    public function updateStatus(Request $request, Task $task)
    {
        abort_unless($this->authz()->canViewTask($task), 403);
        abort_unless($this->authz()->canMoveTaskStatus($task), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Task::STATUSES))],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $this->workflowService->transition(
                $task,
                $validated['status'],
                $validated['position'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'task' => $task->fresh(['assignee', 'subtasks']),
        ]);
    }

    public function storeComment(Request $request, Task $task)
    {
        abort_unless($this->authz()->canViewTask($task), 403);

        $company = $this->getCurrentCompany();
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $parsed = $this->mentionParser->parse($validated['body'], $company->id);

        $comment = $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $parsed['body'],
            'mentions' => $parsed['mentions'],
            'is_internal' => (bool) ($validated['is_internal'] ?? false),
        ]);

        $this->historyService->log($task, 'comment_added', 'comment', null, $comment->id);

        return back()->with('success', 'Comentário adicionado.');
    }

    public function storeAttachment(Request $request, Task $task)
    {
        abort_unless($this->authz()->canViewTask($task), 403);

        $request->validate([
            'attachment' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip,txt',
            ],
        ]);

        $file = $request->file('attachment');
        $safeName = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs("tasks/{$task->id}", $safeName, 'local');

        $task->attachments()->create([
            'user_id' => Auth::id(),
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $this->historyService->log($task, 'attachment_added');

        return back()->with('success', 'Anexo enviado com sucesso.');
    }

    public function downloadAttachment(Task $task, TaskAttachment $attachment): StreamedResponse
    {
        abort_unless($this->authz()->canViewTask($task), 403);
        abort_unless($attachment->task_id === $task->id, 404);

        if (! Storage::disk('local')->exists($attachment->path)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('local')->download($attachment->path, $attachment->filename);
    }

    protected function validateTask(Request $request, $company, ?Task $task = null): array
    {
        $rules = [
            'project_id' => ['required', new BelongsToCompany('projects', $company->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', Rule::in(array_keys(Task::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'status' => ['nullable', Rule::in(array_keys(Task::STATUSES))],
            'assignee_id' => ['nullable', new BelongsToCompany('employees', $company->id)],
            'requester_type' => ['nullable', Rule::in(['internal', 'client'])],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
        ];

        if ($this->authz()->canManage()) {
            // managers can set all fields
        } elseif ($task) {
            $rules = array_intersect_key($rules, array_flip([
                'title', 'description', 'status', 'estimated_hours',
            ]));
        }

        return $request->validate($rules, [], [
            'project_id' => 'projeto',
            'assignee_id' => 'responsável',
        ]) + ['status' => $request->input('status', $task?->status ?? 'backlog')];
    }
}
