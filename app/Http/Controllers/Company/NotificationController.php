<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Task;
use App\Services\CompanyAuthorizationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
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

    protected function authz(): CompanyAuthorizationService
    {
        return app(CompanyAuthorizationService::class);
    }

    /**
     * Retorna as notificações para o header
     */
    public function getNotifications()
    {
        $company = $this->getCurrentCompany();
        $authz = $this->authz();
        $notifications = [];

        if ($authz->canAccessModule('tasks')) {
            $slaTasks = Task::where('company_id', $company->id)
                ->where('status', '!=', 'completed')
                ->whereNotNull('sla_deadline')
                ->whereNotNull('sla_hours')
                ->orderBy('sla_deadline')
                ->limit(20)
                ->get()
                ->filter(fn ($task) => ($task->slaProgressPercent() ?? 0) >= 50)
                ->take(5);

            foreach ($slaTasks as $task) {
                if (! $authz->canViewTask($task)) {
                    continue;
                }

                $percent = $task->slaProgressPercent();
                $level = $task->slaAlertLevel() ?? 'info';
                $type = match ($level) {
                    'danger' => 'danger',
                    'warning' => 'warning',
                    'info' => 'info',
                    default => 'secondary',
                };

                $thresholdLabel = $task->isOverdue()
                    ? 'SLA estourado (100%+)'
                    : ($percent >= 80 ? 'SLA em 80%+' : ($percent >= 50 ? 'SLA em 50%+' : 'SLA próximo'));

                $notifications[] = [
                    'type' => $type,
                    'icon' => 'clock',
                    'title' => "{$thresholdLabel}: {$task->title}",
                    'message' => $task->isOverdue()
                        ? 'Prazo: '.$task->sla_deadline->format('d/m/Y H:i')
                        : 'Prazo SLA: '.$task->sla_deadline->format('d/m/Y H:i').' ('.round($percent ?? 0).'% consumido)',
                    'time' => $task->sla_deadline->diffForHumans(),
                    'url' => route('company.tasks.show', $task),
                ];
            }
        }

        if ($authz->canAccessModule('clients') && $authz->canAccessModule('receivables')) {
            $overdueClients = Client::where('company_id', $company->id)
                ->whereHas('receivables', function ($query) {
                    $query->where('status', 'pending')
                        ->where('due_date', '<', now());
                })
                ->with(['receivables' => function ($query) {
                    $query->where('status', 'pending')
                        ->where('due_date', '<', now())
                        ->orderBy('due_date', 'asc');
                }])
                ->limit(10)
                ->get();

            foreach ($overdueClients as $client) {
                $totalOverdue = $client->receivables()
                    ->where('status', 'pending')
                    ->where('due_date', '<', now())
                    ->sum('value');
                $oldestReceivable = $client->receivables->first();
                if ($oldestReceivable && $totalOverdue > 0) {
                    $days = (int) floor(now()->diffInDays(Carbon::parse($oldestReceivable->due_date), false));
                    if ($days > 0) {
                        $notifications[] = [
                            'type' => 'danger',
                            'icon' => 'exclamation-triangle',
                            'title' => "Cliente {$client->name} com {$days} dias de atraso",
                            'message' => 'Total em atraso: R$ '.number_format($totalOverdue, 2, ',', '.'),
                            'time' => $oldestReceivable->due_date->diffForHumans(),
                            'url' => route('company.clients.show', $client),
                        ];
                    }
                }
            }
        }

        if ($authz->canAccessModule('contracts')) {
            $contractsExpiring = Contract::where('company_id', $company->id)
                ->where('status', 'active')
                ->whereNotNull('end_date')
                ->whereBetween('end_date', [now(), now()->addDays(30)])
                ->with('client')
                ->orderBy('end_date', 'asc')
                ->limit(5)
                ->get();

            foreach ($contractsExpiring as $contract) {
                $daysLeft = (int) floor(now()->diffInDays($contract->end_date, false));
                if ($daysLeft > 0) {
                    $notifications[] = [
                        'type' => 'warning',
                        'icon' => 'calendar-alt',
                        'title' => "Contrato '{$contract->name}' vence em {$daysLeft} dias",
                        'message' => $contract->client ? "Cliente: {$contract->client->name}" : 'Valor: R$ '.number_format($contract->value, 2, ',', '.'),
                        'time' => $contract->end_date->diffForHumans(),
                        'url' => route('company.contracts.edit', $contract),
                    ];
                }
            }
        }

        if ($authz->canAccessModule('payables') && $authz->canViewCompanyDashboard()) {
            $expensesRealized = Payable::where('company_id', $company->id)
                ->where('status', 'paid')
                ->whereYear('paid_date', now()->year)
                ->whereMonth('paid_date', now()->month)
                ->sum('value');

            $expensesForecast = Payable::where('company_id', $company->id)
                ->where('status', 'pending')
                ->whereYear('due_date', now()->year)
                ->whereMonth('due_date', now()->month)
                ->sum('value');

            if ($expensesForecast > 0 && $expensesRealized > $expensesForecast * 1.1) {
                $notifications[] = [
                    'type' => 'warning',
                    'icon' => 'chart-line',
                    'title' => 'Despesas acima do previsto',
                    'message' => 'Despesas realizadas estão 10% acima do previsto para este mês',
                    'time' => now()->diffForHumans(),
                    'url' => route('company.dashboard'),
                ];
            }

            if ($authz->canAccessModule('receivables')) {
                $revenueRealized = Receivable::where('company_id', $company->id)
                    ->where('status', 'paid')
                    ->whereYear('paid_date', now()->year)
                    ->whereMonth('paid_date', now()->month)
                    ->sum('value');

                $profitRealized = $revenueRealized - $expensesRealized;
                $profitMargin = $revenueRealized > 0 ? ($profitRealized / $revenueRealized) * 100 : 0;

                if ($profitMargin < 20 && $revenueRealized > 0) {
                    $notifications[] = [
                        'type' => 'warning',
                        'icon' => 'percent',
                        'title' => 'Margem de lucro abaixo de 20%',
                        'message' => 'Sua margem de lucro atual é de '.number_format($profitMargin, 1, ',', '.').'%',
                        'time' => now()->diffForHumans(),
                        'url' => route('company.dashboard'),
                    ];
                }
            }
        }

        $notifications = array_slice($notifications, 0, 10);

        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications),
        ]);
    }
}
