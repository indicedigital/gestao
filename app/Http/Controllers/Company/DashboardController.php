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
use App\Models\ReceivablePayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        
        // Faturamento do mês atual (realizado) - Soma pelos pagamentos (datas corretas de recebimento)
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $revenueRealized = $this->getRevenueRealizedInPeriod($company->id, $monthStart, $monthEnd);
        
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
        
        // Previsão de despesas do mês = só contas a pagar pendentes com vencimento no mês
        // (a folha de pagamento já é uma conta a pagar; não somar folha estimada em cima para evitar duplicata)
        $expensesForecast = Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('value');

        $expensesForecastPayablesOnly = $expensesForecast;
        $expensesForecastPayrollAdded = 0;

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
            // Sempre partir do dia 1: addMonths a partir de 29–31 pode “pular” fevereiro ou duplicar março.
            $month = $selectedMonth->copy()->startOfMonth()->addMonths($i);
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

        $mrrContractsQuery = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->where('type', 'client_recurring')
            ->where('billing_period', 'monthly');
        $mrrActiveContractsCount = (clone $mrrContractsQuery)->count();
        $mrrValue = (float) (clone $mrrContractsQuery)->sum('value');
        
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
        
        // Fluxo líquido mensal (entradas - saídas)
        $cashInflows = (float) $revenueRealized;
        $cashOutflows = (float) $expensesRealized;
        $netCashFlow = $cashInflows - $cashOutflows;

        // Burn Rate (queima mensal quando saídas > entradas)
        $burnRate = max(0, $cashOutflows - $cashInflows);

        // Caixa disponível editável (fallback para cálculo antigo se ainda não configurado)
        $calculatedCashFallback = $cashInflows - $cashOutflows;
        $availableCash = $company->current_cash_balance !== null
            ? (float) $company->current_cash_balance
            : $calculatedCashFallback;
        
        // Runway (fôlego de caixa)
        $monthsOfRunway = $burnRate > 0 ? ($availableCash / $burnRate) : 0;
        
        // ========== 6. GRÁFICOS ==========
        
        // Histórico financeiro (últimos 6 meses + próximos 3 meses)
        $financialHistory = $this->getFinancialHistory($company, $selectedMonth, 6);
        $financialHistoryRealized = $financialHistory;

        $avgInflows = (float) collect($financialHistoryRealized)->avg('revenue');
        $avgOutflows = (float) collect($financialHistoryRealized)->avg('expenses');
        $dailyResult = (float) ($selectedMonth->daysInMonth > 0 ? ($netCashFlow / $selectedMonth->daysInMonth) : 0);
        $weeklyResult = (float) ($netCashFlow / 4.345);
        $monthlyResult = (float) $netCashFlow;

        // Obrigações de curto prazo: pendências para os próximos 30 dias
        $shortTermObligations = (float) Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->sum('value');

        $liquidityIndex = $shortTermObligations > 0 ? ($availableCash / $shortTermObligations) : null;
        $cashCommitmentPercent = $availableCash > 0 ? (($shortTermObligations / $availableCash) * 100) : null;

        // Evolução de caixa (reconstruída em retrocesso a partir do caixa atual e dos fluxos mensais)
        $cashEvolution = [];
        if (count($financialHistoryRealized) > 0) {
            $balances = array_fill(0, count($financialHistoryRealized), 0.0);
            $lastIdx = count($financialHistoryRealized) - 1;
            $balances[$lastIdx] = (float) $availableCash;

            for ($i = $lastIdx - 1; $i >= 0; $i--) {
                $nextMonthNet = (float) $financialHistoryRealized[$i + 1]['profit'];
                $balances[$i] = $balances[$i + 1] - $nextMonthNet;
            }

            foreach ($financialHistoryRealized as $idx => $item) {
                $cashEvolution[] = [
                    'month' => $item['month'],
                    'balance' => round((float) $balances[$idx], 2),
                    'inflows' => round((float) $item['revenue'], 2),
                    'outflows' => round((float) $item['expenses'], 2),
                    'net' => round((float) $item['profit'], 2),
                ];
            }
        }
        
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
            'expensesForecastPayablesOnly',
            'expensesForecastPayrollAdded',
            'payrollCache',
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
            'mrrActiveContractsCount',
            'mrrValue',
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
            'cashInflows',
            'cashOutflows',
            'netCashFlow',
            'avgInflows',
            'avgOutflows',
            'dailyResult',
            'weeklyResult',
            'monthlyResult',
            'shortTermObligations',
            'liquidityIndex',
            'cashCommitmentPercent',
            'cashEvolution',
            'financialHistory',
            'contractsExpiringList',
            'expensesByCategoryChart'
        ));
    }

    public function updateCash(Request $request)
    {
        $company = $this->getCurrentCompany();
        abort_unless(app(\App\Services\CompanyAuthorizationService::class)->canManage(), 403, 'Sem permissão para alterar o saldo de caixa.');

        $validated = $request->validate([
            'current_cash_balance' => ['required', 'numeric', 'min:0'],
            'month' => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $company->current_cash_balance = (float) $validated['current_cash_balance'];
        $company->save();

        $query = [];
        if (! empty($validated['month'])) {
            $query['month'] = $validated['month'];
        }

        return redirect()
            ->route('company.dashboard', $query)
            ->with('success', 'Caixa atualizado com sucesso.');
    }

    public function cashReportData(Request $request): JsonResponse
    {
        $company = $this->getCurrentCompany();

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $endDate = ! empty($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : now()->endOfDay();
        $startDate = ! empty($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : $endDate->copy()->subDays(89)->startOfDay();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $currentCash = (float) ($company->current_cash_balance ?? 0);
        $dailyRows = $this->aggregateDailyCashFlow($company->id, $startDate, $endDate);

        // Ajuste do caixa final para períodos no passado (aproxima histórico a partir do saldo atual)
        $endingCashAtEndDate = $currentCash;
        if ($endDate->lt(now()->startOfDay())) {
            $afterRows = $this->aggregateDailyCashFlow($company->id, $endDate->copy()->addDay()->startOfDay(), now()->endOfDay());
            $netAfterEnd = collect($afterRows)->sum(fn ($r) => (float) $r['net']);
            $endingCashAtEndDate = $currentCash - $netAfterEnd;
        }

        $totalNet = collect($dailyRows)->sum(fn ($r) => (float) $r['net']);
        $startingCash = $endingCashAtEndDate - $totalNet;

        $evolution = [];
        $running = $startingCash;
        foreach ($dailyRows as $row) {
            $running += (float) $row['net'];
            $evolution[] = [
                'date' => $row['date'],
                'label' => Carbon::parse($row['date'])->format('d/m'),
                'inflows' => round((float) $row['inflows'], 2),
                'outflows' => round((float) $row['outflows'], 2),
                'net' => round((float) $row['net'], 2),
                'balance' => round((float) $running, 2),
            ];
        }

        $totalInflows = (float) collect($dailyRows)->sum(fn ($r) => (float) $r['inflows']);
        $totalOutflows = (float) collect($dailyRows)->sum(fn ($r) => (float) $r['outflows']);
        $netCashFlow = $totalInflows - $totalOutflows;
        $burnRate = max(0, $totalOutflows - $totalInflows);
        $periodDays = max(1, $startDate->diffInDays($endDate) + 1);
        $avgInflows = $totalInflows / $periodDays;
        $avgOutflows = $totalOutflows / $periodDays;

        $dailyResult = $netCashFlow / $periodDays;
        $weeklyResult = $dailyResult * 7;
        $monthlyResult = $dailyResult * 30;

        $shortTermObligations = (float) Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->sum('value');

        $liquidityIndex = $shortTermObligations > 0 ? ($currentCash / $shortTermObligations) : null;
        $cashCommitmentPercent = $currentCash > 0 ? (($shortTermObligations / $currentCash) * 100) : null;
        $runwayMonths = $burnRate > 0 ? ($currentCash / $burnRate) : null;

        // Previsões de caixa (15, 30 e 60 dias)
        $forecastStart = now()->addDay()->startOfDay();
        $forecastByDays = [];
        $forecastEvolution15 = [];
        foreach ([15, 30, 60] as $days) {
            $forecastEnd = now()->addDays($days)->endOfDay();
            $forecastRows = $this->aggregateForecastDailyFlow($company->id, $forecastStart, $forecastEnd);

            $runningBalance = $currentCash;
            $timeline = [];
            foreach ($forecastRows as $row) {
                $runningBalance += (float) $row['net'];
                $timeline[] = [
                    'date' => $row['date'],
                    'label' => Carbon::parse($row['date'])->format('d/m'),
                    'inflows' => round((float) $row['inflows'], 2),
                    'outflows' => round((float) $row['outflows'], 2),
                    'net' => round((float) $row['net'], 2),
                    'balance' => round((float) $runningBalance, 2),
                ];
            }

            if ($days === 15) {
                $forecastEvolution15 = $timeline;
            }

            $projectedInflows = (float) collect($forecastRows)->sum(fn ($r) => (float) $r['inflows']);
            $projectedOutflows = (float) collect($forecastRows)->sum(fn ($r) => (float) $r['outflows']);
            $projectedNet = $projectedInflows - $projectedOutflows;
            $projectedEndCash = $currentCash + $projectedNet;

            $growthTargets = [];
            foreach ([0, 10, 20, 30] as $pct) {
                $targetCash = $pct === 0 ? 0.01 : ($currentCash * (1 + ($pct / 100)));
                $growthTargets[] = [
                    'growth_percent' => $pct,
                    'target_cash' => round($targetCash, 2),
                    'needed_inflow' => round(max(0, $targetCash - $projectedEndCash), 2),
                ];
            }

            $forecastByDays[] = [
                'horizon_days' => $days,
                'start_cash' => round($currentCash, 2),
                'projected_inflows' => round($projectedInflows, 2),
                'projected_outflows' => round($projectedOutflows, 2),
                'projected_net' => round($projectedNet, 2),
                'projected_end_cash' => round($projectedEndCash, 2),
                'growth_targets' => $growthTargets,
                'timeline' => $timeline,
            ];
        }

        // Projeção do próximo mês calendário
        $nextMonthStart = now()->copy()->addMonthNoOverflow()->startOfMonth();
        $nextMonthEnd = $nextMonthStart->copy()->endOfMonth();
        $nextMonthIn = (float) Receivable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$nextMonthStart, $nextMonthEnd])
            ->sum('value');
        $nextMonthOut = (float) Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$nextMonthStart, $nextMonthEnd])
            ->sum('value');
        $nextMonthNet = $nextMonthIn - $nextMonthOut;
        $projectedEndNextMonth = $currentCash + $nextMonthNet;

        $nextMonthGrowthTargets = [];
        foreach ([0, 10, 20, 30] as $pct) {
            $targetCash = $pct === 0 ? 0.01 : ($currentCash * (1 + ($pct / 100)));
            $nextMonthGrowthTargets[] = [
                'growth_percent' => $pct,
                'target_cash' => round($targetCash, 2),
                'needed_inflow' => round(max(0, $targetCash - $projectedEndNextMonth), 2),
            ];
        }

        return response()->json([
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'days' => $periodDays,
            ],
            'summary' => [
                'current_cash' => round($currentCash, 2),
                'starting_cash' => round($startingCash, 2),
                'ending_cash' => round($endingCashAtEndDate, 2),
                'inflows' => round($totalInflows, 2),
                'outflows' => round($totalOutflows, 2),
                'net_cash_flow' => round($netCashFlow, 2),
                'burn_rate' => round($burnRate, 2),
                'runway_months' => $runwayMonths !== null ? round($runwayMonths, 2) : null,
                'avg_inflows' => round($avgInflows, 2),
                'avg_outflows' => round($avgOutflows, 2),
                'daily_result' => round($dailyResult, 2),
                'weekly_result' => round($weeklyResult, 2),
                'monthly_result' => round($monthlyResult, 2),
                'short_term_obligations' => round($shortTermObligations, 2),
                'liquidity_index' => $liquidityIndex !== null ? round($liquidityIndex, 2) : null,
                'cash_commitment_percent' => $cashCommitmentPercent !== null ? round($cashCommitmentPercent, 2) : null,
            ],
            'evolution' => $evolution,
            'forecast_15_days' => $forecastEvolution15,
            'forecast_horizons' => $forecastByDays,
            'next_month_projection' => [
                'month_label' => $nextMonthStart->locale('pt_BR')->translatedFormat('F/Y'),
                'current_cash' => round($currentCash, 2),
                'projected_inflows' => round($nextMonthIn, 2),
                'projected_outflows' => round($nextMonthOut, 2),
                'projected_net' => round($nextMonthNet, 2),
                'projected_end_cash' => round($projectedEndNextMonth, 2),
                'target_growth' => $nextMonthGrowthTargets,
            ],
        ]);
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
        
        // Acumula por nome de categoria (array associativo: alterar cópia retornada por
        // firstWhere() em arrays dentro da Collection não persiste — somava só o 1º lançamento.)
        $byCategoryLabel = [];

        if ($payablesFromExpenses->isNotEmpty()) {
            $descriptions = $payablesFromExpenses->pluck('description')->unique()->toArray();
            $expenses = Expense::where('company_id', $company->id)
                ->whereIn('description', $descriptions)
                ->with('category:id,name,color')
                ->get()
                ->keyBy('description');

            foreach ($payablesFromExpenses as $payable) {
                $expense = $expenses->get($payable->description);

                if (!$expense || !$expense->category) {
                    continue;
                }

                $categoryName = $expense->category->name;
                $amount = (float) $payable->value;

                if (! isset($byCategoryLabel[$categoryName])) {
                    $byCategoryLabel[$categoryName] = [
                        'label' => $categoryName,
                        'value' => 0.0,
                        'color' => $expense->category->color ?? '#5e72e4',
                    ];
                }

                $byCategoryLabel[$categoryName]['value'] += $amount;
            }
        }

        $expensesByCategoryChart = collect(array_values($byCategoryLabel));
        
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

    /**
     * Faturamento realizado em um período (soma pelas datas de pagamento em receivable_payments).
     * Usa receivable_payments para refletir as datas corretas de cada recebimento (parcial ou total).
     */
    protected function getRevenueRealizedInPeriod(int $companyId, Carbon $start, Carbon $end): float
    {
        if (! Schema::hasTable('receivable_payments')) {
            return (float) Receivable::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$start, $end])
                ->sum('value');
        }
        return (float) ReceivablePayment::whereHas('receivable', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->whereBetween('paid_date', [$start, $end])
            ->sum('amount');
    }

    protected function getFinancialHistory(Company $company, Carbon $referenceMonth, int $months = 6): array
    {
        $history = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $referenceMonth->copy()->startOfMonth()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            $monthName = $date->format('M/Y');

            // Receita realizada = soma dos pagamentos pela data de recebimento (receivable_payments)
            $revenue = $this->getRevenueRealizedInPeriod($company->id, $monthStart, $monthEnd);

            $payables = Payable::where('company_id', $company->id)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$monthStart, $monthEnd])
                ->sum('value');

            $history[] = [
                'month' => $monthName,
                'revenue' => $revenue,
                'expenses' => $payables,
                'profit' => $revenue - $payables,
            ];
        }
        
        return $history;
    }

    /**
     * @return array<int, array{date:string,inflows:float,outflows:float,net:float}>
     */
    protected function aggregateDailyCashFlow(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $revenues = ReceivablePayment::whereHas('receivable', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->whereBetween('paid_date', [$startDate, $endDate])
            ->selectRaw('DATE(paid_date) as d, SUM(amount) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $expenses = Payable::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('paid_date', [$startDate, $endDate])
            ->selectRaw('DATE(paid_date) as d, SUM(value) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $rows = [];
        $cursor = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $d = $cursor->toDateString();
            $in = (float) ($revenues[$d] ?? 0);
            $out = (float) ($expenses[$d] ?? 0);
            $rows[] = [
                'date' => $d,
                'inflows' => $in,
                'outflows' => $out,
                'net' => $in - $out,
            ];
            $cursor->addDay();
        }

        return $rows;
    }

    /**
     * @return array<int, array{date:string,inflows:float,outflows:float,net:float}>
     */
    protected function aggregateForecastDailyFlow(int $companyId, Carbon $startDate, Carbon $endDate): array
    {
        $inflows = Receivable::where('company_id', $companyId)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->selectRaw('DATE(due_date) as d, SUM(value) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $outflows = Payable::where('company_id', $companyId)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->selectRaw('DATE(due_date) as d, SUM(value) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $rows = [];
        $cursor = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $d = $cursor->toDateString();
            $in = (float) ($inflows[$d] ?? 0);
            $out = (float) ($outflows[$d] ?? 0);
            $rows[] = [
                'date' => $d,
                'inflows' => $in,
                'outflows' => $out,
                'net' => $in - $out,
            ];
            $cursor->addDay();
        }

        return $rows;
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
