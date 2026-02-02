<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Payable;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DebugDashboardExpenses extends Command
{
    protected $signature = 'dashboard:debug-expenses 
                            {--company= : ID da empresa (opcional, usa a primeira se omitido)} 
                            {--month= : Mês no formato Y-m (opcional, ex: 2026-02)}';

    protected $description = 'Mostra o detalhamento da previsão de despesas do dashboard (para comparar com Contas a Pagar)';

    public function handle(): int
    {
        $companyId = $this->option('company');
        $monthInput = $this->option('month') ?? now()->format('Y-m');

        $company = $companyId
            ? Company::find($companyId)
            : Company::first();

        if (! $company) {
            $this->error('Nenhuma empresa encontrada.');
            return 1;
        }

        $now = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $this->info("=== Detalhamento Despesas - Dashboard ===");
        $this->info("Empresa: {$company->name} (ID: {$company->id})");
        $this->info("Mês: {$now->locale('pt_BR')->translatedFormat('F/Y')} ({$monthInput})");
        $this->newLine();

        // 1. Despesas realizadas (pagas no mês)
        $expensesRealized = Payable::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereBetween('paid_date', [$monthStart, $monthEnd])
            ->sum('value');
        $this->line("1. <fg=green>Despesas realizadas</> (contas PAGAS com data de pagamento no mês): R$ " . number_format($expensesRealized, 2, ',', '.'));

        // 2. Previsão só contas pendentes (vencimento no mês)
        $expensesForecastPayablesOnly = Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [$monthStart, $monthEnd])
            ->sum('value');
        $this->line("2. <fg=yellow>Pendentes no mês</> (contas PENDENTES com vencimento no mês): R$ " . number_format($expensesForecastPayablesOnly, 2, ',', '.'));

        $this->line("3. Folha não é somada em cima: já está nas contas a pagar (pendentes no mês).");

        $totalExpensesForecast = $expensesRealized + $expensesForecastPayablesOnly;
        $this->newLine();
        $this->info("TOTAL PREVISTO no dashboard = 1 + 2 = R$ " . number_format($totalExpensesForecast, 2, ',', '.'));
        $this->newLine();
        $this->comment("O Previsto = Realizado (contas pagas no mês) + Pendentes (venc. no mês).");
        $this->comment("A soma na tela Contas a Pagar (pendentes do mês) deve bater com o item 2.");
        return 0;
    }

    protected function getMonthlyPayrollCost(Company $company, Carbon $month): float
    {
        $payrollPayable = Payable::where('company_id', $company->id)
            ->where('type', 'salary')
            ->where('description', 'like', '%Folha de Pagamento%')
            ->where(function ($query) use ($month) {
                $query->where(function ($q) use ($month) {
                    $q->where('status', 'paid')
                        ->whereBetween('paid_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                })->orWhere(function ($q) use ($month) {
                    $q->where('status', 'pending')
                        ->whereBetween('due_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
                });
            })
            ->first();

        if ($payrollPayable) {
            return (float) $payrollPayable->value;
        }

        return (float) Employee::where('company_id', $company->id)
            ->whereIn('type', ['clt', 'pj'])
            ->where('status', 'active')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->sum('salary');
    }
}
