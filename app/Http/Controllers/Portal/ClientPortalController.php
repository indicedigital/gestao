<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\Client;
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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientPortalController extends Controller
{
    use InteractsWithCompany;

    public function __construct(
        protected SlaService $slaService,
        protected TaskHistoryService $historyService,
        protected TaskWorkflowService $workflowService,
        protected MentionParserService $mentionParser
    ) {}

    public function dashboard()
    {
        $company = $this->getCurrentCompany();
        $clientId = $this->authz()->clientId();
        $client = Client::where('company_id', $company->id)->find($clientId);

        $projects = Project::where('company_id', $company->id)
            ->where('client_id', $clientId)
            ->withCount([
                'tasks as open_tasks' => fn ($q) => $q->where('status', '!=', 'completed'),
                'tasks as completed_tasks' => fn ($q) => $q->where('status', 'completed'),
                'tasks as total_tasks',
                'tasks as homologation_tasks' => fn ($q) => $q->where('status', 'homologation'),
            ])
            ->orderBy('name')
            ->get();

        $taskBase = Task::where('company_id', $company->id)
            ->whereHas('project', fn ($q) => $q->where('client_id', $clientId));

        $stats = (clone $taskBase)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status != 'completed' THEN 1 ELSE 0 END) as open_count,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN status = 'homologation' THEN 1 ELSE 0 END) as homologation_count,
            SUM(CASE WHEN status = 'waiting_client' THEN 1 ELSE 0 END) as waiting_client_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
            SUM(CASE WHEN status != 'completed' AND sla_deadline IS NOT NULL AND sla_deadline < NOW() THEN 1 ELSE 0 END) as overdue_count
        ")->first();

        $byStatus = (clone $taskBase)
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $recentTasks = (clone $taskBase)
            ->with('project:id,name')
            ->latest()
            ->limit(8)
            ->get();

        $homologationTasks = (clone $taskBase)
            ->where('status', 'homologation')
            ->with('project:id,name')
            ->latest()
            ->get();

        $waitingTasks = (clone $taskBase)
            ->where('status', 'waiting_client')
            ->with('project:id,name')
            ->latest()
            ->limit(5)
            ->get();

        $totalTasks = (int) ($stats->total ?? 0);
        $completedCount = (int) ($stats->completed_count ?? 0);
        $completionRate = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;

        return view('portal.dashboard', compact(
            'company', 'client', 'projects', 'recentTasks', 'homologationTasks', 'waitingTasks',
            'stats', 'byStatus', 'completionRate'
        ));
    }

    public function kanban(Project $project)
    {
        abort_unless($this->authz()->canViewProject($project), 403);

        $project->load('client:id,name');
        $tasks = Task::where('project_id', $project->id)
            ->with(['assignee:id,name', 'subtasks:id,task_id,title,status'])
            ->withCount(['comments', 'attachments', 'subtasks'])
            ->orderBy('position')
            ->get();

        $columns = [];
        foreach (Task::STATUSES as $key => $label) {
            $columns[$key] = ['key' => $key, 'label' => $label, 'tasks' => $tasks->where('status', $key)->values()];
        }

        return view('portal.kanban', compact('project', 'columns'));
    }

    public function createTask()
    {
        $company = $this->getCurrentCompany();
        $clientId = $this->authz()->clientId();

        $projects = Project::where('company_id', $company->id)
            ->where('client_id', $clientId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('portal.tasks.create', compact('company', 'projects'));
    }

    public function storeTask(Request $request)
    {
        $company = $this->getCurrentCompany();
        $clientId = $this->authz()->clientId();

        $validated = $request->validate([
            'project_id' => ['required', new BelongsToCompany('projects', $company->id)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(array_keys(Task::PRIORITIES))],
            'category' => ['required', Rule::in(array_keys(Task::CATEGORIES))],
        ]);

        $project = Project::where('company_id', $company->id)
            ->where('client_id', $clientId)
            ->findOrFail($validated['project_id']);

        $task = Task::create([
            ...$validated,
            'company_id' => $company->id,
            'created_by' => Auth::id(),
            'creation_channel' => 'client',
            'requester_type' => 'client',
            'requester_name' => Auth::user()->name,
            'status' => 'backlog',
        ]);

        $this->slaService->applyToTask($task);
        $this->historyService->log($task, 'created');

        return redirect()->route('portal.tasks.show', $task)->with('success', 'Solicitação enviada com sucesso!');
    }

    public function showTask(Task $task)
    {
        abort_unless($this->authz()->canViewTask($task), 403);

        $task->load(['project', 'assignee', 'publicComments.user', 'attachments', 'subtasks']);

        return view('portal.tasks.show', compact('task'));
    }

    public function storeComment(Request $request, Task $task)
    {
        abort_unless($this->authz()->canViewTask($task), 403);

        $company = $this->getCurrentCompany();
        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $parsed = $this->mentionParser->parse($validated['body'], $company->id);

        $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $parsed['body'],
            'mentions' => $parsed['mentions'],
        ]);

        return back()->with('success', 'Comentário enviado.');
    }

    public function storeAttachment(Request $request, Task $task)
    {
        abort_unless($this->authz()->canViewTask($task), 403);

        $request->validate([
            'attachment' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,zip,txt'],
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

        return back()->with('success', 'Anexo enviado.');
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

    public function approveHomologation(Task $task)
    {
        abort_unless($this->authz()->canApproveHomologation($task), 403);

        $this->workflowService->transition($task, 'completed');

        return back()->with('success', 'Homologação aprovada! Task concluída.');
    }
}
