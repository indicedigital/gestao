<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    protected function getCurrentCompany(): Company
    {
        $user = Auth::user();
        
        // Super admin não pode acessar rotas de empresa
        if ($user->is_super_admin ?? false) {
            abort(403, 'Super administradores devem usar o painel administrativo.');
        }
        
        $companyId = session('current_company_id');
        if (!$companyId) {
            $company = $user->currentCompany();
            if ($company) {
                session(['current_company_id' => $company->id]);
                return $company;
            }
            abort(403, 'Você não possui uma empresa vinculada.');
        }
        return Company::findOrFail($companyId);
    }

    public function index()
    {
        $company = $this->getCurrentCompany();
        $projects = Project::where('company_id', $company->id)
            ->with('client')
            ->latest()
            ->paginate(15);
        
        return view('company.projects.index', compact('projects', 'company'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->get();
        $contracts = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->get();
        
        return view('company.projects.create', compact('company', 'clients', 'contracts'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fixed,recurring',
            'total_value' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1',
            'status' => 'nullable|in:planning,in_progress,paused,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'deadline' => 'nullable|date',
            'scope' => 'nullable|string',
            'deliverables' => 'nullable|array',
        ]);

        $validated['company_id'] = $company->id;
        if (!isset($validated['status'])) {
            $validated['status'] = 'planning';
        }
        if (isset($validated['deliverables'])) {
            $validated['deliverables'] = json_encode($validated['deliverables']);
        }

        Project::create($validated);

        return redirect()->route('company.projects.index')
            ->with('success', 'Projeto criado com sucesso!');
    }

    public function show(Project $project)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($project, $company);
        
        $project->load('client', 'contract', 'employees', 'costs', 'receivables');
        return view('company.projects.show', compact('project', 'company'));
    }

    public function edit(Project $project)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($project, $company);
        
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->get();
        $contracts = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereIn('type', ['client_recurring', 'client_fixed'])
            ->get();
        
        return view('company.projects.edit', compact('project', 'company', 'clients', 'contracts'));
    }

    public function update(Request $request, Project $project)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($project, $company);
        
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:fixed,recurring',
            'total_value' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1',
            'status' => 'nullable|in:planning,in_progress,paused,completed,cancelled',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'deadline' => 'nullable|date',
            'scope' => 'nullable|string',
            'deliverables' => 'nullable|array',
        ]);

        if (isset($validated['deliverables'])) {
            $validated['deliverables'] = json_encode($validated['deliverables']);
        }

        $project->update($validated);

        return redirect()->route('company.projects.index')
            ->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy(Project $project)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($project, $company);
        
        $project->delete();

        return redirect()->route('company.projects.index')
            ->with('success', 'Projeto removido com sucesso!');
    }

    protected function authorizeAccess(Project $project, Company $company): void
    {
        if ($project->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}
