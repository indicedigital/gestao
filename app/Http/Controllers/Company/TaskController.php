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
use Carbon\Carbon;
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
        $projects = $this->scopedProjectsQuery($company)->get(['id', 'name']);
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('company.tasks.index', compact('company', 'tasks', 'projects', 'employees', 'authz'));
    }

    public function trashed(Request $request)
    {
        abort_unless($this->authz()->canViewTrashedTasks(), 403);

        $company = $this->getCurrentCompany();

        $tasks = Task::onlyTrashed()
            ->where('company_id', $company->id)
            ->with(['project:id,name', 'assignee:id,name', 'histories' => fn ($q) => $q->where('action', 'deleted')->latest()->limit(1)->with('user:id,name')])
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();

        return view('company.tasks.trash', compact('company', 'tasks'));
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
        $company = $this->getCurrentCompany();
        $authz = $this->authz();
        $selectedProject = $request->query('project_id');

        $contextProject = null;

        if ($selectedProject) {
            $contextProject = Project::where('company_id', $company->id)
                ->with('client:id,name')
                ->findOrFail($selectedProject);
            abort_unless($authz->canCreateTaskOnProject($contextProject), 403);
            $projects = collect([$contextProject]);
        } else {
            abort_unless($authz->canCreateTask(), 403);
            $projects = $this->scopedProjectsQuery($company)->get(['id', 'name']);
        }

        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('company.tasks.create', compact('company', 'projects', 'employees', 'selectedProject', 'contextProject'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        $validated = $this->validateTask($request, $company);

        $project = Project::where('company_id', $company->id)->findOrFail($validated['project_id']);
        abort_unless($this->authz()->canCreateTaskOnProject($project), 403);

        $request->validate([
            'subtasks' => ['nullable', 'array'],
            'subtasks.*.title' => ['nullable', 'string', 'max:255'],
            'subtasks.*.assignee_id' => ['nullable', new BelongsToCompany('employees', $company->id)],
            'subtasks.*.due_date' => ['nullable', 'date'],
        ], [], [
            'subtasks.*.due_date' => 'prazo da subtask',
        ]);

        $validated['company_id'] = $company->id;
        $validated['created_by'] = Auth::id();
        $validated['creation_channel'] = 'system';

        if (! empty($validated['sla_deadline'])) {
            $validated['sla_deadline'] = Carbon::parse($validated['sla_deadline']);
        } else {
            unset($validated['sla_deadline']);
        }

        $task = Task::create($validated);
        $this->slaService->applyToTask($task);
        $this->createSubtasksFromRequest($task, $request);
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
        $canEdit = ! $task->trashed() && $this->authz()->canUpdateTask($task);
        $canDelete = $this->authz()->canDeleteTask($task);
        $canManageSubtasks = $this->authz()->canManageSubtasks($task);

        return view('company.tasks.show', compact('company', 'task', 'employees', 'canEdit', 'canDelete', 'canManageSubtasks'));
    }

    public function edit(Task $task)
    {
        abort_unless($this->authz()->canUpdateTask($task), 403);

        $company = $this->getCurrentCompany();
        $task->load(['project', 'subtasks.assignee']);
        $projects = Project::where('company_id', $company->id)->orderBy('name')->get(['id', 'name']);
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $isManager = $this->authz()->canManage();
        $canEditPlanning = $this->authz()->canEditTaskPlanningFields($task);
        $contextProject = $task->project;

        return view('company.tasks.edit', compact(
            'company',
            'task',
            'projects',
            'employees',
            'isManager',
            'canEditPlanning',
            'contextProject',
        ));
    }

    public function update(Request $request, Task $task)
    {
        abort_unless($this->authz()->canUpdateTask($task), 403);

        $company = $this->getCurrentCompany();
        $original = $task->getOriginal();
        $validated = $this->validateTask($request, $company, $task);
        $canEditPlanning = $this->authz()->canEditTaskPlanningFields($task);

        if (! $canEditPlanning) {
            $validated = array_intersect_key($validated, array_flip([
                'title', 'description', 'status', 'estimated_hours',
            ]));
        } elseif (! $this->authz()->canManage()) {
            unset($validated['project_id']);
        }

        if ($canEditPlanning) {
            $request->validate([
                'subtasks' => ['nullable', 'array'],
                'subtasks.*.title' => ['nullable', 'string', 'max:255'],
                'subtasks.*.assignee_id' => ['nullable', new BelongsToCompany('employees', $company->id)],
                'subtasks.*.due_date' => ['nullable', 'date'],
            ], [], [
                'subtasks.*.due_date' => 'prazo da subtask',
            ]);
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

        if (array_key_exists('sla_deadline', $validated)) {
            $validated['sla_deadline'] = $validated['sla_deadline']
                ? Carbon::parse($validated['sla_deadline'])
                : null;
        }

        if ($validated) {
            $priorityChanged = isset($validated['priority']) && $validated['priority'] !== $task->priority;
            $task->update($validated);
            if (array_key_exists('sla_deadline', $validated) && $task->sla_deadline) {
                $task->sla_hours = max(1, (int) ceil(now()->diffInMinutes($task->sla_deadline) / 60));
                $task->save();
            }
            $this->historyService->logChanges($task, $original, $validated);

            if ($priorityChanged) {
                $this->slaService->applyToTask($task->fresh());
            }
        }

        if ($canEditPlanning) {
            $this->createSubtasksFromRequest($task, $request);
        }

        return redirect()->route('company.tasks.show', $task)->with('success', 'Task atualizada com sucesso!');
    }

    public function destroy(Task $task)
    {
        abort_unless($this->authz()->canDeleteTask($task), 403);

        $projectId = $task->project_id;
        $title = $task->title;

        $this->historyService->log(
            $task,
            'deleted',
            null,
            $title,
            Auth::user()->name ?? 'Usuário'
        );

        $task->delete();

        return redirect()->route('company.projects.kanban', $projectId)
            ->with('success', 'Task excluída. Ela não aparecerá mais no quadro, mas o histórico foi preservado.');
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

    protected function scopedProjectsQuery($company)
    {
        $authz = $this->authz();
        $query = Project::where('company_id', $company->id)->orderBy('name');

        if ($authz->isFreelancer()) {
            $authz->applyFreelancerProjectScope($query);
        } elseif (! $authz->hasFullDataScope('projects')) {
            $authz->applyProjectScope($query);
        }

        return $query;
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
            'sla_deadline' => ['nullable', 'date'],
        ];

        if ($this->authz()->canManage()) {
            // managers can set all fields
        } elseif ($task && $this->authz()->canEditTaskPlanningFields($task)) {
            if (! $this->authz()->canManage()) {
                unset($rules['project_id']);
            }
        } elseif ($task) {
            $rules = array_intersect_key($rules, array_flip([
                'title', 'description', 'status', 'estimated_hours',
            ]));
        }

        return $request->validate($rules, [], [
            'project_id' => 'projeto',
            'assignee_id' => 'responsável',
            'sla_deadline' => 'prazo de entrega',
        ]) + ['status' => $request->input('status', $task?->status ?? 'backlog')];
    }

    protected function createSubtasksFromRequest(Task $task, Request $request): void
    {
        $position = 0;
        foreach ($request->input('subtasks', []) as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $dueDate = ! empty($row['due_date']) ? Carbon::parse($row['due_date']) : null;

            $subtask = $task->subtasks()->create([
                'title' => $title,
                'assignee_id' => $row['assignee_id'] ?? null,
                'due_date' => $dueDate,
                'position' => $position++,
            ]);

            $this->historyService->log($task, 'subtask_created', 'subtask', null, $subtask->title);
        }
    }
}
