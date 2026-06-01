<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Concerns\RendersProjectTab;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Task;
use App\Rules\BelongsToCompany;
use App\Services\ProjectContractSyncService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProjectController extends Controller
{
    use InteractsWithCompany, RendersProjectTab;

    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();
        $authz = $this->authz();

        $query = Project::where('company_id', $company->id)->with('client:id,name');

        if (! $authz->hasFullDataScope('projects')) {
            $authz->applyProjectScope($query);
        }

        $projects = $query->latest()->paginate(15);

        $authz = $this->authz();

        $isMobile = $request->has('mobile') ||
            $request->cookie('is_mobile') === '1' ||
            (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));

        if ($isMobile) {
            return view('company.projects.index-mobile', compact('projects', 'company', 'authz'));
        }

        return view('company.projects.index', compact('projects', 'company', 'authz'));
    }

    public function create()
    {
        abort_unless($this->authz()->canManageProjects(), 403);

        $company = $this->getCurrentCompany();
        $contracts = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->with('client:id,name')
            ->orderBy('name')
            ->get();

        return view('company.projects.create', compact('company', 'contracts'));
    }

    public function store(Request $request)
    {
        abort_unless($this->authz()->canManageProjects(), 403);

        $company = $this->getCurrentCompany();

        $validated = $request->validate([
            'contract_id' => ['required', new BelongsToCompany('contracts', $company->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,paused,implementing,completed,cancelled,planning,in_progress',
            'deadline' => 'nullable|date',
            'scope' => 'nullable|string',
            'deliverables' => 'nullable|string',
        ]);

        $contract = Contract::where('company_id', $company->id)
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->findOrFail($validated['contract_id']);

        try {
            $fromContract = app(ProjectContractSyncService::class)->apply($contract);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['contract_id' => $e->getMessage()]);
        }

        $payload = array_merge($fromContract, [
            'company_id' => $company->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'implementing',
            'deadline' => $validated['deadline'] ?? null,
            'scope' => $validated['scope'] ?? null,
        ]);

        if (! empty($validated['deliverables'])) {
            $payload['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
        }

        Project::create($payload);

        return redirect()->route('company.projects.index')
            ->with('success', 'Projeto criado com sucesso!');
    }

    public function show(Project $project)
    {
        abort_unless($this->authz()->canViewProject($project), 403);

        if (! $this->authz()->canViewProjectOverview()) {
            return redirect()->route('company.projects.kanban', $project);
        }

        $company = $this->getCurrentCompany();
        $project->load([
            'client', 'contract', 'employees',
            'tasks' => fn ($q) => $q->select('id', 'project_id', 'status', 'sla_deadline'),
        ]);

        $openTasks = $project->tasks->where('status', '!=', 'completed')->count();
        $closedTasks = $project->tasks->where('status', 'completed')->count();
        $overdueTasks = $project->tasks->filter(fn ($t) => $t->status !== 'completed' && $t->isOverdue())->count();

        return $this->renderProjectTab('show', compact('project', 'company', 'openTasks', 'closedTasks', 'overdueTasks'));
    }

    public function edit(Project $project)
    {
        abort_unless($this->authz()->canManageProjects(), 403);

        $company = $this->getCurrentCompany();
        $contracts = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->with('client:id,name')
            ->orderBy('name')
            ->get();

        return view('company.projects.edit', compact('project', 'company', 'contracts'));
    }

    public function update(Request $request, Project $project)
    {
        abort_unless($this->authz()->canManageProjects(), 403);

        $company = $this->getCurrentCompany();

        $validated = $request->validate([
            'contract_id' => ['required', new BelongsToCompany('contracts', $company->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,paused,implementing,completed,cancelled,planning,in_progress',
            'deadline' => 'nullable|date',
            'scope' => 'nullable|string',
            'deliverables' => 'nullable|string',
        ]);

        $contract = Contract::where('company_id', $company->id)
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->findOrFail($validated['contract_id']);

        try {
            $fromContract = app(ProjectContractSyncService::class)->apply($contract);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['contract_id' => $e->getMessage()]);
        }

        $payload = array_merge($fromContract, [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? $project->status,
            'deadline' => $validated['deadline'] ?? null,
            'scope' => $validated['scope'] ?? null,
        ]);

        if (! empty($validated['deliverables'])) {
            $payload['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
        } else {
            $payload['deliverables'] = null;
        }

        $project->update($payload);

        return redirect()->route('company.projects.index')
            ->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project)
    {
        abort_unless($this->authz()->canManageProjects(), 403);

        $project->delete();

        return redirect()->route('company.projects.index')
            ->with('success', 'Projeto removido com sucesso!');
    }
}
