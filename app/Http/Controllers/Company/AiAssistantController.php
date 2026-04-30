<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FiscalEntryNote;
use App\Models\FiscalExitNote;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiAssistantController extends Controller
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

    public function chat(Request $request): JsonResponse
    {
        $company = $this->getCurrentCompany();

        $validated = $request->validate([
            'theme' => ['required', 'in:clientes,contratos,financeiro,contabil,fluxo_caixa,despesas'],
            'message' => ['required', 'string', 'max:3000'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'in:user,assistant,model'],
            'history.*.text' => ['required_with:history', 'string', 'max:4000'],
        ]);

        $apiKey = (string) config('services.gemini.api_key', '');
        if ($apiKey === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Configure GEMINI_API_KEY no .env para usar o assistente.',
            ], 422);
        }

        $theme = $validated['theme'];
        $userMessage = trim($validated['message']);
        $history = $this->normalizeHistory((array) ($validated['history'] ?? []));
        $context = $this->getThemeContext($company, $theme);

        $themeLabels = [
            'clientes' => 'Clientes',
            'contratos' => 'Contratos',
            'financeiro' => 'Financeiro',
            'contabil' => 'Contábil',
            'fluxo_caixa' => 'Fluxo de Caixa',
            'despesas' => 'Despesas',
        ];

        $systemPrompt = <<<PROMPT
Você é um analista financeiro/operacional para PMEs.
Responda SEMPRE em português do Brasil, de forma objetiva e útil.
Use apenas o contexto fornecido. Se algo não estiver no contexto, deixe claro.
Se houver cálculo, mostre a fórmula resumida.
Traga no máximo 8 bullets e, se fizer sentido, finalize com 2 ações recomendadas.
Considere o histórico da conversa para manter contexto entre mensagens.
PROMPT;

        $payload = [
            'contents' => $this->buildConversationContents($themeLabels[$theme], $context, $history, $userMessage),
            'generationConfig' => [
                'temperature' => 0.3,
                'topP' => 0.9,
                'maxOutputTokens' => 1800,
            ],
            'systemInstruction' => [
                'parts' => [[
                    'text' => $systemPrompt,
                ]],
            ],
        ];

        $httpClient = Http::timeout(45);
        $caBundlePath = (string) config('services.gemini.ca_bundle', '');
        $httpVerify = config('services.gemini.http_verify', true);
        if ($caBundlePath !== '') {
            $httpClient = $httpClient->withOptions(['verify' => $caBundlePath]);
        } else {
            $httpClient = $httpClient->withOptions(['verify' => $httpVerify]);
        }

        $configuredModel = (string) config('services.gemini.model', 'gemini-2.5-flash');
        $model = $this->resolveGeminiModel($httpClient, $apiKey, $configuredModel);
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $resp = $httpClient
            ->withHeaders(['x-goog-api-key' => $apiKey])
            ->post($url, $payload);

        if (! $resp->ok()) {
            return response()->json([
                'ok' => false,
                'message' => 'Falha ao consultar o Gemini.',
                'error' => $resp->json(),
            ], 502);
        }

        $text = $this->extractTextFromGeminiResponse((array) $resp->json());
        if (! is_string($text) || trim($text) === '') {
            $text = 'Não consegui gerar resposta com os dados atuais.';
        }

        return response()->json([
            'ok' => true,
            'answer' => trim($text),
            'theme' => $theme,
        ]);
    }

    protected function getThemeContext(Company $company, string $theme): array
    {
        $cacheKey = "ai_assistant_context:company:{$company->id}:theme:{$theme}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($company, $theme) {
            return match ($theme) {
                'clientes' => $this->buildClientsContext($company),
                'contratos' => $this->buildContractsContext($company),
                'financeiro' => $this->buildFinancialContext($company),
                'contabil' => $this->buildAccountingContext($company),
                'fluxo_caixa' => $this->buildCashflowContext($company),
                'despesas' => $this->buildExpensesContext($company),
                default => ['error' => 'Tema inválido'],
            };
        });
    }

    protected function buildClientsContext(Company $company): array
    {
        $topClients = Client::query()
            ->where('company_id', $company->id)
            ->withSum(['receivables as receivables_total' => function ($q) {
                $q->whereIn('status', ['paid', 'partial', 'pending']);
            }], 'value')
            ->orderByDesc('receivables_total')
            ->limit(12)
            ->get(['id', 'name', 'status'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'status' => $c->status,
                'receivables_total' => round((float) ($c->receivables_total ?? 0), 2),
            ])->values();

        return [
            'snapshot_at' => now()->toDateTimeString(),
            'totals' => [
                'total_clients' => (int) Client::where('company_id', $company->id)->count(),
                'active_clients' => (int) Client::where('company_id', $company->id)->where('status', 'active')->count(),
                'overdue_clients' => (int) Client::where('company_id', $company->id)->whereHas('receivables', function ($q) {
                    $q->whereIn('status', ['pending', 'partial'])->whereDate('due_date', '<', now()->toDateString());
                })->count(),
                'contracts_active' => (int) Contract::where('company_id', $company->id)->where('status', 'active')->count(),
            ],
            'top_clients' => $topClients,
        ];
    }

    protected function buildContractsContext(Company $company): array
    {
        $contracts = Contract::query()
            ->where('company_id', $company->id)
            ->orderByDesc('value')
            ->limit(15)
            ->get(['id', 'description', 'type', 'billing_period', 'status', 'value', 'start_date', 'end_date']);

        return [
            'snapshot_at' => now()->toDateTimeString(),
            'totals' => [
                'active' => (int) Contract::where('company_id', $company->id)->where('status', 'active')->count(),
                'recurring_monthly_active' => (int) Contract::where('company_id', $company->id)
                    ->where('status', 'active')->where('type', 'client_recurring')->where('billing_period', 'monthly')->count(),
                'mrr' => round((float) Contract::where('company_id', $company->id)
                    ->where('status', 'active')->where('type', 'client_recurring')->where('billing_period', 'monthly')->sum('value'), 2),
            ],
            'top_by_value' => $contracts->map(fn ($c) => [
                'id' => $c->id,
                'description' => $c->description,
                'type' => $c->type,
                'billing_period' => $c->billing_period,
                'status' => $c->status,
                'value' => round((float) $c->value, 2),
                'start_date' => optional($c->start_date)->format('Y-m-d'),
                'end_date' => optional($c->end_date)->format('Y-m-d'),
            ])->values(),
        ];
    }

    protected function buildFinancialContext(Company $company): array
    {
        $months = [];
        $start = now()->copy()->startOfMonth()->subMonths(5);
        for ($i = 0; $i < 9; $i++) {
            $m = $start->copy()->addMonths($i);
            $from = $m->copy()->startOfMonth();
            $to = $m->copy()->endOfMonth();

            $realIn = $this->getRevenueRealizedInPeriod($company->id, $from, $to);
            $realOut = (float) Payable::where('company_id', $company->id)
                ->where('status', 'paid')
                ->whereBetween('paid_date', [$from, $to])
                ->sum('value');
            $forecastIn = (float) Receivable::where('company_id', $company->id)
                ->where('status', 'pending')
                ->whereBetween('due_date', [$from, $to])
                ->sum('value');
            $forecastOut = (float) Payable::where('company_id', $company->id)
                ->where('status', 'pending')
                ->whereBetween('due_date', [$from, $to])
                ->sum('value');

            $months[] = [
                'month' => $m->format('Y-m'),
                'real_in' => round($realIn, 2),
                'real_out' => round($realOut, 2),
                'real_profit' => round($realIn - $realOut, 2),
                'forecast_in' => round($forecastIn, 2),
                'forecast_out' => round($forecastOut, 2),
                'forecast_profit' => round($forecastIn - $forecastOut, 2),
            ];
        }

        $today = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $next30Start = $today->copy()->startOfDay();
        $next30End = $today->copy()->addDays(30)->endOfDay();

        $receivablesPending = (float) Receivable::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'partial'])
            ->sum('value');
        $payablesPending = (float) Payable::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'partial'])
            ->sum('value');

        $receivablesOverdue = (float) Receivable::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', $today->toDateString())
            ->sum('value');
        $payablesOverdue = (float) Payable::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', $today->toDateString())
            ->sum('value');

        $expectedInNext30 = (float) Receivable::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [$next30Start, $next30End])
            ->sum('value');
        $expectedOutNext30 = (float) Payable::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [$next30Start, $next30End])
            ->sum('value');

        $realizedInCurrentMonth = $this->getRevenueRealizedInPeriod($company->id, $monthStart, $monthEnd);
        $realizedOutCurrentMonth = (float) Payable::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereBetween('paid_date', [$monthStart, $monthEnd])
            ->sum('value');

        $topClientsCurrentMonth = $this->getTopClientsPaymentsForPeriod($company->id, $monthStart, $monthEnd, 10);

        return [
            'snapshot_at' => now()->toDateTimeString(),
            'range' => [
                'past_6_and_next_3' => [
                    'from' => $start->format('Y-m'),
                    'to' => $start->copy()->addMonths(8)->format('Y-m'),
                ],
            ],
            'totals' => [
                'receivables_open_total' => round($receivablesPending, 2),
                'payables_open_total' => round($payablesPending, 2),
                'open_balance' => round($receivablesPending - $payablesPending, 2),
            ],
            'overdue' => [
                'receivables' => round($receivablesOverdue, 2),
                'payables' => round($payablesOverdue, 2),
                'net' => round($receivablesOverdue - $payablesOverdue, 2),
            ],
            'current_month_realized' => [
                'inflows' => round($realizedInCurrentMonth, 2),
                'outflows' => round($realizedOutCurrentMonth, 2),
                'net' => round($realizedInCurrentMonth - $realizedOutCurrentMonth, 2),
            ],
            'top_clients_current_month' => $topClientsCurrentMonth,
            'next_30_days_forecast' => [
                'expected_inflows' => round($expectedInNext30, 2),
                'expected_outflows' => round($expectedOutNext30, 2),
                'net' => round($expectedInNext30 - $expectedOutNext30, 2),
            ],
            'months' => $months,
        ];
    }

    protected function buildAccountingContext(Company $company): array
    {
        $entryExists = Schema::hasTable('fiscal_entry_notes');
        $exitExists = Schema::hasTable('fiscal_exit_notes');

        $entryByMonth = collect();
        if ($entryExists) {
            $entryByMonth = FiscalEntryNote::query()
                ->where('company_id', $company->id)
                ->selectRaw('DATE_FORMAT(entry_date, "%Y-%m") as ym, COUNT(*) as qty, SUM(total_amount) as total')
                ->groupBy('ym')
                ->orderByDesc('ym')
                ->limit(6)
                ->get()
                ->map(fn ($r) => [
                    'month' => $r->ym,
                    'qty' => (int) $r->qty,
                    'total' => round((float) ($r->total ?? 0), 2),
                ]);
        }

        $exitByMonth = collect();
        if ($exitExists) {
            $exitByMonth = FiscalExitNote::query()
                ->where('company_id', $company->id)
                ->selectRaw('DATE_FORMAT(received_date, "%Y-%m") as ym, COUNT(*) as qty, SUM(amount_received) as total')
                ->groupBy('ym')
                ->orderByDesc('ym')
                ->limit(6)
                ->get()
                ->map(fn ($r) => [
                    'month' => $r->ym,
                    'qty' => (int) $r->qty,
                    'total' => round((float) ($r->total ?? 0), 2),
                ]);
        }

        return [
            'snapshot_at' => now()->toDateTimeString(),
            'totals' => [
                'entry_notes' => $entryExists ? (int) FiscalEntryNote::where('company_id', $company->id)->count() : 0,
                'entry_issued' => $entryExists ? (int) FiscalEntryNote::where('company_id', $company->id)->where('is_issued', true)->count() : 0,
                'exit_notes' => $exitExists ? (int) FiscalExitNote::where('company_id', $company->id)->count() : 0,
                'exit_issued' => $exitExists ? (int) FiscalExitNote::where('company_id', $company->id)->where('is_issued', true)->count() : 0,
            ],
            'last_6_months_entry' => $entryByMonth->values(),
            'last_6_months_exit' => $exitByMonth->values(),
        ];
    }

    protected function buildCashflowContext(Company $company): array
    {
        $currentCash = (float) ($company->current_cash_balance ?? 0);

        $last30In = (float) ReceivablePayment::whereHas('receivable', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->whereBetween('paid_date', [now()->subDays(30)->startOfDay(), now()->endOfDay()])->sum('amount');

        $last30Out = (float) Payable::where('company_id', $company->id)
            ->where('status', 'paid')
            ->whereBetween('paid_date', [now()->subDays(30)->startOfDay(), now()->endOfDay()])
            ->sum('value');

        $shortTerm = (float) Payable::where('company_id', $company->id)
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->sum('value');

        $burnRate = max(0, $last30Out - $last30In);

        return [
            'snapshot_at' => now()->toDateTimeString(),
            'current_cash' => round($currentCash, 2),
            'last_30_days' => [
                'inflows' => round($last30In, 2),
                'outflows' => round($last30Out, 2),
                'net' => round($last30In - $last30Out, 2),
            ],
            'burn_rate' => round($burnRate, 2),
            'runway_months' => $burnRate > 0 ? round($currentCash / $burnRate, 2) : null,
            'short_term_obligations' => round($shortTerm, 2),
            'liquidity_index' => $shortTerm > 0 ? round($currentCash / $shortTerm, 2) : null,
        ];
    }

    protected function buildExpensesContext(Company $company): array
    {
        $byCategory = Expense::query()
            ->where('expenses.company_id', $company->id)
            ->leftJoin('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('COALESCE(expense_categories.name, "Sem categoria") as category_name, SUM(expenses.value) as total, COUNT(*) as qty')
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category_name,
                'total' => round((float) ($r->total ?? 0), 2),
                'qty' => (int) $r->qty,
            ]);

        $monthly = Payable::query()
            ->where('company_id', $company->id)
            ->selectRaw('DATE_FORMAT(COALESCE(paid_date, due_date), "%Y-%m") as ym, SUM(value) as total')
            ->where(function ($q) {
                $q->whereNotNull('paid_date')->orWhereNotNull('due_date');
            })
            ->groupBy('ym')
            ->orderByDesc('ym')
            ->limit(6)
            ->get()
            ->map(fn ($r) => [
                'month' => $r->ym,
                'total' => round((float) ($r->total ?? 0), 2),
            ]);

        return [
            'snapshot_at' => now()->toDateTimeString(),
            'totals' => [
                'expense_items' => (int) Expense::where('company_id', $company->id)->count(),
                'expense_categories' => (int) ExpenseCategory::where('company_id', $company->id)->count(),
                'active_payables_pending' => round((float) Payable::where('company_id', $company->id)->where('status', 'pending')->sum('value'), 2),
            ],
            'by_category' => $byCategory->values(),
            'last_6_months_payables' => $monthly->values(),
        ];
    }

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

    protected function getTopClientsPaymentsForPeriod(int $companyId, Carbon $start, Carbon $end, int $limit = 10): array
    {
        if (Schema::hasTable('receivable_payments')) {
            return ReceivablePayment::query()
                ->join('receivables', 'receivable_payments.receivable_id', '=', 'receivables.id')
                ->leftJoin('clients', 'receivables.client_id', '=', 'clients.id')
                ->where('receivables.company_id', $companyId)
                ->whereBetween('receivable_payments.paid_date', [$start, $end])
                ->selectRaw('receivables.client_id as client_id, COALESCE(clients.name, "Sem cliente") as client_name, SUM(receivable_payments.amount) as total_paid')
                ->groupBy('receivables.client_id', 'clients.name')
                ->orderByDesc('total_paid')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'client_id' => (int) ($row->client_id ?? 0),
                    'client_name' => (string) ($row->client_name ?? 'Sem cliente'),
                    'total_paid' => round((float) ($row->total_paid ?? 0), 2),
                ])
                ->values()
                ->all();
        }

        return Receivable::query()
            ->leftJoin('clients', 'receivables.client_id', '=', 'clients.id')
            ->where('receivables.company_id', $companyId)
            ->where('receivables.status', 'paid')
            ->whereBetween('receivables.paid_date', [$start, $end])
            ->selectRaw('receivables.client_id as client_id, COALESCE(clients.name, "Sem cliente") as client_name, SUM(receivables.value) as total_paid')
            ->groupBy('receivables.client_id', 'clients.name')
            ->orderByDesc('total_paid')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'client_id' => (int) ($row->client_id ?? 0),
                'client_name' => (string) ($row->client_name ?? 'Sem cliente'),
                'total_paid' => round((float) ($row->total_paid ?? 0), 2),
            ])
            ->values()
            ->all();
    }

    protected function resolveGeminiModel($httpClient, string $apiKey, string $configuredModel): string
    {
        $models = Cache::remember('ai_assistant:gemini_models_v2', now()->addHours(6), function () use ($httpClient, $apiKey) {
            $listUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
            $resp = $httpClient
                ->withHeaders(['x-goog-api-key' => $apiKey])
                ->get($listUrl);

            if (! $resp->ok()) {
                return [];
            }

            return collect((array) data_get($resp->json(), 'models', []))
                ->filter(function ($item) {
                    $methods = (array) data_get($item, 'supportedGenerationMethods', []);

                    return in_array('generateContent', $methods, true);
                })
                ->map(fn ($item) => (string) data_get($item, 'name', ''))
                ->filter()
                ->map(function ($name) {
                    return str_starts_with($name, 'models/') ? substr($name, 7) : $name;
                })
                ->values()
                ->all();
        });

        $modelsCollection = collect($models);
        $configuredModel = trim($configuredModel);
        if ($configuredModel !== '' && $modelsCollection->contains($configuredModel)) {
            return $configuredModel;
        }

        $preferred = [
            'gemini-2.5-flash',
            'gemini-2.5-flash-lite',
            'gemini-2.0-flash',
            'gemini-2.0-flash-lite',
            'gemini-1.5-flash',
            'gemini-1.5-flash-latest',
        ];
        foreach ($preferred as $candidate) {
            if ($modelsCollection->contains($candidate)) {
                return $candidate;
            }
        }

        return (string) ($modelsCollection->first() ?? 'gemini-2.5-flash');
    }

    protected function buildConversationContents(string $themeLabel, array $context, array $history, string $userMessage): array
    {
        $contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (! is_string($contextJson)) {
            $contextJson = '{}';
        }

        $contents = [[
            'role' => 'user',
            'parts' => [[
                'text' => "Tema selecionado: {$themeLabel}\n\nContexto resumido em JSON:\n{$contextJson}\n\nUse este contexto como fonte de verdade para a conversa.",
            ]],
        ]];

        foreach ($history as $item) {
            $contents[] = [
                'role' => $item['role'],
                'parts' => [[
                    'text' => $item['text'],
                ]],
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [[
                'text' => $userMessage,
            ]],
        ];

        return $contents;
    }

    protected function normalizeHistory(array $history): array
    {
        return collect($history)
            ->map(function ($item) {
                return [
                    'role' => (string) data_get($item, 'role', ''),
                    'text' => trim((string) data_get($item, 'text', '')),
                ];
            })
            ->filter(function ($item) {
                return in_array($item['role'], ['user', 'assistant', 'model'], true) && $item['text'] !== '';
            })
            ->map(function ($item) {
                $role = $item['role'] === 'assistant' ? 'model' : $item['role'];

                return [
                    'role' => $role,
                    'text' => Str::limit($item['text'], 1200, '...'),
                ];
            })
            ->values()
            ->all();
    }

    protected function extractTextFromGeminiResponse(array $response): string
    {
        $parts = (array) data_get($response, 'candidates.0.content.parts', []);
        $text = collect($parts)
            ->map(fn ($part) => (string) data_get($part, 'text', ''))
            ->filter(fn ($partText) => trim($partText) !== '')
            ->implode("\n");

        return trim($text);
    }
}

