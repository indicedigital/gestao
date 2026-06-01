<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\Daily;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeveloperDashboardController extends Controller
{
    use InteractsWithCompany;

    private const TZ = 'America/Sao_Paulo';

    public function index(Request $request)
    {
        abort_unless($this->authz()->canAccessModule('developer_dashboard'), 403);

        $company = $this->getCurrentCompany();
        $authz = $this->authz();
        $user = Auth::user();
        $employeeIds = $authz->employeeIds();

        $brNow = $this->brNow();
        $today = $brNow->copy()->startOfDay();
        $todayDate = $today->toDateString();
        $weekStart = $brNow->copy()->startOfWeek();
        $monthStart = $brNow->copy()->startOfMonth();

        $greeting = $this->greeting($brNow);
        $dateLabel = $this->dateLabelPt($brNow);
        $dateLabelShort = $this->shortDayLabel($brNow);
        $timeLabel = $brNow->format('H:i');

        $assignedTaskQuery = fn () => Task::where('company_id', $company->id)
            ->whereIn('assignee_id', $employeeIds);

        $pendingCount = $assignedTaskQuery()->where('status', '!=', 'completed')->count();
        $inProgressCount = $assignedTaskQuery()->where('status', 'in_progress')->count();
        $reviewCount = $assignedTaskQuery()->where('status', 'review')->count();
        $overdueCount = $assignedTaskQuery()
            ->where('status', '!=', 'completed')
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', $brNow)
            ->count();

        $completedWeek = $assignedTaskQuery()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $weekStart)
            ->count();

        $completedMonth = $assignedTaskQuery()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $monthStart)
            ->count();

        $totalAssigned = $assignedTaskQuery()->count();
        $completedTotal = $assignedTaskQuery()->where('status', 'completed')->count();
        $completionRate = $totalAssigned > 0 ? round(($completedTotal / $totalAssigned) * 100) : 0;

        $activeProjects = Project::where('company_id', $company->id)
            ->where(function ($q) use ($employeeIds) {
                $q->whereHas('tasks', fn ($t) => $t->whereIn('assignee_id', $employeeIds)->where('status', '!=', 'completed'))
                    ->orWhereHas('employees', fn ($e) => $e->whereIn('employees.id', $employeeIds));
            })
            ->count();

        $todayDailies = Daily::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->whereDate('work_date', $todayDate)
            ->with(['task:id,title', 'project:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $todayHours = (float) $todayDailies->sum('hours');
        $dailyTarget = 8.0;
        $todayProgress = min(100, round(($todayHours / $dailyTarget) * 100));

        $weekHours = (float) Daily::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('work_date', '>=', $weekStart)
            ->sum('hours');

        $monthHours = (float) Daily::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('work_date', '>=', $monthStart)
            ->sum('hours');

        $monthWorkDays = Daily::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('work_date', '>=', $monthStart)
            ->distinct('work_date')
            ->count('work_date');

        $upcomingTasks = $assignedTaskQuery()
            ->where('status', '!=', 'completed')
            ->with(['project:id,name'])
            ->orderByRaw("CASE priority WHEN 'P0' THEN 0 WHEN 'P1' THEN 1 WHEN 'P2' THEN 2 ELSE 3 END")
            ->orderByRaw('sla_deadline IS NULL, sla_deadline ASC')
            ->orderBy('updated_at', 'desc')
            ->limit(12)
            ->get();

        $recentCompleted = $assignedTaskQuery()
            ->where('status', 'completed')
            ->with(['project:id,name'])
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        $hoursLast7Days = Daily::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->where('work_date', '>=', $brNow->copy()->subDays(6)->startOfDay())
            ->selectRaw('DATE(work_date) as day, SUM(hours) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $hoursChartLabels = [];
        $hoursChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $brNow->copy()->subDays($i);
            $dayKey = $day->format('Y-m-d');
            $hoursChartLabels[] = $this->shortDayLabel($day);
            $hoursChartData[] = round((float) ($hoursLast7Days[$dayKey] ?? 0), 2);
        }

        $statusDistribution = $assignedTaskQuery()
            ->where('status', '!=', 'completed')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusChartLabels = [];
        $statusChartData = [];
        $statusChartColors = [];
        $statusColors = [
            'backlog' => '#8b949e',
            'todo' => '#5e72e4',
            'in_progress' => '#11cdef',
            'review' => '#9d8df7',
            'waiting_client' => '#fb6340',
            'homologation' => '#ffd600',
        ];
        foreach ($statusDistribution as $status => $total) {
            $statusChartLabels[] = Task::STATUSES[$status] ?? $status;
            $statusChartData[] = (int) $total;
            $statusChartColors[] = $statusColors[$status] ?? '#64748b';
        }

        $hoursByProject = Daily::query()
            ->where('dailies.company_id', $company->id)
            ->where('dailies.user_id', $user->id)
            ->where('dailies.work_date', '>=', $weekStart)
            ->leftJoin('projects', 'projects.id', '=', 'dailies.project_id')
            ->select('projects.name', DB::raw('SUM(dailies.hours) as total'))
            ->groupBy('projects.id', 'projects.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $priorityDistribution = $assignedTaskQuery()
            ->where('status', '!=', 'completed')
            ->select('priority', DB::raw('COUNT(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $estimatedOpenHours = (float) $assignedTaskQuery()
            ->where('status', '!=', 'completed')
            ->sum('estimated_hours');

        $actualOpenHours = (float) $assignedTaskQuery()
            ->where('status', '!=', 'completed')
            ->sum('actual_hours');

        $slaOnTime = $assignedTaskQuery()
            ->where('status', 'completed')
            ->whereNotNull('sla_deadline')
            ->whereNotNull('completed_at')
            ->whereColumn('completed_at', '<=', 'sla_deadline')
            ->count();

        $slaClosed = $assignedTaskQuery()
            ->where('status', 'completed')
            ->whereNotNull('sla_deadline')
            ->count();

        $slaRate = $slaClosed > 0 ? round(($slaOnTime / $slaClosed) * 100) : 100;

        return view('company.developer-dashboard.index', compact(
            'company',
            'user',
            'greeting',
            'dateLabel',
            'dateLabelShort',
            'timeLabel',
            'pendingCount',
            'inProgressCount',
            'reviewCount',
            'overdueCount',
            'completedWeek',
            'completedMonth',
            'completionRate',
            'activeProjects',
            'todayDailies',
            'todayHours',
            'dailyTarget',
            'todayProgress',
            'weekHours',
            'monthHours',
            'monthWorkDays',
            'upcomingTasks',
            'recentCompleted',
            'hoursChartLabels',
            'hoursChartData',
            'statusChartLabels',
            'statusChartData',
            'statusChartColors',
            'hoursByProject',
            'priorityDistribution',
            'estimatedOpenHours',
            'actualOpenHours',
            'slaRate',
        ));
    }

    protected function brNow(): Carbon
    {
        return Carbon::now(self::TZ);
    }

    protected function greeting(Carbon $now): string
    {
        $hour = (int) $now->format('G');

        if ($hour >= 5 && $hour < 12) {
            return 'Bom dia';
        }

        if ($hour >= 12 && $hour < 18) {
            return 'Boa tarde';
        }

        return 'Boa noite';
    }

    protected function dateLabelPt(Carbon $now): string
    {
        $weekdays = [
            0 => 'domingo', 1 => 'segunda-feira', 2 => 'terça-feira', 3 => 'quarta-feira',
            4 => 'quinta-feira', 5 => 'sexta-feira', 6 => 'sábado',
        ];
        $months = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];

        $weekday = $weekdays[$now->dayOfWeek] ?? '';
        $month = $months[(int) $now->format('n')] ?? '';

        return ucfirst($weekday).', '.$now->format('j').' de '.$month;
    }

    protected function shortDayLabel(Carbon $day): string
    {
        $abbr = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        return ($abbr[$day->dayOfWeek] ?? '').' '.$day->format('d/m');
    }
}
