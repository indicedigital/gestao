<?php

namespace App\Http\Controllers\Company;

use App\Exports\DailiesExport;
use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\Daily;
use App\Models\Employee;
use App\Models\Subtask;
use App\Models\Task;
use App\Rules\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DailyController extends Controller
{
    use InteractsWithCompany;

    private const TZ = 'America/Sao_Paulo';

    private const DAILY_TARGET = 8.0;

    public function index(Request $request)
    {
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

        $dayTotal = (float) $dailies->sum('hours');
        $dayProgress = min(100, round(($dayTotal / self::DAILY_TARGET) * 100));

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
        $monthTargetHours = $businessDays * self::DAILY_TARGET;
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

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();

        $validated = $request->validate([
            'task_id' => ['required', new BelongsToCompany('tasks', $company->id)],
            'subtask_id' => ['nullable', 'exists:subtasks,id'],
            'work_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:5000'],
            'hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'blockers' => ['nullable', 'string', 'max:2000'],
        ]);

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
            'hours' => $validated['hours'],
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

        $dailies = Daily::where('company_id', $company->id)
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
