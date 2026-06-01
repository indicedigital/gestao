<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Services\ProductivityAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductivityController extends Controller
{
    use InteractsWithCompany;

    public function __construct(
        protected ProductivityAnalyticsService $analytics
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        abort_unless($this->authz()->canViewProductivity(), 403);

        $company = $this->getCurrentCompany();
        $filters = $this->extractFilters($request);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->tabResponse($company->id, $filters);
        }

        $data = $this->analytics->analyzeForTab($company->id, $filters, $filters['tab']);

        return view('company.dailies.productivity', array_merge($data, [
            'company' => $company,
            'filters' => $filters,
            'authz' => $this->authz(),
        ]));
    }

    public function tab(Request $request): JsonResponse
    {
        abort_unless($this->authz()->canViewProductivity(), 403);

        return $this->tabResponse($this->getCurrentCompany()->id, $this->extractFilters($request));
    }

    /** @return array<string, mixed> */
    protected function extractFilters(Request $request): array
    {
        $filters = $request->only([
            'period', 'from', 'to', 'employee_id', 'selected_employee_id',
            'team', 'project_id', 'client_id', 'category', 'status',
            'priority', 'overdue', 'goal_met', 'inactive',
        ]);
        $filters['tab'] = $request->query('tab', 'overview');

        return $filters;
    }

    protected function tabResponse(int $companyId, array $filters): JsonResponse
    {
        $tab = $filters['tab'] ?? 'overview';
        $data = $this->analytics->analyzeForTab($companyId, $filters, $tab);
        $view = 'company.dailies.productivity._'.$tab;

        if (! view()->exists($view)) {
            abort(404);
        }

        $html = view($view, array_merge($data, ['filters' => $filters]))->render();

        $payload = [
            'html' => $html,
            'tab' => $tab,
            'period' => $data['period'] ?? null,
        ];

        if (isset($data['charts'])) {
            $payload['charts'] = $data['charts'];
        }
        if (isset($data['comparatives'])) {
            $payload['comparatives'] = $data['comparatives'];
        }
        if (isset($data['history'])) {
            $payload['history'] = $data['history'];
        }
        if (isset($data['alert_count'])) {
            $payload['alert_count'] = $data['alert_count'];
        }
        if (! empty($data['employeeDetail'])) {
            $detail = $data['employeeDetail'];
            $payload['employeeCharts'] = $detail['charts'] ?? null;
            $payload['employeeMetrics'] = [
                'productivity_pct' => $detail['productivity_pct'] ?? 0,
                'team_avg_productivity' => $detail['team_avg_productivity'] ?? 0,
            ];
        }

        return response()->json($payload);
    }
}
