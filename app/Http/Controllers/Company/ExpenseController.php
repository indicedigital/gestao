<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Company\Concerns\AuthorizesCompanyManagement;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payable;
use App\Models\Supplier;
use App\Rules\BelongsToCompany;
use App\Services\FixedExpenseService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    use AuthorizesCompanyManagement;

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

    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();
        $type = $request->get('type', 'fixed'); // fixed ou variable

        $listMonth = $this->parseExpenseListMonth($request->input('month'));
        $monthInput = $listMonth->format('Y-m');

        $categoryFilter = $this->resolveExpenseCategoryFilter($request, $company->id, $type);

        $noCategoryFilter = ['uncategorized' => false, 'category_id' => null];

        // Nova instância de query por chamada (evita LIMIT da paginação afetar sum/count).
        $expenses = $this->expensesIndexBaseQuery($company->id, $type, $categoryFilter, $listMonth)
            ->with(['category', 'supplier'])
            ->when($type === 'variable', fn ($q) => $q->orderByDesc('due_date')->orderByDesc('id'))
            ->when($type === 'fixed', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        $fixedCount = Expense::where('company_id', $company->id)->where('type', 'fixed')->count();
        $variableCount = Expense::where('company_id', $company->id)->where('type', 'variable')->count();

        $filteredTotal = $this->sumExpenseValues($this->expensesIndexBaseQuery($company->id, $type, $categoryFilter, $listMonth));
        $filteredCount = (int) $this->expensesIndexBaseQuery($company->id, $type, $categoryFilter, $listMonth)->count();
        $filteredAverage = $filteredCount > 0 ? round($filteredTotal / $filteredCount, 2) : 0.0;

        $typeTotal = $this->sumExpenseValues($this->expensesIndexBaseQuery($company->id, $type, $noCategoryFilter, $listMonth));
        $typeCount = (int) $this->expensesIndexBaseQuery($company->id, $type, $noCategoryFilter, $listMonth)->count();

        $hasUncategorized = $this->expensesIndexBaseQuery($company->id, $type, $noCategoryFilter, $listMonth)
            ->whereNull('expense_category_id')
            ->exists();

        $filterCategories = ExpenseCategory::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $categoryBreakdown = $this->buildExpenseCategoryBreakdown($company->id, $type, $listMonth);

        $selectedCategoryKey = $categoryFilter['uncategorized']
            ? 'none'
            : ($categoryFilter['category_id'] !== null ? (string) $categoryFilter['category_id'] : '');

        $hasCategoryFilter = $categoryFilter['uncategorized'] || $categoryFilter['category_id'] !== null;

        $tabQueryFixed = array_merge(['type' => 'fixed', 'month' => $monthInput], $selectedCategoryKey !== '' ? ['category_id' => $selectedCategoryKey] : []);
        $tabQueryVariable = array_merge(['type' => 'variable', 'month' => $monthInput], $selectedCategoryKey !== '' ? ['category_id' => $selectedCategoryKey] : []);

        $selectedMonthLabel = $listMonth->copy()->locale('pt_BR')->translatedFormat('F \d\e Y');

        // Detecta se é mobile
        $isMobile = $request->has('mobile') ||
                   $request->cookie('is_mobile') === '1' ||
                   (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));

        $kpiPayload = compact(
            'categoryBreakdown',
            'filteredTotal',
            'filteredCount',
            'filteredAverage',
            'typeTotal',
            'typeCount',
            'hasUncategorized',
            'filterCategories',
            'selectedCategoryKey',
            'hasCategoryFilter',
            'monthInput',
            'selectedMonthLabel',
            'tabQueryFixed',
            'tabQueryVariable'
        );

        if ($isMobile) {
            return view('company.expenses.index-mobile', compact('expenses', 'company', 'type', 'fixedCount', 'variableCount') + $kpiPayload);
        }

        return view('company.expenses.index', compact('expenses', 'company', 'type', 'fixedCount', 'variableCount') + $kpiPayload);
    }

    /**
     * Série mensal (jan–dez) para gráfico de evolução no ano.
     */
    public function monthlyEvolution(Request $request): JsonResponse
    {
        $company = $this->getCurrentCompany();

        $year = (int) $request->input('year', now()->year);
        $year = max(2000, min(2100, $year));

        $type = $request->input('type', 'variable');
        if (! in_array($type, ['fixed', 'variable'], true)) {
            $type = 'variable';
        }

        $categoryFilter = $this->resolveExpenseCategoryFilter($request, $company->id, $type);
        $noCategoryFilter = ['uncategorized' => false, 'category_id' => null];

        $labels = [];
        $sums = [];
        $counts = [];
        $typeSums = [];
        $typeCounts = [];

        for ($m = 1; $m <= 12; $m++) {
            $labels[] = Carbon::create($year, $m, 1)->locale('pt_BR')->translatedFormat('M');

            if ($type === 'variable') {
                $monthRef = Carbon::create($year, $m, 1)->startOfMonth();
                $sums[] = $this->sumExpenseValues($this->expensesIndexBaseQuery($company->id, 'variable', $categoryFilter, $monthRef));
                $counts[] = (int) $this->expensesIndexBaseQuery($company->id, 'variable', $categoryFilter, $monthRef)->count();
                $typeSums[] = $this->sumExpenseValues($this->expensesIndexBaseQuery($company->id, 'variable', $noCategoryFilter, $monthRef));
                $typeCounts[] = (int) $this->expensesIndexBaseQuery($company->id, 'variable', $noCategoryFilter, $monthRef)->count();
            } else {
                $monthRef = Carbon::create($year, $m, 1)->startOfMonth();
                $sums[] = $this->sumExpenseValues($this->expensesIndexBaseQuery($company->id, 'fixed', $categoryFilter, $monthRef));
                $counts[] = (int) $this->expensesIndexBaseQuery($company->id, 'fixed', $categoryFilter, $monthRef)->count();
                $typeSums[] = $this->sumExpenseValues($this->expensesIndexBaseQuery($company->id, 'fixed', $noCategoryFilter, $monthRef));
                $typeCounts[] = (int) $this->expensesIndexBaseQuery($company->id, 'fixed', $noCategoryFilter, $monthRef)->count();
            }
        }

        $averages = [];
        for ($i = 0; $i < 12; $i++) {
            $averages[] = $counts[$i] > 0 ? round($sums[$i] / $counts[$i], 2) : 0.0;
        }

        $shares = [];
        for ($i = 0; $i < 12; $i++) {
            $shares[] = $typeSums[$i] > 0 ? round(($sums[$i] / $typeSums[$i]) * 100, 2) : 0.0;
        }

        return response()->json([
            'year' => $year,
            'type' => $type,
            'labels' => $labels,
            'sums' => $sums,
            'counts' => $counts,
            'averages' => $averages,
            'type_sums' => $typeSums,
            'type_counts' => $typeCounts,
            'shares' => $shares,
        ]);
    }

    /**
     * Mês da listagem (Y-m). Inválido ou ausente → mês atual.
     */
    protected function parseExpenseListMonth(?string $input): Carbon
    {
        if (! $input || ! preg_match('/^\d{4}-\d{2}$/', $input)) {
            return now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $input)->startOfMonth();
        } catch (\Throwable) {
            return now()->startOfMonth();
        }
    }

    /**
     * Despesas variáveis: vencimento (due_date) dentro do mês. Fixas: todo cadastro (valor mensal, sem data única na linha).
     */
    protected function applyExpenseDueMonthScope(\Illuminate\Database\Eloquent\Builder $query, string $type, Carbon $monthRef): void
    {
        if ($type !== 'variable') {
            return;
        }

        $start = $monthRef->copy()->startOfMonth()->toDateString();
        $end = $monthRef->copy()->endOfMonth()->toDateString();

        $query->whereBetween('due_date', [$start, $end]);
    }

    /**
     * Soma SQL de value (evita inconsistência de cast em drivers).
     */
    protected function sumExpenseValues(\Illuminate\Database\Eloquent\Builder $query): float
    {
        $sum = $query->sum('value');

        return round((float) ($sum ?? 0), 2);
    }

    /**
     * Query base da listagem de despesas (sempre instância nova — evita LIMIT da paginação vazar no sum).
     *
     * @param  array{uncategorized: bool, category_id: int|null}  $categoryFilter
     */
    protected function expensesIndexBaseQuery(int $companyId, string $type, array $categoryFilter, Carbon $listMonth): \Illuminate\Database\Eloquent\Builder
    {
        $query = Expense::where('company_id', $companyId)->where('type', $type);

        $this->applyExpenseDueMonthScope($query, $type, $listMonth);

        return $this->applyExpenseCategoryFilter($query, $categoryFilter);
    }

    /**
     * @param  array{uncategorized: bool, category_id: int|null}  $categoryFilter
     */
    protected function applyExpenseCategoryFilter(Builder $query, array $categoryFilter): Builder
    {
        return $query
            ->when($categoryFilter['uncategorized'], fn ($q) => $q->whereNull('expense_category_id'))
            ->when(
                $categoryFilter['category_id'] !== null,
                fn ($q) => $q->where('expense_category_id', $categoryFilter['category_id'])
            );
    }

    /**
     * @return array{uncategorized: bool, category_id: int|null}
     */
    protected function resolveExpenseCategoryFilter(Request $request, int $companyId, string $type): array
    {
        $raw = $request->input('category_id');

        if ($raw === 'none') {
            return ['uncategorized' => true, 'category_id' => null];
        }

        if ($raw === null || $raw === '') {
            return ['uncategorized' => false, 'category_id' => null];
        }

        $id = (int) $raw;
        if ($id <= 0) {
            return ['uncategorized' => false, 'category_id' => null];
        }

        $exists = ExpenseCategory::where('company_id', $companyId)->whereKey($id)->exists();

        return [
            'uncategorized' => false,
            'category_id' => $exists ? $id : null,
        ];
    }

    /**
     * Totais por categoria de despesa para o tipo (fixa/variável), ignorando filtro da listagem.
     *
     * @return \Illuminate\Support\Collection<int, array{id: int|null, name: string, color: string, total: float, count: int}>
     */
    protected function buildExpenseCategoryBreakdown(int $companyId, string $type, Carbon $listMonth): \Illuminate\Support\Collection
    {
        $rows = Expense::query()
            ->where('company_id', $companyId)
            ->where('type', $type);

        $this->applyExpenseDueMonthScope($rows, $type, $listMonth);

        $rows = $rows
            ->select('expense_category_id', DB::raw('SUM(value) as total_sum'), DB::raw('COUNT(*) as expense_count'))
            ->groupBy('expense_category_id')
            ->get();

        $catIds = $rows->pluck('expense_category_id')->filter()->unique()->values();
        $categories = $catIds->isEmpty()
            ? collect()
            : ExpenseCategory::where('company_id', $companyId)->whereIn('id', $catIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($categories) {
            if ($row->expense_category_id === null) {
                return [
                    'id' => null,
                    'name' => 'Sem categoria',
                    'color' => '#94a3b8',
                    'total' => round((float) $row->total_sum, 2),
                    'count' => (int) $row->expense_count,
                ];
            }

            $cat = $categories->get($row->expense_category_id);

            return [
                'id' => (int) $row->expense_category_id,
                'name' => $cat->name ?? '—',
                'color' => $cat->color ?? '#5e72e4',
                'total' => round((float) $row->total_sum, 2),
                'count' => (int) $row->expense_count,
            ];
        })->sortByDesc('total')->values();
    }

    public function create()
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $categories = ExpenseCategory::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        return view('company.expenses.create', compact('company', 'categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'type' => 'required|in:fixed,variable',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'expense_category_id' => ['nullable', new BelongsToCompany('expense_categories', $company->id)],
            'supplier_id' => ['nullable', new BelongsToCompany('suppliers', $company->id)],
            'due_date_day' => 'required_if:type,fixed|nullable|integer|min:1|max:31',
            'due_date' => 'required_if:type,variable|nullable|date',
            'is_paid' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['is_active'] = true;

        $expense = Expense::create($validated);
        
        // Se for despesa variável, cria uma conta a pagar
        if ($expense->type === 'variable') {
            $supplierName = $expense->supplier ? $expense->supplier->name : null;
            $isPaid = $request->boolean('is_paid');
            $payableData = [
                'company_id' => $company->id,
                'type' => 'service',
                'category' => 'other',
                'description' => $expense->description,
                'value' => $expense->value,
                'due_date' => $expense->due_date,
                'status' => $isPaid ? 'paid' : 'pending',
                'supplier_name' => $supplierName,
                'notes' => $expense->notes,
            ];

            if ($isPaid) {
                $payableData['paid_date'] = $expense->due_date;
                $payableData['payment_method'] = 'Pix';
            }

            Payable::create($payableData);
        } else {
            // Se for despesa fixa, gera as duplicatas
            $service = new FixedExpenseService();
            $service->generatePayablesForFixedExpenses();
        }

        return redirect()->route('company.expenses.index', [
            'type' => $expense->type,
            'month' => now()->format('Y-m'),
        ])->with('success', 'Despesa criada com sucesso!');
    }

    public function show(Expense $expense)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        return view('company.expenses.show', compact('expense', 'company'));
    }

    public function edit(Expense $expense)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        $categories = ExpenseCategory::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get();
        
        return view('company.expenses.edit', compact('expense', 'company', 'categories', 'suppliers'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        $validated = $request->validate([
            'type' => 'required|in:fixed,variable',
            'description' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'expense_category_id' => ['nullable', new BelongsToCompany('expense_categories', $company->id)],
            'supplier_id' => ['nullable', new BelongsToCompany('suppliers', $company->id)],
            'due_date_day' => 'required_if:type,fixed|nullable|integer|min:1|max:31',
            'due_date' => 'required_if:type,variable|nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $expense->update($validated);
        
        // Se for despesa fixa, atualiza as duplicatas pendentes
        if ($expense->type === 'fixed') {
            $service = new FixedExpenseService();
            $service->updatePendingPayablesForExpense($expense);
        }

        return redirect()->route('company.expenses.index', [
            'type' => $expense->type,
            'month' => now()->format('Y-m'),
        ])->with('success', 'Despesa atualizada com sucesso!');
    }

    public function destroy(Expense $expense)
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($expense, $company);
        
        $type = $expense->type;
        $expense->delete();

        return redirect()->route('company.expenses.index', [
            'type' => $type,
            'month' => now()->format('Y-m'),
        ])->with('success', 'Despesa removida com sucesso!');
    }

    protected function authorizeAccess(Expense $expense, Company $company): void
    {
        if ($expense->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}
