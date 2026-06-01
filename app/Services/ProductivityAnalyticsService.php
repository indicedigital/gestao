<?php

namespace App\Services;

use App\Models\Daily;
use App\Models\Employee;
use App\Models\ProductivityGoal;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductivityAnalyticsService
{
    public const TZ = 'America/Sao_Paulo';

    public const DAILY_HOURS_TARGET = 8.0;

    public const DAILY_TASKS_TARGET = 2;

    public const COMPLETION_RATE_TARGET = 75.0;

    /** @var array<string, mixed> */
    protected array $filters = [];

    protected int $companyId;

    protected Carbon $from;

    protected Carbon $to;

    protected Carbon $prevFrom;

    protected Carbon $prevTo;

    protected int $businessDays;

    protected int $prevBusinessDays;

    /** @var Collection<int, Employee> */
    protected Collection $employees;

    /** @var array<int, array<string, mixed>> */
    protected array $employeeMetrics = [];

    /** @var array<string, mixed>|null */
    protected ?array $globalKpisCache = null;

    /** @var Collection<int, ProductivityGoal>|null */
    protected ?Collection $goalsCache = null;

    /** @return array<string, mixed> */
    public function filterOptions(int $companyId): array
    {
        $this->companyId = $companyId;

        return $this->buildFilterOptions();
    }

    /** @return array<string, mixed> */
    public function periodMeta(array $filters): array
    {
        $this->filters = $this->normalizeFilters($filters);
        $this->resolvePeriod();

        return [
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
            'label' => $this->periodLabel(),
            'type' => $this->filters['period'],
            'business_days' => $this->businessDays,
        ];
    }

    /** @return array<string, mixed> */
    public function analyzeForTab(int $companyId, array $filters, string $tab): array
    {
        $this->bootstrap($companyId, $filters);
        $base = [
            'period' => [
                'from' => $this->from->toDateString(),
                'to' => $this->to->toDateString(),
                'label' => $this->periodLabel(),
                'type' => $this->filters['period'],
                'business_days' => $this->businessDays,
            ],
            'filterOptions' => $this->buildFilterOptions(),
        ];

        return match ($tab) {
            'collaborators' => array_merge($base, $this->collaboratorsPayload()),
            'ranking' => array_merge($base, ['ranking' => $this->buildRanking()]),
            'alerts' => array_merge($base, $this->alertsPayload()),
            'insights' => array_merge($base, $this->insightsPayload()),
            'history' => array_merge($base, [
                'history' => $this->buildHistory(),
                'comparatives' => $this->buildComparatives(),
            ]),
            'table' => array_merge($base, ['table' => $this->buildAnalyticTable()]),
            'goals' => array_merge($base, [
                'goals' => $this->buildGoalsSummary(),
                'charts' => ['goal_evolution' => $this->buildGoalEvolution()],
                'table' => $this->buildAnalyticTable(),
            ]),
            default => array_merge($base, [
                'kpis' => $this->getGlobalKpis(),
                'charts' => $this->buildCharts(),
            ]),
        };
    }

    /** @return array<string, mixed> */
    protected function collaboratorsPayload(): array
    {
        $selectedEmployee = null;
        $employeeDetail = null;

        if ($this->filters['selected_employee_id']) {
            $selectedEmployee = $this->employees->firstWhere('id', (int) $this->filters['selected_employee_id']);
            if ($selectedEmployee) {
                $employeeDetail = $this->buildEmployeeDetail($selectedEmployee);
            }
        }

        return [
            'table' => $this->buildAnalyticTable(),
            'selectedEmployee' => $selectedEmployee,
            'employeeDetail' => $employeeDetail,
        ];
    }

    /** @return array<string, mixed> */
    protected function alertsPayload(): array
    {
        $kpis = $this->getGlobalKpis();
        $ranking = $this->buildRanking();
        $alerts = $this->buildAlerts();

        return [
            'alerts' => $alerts,
            'insights' => $this->buildInsights($kpis, $ranking, $alerts),
            'alert_count' => count($alerts),
        ];
    }

    /** @return array<string, mixed> */
    protected function insightsPayload(): array
    {
        $kpis = $this->getGlobalKpis();
        $ranking = $this->buildRanking();
        $alerts = $this->buildAlerts();

        return [
            'insights' => $this->buildInsights($kpis, $ranking, $alerts),
            'comparatives' => $this->buildComparatives(),
        ];
    }

    protected function bootstrap(int $companyId, array $filters): void
    {
        $this->companyId = $companyId;
        $this->filters = $this->normalizeFilters($filters);
        $this->globalKpisCache = null;
        $this->goalsCache = null;
        $this->resolvePeriod();

        $this->employees = Employee::where('company_id', $companyId)
            ->when($this->filters['employee_id'], fn ($q, $id) => $q->whereKey($id))
            ->when($this->filters['team'], fn ($q, $team) => $q->where('position', $team))
            ->when($this->filters['inactive'], fn ($q) => $q->where('status', '!=', 'active'))
            ->when(! $this->filters['inactive'], fn ($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'position', 'role', 'hire_date', 'status']);

        $this->employeeMetrics = $this->buildEmployeeMetrics();
    }

    /** @return array<string, mixed> */
    protected function getGlobalKpis(): array
    {
        if ($this->globalKpisCache !== null) {
            return $this->globalKpisCache;
        }

        return $this->globalKpisCache = $this->buildGlobalKpis();
    }

    public function analyze(int $companyId, array $filters = []): array
    {
        return $this->analyzeForTab($companyId, $filters, $filters['tab'] ?? 'overview');
    }

    /** @return array<string, mixed> */
    protected function normalizeFilters(array $filters): array
    {
        return [
            'period' => in_array($filters['period'] ?? 'month', ['today', 'week', 'month', 'custom'], true)
                ? ($filters['period'] ?? 'month')
                : 'month',
            'from' => $filters['from'] ?? null,
            'to' => $filters['to'] ?? null,
            'employee_id' => ! empty($filters['employee_id']) ? (int) $filters['employee_id'] : null,
            'selected_employee_id' => ! empty($filters['selected_employee_id']) ? (int) $filters['selected_employee_id'] : null,
            'team' => $filters['team'] ?? null,
            'project_id' => ! empty($filters['project_id']) ? (int) $filters['project_id'] : null,
            'client_id' => ! empty($filters['client_id']) ? (int) $filters['client_id'] : null,
            'category' => $filters['category'] ?? null,
            'status' => $filters['status'] ?? null,
            'priority' => $filters['priority'] ?? null,
            'overdue' => filter_var($filters['overdue'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'goal_met' => isset($filters['goal_met']) && $filters['goal_met'] !== ''
                ? filter_var($filters['goal_met'], FILTER_VALIDATE_BOOLEAN)
                : null,
            'inactive' => filter_var($filters['inactive'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    protected function resolvePeriod(): void
    {
        $now = Carbon::now(self::TZ);

        match ($this->filters['period']) {
            'today' => $this->from = $now->copy()->startOfDay(),
            'week' => $this->from = $now->copy()->startOfWeek(),
            'custom' => $this->from = Carbon::parse($this->filters['from'] ?? $now->copy()->startOfMonth(), self::TZ)->startOfDay(),
            default => $this->from = $now->copy()->startOfMonth(),
        };

        match ($this->filters['period']) {
            'today' => $this->to = $now->copy()->endOfDay(),
            'week' => $this->to = $now->copy()->endOfWeek(),
            'custom' => $this->to = Carbon::parse($this->filters['to'] ?? $now->toDateString(), self::TZ)->endOfDay(),
            default => $this->to = $now->copy()->endOfMonth(),
        };

        if ($this->to->lt($this->from)) {
            [$this->from, $this->to] = [$this->to->copy()->startOfDay(), $this->from->copy()->endOfDay()];
        }

        $days = (int) $this->from->diffInDays($this->to) + 1;
        $this->prevTo = $this->from->copy()->subDay()->endOfDay();
        $this->prevFrom = $this->prevTo->copy()->subDays(max(0, $days - 1))->startOfDay();

        $this->businessDays = $this->countBusinessDays($this->from, $this->to);
        $this->prevBusinessDays = $this->countBusinessDays($this->prevFrom, $this->prevTo);
    }

    /** @return array<int, array<string, mixed>> */
    protected function buildEmployeeMetrics(): array
    {
        $employeeIds = $this->employees->pluck('id')->all();
        if (empty($employeeIds)) {
            return [];
        }

        $from = $this->from->toDateString();
        $to = $this->to->toDateString();
        $prevFrom = $this->prevFrom->toDateString();
        $prevTo = $this->prevTo->toDateString();

        $dailyStats = Daily::where('company_id', $this->companyId)
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$from, $to])
            ->when($this->filters['project_id'], fn ($q, $id) => $q->where('project_id', $id))
            ->selectRaw('employee_id, SUM(hours) as total_hours, COUNT(*) as entries, COUNT(DISTINCT work_date) as days_worked')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $prevDailyStats = Daily::where('company_id', $this->companyId)
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$prevFrom, $prevTo])
            ->selectRaw('employee_id, SUM(hours) as total_hours, COUNT(DISTINCT work_date) as days_worked')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $taskBase = Task::where('company_id', $this->companyId)
            ->whereIn('assignee_id', $employeeIds)
            ->when($this->filters['project_id'], fn ($q, $id) => $q->where('project_id', $id))
            ->when($this->filters['client_id'], fn ($q, $id) => $q->whereHas('project', fn ($p) => $p->where('client_id', $id)))
            ->when($this->filters['category'], fn ($q, $c) => $q->where('category', $c))
            ->when($this->filters['status'], fn ($q, $s) => $q->where('status', $s))
            ->when($this->filters['priority'], fn ($q, $p) => $q->where('priority', $p));

        $now = Carbon::now(self::TZ);
        $taskStats = (clone $taskBase)
            ->selectRaw(
                "assignee_id,
                SUM(CASE WHEN status = 'completed' AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as completed_cnt,
                AVG(CASE WHEN status = 'completed' AND completed_at BETWEEN ? AND ? THEN actual_hours END) as avg_hours,
                SUM(CASE WHEN status != 'completed' THEN 1 ELSE 0 END) as pending_cnt,
                SUM(CASE WHEN status != 'completed' AND sla_deadline IS NOT NULL AND sla_deadline < ? THEN 1 ELSE 0 END) as overdue_cnt,
                SUM(CASE WHEN status = 'completed' AND completed_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as prev_completed_cnt",
                [$this->from, $this->to, $this->from, $this->to, $now, $this->prevFrom, $this->prevTo]
            )
            ->groupBy('assignee_id')
            ->get()
            ->keyBy('assignee_id');

        $metrics = [];
        foreach ($this->employees as $employee) {
            $id = $employee->id;
            $daily = $dailyStats->get($id);
            $prevDaily = $prevDailyStats->get($id);
            $stats = $taskStats->get($id);
            $pending = (int) ($stats->pending_cnt ?? 0);
            $overdue = (int) ($stats->overdue_cnt ?? 0);
            $prevComp = (int) ($stats->prev_completed_cnt ?? 0);

            $hours = (float) ($daily->total_hours ?? 0);
            $daysWorked = (int) ($daily->days_worked ?? 0);
            $completedCount = (int) ($stats->completed_cnt ?? 0);
            $totalTasks = $completedCount + $pending;
            $completionRate = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100, 1) : 0;
            $avgHours = $completedCount > 0 ? round((float) ($stats->avg_hours ?? 0), 2) : 0;
            $consistency = $this->businessDays > 0 ? round(($daysWorked / $this->businessDays) * 100, 1) : 0;

            $hoursTarget = $this->goalFor($id, 'hours_target');
            $tasksTarget = $this->goalFor($id, 'tasks_target');
            $rateTarget = $this->goalFor($id, 'completion_rate_target');

            $expectedHours = $this->businessDays * $hoursTarget;
            $productivityPct = $expectedHours > 0 ? min(150, round(($hours / $expectedHours) * 100, 1)) : 0;
            $dailyAvg = $daysWorked > 0 ? round($hours / $daysWorked, 2) : 0;

            $prevHours = (float) ($prevDaily->total_hours ?? 0);
            $growth = $prevHours > 0 ? round((($hours - $prevHours) / $prevHours) * 100, 1) : ($hours > 0 ? 100 : 0);
            $taskGrowth = $prevComp > 0 ? round((($completedCount - $prevComp) / $prevComp) * 100, 1) : ($completedCount > 0 ? 100 : 0);

            $efficiency = $this->calcEfficiency($completionRate, $consistency, $overdue, $pending);
            $score = $this->calcScore($productivityPct, $completionRate, $efficiency, $consistency);

            $goalMet = $productivityPct >= 80 && $completionRate >= $rateTarget && $completedCount >= ($tasksTarget * max(1, $this->businessDays / 5));

            if ($this->filters['overdue'] && $overdue === 0) {
                continue;
            }
            if ($this->filters['goal_met'] === true && ! $goalMet) {
                continue;
            }
            if ($this->filters['goal_met'] === false && $goalMet) {
                continue;
            }

            $metrics[$id] = [
                'employee' => $employee,
                'hours' => $hours,
                'days_worked' => $daysWorked,
                'productive_days' => $daysWorked > 0 && ($hours / max(1, $daysWorked)) >= ($hoursTarget * 0.6) ? $daysWorked : 0,
                'unproductive_days' => max(0, $this->businessDays - $daysWorked),
                'entries' => (int) ($daily->entries ?? 0),
                'completed' => $completedCount,
                'pending' => $pending,
                'overdue' => $overdue,
                'completion_rate' => $completionRate,
                'avg_execution_hours' => $avgHours,
                'consistency' => $consistency,
                'productivity_pct' => $productivityPct,
                'daily_avg_hours' => $dailyAvg,
                'efficiency' => $efficiency,
                'score' => $score,
                'growth' => $growth,
                'task_growth' => $taskGrowth,
                'goal_met' => $goalMet,
                'hours_target' => $hoursTarget,
                'tasks_target' => $tasksTarget,
                'rate_target' => $rateTarget,
                'level' => $this->performanceLevel($score),
                'trend' => $growth >= 5 ? 'up' : ($growth <= -5 ? 'down' : 'stable'),
                'team' => $employee->position ?? 'Geral',
            ];
        }

        uasort($metrics, fn ($a, $b) => $b['score'] <=> $a['score']);
        $pos = 1;
        foreach ($metrics as &$m) {
            $m['rank'] = $pos++;
        }
        unset($m);

        return $metrics;
    }

    /** @return array<string, mixed> */
    protected function buildGlobalKpis(): array
    {
        $metrics = collect($this->employeeMetrics);
        $count = $metrics->count();
        $activeCount = $this->employees->where('status', 'active')->count();

        $totalCompleted = $metrics->sum('completed');
        $totalPending = $metrics->sum('pending');
        $totalOverdue = $metrics->sum('overdue');
        $totalTasks = $totalCompleted + $totalPending;
        $totalHours = $metrics->sum('hours');

        $avgProductivity = $count > 0 ? round($metrics->avg('productivity_pct'), 1) : 0;
        $avgCompletion = $count > 0 ? round($metrics->avg('completion_rate'), 1) : 0;
        $avgExecution = $count > 0 ? round($metrics->avg('avg_execution_hours'), 2) : 0;
        $avgTasksPerEmployee = $count > 0 ? round($totalCompleted / $count, 1) : 0;
        $productiveCount = $metrics->filter(fn ($m) => $m['productive_days'] > 0)->count();
        $aboveGoal = $metrics->where('goal_met', true)->count();
        $belowGoal = $metrics->where('goal_met', false)->count();

        $best = $metrics->sortByDesc('score')->first();
        $worst = $metrics->sortBy('score')->first();

        $prevMetrics = $this->buildPreviousPeriodSummary();
        $growthPct = $prevMetrics['productivity'] > 0
            ? round((($avgProductivity - $prevMetrics['productivity']) / $prevMetrics['productivity']) * 100, 1)
            : ($avgProductivity > 0 ? 100 : 0);

        $completionRate = $totalTasks > 0 ? round(($totalCompleted / $totalTasks) * 100, 1) : 0;
        $delayRate = $totalTasks > 0 ? round(($totalOverdue / $totalTasks) * 100, 1) : 0;
        $efficiencyIndex = $count > 0 ? round($metrics->avg('efficiency'), 1) : 0;
        $consistencyIndex = $count > 0 ? round($metrics->avg('consistency'), 1) : 0;
        $operationalVolume = $totalCompleted + $metrics->sum('entries');

        return [
            'active_employees' => $activeCount,
            'productive_employees' => $productiveCount,
            'avg_productivity' => $avgProductivity,
            'total_completed' => $totalCompleted,
            'total_pending' => $totalPending,
            'total_overdue' => $totalOverdue,
            'completion_rate' => $completionRate,
            'avg_execution_hours' => $avgExecution,
            'avg_tasks_per_employee' => $avgTasksPerEmployee,
            'avg_team_productivity' => $avgProductivity,
            'above_goal' => $aboveGoal,
            'below_goal' => $belowGoal,
            'best_performer' => $best ? ['name' => $best['employee']->name, 'score' => $best['score']] : null,
            'worst_performer' => $worst ? ['name' => $worst['employee']->name, 'score' => $worst['score']] : null,
            'growth_pct' => $growthPct,
            'decline_pct' => $growthPct < 0 ? abs($growthPct) : 0,
            'operational_volume' => $operationalVolume,
            'efficiency_index' => $efficiencyIndex,
            'delay_rate' => $delayRate,
            'consistency_index' => $consistencyIndex,
            'total_hours' => round($totalHours, 1),
        ];
    }

    /** @return array<string, mixed> */
    protected function buildPreviousPeriodSummary(): array
    {
        $employeeIds = $this->employees->pluck('id')->all();
        if (empty($employeeIds)) {
            return ['productivity' => 0, 'completed' => 0];
        }

        $hours = (float) Daily::where('company_id', $this->companyId)
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('work_date', [$this->prevFrom->toDateString(), $this->prevTo->toDateString()])
            ->sum('hours');

        $expected = $this->prevBusinessDays * self::DAILY_HOURS_TARGET * max(1, count($employeeIds));
        $productivity = $expected > 0 ? round(($hours / $expected) * 100, 1) : 0;

        $completed = Task::where('company_id', $this->companyId)
            ->whereIn('assignee_id', $employeeIds)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$this->prevFrom, $this->prevTo])
            ->count();

        return compact('productivity', 'completed');
    }

    /** @return array<string, mixed> */
    protected function buildCharts(): array
    {
        $employeeIds = $this->employees->pluck('id')->all();
        $from = $this->from->toDateString();
        $to = $this->to->toDateString();

        $evolution = Daily::where('company_id', $this->companyId)
            ->when(! empty($employeeIds), fn ($q) => $q->whereIn('employee_id', $employeeIds))
            ->whereBetween('work_date', [$from, $to])
            ->selectRaw('DATE(work_date) as day, SUM(hours) as hours')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $labels = [];
        $evolutionHours = [];
        $cursor = $this->from->copy();
        while ($cursor->lte($this->to)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('d/m');
            $row = $evolution->firstWhere('day', $key);
            $evolutionHours[] = round((float) ($row->hours ?? 0), 1);
            $cursor->addDay();
        }

        $topMetrics = collect($this->employeeMetrics)->take(10);
        $employeeLabels = $topMetrics->map(fn ($m) => $m['employee']->name)->values()->all();
        $employeeHours = $topMetrics->map(fn ($m) => round($m['hours'], 1))->values()->all();
        $employeeEfficiency = $topMetrics->map(fn ($m) => $m['efficiency'])->values()->all();
        $employeeCompletion = $topMetrics->map(fn ($m) => $m['completion_rate'])->values()->all();
        $employeeScores = $topMetrics->map(fn ($m) => $m['score'])->values()->all();

        $teamGroups = collect($this->employeeMetrics)->groupBy('team');
        $teamLabels = $teamGroups->keys()->values()->all();
        $teamProductivity = $teamGroups->map(fn ($g) => round($g->avg('productivity_pct'), 1))->values()->all();

        $kpis = $this->getGlobalKpis();

        $statusDist = Task::where('company_id', $this->companyId)
            ->when(! empty($employeeIds), fn ($q) => $q->whereIn('assignee_id', $employeeIds))
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $categoryDist = Task::where('company_id', $this->companyId)
            ->when(! empty($employeeIds), fn ($q) => $q->whereIn('assignee_id', $employeeIds))
            ->whereBetween('created_at', [$this->from, $this->to])
            ->selectRaw('category, COUNT(*) as cnt')
            ->groupBy('category')
            ->pluck('cnt', 'category');

        $goalEvolution = $this->buildGoalEvolution($evolution);

        return [
            'evolution' => ['labels' => $labels, 'hours' => $evolutionHours],
            'by_employee' => [
                'labels' => $employeeLabels,
                'hours' => $employeeHours,
                'efficiency' => $employeeEfficiency,
                'completion' => $employeeCompletion,
                'scores' => $employeeScores,
            ],
            'by_team' => ['labels' => $teamLabels, 'productivity' => $teamProductivity],
            'task_status' => [
                'completed' => $kpis['total_completed'],
                'pending' => $kpis['total_pending'],
                'overdue' => $kpis['total_overdue'],
            ],
            'status_distribution' => $statusDist->all(),
            'category_distribution' => $categoryDist->all(),
            'goal_evolution' => $goalEvolution,
            'trend' => $kpis['growth_pct'],
        ];
    }

    /** @return array{labels: array<int, string>, values: array<int, float|int>} */
    protected function buildGoalEvolution(?Collection $dailyEvolution = null): array
    {
        $employeeIds = $this->employees->pluck('id')->all();
        $from = $this->from->toDateString();
        $to = $this->to->toDateString();

        if ($dailyEvolution === null) {
            $dailyEvolution = Daily::where('company_id', $this->companyId)
                ->when(! empty($employeeIds), fn ($q) => $q->whereIn('employee_id', $employeeIds))
                ->whereBetween('work_date', [$from, $to])
                ->selectRaw('DATE(work_date) as day, SUM(hours) as hours')
                ->groupBy('day')
                ->get()
                ->keyBy('day');
        } else {
            $dailyEvolution = $dailyEvolution->keyBy('day');
        }

        $goalLabels = [];
        $goalEvolution = [];
        $weekCursor = $this->from->copy()->startOfWeek();
        $headcount = max(1, count($employeeIds));

        while ($weekCursor->lte($this->to)) {
            $weekEnd = $weekCursor->copy()->endOfWeek()->min($this->to);
            $weekHours = 0.0;
            $cursor = $weekCursor->copy();
            while ($cursor->lte($weekEnd)) {
                $weekHours += (float) ($dailyEvolution->get($cursor->toDateString())?->hours ?? 0);
                $cursor->addDay();
            }
            $weekTarget = $this->countBusinessDays($weekCursor, $weekEnd) * self::DAILY_HOURS_TARGET * $headcount;
            $goalLabels[] = $weekCursor->format('d/m');
            $goalEvolution[] = $weekTarget > 0 ? min(100, round(($weekHours / $weekTarget) * 100)) : 0;
            $weekCursor->addWeek();
        }

        return ['labels' => $goalLabels, 'values' => $goalEvolution];
    }

    /** @return array<int, array<string, mixed>> */
    protected function buildRanking(): array
    {
        $all = collect($this->employeeMetrics);

        return [
            'general' => $all->take(15)->values()->all(),
            'growth' => $all->sortByDesc('growth')->take(5)->values()->all(),
            'decline' => $all->sortBy('growth')->take(5)->values()->all(),
            'efficiency' => $all->sortByDesc('efficiency')->take(5)->values()->all(),
            'overdue' => $all->sortByDesc('overdue')->take(5)->values()->all(),
            'consistency' => $all->sortByDesc('consistency')->take(5)->values()->all(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function buildAlerts(): array
    {
        $alerts = [];
        $avgScore = collect($this->employeeMetrics)->avg('score') ?: 0;
        $avgProductivity = collect($this->employeeMetrics)->avg('productivity_pct') ?: 0;

        foreach ($this->employeeMetrics as $m) {
            $name = $m['employee']->name;

            if ($m['productivity_pct'] < $avgProductivity * 0.7) {
                $alerts[] = $this->alert('warning', 'Produtividade abaixo da média', "{$name} está com produtividade de {$m['productivity_pct']}% (média: ".round($avgProductivity, 1).'%).', $m);
            }
            if ($m['growth'] <= -20) {
                $alerts[] = $this->alert('danger', 'Queda brusca de produtividade', "{$name} teve queda de ".abs($m['growth']).'% em relação ao período anterior.', $m);
            }
            if ($m['overdue'] >= 3) {
                $alerts[] = $this->alert('danger', 'Excesso de atrasos', "{$name} possui {$m['overdue']} atividades com SLA estourado.", $m);
            }
            if ($m['pending'] >= 8) {
                $alerts[] = $this->alert('warning', 'Excesso de pendências', "{$name} acumula {$m['pending']} tarefas pendentes.", $m);
            }
            if ($m['days_worked'] === 0 && $m['employee']->status === 'active') {
                $alerts[] = $this->alert('info', 'Baixa atividade', "{$name} não registrou horas no período.", $m);
            }
            if ($m['consistency'] < 40) {
                $alerts[] = $this->alert('warning', 'Produtividade inconsistente', "{$name} apresenta consistência de apenas {$m['consistency']}%.", $m);
            }
            if (! $m['goal_met'] && $m['completed'] > 0) {
                $alerts[] = $this->alert('warning', 'Meta não atingida', "{$name} não atingiu a meta do período.", $m);
            }
            if ($m['score'] < $avgScore * 0.5) {
                $alerts[] = $this->alert('danger', 'Risco operacional', "{$name} apresenta score crítico ({$m['score']}).", $m);
            }
        }

        usort($alerts, fn ($a, $b) => ['danger' => 0, 'warning' => 1, 'info' => 2][$a['severity']] <=> ['danger' => 0, 'warning' => 1, 'info' => 2][$b['severity']]);

        return array_slice($alerts, 0, 20);
    }

    /** @return array<string, mixed> */
    protected function alert(string $severity, string $title, string $message, array $metric): array
    {
        return [
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'employee_id' => $metric['employee']->id,
            'employee_name' => $metric['employee']->name,
        ];
    }

    /** @return array<int, string> */
    protected function buildInsights(array $kpis, array $ranking, array $alerts): array
    {
        $insights = [];

        if ($kpis['growth_pct'] > 0) {
            $insights[] = "Produtividade aumentou {$kpis['growth_pct']}% em relação ao período anterior.";
        } elseif ($kpis['growth_pct'] < 0) {
            $insights[] = 'Produtividade caiu '.abs($kpis['growth_pct']).'% em relação ao período anterior.';
        }

        $topTeam = collect($this->employeeMetrics)->groupBy('team')->map(fn ($g) => $g->avg('score'))->sortDesc()->keys()->first();
        if ($topTeam) {
            $insights[] = "Equipe \"{$topTeam}\" possui melhor performance média do período.";
        }

        $riskCount = collect($alerts)->where('severity', 'danger')->count();
        if ($riskCount > 0) {
            $insights[] = "{$riskCount} colaborador(es) apresentam alertas críticos de performance.";
        }

        if ($kpis['delay_rate'] > 0) {
            $insights[] = "Taxa de atraso operacional: {$kpis['delay_rate']}% das atividades.";
        }

        $topGrower = $ranking['growth'][0] ?? null;
        if ($topGrower && $topGrower['growth'] > 10) {
            $insights[] = "{$topGrower['employee']->name} teve crescimento de {$topGrower['growth']}% — destaque do período.";
        }

        if ($kpis['below_goal'] > 0) {
            $insights[] = "{$kpis['below_goal']} colaborador(es) abaixo da meta da empresa.";
        }

        if ($kpis['avg_execution_hours'] > 0) {
            $insights[] = 'Tempo médio de execução: '.number_format($kpis['avg_execution_hours'], 1, ',', '.').'h por atividade concluída.';
        }

        return $insights;
    }

    /** @return array<int, array<string, mixed>> */
    protected function buildAnalyticTable(): array
    {
        return collect($this->employeeMetrics)->map(fn ($m) => [
            'id' => $m['employee']->id,
            'name' => $m['employee']->name,
            'team' => $m['team'],
            'productivity' => $m['productivity_pct'],
            'score' => $m['score'],
            'efficiency' => $m['efficiency'],
            'completed' => $m['completed'],
            'pending' => $m['pending'],
            'overdue' => $m['overdue'],
            'completion_rate' => $m['completion_rate'],
            'avg_hours' => $m['avg_execution_hours'],
            'growth' => $m['growth'],
            'trend' => $m['trend'],
            'status' => $m['level'],
            'goal_met' => $m['goal_met'],
            'rank' => $m['rank'],
            'alerts' => $m['overdue'] + ($m['goal_met'] ? 0 : 1),
        ])->values()->all();
    }

    /** @return array<string, mixed> */
    protected function buildGoalsSummary(): array
    {
        $companyGoals = ProductivityGoal::where('company_id', $this->companyId)
            ->whereNull('employee_id')
            ->get()
            ->keyBy('period_type');

        $metrics = collect($this->employeeMetrics);
        $total = max(1, $metrics->count());
        $metCount = $metrics->where('goal_met', true)->count();

        return [
            'daily_hours' => (float) ($companyGoals->get('daily')?->hours_target ?? self::DAILY_HOURS_TARGET),
            'weekly_tasks' => (int) ($companyGoals->get('weekly')?->tasks_target ?? self::DAILY_TASKS_TARGET * 5),
            'monthly_rate' => (float) ($companyGoals->get('monthly')?->completion_rate_target ?? self::COMPLETION_RATE_TARGET),
            'achievement_pct' => round(($metCount / $total) * 100, 1),
            'met_count' => $metCount,
            'total_count' => $metrics->count(),
            'forecast' => min(100, round($metrics->avg('productivity_pct') ?: 0, 1)),
        ];
    }

    /** @return array<string, mixed> */
    protected function buildHistory(): array
    {
        $employeeIds = $this->employees->pluck('id')->all();
        $now = Carbon::now(self::TZ)->startOfMonth();
        $rangeStart = $now->copy()->subMonths(5)->startOfMonth();

        $hoursByMonth = Daily::where('company_id', $this->companyId)
            ->when(! empty($employeeIds), fn ($q) => $q->whereIn('employee_id', $employeeIds))
            ->whereBetween('work_date', [$rangeStart->toDateString(), $now->copy()->endOfMonth()->toDateString()])
            ->selectRaw('YEAR(work_date) as y, MONTH(work_date) as m, SUM(hours) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn ($r) => $r->y.'-'.$r->m);

        $completedByMonth = Task::where('company_id', $this->companyId)
            ->when(! empty($employeeIds), fn ($q) => $q->whereIn('assignee_id', $employeeIds))
            ->where('status', 'completed')
            ->where('completed_at', '>=', $rangeStart)
            ->selectRaw('YEAR(completed_at) as y, MONTH(completed_at) as m, COUNT(*) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn ($r) => $r->y.'-'.$r->m);

        $months = [];
        $headcount = max(1, count($employeeIds));

        for ($i = 5; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i);
            $key = $start->format('Y').'-'.(int) $start->format('n');
            $hours = (float) ($hoursByMonth->get($key)?->total ?? 0);
            $completed = (int) ($completedByMonth->get($key)?->total ?? 0);
            $biz = $this->countBusinessDays($start, $start->copy()->endOfMonth());
            $target = $biz * self::DAILY_HOURS_TARGET * $headcount;

            $months[] = [
                'label' => $start->translatedFormat('M/y'),
                'hours' => round($hours, 1),
                'completed' => $completed,
                'productivity' => $target > 0 ? round(($hours / $target) * 100, 1) : 0,
            ];
        }

        return ['monthly' => $months];
    }

    /** @return array<string, mixed> */
    protected function buildComparatives(): array
    {
        $current = collect($this->employeeMetrics);
        $prev = $this->buildPreviousPeriodSummary();

        return [
            'productivity_current' => round($current->avg('productivity_pct') ?: 0, 1),
            'productivity_previous' => $prev['productivity'],
            'completed_current' => $current->sum('completed'),
            'completed_previous' => $prev['completed'],
        ];
    }

    /** @return array<string, mixed> */
    protected function buildEmployeeDetail(Employee $employee): array
    {
        $m = $this->employeeMetrics[$employee->id] ?? null;
        if (! $m) {
            return [];
        }

        $from = $this->from->toDateString();
        $to = $this->to->toDateString();

        $dailySeries = Daily::where('company_id', $this->companyId)
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$from, $to])
            ->selectRaw('DATE(work_date) as day, SUM(hours) as hours')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $dayLabels = $dailySeries->map(fn ($r) => Carbon::parse($r->day)->format('d/m'))->all();
        $dayHours = $dailySeries->map(fn ($r) => round((float) $r->hours, 1))->all();

        $bestDay = $dailySeries->sortByDesc('hours')->first();
        $worstDay = $dailySeries->where('hours', '>', 0)->sortBy('hours')->first();

        $categoryBreakdown = Task::where('company_id', $this->companyId)
            ->where('assignee_id', $employee->id)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->selectRaw('category, COUNT(*) as cnt')
            ->groupBy('category')
            ->pluck('cnt', 'category');

        $teamAvg = collect($this->employeeMetrics)
            ->where('team', $m['team'])
            ->avg('productivity_pct') ?: 0;

        $hourlyMap = Daily::where('company_id', $this->companyId)
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$from, $to])
            ->selectRaw('HOUR(created_at) as hr, SUM(hours) as total')
            ->groupBy('hr')
            ->pluck('total', 'hr');

        return array_merge($m, [
            'hire_date' => $employee->hire_date?->format('d/m/Y'),
            'position' => $employee->position,
            'role' => $employee->role,
            'status' => $employee->status,
            'charts' => [
                'daily' => ['labels' => $dayLabels, 'hours' => $dayHours],
                'categories' => $categoryBreakdown->all(),
                'hourly' => $hourlyMap->all(),
            ],
            'best_day' => $bestDay ? ['date' => Carbon::parse($bestDay->day)->format('d/m/Y'), 'hours' => $bestDay->hours] : null,
            'worst_day' => $worstDay ? ['date' => Carbon::parse($worstDay->day)->format('d/m/Y'), 'hours' => $worstDay->hours] : null,
            'team_avg_productivity' => round($teamAvg, 1),
            'vs_team' => round($m['productivity_pct'] - $teamAvg, 1),
        ]);
    }

    /** @return array<string, mixed> */
    protected function buildFilterOptions(): array
    {
        return [
            'employees' => Employee::where('company_id', $this->companyId)->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'teams' => Employee::where('company_id', $this->companyId)->whereNotNull('position')->distinct()->orderBy('position')->pluck('position'),
            'projects' => Project::where('company_id', $this->companyId)->orderBy('name')->get(['id', 'name', 'client_id']),
            'categories' => Task::CATEGORIES,
            'statuses' => Task::STATUSES,
            'priorities' => Task::PRIORITIES,
        ];
    }

    protected function goalFor(int $employeeId, string $field): float|int
    {
        if ($this->goalsCache === null) {
            $this->goalsCache = ProductivityGoal::where('company_id', $this->companyId)->get();
        }

        $individual = $this->goalsCache->first(fn ($g) => $g->employee_id === $employeeId);
        $company = $this->goalsCache->first(fn ($g) => $g->employee_id === null);

        return (float) ($individual?->$field ?? $company?->$field ?? match ($field) {
            'hours_target' => self::DAILY_HOURS_TARGET,
            'tasks_target' => self::DAILY_TASKS_TARGET,
            'completion_rate_target' => self::COMPLETION_RATE_TARGET,
            default => 0,
        });
    }

    protected function calcEfficiency(float $completionRate, float $consistency, int $overdue, int $pending): float
    {
        $penalty = min(40, ($overdue * 5) + ($pending > 10 ? 10 : 0));

        return max(0, min(100, round(($completionRate * 0.5) + ($consistency * 0.3) + 20 - $penalty, 1)));
    }

    protected function calcScore(float $productivity, float $completion, float $efficiency, float $consistency): float
    {
        return round(
            ($productivity * 0.35) + ($completion * 0.25) + ($efficiency * 0.25) + ($consistency * 0.15),
            1
        );
    }

    protected function performanceLevel(float $score): string
    {
        return match (true) {
            $score >= 80 => 'high',
            $score >= 60 => 'stable',
            $score >= 40 => 'attention',
            default => 'critical',
        };
    }

    protected function countBusinessDays(Carbon $from, Carbon $to): int
    {
        $count = 0;
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            if (! $cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }

        return max(1, $count);
    }

    protected function periodLabel(): string
    {
        return match ($this->filters['period']) {
            'today' => 'Hoje',
            'week' => 'Esta semana',
            'custom' => $this->from->format('d/m/Y').' — '.$this->to->format('d/m/Y'),
            default => $this->from->translatedFormat('F Y'),
        };
    }
}
