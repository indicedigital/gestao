<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Payable;
use App\Models\Receivable;
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
     * Retorna as notificações para o header
     */
    public function getNotifications()
    {
        $company = $this->getCurrentCompany();
        $notifications = [];
        
        // Clientes inadimplentes
        $overdueClients = Client::where('company_id', $company->id)
            ->whereHas('receivables', function($query) {
                $query->where('status', 'pending')
                      ->where('due_date', '<', now());
            })
            ->with(['receivables' => function($query) {
                $query->where('status', 'pending')
                      ->where('due_date', '<', now())
                      ->orderBy('due_date', 'asc')
                      ->limit(1);
            }])
            ->get();
        
        foreach ($overdueClients as $client) {
            $totalOverdue = $client->receivables->sum('value');
            $oldestReceivable = $client->receivables->first();
            if ($oldestReceivable) {
                $days = (int) floor(now()->diffInDays(Carbon::parse($oldestReceivable->due_date), false));
                if ($days > 0) {
                    $notifications[] = [
                        'type' => 'danger',
                        'icon' => 'exclamation-triangle',
                        'title' => "Cliente {$client->name} com {$days} dias de atraso",
                        'message' => "Total em atraso: R$ " . number_format($totalOverdue, 2, ',', '.'),
                        'time' => $oldestReceivable->due_date->diffForHumans(),
                        'url' => route('company.clients.show', $client),
                    ];
                }
            }
        }
        
        // Contratos perto do vencimento (próximos 30 dias)
        $contractsExpiring = Contract::where('company_id', $company->id)
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->with('client')
            ->orderBy('end_date', 'asc')
            ->get();
        
        foreach ($contractsExpiring as $contract) {
            $daysLeft = (int) floor(now()->diffInDays($contract->end_date, false));
            if ($daysLeft > 0) {
                $notifications[] = [
                    'type' => 'warning',
                    'icon' => 'calendar-alt',
                    'title' => "Contrato '{$contract->name}' vence em {$daysLeft} dias",
                    'message' => $contract->client ? "Cliente: {$contract->client->name}" : "Valor: R$ " . number_format($contract->value, 2, ',', '.'),
                    'time' => $contract->end_date->diffForHumans(),
                    'url' => route('company.contracts.edit', $contract),
                ];
            }
        }
        
        // Despesas acima do previsto (último mês)
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
                'message' => "Despesas realizadas estão 10% acima do previsto para este mês",
                'time' => now()->diffForHumans(),
                'url' => route('company.dashboard'),
            ];
        }
        
        // Margem de lucro abaixo de 20%
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
                'message' => "Sua margem de lucro atual é de " . number_format($profitMargin, 1, ',', '.') . "%",
                'time' => now()->diffForHumans(),
                'url' => route('company.dashboard'),
            ];
        }
        
        // Limita a 10 notificações mais recentes
        $notifications = array_slice($notifications, 0, 10);
        
        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications),
        ]);
    }
}
