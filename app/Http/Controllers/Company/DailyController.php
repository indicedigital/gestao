<?php

namespace App\Http\Controllers\Company;

use App\Exports\DailiesExport;
use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\Daily;
use App\Models\Employee;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use App\Rules\BelongsToCompany;
use App\Support\DurationFormatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class DailyController extends Controller
{
    use InteractsWithCompany;

    private const TZ = 'America/Sao_Paulo';

    public function index(Request $request)
    {
        abort_unless(
            $this->authz()->canRegisterOwnDailies() || $this->authz()->canViewTeamDailies(),
            403,
            'Sem permissão para acessar o Daily.'
        );

        if ($this->authz()->canViewTeamDailies()) {
            return $this->adminIndex($request);
        }

        $company = $this->getCurrentCompany();
        $authz = $this->authz();
        $brNow = Carbon::now(self::TZ);

        $date = $request->query('date', $brNow->toDateString());
        $selectedDate = Carbon::parse($date, self::TZ)->startOfDay();

        $monthParam = $request->query('month', $selectedDate->format('Y-m'));
        $monthStart = Carbon::createFromFormat('Y-m', $monthParam, self::TZ)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $dailies = Daily::where('company_id', $company->id)
            ->where('user_id', Auth::id())
            ->whereDate('work_date', $date)
            ->with(['task:id,title', 'subtask:id,title', 'project:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $employee = Employee::where('company_id', $company->id)
            ->where('email', Auth::user()->email)
            ->first(['id', 'daily_hours_goal', 'monthly_hours_goal']);

        $dailyTarget = $employee?->resolveDailyHoursGoal() ?? Employee::DEFAULT_DAILY_HOURS_GOAL;

        $dayTotal = (float) $dailies->sum('hours');
        $dayProgress = $employee
            ? $employee->dailyProgress($dayTotal)
            : min(100, (int) round(($dayTotal / Employee::DEFAULT_DAILY_HOURS_GOAL) * 100));

        $tasksQuery = Task::where('company_id', $company->id)
            ->where('status', '!=', 'completed')
            ->with(['project:id,name', 'subtasks:id,task_id,title']);

        if (! $authz->hasFullDataScope('tasks')) {
            $authz->applyTaskScope($tasksQuery);
        }

        $tasks = $tasksQuery->orderBy('title')->get(['id', 'title', 'project_id', 'assignee_id']);

        $monthStats = Daily::where('company_id', $company->id)
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('SUM(hours) as total_hours, COUNT(DISTINCT work_date) as days_worked, COUNT(*) as entries_count')
            ->first();

        $monthTotalHours = (float) ($monthStats->total_hours ?? 0);
        $monthDaysWorked = (int) ($monthStats->days_worked ?? 0);
        $monthEntries = (int) ($monthStats->entries_count ?? 0);

        $businessDays = $this->businessDaysInMonth($monthStart);
        $monthTargetHours = $employee
            ? $employee->resolveMonthlyHoursGoal($businessDays)
            : $businessDays * Employee::DEFAULT_DAILY_HOURS_GOAL;
        $monthProgress = $monthTargetHours > 0
            ? min(100, round(($monthTotalHours / $monthTargetHours) * 100))
            : 0;

        $hoursByDay = Daily::where('company_id', $company->id)
            ->where('user_id', Auth::id())
            ->whereBetween('work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('DATE(work_date) as day, SUM(hours) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $maxDayHours = max(1.0, (float) $hoursByDay->max());

        $calendarDays = [];
        $pad = $monthStart->dayOfWeek;
        for ($i = 0; $i < $pad; $i++) {
            $calendarDays[] = ['empty' => true];
        }
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            $key = $cursor->toDateString();
            $hours = (float) ($hoursByDay[$key] ?? 0);
            $calendarDays[] = [
                'empty' => false,
                'date' => $key,
                'day' => $cursor->day,
                'hours' => $hours,
                'intensity' => $hours > 0 ? max(20, round(($hours / $maxDayHours) * 100)) : 0,
                'is_today' => $key === $brNow->toDateString(),
                'is_selected' => $key === $date,
                'is_weekend' => $cursor->isWeekend(),
            ];
            $cursor->addDay();
        }

        $historyDays = Daily::where('company_id', $company->id)
            ->where('user_id', Auth::id())
            ->where('work_date', '>=', $brNow->copy()->subDays(45)->toDateString())
            ->selectRaw('DATE(work_date) as day, SUM(hours) as total, COUNT(*) as entries')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(18)
            ->get();

        $prevDate = $selectedDate->copy()->subDay()->toDateString();
        $nextDate = $selectedDate->copy()->addDay()->toDateString();
        $monthLabel = $this->monthLabelPt($monthStart);
        $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');

        return view('company.dailies.index', compact(
            'company',
            'dailies',
            'date',
            'dayTotal',
            'dayProgress',
            'dailyTarget',
            'tasks',
            'monthStats',
            'monthTotalHours',
            'monthDaysWorked',
            'monthEntries',
            'monthTargetHours',
            'monthProgress',
            'monthLabel',
            'monthParam',
            'calendarDays',
            'historyDays',
            'prevDate',
            'nextDate',
            'prevMonth',
            'nextMonth',
            'businessDays',
        ));
    }

    public function adminIndex(Request $request)
    {
        abort_unless($this->authz()->canViewTeamDailies(), 403);

        $company = $this->getCurrentCompany();
        $brNow = Carbon::now(self::TZ);

        $date = $request->query('date', $brNow->toDateString());
        $viewMode = in_array($request->query('view'), ['table', 'cards'], true)
            ? $request->query('view')
            : 'cards';
        $search = trim((string) $request->query('q', ''));

        $employees = Employee::where('company_id', $company->id)
            ->where('status', 'active')
            ->forOperationalMetrics()
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('position', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'position', 'role', 'daily_hours_goal', 'monthly_hours_goal']);

        $dayStats = Daily::where('company_id', $company->id)
            ->whereDate('work_date', $date)
            ->selectRaw('employee_id, user_id, SUM(hours) as total_hours, COUNT(*) as entries')
            ->groupBy('employee_id', 'user_id')
            ->get();

        $userIdsByEmail = User::whereIn('email', $employees->pluck('email')->filter()->unique())
            ->pluck('id', 'email');

        $collaborators = $employees->map(function (Employee $employee) use ($dayStats, $userIdsByEmail) {
            $userId = $employee->email ? ($userIdsByEmail[$employee->email] ?? null) : null;

            $rows = $dayStats->filter(function ($row) use ($employee, $userId) {
                if ($row->employee_id && (int) $row->employee_id === (int) $employee->id) {
                    return true;
                }

                return $userId && (int) $row->user_id === (int) $userId;
            });

            $totalHours = (float) $rows->sum('total_hours');
            $entries = (int) $rows->sum('entries');
            $dailyTarget = $employee->resolveDailyHoursGoal();
            $progress = $employee->dailyProgress($totalHours);

            return [
                'employee' => $employee,
                'total_hours' => $totalHours,
                'entries' => $entries,
                'daily_target' => $dailyTarget,
                'progress' => $progress,
                'has_entries' => $entries > 0,
            ];
        });

        $teamDayTotal = (float) $collaborators->sum('total_hours');
        $teamWithEntries = $collaborators->where('has_entries', true)->count();
        $selectedDate = Carbon::parse($date, self::TZ);

        return view('company.dailies.admin-index', compact(
            'company',
            'date',
            'viewMode',
            'search',
            'collaborators',
            'teamDayTotal',
            'teamWithEntries',
            'selectedDate',
        ));
    }

    public function showCollaborator(Request $request, Employee $employee)
    {
        abort_unless($this->authz()->canViewTeamDailies(), 403);

        $company = $this->getCurrentCompany();
        if ($employee->company_id !== $company->id || ! $employee->isTecnico()) {
            abort(404);
        }

        $brNow = Carbon::now(self::TZ);
        $date = $request->query('date', $brNow->toDateString());
        $monthParam = $request->query('month', Carbon::parse($date, self::TZ)->format('Y-m'));

        $monthStart = Carbon::createFromFormat('Y-m', $monthParam, self::TZ)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $userId = $employee->email
            ? User::where('email', $employee->email)->value('id')
            : null;

        $dailyScope = fn ($query) => $query->where('company_id', $company->id)
            ->where(function ($q) use ($employee, $userId) {
                $q->where('employee_id', $employee->id);
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            });

        $dailies = Daily::query()
            ->tap($dailyScope)
            ->whereDate('work_date', $date)
            ->with(['task:id,title', 'subtask:id,title', 'project:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $dayTotal = (float) $dailies->sum('hours');
        $dailyTarget = $employee->resolveDailyHoursGoal();
        $dayProgress = $employee->dailyProgress($dayTotal);

        $historyDays = Daily::query()
            ->tap($dailyScope)
            ->where('work_date', '>=', $brNow->copy()->subDays(90)->toDateString())
            ->selectRaw('DATE(work_date) as day, SUM(hours) as total, COUNT(*) as entries')
            ->groupBy('day')
            ->orderByDesc('day')
            ->get();

        $hoursByDay = Daily::query()
            ->tap($dailyScope)
            ->whereBetween('work_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->selectRaw('DATE(work_date) as day, SUM(hours) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $maxDayHours = max(1.0, (float) $hoursByDay->max());

        $calendarDays = [];
        $pad = $monthStart->dayOfWeek;
        for ($i = 0; $i < $pad; $i++) {
            $calendarDays[] = ['empty' => true];
        }
        $cursor = $monthStart->copy();
        while ($cursor->lte($monthEnd)) {
            $key = $cursor->toDateString();
            $hours = (float) ($hoursByDay[$key] ?? 0);
            $calendarDays[] = [
                'empty' => false,
                'date' => $key,
                'day' => $cursor->day,
                'hours' => $hours,
                'intensity' => $hours > 0 ? max(20, round(($hours / $maxDayHours) * 100)) : 0,
                'is_today' => $key === $brNow->toDateString(),
                'is_selected' => $key === $date,
            ];
            $cursor->addDay();
        }

        $prevDate = Carbon::parse($date, self::TZ)->subDay()->toDateString();
        $nextDate = Carbon::parse($date, self::TZ)->addDay()->toDateString();
        $monthLabel = $this->monthLabelPt($monthStart);
        $prevMonth = $monthStart->copy()->subMonth()->format('Y-m');
        $nextMonth = $monthStart->copy()->addMonth()->format('Y-m');

        return view('company.dailies.admin-show', compact(
            'company',
            'employee',
            'dailies',
            'date',
            'dayTotal',
            'dayProgress',
            'dailyTarget',
            'historyDays',
            'calendarDays',
            'monthParam',
            'monthLabel',
            'prevDate',
            'nextDate',
            'prevMonth',
            'nextMonth',
        ));
    }

    public function store(Request $request)
    {
        abort_unless($this->authz()->canRegisterOwnDailies(), 403, 'Sem permissão para registrar daily.');

        abort_if($this->authz()->canViewTeamDailies(), 403, 'Administradores utilizam a visão de equipe para consultar dailies.');

        $company = $this->getCurrentCompany();

        $validated = $request->validate([
            'task_id' => ['required', new BelongsToCompany('tasks', $company->id)],
            'subtask_id' => ['nullable', 'exists:subtasks,id'],
            'work_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:5000'],
            'duration_value' => ['required', 'numeric'],
            'duration_unit' => ['required', 'in:hours,minutes'],
            'blockers' => ['nullable', 'string', 'max:2000'],
        ]);

        $hours = DurationFormatter::toHours(
            (float) $validated['duration_value'],
            $validated['duration_unit']
        );

        $durationCheck = DurationFormatter::validateHours($hours, $validated['duration_unit']);
        if (! $durationCheck['valid']) {
            throw ValidationException::withMessages([
                'duration_value' => $durationCheck['message'],
            ]);
        }

        $task = Task::where('company_id', $company->id)->findOrFail($validated['task_id']);
        abort_unless($this->authz()->canRegisterDaily($task), 403);

        if (! empty($validated['subtask_id'])) {
            $belongs = Subtask::where('task_id', $task->id)
                ->whereKey($validated['subtask_id'])
                ->exists();
            if (! $belongs) {
                return back()->withErrors(['subtask_id' => 'Subtask não pertence à task selecionada.']);
            }
        }

        $employee = Employee::where('company_id', $company->id)
            ->where('email', Auth::user()->email)
            ->first(['id']);

        Daily::create([
            'company_id' => $company->id,
            'user_id' => Auth::id(),
            'employee_id' => $employee?->id,
            'project_id' => $task->project_id,
            'task_id' => $task->id,
            'subtask_id' => $validated['subtask_id'] ?? null,
            'work_date' => $validated['work_date'],
            'description' => $validated['description'],
            'hours' => $hours,
            'blockers' => $validated['blockers'] ?? null,
        ]);

        $task->recalculateActualHours();
        if (! empty($validated['subtask_id'])) {
            Subtask::find($validated['subtask_id'])?->recalculateHoursSpent();
        }

        return back()->with('success', 'Daily registrada com sucesso!');
    }

    public function destroy(Daily $daily)
    {
        $company = $this->getCurrentCompany();

        if ($daily->company_id !== $company->id || $daily->user_id !== Auth::id()) {
            abort(403);
        }

        $task = $daily->task;
        $subtask = $daily->subtask;
        $daily->delete();

        $task?->recalculateActualHours();
        $subtask?->recalculateHoursSpent();

        return back()->with('success', 'Registro removido.');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($this->authz()->canViewProductivity(), 403);

        $company = $this->getCurrentCompany();

        $technicalIds = Employee::where('company_id', $company->id)
            ->forOperationalMetrics()
            ->pluck('id');

        $dailies = Daily::where('company_id', $company->id)
            ->whereIn('employee_id', $technicalIds)
            ->whereYear('work_date', now()->year)
            ->whereMonth('work_date', now()->month)
            ->with(['task', 'project', 'employee', 'user'])
            ->orderBy('work_date')
            ->get();

        return Excel::download(new DailiesExport($dailies), 'dailies-'.now()->format('Y-m').'.xlsx');
    }

    protected function businessDaysInMonth(Carbon $monthStart): int
    {
        $end = $monthStart->copy()->endOfMonth();
        $count = 0;
        $cursor = $monthStart->copy();

        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }

        return max(1, $count);
    }

    protected function monthLabelPt(Carbon $month): string
    {
        $months = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];

        return ($months[(int) $month->format('n')] ?? '').' de '.$month->format('Y');
    }
}
