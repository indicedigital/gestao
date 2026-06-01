<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Concerns\RendersProjectTab;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Rules\BelongsToCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectKanbanController extends Controller
{
    use InteractsWithCompany, RendersProjectTab;

    public function show(Request $request, Project $project)
    {
        $company = $this->getCurrentCompany();
        abort_unless($this->authz()->canViewProject($project), 403);

        $project->load(['client:id,name', 'contract:id,name', 'employees:id,name']);

        $query = Task::where('project_id', $project->id)
            ->with(['assignee:id,name', 'subtasks:id,task_id,title,status'])
            ->withCount('comments');

        if (! $this->authz()->hasFullDataScope('tasks')) {
            $this->authz()->applyTaskScope($query);
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
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

        $tasks = $query->orderBy('position')->get();
        $kanbanColumns = $this->buildKanbanColumns($tasks);
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $compactView = $request->boolean('compact', false);
        $showSubtasks = $request->boolean('subtasks', true);
        $canMove = ! $this->authz()->isClient() && (
            $this->authz()->canManage() ||
            in_array($this->authz()->role(), ['user', 'freelancer'], true)
        );

        return $this->renderProjectTab('kanban', compact(
            'company', 'project', 'kanbanColumns', 'employees', 'compactView', 'showSubtasks', 'canMove'
        ));
    }

    public function dashboard(Project $project)
    {
        $company = $this->getCurrentCompany();
        abort_unless($this->authz()->canViewProject($project), 403);
        abort_unless($this->authz()->canViewProjectDashboard(), 403);

        $project->load(['client:id,name', 'contract:id,name']);

        $stats = Task::where('project_id', $project->id)
            ->selectRaw("
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status != 'completed' THEN 1 ELSE 0 END) as open_tasks,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as closed_tasks,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
                SUM(CASE WHEN status IN ('waiting_client', 'homologation') THEN 1 ELSE 0 END) as waiting_client_tasks,
                SUM(CASE WHEN status != 'completed' AND sla_deadline IS NOT NULL AND sla_deadline < NOW() THEN 1 ELSE 0 END) as overdue_tasks,
                SUM(CASE WHEN status != 'completed' AND sla_deadline IS NOT NULL AND sla_deadline >= NOW() THEN 1 ELSE 0 END) as on_time_tasks
            ")
            ->first();

        $avgDeliveryHours = Task::where('project_id', $project->id)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
            ->value('avg_hours');

        $hoursUsed = (float) DB::table('dailies')->where('project_id', $project->id)->sum('hours');
        $allocatedHours = (float) $project->employees()->sum('project_employees.allocated_hours');

        $byCategory = Task::where('project_id', $project->id)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $byPriority = Task::where('project_id', $project->id)
            ->where('status', '!=', 'completed')
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $byStatus = Task::where('project_id', $project->id)
            ->where('status', '!=', 'completed')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $teamCount = $project->employees()->count();

        $monthStart = now()->startOfMonth();
        $burnDown = Task::where('project_id', $project->id)
            ->where('created_at', '>=', $monthStart)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as opened')
            ->groupBy('day')->orderBy('day')->get();

        $burnDownClosed = Task::where('project_id', $project->id)
            ->where('status', 'completed')
            ->where('completed_at', '>=', $monthStart)
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as closed')
            ->groupBy('day')->orderBy('day')->get();

        $openTasks = (int) ($stats->open_tasks ?? 0);
        $closedTasks = (int) ($stats->closed_tasks ?? 0);
        $overdueTasks = (int) ($stats->overdue_tasks ?? 0);
        $totalTasks = (int) ($stats->total_tasks ?? 0);
        $inProgressTasks = (int) ($stats->in_progress_tasks ?? 0);
        $waitingClientTasks = (int) ($stats->waiting_client_tasks ?? 0);
        $onTimeTasks = (int) ($stats->on_time_tasks ?? 0);
        $completionRate = $totalTasks > 0 ? round(($closedTasks / $totalTasks) * 100, 1) : 0;
        $hoursRemaining = max(0, $allocatedHours - $hoursUsed);
        $hoursUsagePct = $allocatedHours > 0 ? min(100, round(($hoursUsed / $allocatedHours) * 100, 1)) : 0;
        $slaComplianceRate = ($onTimeTasks + $overdueTasks) > 0
            ? round(($onTimeTasks / ($onTimeTasks + $overdueTasks)) * 100, 1)
            : 100;

        return $this->renderProjectTab('dashboard', compact(
            'company', 'project', 'openTasks', 'closedTasks', 'overdueTasks', 'totalTasks',
            'inProgressTasks', 'waitingClientTasks', 'onTimeTasks', 'completionRate',
            'avgDeliveryHours', 'hoursUsed', 'allocatedHours', 'hoursRemaining', 'hoursUsagePct',
            'slaComplianceRate', 'teamCount', 'byCategory', 'byPriority', 'byStatus',
            'burnDown', 'burnDownClosed'
        ));
    }

    public function team(Project $project)
    {
        abort_unless($this->authz()->canViewProject($project), 403);

        $company = $this->getCurrentCompany();
        $project->load('employees:id,name');
        $employees = Employee::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $canEdit = $this->authz()->canManageTeam();
        $canViewFinancial = $this->authz()->canViewProjectFinancial();

        return $this->renderProjectTab('team', compact('company', 'project', 'employees', 'canEdit', 'canViewFinancial'));
    }

    public function updateTeam(Request $request, Project $project)
    {
        abort_unless($this->authz()->canManageTeam(), 403);
        abort_unless($this->authz()->canViewProject($project), 403);

        $company = $this->getCurrentCompany();
        $validated = $request->validate([
            'employees' => ['nullable', 'array'],
            'employees.*.employee_id' => ['required', new BelongsToCompany('employees', $company->id)],
            'employees.*.role' => ['nullable', 'string', 'max:100'],
            'employees.*.hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'employees.*.allocated_hours' => ['nullable', 'integer', 'min:0'],
        ]);

        $sync = [];
        $canViewFinancial = $this->authz()->canViewProjectFinancial();
        $existingRates = $canViewFinancial ? [] : $project->employees()->pluck('project_employees.hourly_rate', 'employees.id');

        foreach ($validated['employees'] ?? [] as $row) {
            $sync[$row['employee_id']] = [
                'role' => $row['role'] ?? null,
                'hourly_rate' => $canViewFinancial
                    ? ($row['hourly_rate'] ?? null)
                    : ($existingRates[$row['employee_id']] ?? null),
                'allocated_hours' => $row['allocated_hours'] ?? null,
                'is_active' => true,
            ];
        }

        $project->employees()->sync($sync);

        return redirect()->route('company.projects.team', $project)
            ->with('success', 'Time do projeto atualizado!');
    }

    protected function buildKanbanColumns($tasks): array
    {
        $columns = [];
        foreach (Task::STATUSES as $key => $label) {
            $columns[$key] = ['key' => $key, 'label' => $label, 'tasks' => collect()];
        }
        foreach ($tasks as $task) {
            $key = array_key_exists($task->status, $columns) ? $task->status : 'backlog';
            $columns[$key]['tasks']->push($task);
        }

        return $columns;
    }
}
