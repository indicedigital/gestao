<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Company\Concerns\AuthorizesCompanyManagement;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    use AuthorizesCompanyManagement;

    protected function getCurrentCompany(): Company
    {
        $user = Auth::user();
        if ($user->is_super_admin ?? false) {
            abort(403, 'Super administradores devem usar o painel administrativo.');
        }

        $companyId = session('current_company_id');
        if (! $companyId) {
            $company = $user->currentCompany();
            if ($company) {
                session(['current_company_id' => $company->id]);

                return $company;
            }
            abort(403, 'Você não possui uma empresa vinculada.');
        }

        return Company::findOrFail($companyId);
    }

    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();

        $viewMode = $request->query('view', 'kanban');
        if (! in_array($viewMode, ['kanban', 'table'], true)) {
            $viewMode = 'kanban';
        }

        $tableLeads = Lead::query()
            ->where('company_id', $company->id)
            ->latest()
            ->paginate(15)
            ->appends(['view' => $viewMode]);

        $kanbanColumns = $this->kanbanColumns();
        $kanbanLeads = Lead::query()
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        foreach ($kanbanLeads as $lead) {
            $stageKey = $this->resolveKanbanStageKey((string) ($lead->project_stage ?? ''));
            $kanbanColumns[$stageKey]['leads']->push($lead);
        }

        return view('company.leads.index', compact('company', 'viewMode', 'tableLeads', 'kanbanColumns'));
    }

    public function create()
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();

        return view('company.leads.create', compact('company'));
    }

    public function store(Request $request)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $validated = $this->validateLead($request);
        $validated['company_id'] = $company->id;

        Lead::create($validated);

        return redirect()
            ->route('company.leads.index')
            ->with('success', 'Lead cadastrado com sucesso!');
    }

    public function edit(Lead $lead)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($lead, $company);

        return view('company.leads.edit', compact('company', 'lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($lead, $company);

        $validated = $this->validateLead($request);
        $lead->update($validated);

        return redirect()
            ->route('company.leads.index')
            ->with('success', 'Lead atualizado com sucesso!');
    }

    public function destroy(Lead $lead)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($lead, $company);
        $lead->delete();

        return redirect()
            ->route('company.leads.index')
            ->with('success', 'Lead removido com sucesso!');
    }

    protected function validateLead(Request $request): array
    {
        $validScopes = ['aplicativo', 'site', 'sistema', 'landing_page', 'automacao', 'outro'];
        $validPlatforms = ['android', 'iphone'];

        $validated = $request->validate([
            'meeting_date' => ['nullable', 'date'],
            'project_name' => ['required', 'string', 'max:255'],
            'brief_description' => ['nullable', 'string'],
            'project_scopes' => ['required', 'array', 'min:1'],
            'project_scopes.*' => ['required', Rule::in($validScopes)],
            'project_scope_other' => ['nullable', 'string', 'max:255'],
            'app_platforms' => ['nullable', 'array'],
            'app_platforms.*' => ['required', Rule::in($validPlatforms)],
            'project_kind' => ['required', Rule::in(['desenvolvimento', 'correcoes', 'melhorias'])],
            'project_stage' => ['nullable', 'string', 'max:255'],
            'is_online' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'has_domain' => ['nullable', 'boolean'],
            'domain_info' => ['nullable', 'string', 'max:255'],
            'has_server' => ['nullable', 'boolean'],
            'server_info' => ['nullable', 'string', 'max:255'],
            'expected_budget' => ['nullable', 'numeric', 'min:0'],
            'expected_deadline' => ['nullable', 'date'],
        ], [
            'project_scopes.required' => 'Selecione ao menos um tipo de projeto.',
            'project_scopes.min' => 'Selecione ao menos um tipo de projeto.',
        ], [
            'project_scopes' => 'tipos de projeto',
            'project_scope_other' => 'detalhe de outro tipo',
            'app_platforms' => 'plataformas do aplicativo',
            'expected_budget' => 'expectativa de orçamento',
            'expected_deadline' => 'prazo esperado',
        ]);

        $this->conditionalLeadValidation($request);

        $validated['is_online'] = $request->boolean('is_online');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['has_domain'] = $request->boolean('has_domain');
        $validated['has_server'] = $request->boolean('has_server');

        return $validated;
    }

    protected function conditionalLeadValidation(Request $request): array
    {
        $projectScopes = collect((array) $request->input('project_scopes', []))
            ->map(fn ($value) => (string) $value)
            ->values();

        $hasDomain = $request->boolean('has_domain');
        $hasServer = $request->boolean('has_server');

        $validator = validator(
            [
                'project_scope_other' => $request->input('project_scope_other'),
                'app_platforms' => $request->input('app_platforms'),
                'domain_info' => $request->input('domain_info'),
                'server_info' => $request->input('server_info'),
            ],
            [
                'project_scope_other' => $projectScopes->contains('outro') ? ['required', 'string', 'max:255'] : ['nullable'],
                'app_platforms' => $projectScopes->contains('aplicativo') ? ['required', 'array', 'min:1'] : ['nullable'],
                'domain_info' => $hasDomain ? ['required', 'string', 'max:255'] : ['nullable'],
                'server_info' => $hasServer ? ['required', 'string', 'max:255'] : ['nullable'],
            ],
            [
                'project_scope_other.required' => 'Informe o tipo de projeto quando selecionar "Outro".',
                'app_platforms.required' => 'Selecione ao menos uma plataforma quando o tipo for aplicativo.',
                'app_platforms.min' => 'Selecione ao menos uma plataforma quando o tipo for aplicativo.',
                'domain_info.required' => 'Informe os dados do domínio já existente.',
                'server_info.required' => 'Informe os dados do servidor já existente.',
            ]
        );

        $validator->validate();

        return [];
    }

    protected function authorizeAccess(Lead $lead, Company $company): void
    {
        if ((int) $lead->company_id !== (int) $company->id) {
            abort(403, 'Acesso negado.');
        }
    }

    protected function kanbanColumns(): array
    {
        return [
            'novo' => ['label' => 'Novo', 'leads' => collect()],
            'diagnostico' => ['label' => 'Diagnóstico', 'leads' => collect()],
            'proposta' => ['label' => 'Proposta', 'leads' => collect()],
            'negociacao' => ['label' => 'Negociação', 'leads' => collect()],
            'fechado' => ['label' => 'Fechado', 'leads' => collect()],
            'outros' => ['label' => 'Outros estágios', 'leads' => collect()],
        ];
    }

    protected function resolveKanbanStageKey(string $stage): string
    {
        $normalized = mb_strtolower(trim($stage));

        if ($normalized === '' || str_contains($normalized, 'novo')) {
            return 'novo';
        }
        if (str_contains($normalized, 'diagnost') || str_contains($normalized, 'reuni')) {
            return 'diagnostico';
        }
        if (str_contains($normalized, 'propost') || str_contains($normalized, 'orc')) {
            return 'proposta';
        }
        if (str_contains($normalized, 'negoci')) {
            return 'negociacao';
        }
        if (str_contains($normalized, 'fech') || str_contains($normalized, 'ganh') || str_contains($normalized, 'ativo')) {
            return 'fechado';
        }

        return 'outros';
    }
}

