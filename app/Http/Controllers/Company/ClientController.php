<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Exports\ClientsExport;
use App\Models\Client;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class ClientController extends Controller
{
    /**
     * Obtém a empresa atual do contexto
     */
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

    /**
     * Lista os clientes da empresa
     */
    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $query = Client::where('company_id', $company->id);
        
        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }
        
        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtro por cidade
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }
        
        // Ordenação
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginação
        $perPage = $request->get('per_page', 15);
        $clients = $query->paginate($perPage)->withQueryString();
        
        // Estatísticas
        $stats = [
            'total' => Client::where('company_id', $company->id)->count(),
            'active' => Client::where('company_id', $company->id)->where('status', 'active')->count(),
            'inactive' => Client::where('company_id', $company->id)->where('status', 'inactive')->count(),
            'blocked' => Client::where('company_id', $company->id)->where('status', 'blocked')->count(),
        ];
        
        // Detecta se é mobile
        $isMobile = $request->has('mobile') || 
                   $request->cookie('is_mobile') === '1' ||
                   (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));
        
        if ($isMobile) {
            return view('company.clients.index-mobile', compact('clients', 'company', 'stats'));
        }
        
        return view('company.clients.index', compact('clients', 'company', 'stats'));
    }
    
    /**
     * Exporta clientes para Excel
     */
    public function exportExcel(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $query = Client::where('company_id', $company->id);
        
        // Aplica os mesmos filtros da listagem
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }
        
        $clients = $query->get();
        
        return Excel::download(new ClientsExport($clients), 'clientes_' . date('Y-m-d_His') . '.xlsx');
    }
    
    /**
     * Exporta clientes para PDF
     */
    public function exportPdf(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $query = Client::where('company_id', $company->id);
        
        // Aplica os mesmos filtros da listagem
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }
        
        $clients = $query->get();
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        $html = view('company.clients.export-pdf', compact('clients', 'company'))->render();
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        return $dompdf->stream('clientes_' . date('Y-m-d_His') . '.pdf');
    }

    /**
     * Mostra o formulário de criação
     */
    public function create()
    {
        $company = $this->getCurrentCompany();
        return view('company.clients.create', compact('company'));
    }

    /**
     * Armazena um novo cliente
     */
    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'type' => 'required|in:pf,pj',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:cpf,cnpj',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive,blocked',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }
        if (!isset($validated['country'])) {
            $validated['country'] = 'Brasil';
        }

        Client::create($validated);

        return redirect()->route('company.clients.index')
            ->with('success', 'Cliente criado com sucesso!');
    }

    /**
     * Mostra um cliente específico
     */
    public function show(Request $request, Client $client)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($client, $company);
        
        // Filtros de período
        $year = $request->input('year', now()->year);
        $semester = $request->input('semester', null); // 1 ou 2
        
        // Carrega relacionamentos
        $client->load('projects', 'contracts', 'receivables');
        
        // Contratos ativos
        $activeContracts = $client->contracts()
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Projetos
        $projects = $client->projects()
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Faturamento - Total histórico
        $totalRevenue = $client->receivables()
            ->where('status', 'paid')
            ->sum('value');
        
        // Faturamento filtrado por período
        $receivablesQuery = $client->receivables();
        
        if ($semester) {
            if ($semester == 1) {
                $startDate = "{$year}-01-01";
                $endDate = "{$year}-06-30";
            } else {
                $startDate = "{$year}-07-01";
                $endDate = "{$year}-12-31";
            }
            $receivablesQuery->where(function($q) use ($startDate, $endDate) {
                $q->where(function($subQ) use ($startDate, $endDate) {
                    $subQ->where('status', 'paid')
                         ->whereBetween('paid_date', [$startDate, $endDate]);
                })->orWhere(function($subQ) use ($startDate, $endDate) {
                    $subQ->where('status', 'pending')
                         ->whereBetween('due_date', [$startDate, $endDate]);
                });
            });
        } else {
            $receivablesQuery->where(function($q) use ($year) {
                $q->where(function($subQ) use ($year) {
                    $subQ->where('status', 'paid')
                         ->whereYear('paid_date', $year);
                })->orWhere(function($subQ) use ($year) {
                    $subQ->where('status', 'pending')
                         ->whereYear('due_date', $year);
                });
            });
        }
        
        $receivables = $receivablesQuery->orderBy('due_date', 'desc')->get();
        
        // Faturamento realizado no período
        $revenueRealized = $client->receivables()
            ->where('status', 'paid');
        
        if ($semester) {
            if ($semester == 1) {
                $startDate = "{$year}-01-01";
                $endDate = "{$year}-06-30";
            } else {
                $startDate = "{$year}-07-01";
                $endDate = "{$year}-12-31";
            }
            $revenueRealized->whereBetween('paid_date', [$startDate, $endDate]);
        } else {
            $revenueRealized->whereYear('paid_date', $year);
        }
        
        $revenueRealized = $revenueRealized->sum('value');
        
        // Faturamento previsto no período
        $revenueForecast = $client->receivables()
            ->where('status', 'pending');
        
        if ($semester) {
            if ($semester == 1) {
                $startDate = "{$year}-01-01";
                $endDate = "{$year}-06-30";
            } else {
                $startDate = "{$year}-07-01";
                $endDate = "{$year}-12-31";
            }
            $revenueForecast->whereBetween('due_date', [$startDate, $endDate]);
        } else {
            $revenueForecast->whereYear('due_date', $year);
        }
        
        $revenueForecast = $revenueForecast->sum('value');
        
        // Contas vencidas
        $overdueReceivables = $client->receivables()
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();
        
        $overdueValue = $overdueReceivables->sum('value');
        
        // Histórico mensal (últimos 12 meses)
        $monthlyHistory = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $monthRevenue = $client->receivables()
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$monthStart, $monthEnd])
                ->sum('value');
            
            $monthlyHistory[] = [
                'month' => $date->locale('pt_BR')->translatedFormat('M/Y'),
                'revenue' => $monthRevenue,
            ];
        }
        
        // Estatísticas
        $stats = [
            'total_contracts' => $client->contracts()->count(),
            'active_contracts' => $activeContracts->count(),
            'total_projects' => $projects->count(),
            'active_projects' => $projects->where('status', 'in_progress')->count(),
            'total_revenue' => $totalRevenue,
            'overdue_count' => $overdueReceivables->count(),
            'overdue_value' => $overdueValue,
        ];
        
        return view('company.clients.show', compact(
            'client',
            'company',
            'activeContracts',
            'projects',
            'receivables',
            'totalRevenue',
            'revenueRealized',
            'revenueForecast',
            'overdueReceivables',
            'overdueValue',
            'monthlyHistory',
            'stats',
            'year',
            'semester'
        ));
    }

    /**
     * Mostra o formulário de edição
     */
    public function edit(Client $client)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($client, $company);
        
        return view('company.clients.edit', compact('client', 'company'));
    }

    /**
     * Atualiza um cliente
     */
    public function update(Request $request, Client $client)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($client, $company);
        
        $validated = $request->validate([
            'type' => 'required|in:pf,pj',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:cpf,cnpj',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive,blocked',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return redirect()->route('company.clients.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    /**
     * Remove um cliente
     */
    public function destroy(Client $client)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($client, $company);
        
        $client->delete();

        return redirect()->route('company.clients.index')
            ->with('success', 'Cliente removido com sucesso!');
    }

    /**
     * Verifica se o cliente pertence à empresa
     */
    protected function authorizeAccess(Client $client, Company $company): void
    {
        if ($client->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}
