<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Payable;
use App\Models\Project;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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

    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        // Filtro de mês (formato: Y-m, ex: 2026-01)
        $monthFilter = $request->input('month', now()->format('Y-m'));
        $selectedMonth = Carbon::createFromFormat('Y-m', $monthFilter)->startOfMonth();
        $selectedMonthEnd = $selectedMonth->copy()->endOfMonth();
        
        $now = $selectedMonth->copy();
        $currentMonth = $selectedMonth;
        $currentMonthEnd = $selectedMonthEnd;
        
        // Cache da folha salarial (calcula uma vez)
        $payrollCache = $this->getMonthlyPayrollCost($company, $now);
        
        // ========== 1. FINANCEIRO - VISÃO PRINCIPAL ==========
        
        // Faturamento do mês atual (realizado) - Otimizado com whereBetween
        $revenueRealized = Receivable::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereBetween('paid_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('value');
        
        // Previsão de faturamento do mês atual (contas a receber com vencimento no mês)
        $revenueForecast = Receivable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('value');
        
        $totalRevenueForecast = $revenueRealized + $revenueForecast;
        $revenueVariation = $totalRevenueForecast > 0 
            ? (($revenueRealized / $totalRevenueForecast) * 100) - 100 
            : 0;
        
        // Despesas do mês atual (realizadas) - Otimizado
        $expensesRealized = Payable::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereBetween('paid_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('value');
        
        // Previsão de despesas do mês atual - Otimizado
        $expensesForecast = Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('value');
        
        // Verifica se a folha já foi paga no mês atual
        $payrollPaidThisMonth = Payable::where('company_id', $company->id)
            ->where('type', 'salary')
            ->where('description', 'like', '%Folha de Pagamento%')
            ->where('status', 'paid')
            ->whereBetween('paid_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('value');
        
        // Se a folha não foi paga no mês atual, adiciona à previsão
        if ($payrollPaidThisMonth == 0 && $payrollCache > 0) {
            $expensesForecast += $payrollCache;
        }
        
        $totalExpensesForecast = $expensesRealized + $expensesForecast;
        
        // Principais categorias de despesas - Otimizado
        $expensesByCategory = Payable::where('company_id', $company->id)
            ->where(function($query) use ($now) {
                $query->where(function($q) use ($now) {
                    $q->where('status', 'paid')
                      ->whereBetween('paid_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                })->orWhere(function($q) use ($now) {
                    $q->where('status', 'pending')
                      ->whereBetween('due_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                });
            })
            ->select('type', DB::raw('SUM(value) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();
        
        // Adiciona a folha salarial aos indicadores de despesa
        if ($payrollCache > 0) {
            $expensesByCategory['Folha Salarial'] = $payrollCache;
        }
        
        // Lucro do mês atual
        $profitRealized = $revenueRealized - $expensesRealized;
        $profitForecast = $totalRevenueForecast - $totalExpensesForecast;
        $profitMargin = $revenueRealized > 0 ? ($profitRealized / $revenueRealized) * 100 : 0;
        $profitMarginForecast = $totalRevenueForecast > 0 ? ($profitForecast / $totalRevenueForecast) * 100 : 0;
        
        // ========== 2. PROJEÇÕES (PRÓXIMOS 3 MESES) ==========
        $projections = [];
        for ($i = 1; $i <= 3; $i++) {
            $month = $selectedMonth->copy()->addMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            // Faturamento previsto - Otimizado
            $projRevenue = Receivable::where('company_id', $company->id)
                ->where('status', 'pending')
                ->whereBetween('due_date', [$monthStart, $monthEnd])
                ->sum('value');
            
            // Despesa prevista - Otimizado
            $projExpenses = Payable::where('company_id', $company->id)
                ->where('status', 'pending')
                ->whereBetween('due_date', [$monthStart, $monthEnd])
                ->sum('value');
            
            // Adiciona folha salarial prevista (usa cache para evitar query repetida)
            $projExpenses += $payrollCache;
            
            $projProfit = $projRevenue - $projExpenses;
            $projMargin = $projRevenue > 0 ? ($projProfit / $projRevenue) * 100 : 0;
            
            $projections[] = [
                'month' => $month->locale('pt_BR')->translatedFormat('F \d\e Y'),
                'month_short' => $month->format('M/Y'),
                'revenue' => $projRevenue,
                'expenses' => $projExpenses,
                'profit' => $projProfit,
                'margin' => $projMargin,
            ];
        }
        
        // ========== 3. CONTAS A PAGAR E RECEBER ==========
        
        // Próximos vencimentos (7, 15, 30 dias) - Otimizado com limit
        $upcomingReceivables7 = Receivable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->with('client:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        $upcomingReceivables15 = Receivable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->addDays(7), now()->addDays(15)])
            ->with('client:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        $upcomingReceivables30 = Receivable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->addDays(15), now()->addDays(30)])
            ->with('client:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        $upcomingPayables7 = Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->with('employee:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        $upcomingPayables15 = Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->addDays(7), now()->addDays(15)])
            ->with('employee:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        $upcomingPayables30 = Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->addDays(15), now()->addDays(30)])
            ->with('employee:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
        
        // Contas vencidas (incluindo parciais) - Otimizado
        $overdueReceivables = Receivable::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now())
            ->with('client:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($receivable) {
                $receivable->overdue_value = $receivable->status === 'partial' 
                    ? ($receivable->value - ($receivable->paid_value ?? 0))
                    : $receivable->value;
                return $receivable;
            });
        
        $overduePayables = Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->with('employee:id,name')
            ->orderBy('due_date', 'asc')
            ->limit(50)
            ->get();
        
        // Soma dos valores vencidos (considerando parciais)
        $totalOverdueReceivables = $overdueReceivables->sum('overdue_value');
        $totalOverduePayables = $overduePayables->sum('value');
        $countOverdueReceivables = $overdueReceivables->count();
        $countOverduePayables = $overduePayables->count();
        
        // Maior atraso (em dias) - Otimizado
        $maxOverdueDays = 0;
        if ($overdueReceivables->isNotEmpty()) {
            $oldestReceivable = $overdueReceivables->first();
            $maxOverdueDays = (int) floor(now()->diffInDays($oldestReceivable->due_date, false));
        }
        if ($overduePayables->isNotEmpty()) {
            $oldestPayable = $overduePayables->first();
            $days = (int) floor(now()->diffInDays($oldestPayable->due_date, false));
            if ($days > $maxOverdueDays) {
                $maxOverdueDays = $days;
            }
        }
        
        // ========== 4. INDICADORES OPERACIONAIS ==========
        
        // Clientes - Otimizado com select
        $totalClients = Client::where('company_id', $company->id)->count();
        $activeClients = Client::where('company_id', $company->id)->where('status', 'active')->count();
        $overdueClients = Client::where('company_id', $company->id)
            ->whereHas('receivables', function($query) {
                $query->where('status', 'pending')
                      ->where('due_date', '<', now());
            })
            ->count();
        
        // Contratos - Otimizado
        $totalContracts = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->count();
        
        $contractsExpiring30 = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->count();
        
        $contractsExpiring60 = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->addDays(30), now()->addDays(60)])
            ->count();
        
        $contractsExpiring90 = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->addDays(60), now()->addDays(90)])
            ->count();
        
        // Ticket médio - Otimizado
        $avgTicketPerClient = $activeClients > 0 
            ? $revenueRealized / $activeClients
            : 0;
        
        $avgTicketPerContract = $totalContracts > 0
            ? Contract::where('company_id', $company->id)
                ->where('status', 'active')
                ->avg('value')
            : 0;
        
        // Equipe - Otimizado
        $totalEmployees = Employee::where('company_id', $company->id)->count();
        $cltEmployees = Employee::where('company_id', $company->id)
            ->where('type', 'clt')
            ->where('status', 'active')
            ->count();
        $pjEmployees = Employee::where('company_id', $company->id)
            ->where('type', 'pj')
            ->where('status', 'active')
            ->count();
        
        $monthlyPersonnelCost = $payrollCache;
        $avgCostPerEmployee = $totalEmployees > 0 ? $monthlyPersonnelCost / $totalEmployees : 0;
        
        // ========== 5. KPIS EXTRAS ==========
        
        // Burn rate (despesa mensal média)
        $burnRate = $expensesRealized;
        
        // Caixa disponível (receitas - despesas do mês)
        $availableCash = $revenueRealized - $expensesRealized;
        
        // Meses de fôlego financeiro (simplificado - usando caixa atual / despesa mensal)
        $monthsOfRunway = $burnRate > 0 ? ($availableCash / $burnRate) : 0;
        
        // ========== 6. GRÁFICOS ==========
        
        // Histórico financeiro (últimos 6 meses + próximos 3 meses)
        $financialHistory = $this->getFinancialHistory($company, 6);
        
        // Adiciona projeções ao histórico
        foreach ($projections as $proj) {
            $financialHistory[] = [
                'month' => $proj['month_short'],
                'revenue' => $proj['revenue'],
                'expenses' => $proj['expenses'],
                'profit' => $proj['profit'],
            ];
        }
        
        // Contratos a vencer - Otimizado com limit
        $contractsExpiringList = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays(90)])
            ->with('client:id,name')
            ->orderBy('end_date', 'asc')
            ->limit(20)
            ->get();
        
        // Despesas por categoria no mês - OTIMIZADO: Remove N+1 query
        $expensesByCategoryChart = $this->getExpensesByCategoryChart($company, $now, $payrollCache);
        
        // Detecta se é mobile
        $isMobile = $this->isMobile($request);
        
        $view = $isMobile ? 'company.dashboard-mobile' : 'company.dashboard';
        
        return view($view, compact(
            'company',
            'selectedMonth',
            'monthFilter',
            'revenueRealized',
            'revenueForecast',
            'totalRevenueForecast',
            'revenueVariation',
            'expensesRealized',
            'expensesForecast',
            'totalExpensesForecast',
            'expensesByCategory',
            'profitRealized',
            'profitForecast',
            'profitMargin',
            'profitMarginForecast',
            'projections',
            'upcomingReceivables7',
            'upcomingReceivables15',
            'upcomingReceivables30',
            'upcomingPayables7',
            'upcomingPayables15',
            'upcomingPayables30',
            'overdueReceivables',
            'overduePayables',
            'totalOverdueReceivables',
            'totalOverduePayables',
            'countOverdueReceivables',
            'countOverduePayables',
            'maxOverdueDays',
            'totalClients',
            'activeClients',
            'overdueClients',
            'totalContracts',
            'contractsExpiring30',
            'contractsExpiring60',
            'contractsExpiring90',
            'avgTicketPerClient',
            'avgTicketPerContract',
            'totalEmployees',
            'cltEmployees',
            'pjEmployees',
            'monthlyPersonnelCost',
            'avgCostPerEmployee',
            'burnRate',
            'availableCash',
            'monthsOfRunway',
            'financialHistory',
            'contractsExpiringList',
            'expensesByCategoryChart'
        ));
    }

    /**
     * Calcula o custo da folha salarial do mês
     */
    protected function getMonthlyPayrollCost(Company $company, Carbon $month): float
    {
        // Tenta buscar da payable primeiro (mais rápido)
        $payrollPayable = Payable::where('company_id', $company->id)
            ->where('type', 'salary')
            ->where('description', 'like', '%Folha de Pagamento%')
            ->where(function($query) use ($month) {
                $query->where(function($q) use ($month) {
                    $q->where('status', 'paid')
                      ->whereBetween('paid_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                })->orWhere(function($q) use ($month) {
                    $q->where('status', 'pending')
                      ->whereBetween('due_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                });
            })
            ->first();
        
        if ($payrollPayable) {
            return (float) $payrollPayable->value;
        }
        
        // Calcula diretamente dos funcionários ativos
        return (float) Employee::where('company_id', $company->id)
            ->whereIn('type', ['clt', 'pj'])
            ->where('status', 'active')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->sum('salary');
    }

    /**
     * Obtém despesas por categoria otimizado (evita N+1)
     */
    protected function getExpensesByCategoryChart(Company $company, Carbon $month, float $payrollValue): \Illuminate\Support\Collection
    {
        // Busca payables do mês que correspondem a despesas
        $payablesFromExpenses = Payable::where('company_id', $company->id)
            ->where(function($query) use ($month) {
                $query->where(function($q) use ($month) {
                    $q->where('status', 'paid')
                      ->whereBetween('paid_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                })->orWhere(function($q) use ($month) {
                    $q->where('status', 'pending')
                      ->whereBetween('due_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                });
            })
            ->where(function($q) {
                $q->where('category', 'recurring')
                  ->orWhere(function($subQ) {
                      $subQ->where('type', 'service')
                           ->where('category', 'other');
                  });
            })
            ->select('id', 'description', 'value')
            ->get();
        
        if ($payablesFromExpenses->isEmpty()) {
            $expensesByCategoryChart = collect();
        } else {
            // Busca TODAS as expenses de uma vez (evita N+1)
            $descriptions = $payablesFromExpenses->pluck('description')->unique()->toArray();
            $expenses = Expense::where('company_id', $company->id)
                ->whereIn('description', $descriptions)
                ->with('category:id,name,color')
                ->get()
                ->keyBy('description');
            
            // Agrupa por categoria
            $expensesByCategoryChart = collect();
            foreach ($payablesFromExpenses as $payable) {
                $expense = $expenses->get($payable->description);
                
                if ($expense && $expense->category) {
                    $categoryName = $expense->category->name;
                    $categoryColor = $expense->category->color ?? '#5e72e4';
                    
                    $existing = $expensesByCategoryChart->firstWhere('label', $categoryName);
                    
                    if ($existing) {
                        $existing['value'] += (float) $payable->value;
                    } else {
                        $expensesByCategoryChart->push([
                            'label' => $categoryName,
                            'value' => (float) $payable->value,
                            'color' => $categoryColor,
                        ]);
                    }
                }
            }
        }
        
        // Adiciona a folha salarial ao gráfico
        if ($payrollValue > 0) {
            $expensesByCategoryChart->push([
                'label' => 'Folha Salarial',
                'value' => $payrollValue,
                'color' => '#f5365c',
            ]);
        }
        
        // Ordena por valor decrescente
        return $expensesByCategoryChart->sortByDesc('value')->values();
    }

    protected function getFinancialHistory(Company $company, int $months = 6): array
    {
        $history = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            $monthName = $date->format('M/Y');
            
            $receivables = Receivable::where('company_id', $company->id)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$monthStart, $monthEnd])
                ->sum('value');
            
            $payables = Payable::where('company_id', $company->id)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$monthStart, $monthEnd])
                ->sum('value');
            
            $history[] = [
                'month' => $monthName,
                'revenue' => $receivables,
                'expenses' => $payables,
                'profit' => $receivables - $payables,
            ];
        }
        
        return $history;
    }
    
    /**
     * Detecta se a requisição é de um dispositivo mobile
     */
    protected function isMobile(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');
        
        // Verifica se é mobile baseado no User-Agent
        $mobileAgents = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 
            'BlackBerry', 'Windows Phone', 'Opera Mini'
        ];
        
        foreach ($mobileAgents as $agent) {
            if (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }
        
        // Verifica se há parâmetro específico para forçar mobile
        if ($request->has('mobile') && $request->input('mobile') === '1') {
            return true;
        }
        
        // Verifica largura da tela via cookie (se disponível)
        if ($request->hasCookie('is_mobile')) {
            return $request->cookie('is_mobile') === '1';
        }
        
        return false;
    }
}
