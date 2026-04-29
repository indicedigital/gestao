<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\FiscalExitNote;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FiscalExitNoteController extends Controller
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

    public function index(Request $request): View
    {
        $company = $this->getCurrentCompany();
        $monthFilter = $request->input('month', now()->format('Y-m'));

        $query = FiscalExitNote::forCompany($company->id)
            ->with(['client', 'receivable', 'receivablePayment'])
            ->orderByDesc('received_date')
            ->orderByDesc('id');

        if ($monthFilter && preg_match('/^\d{4}-\d{2}$/', $monthFilter)) {
            $d = Carbon::createFromFormat('Y-m', $monthFilter);
            $query->receivedInMonth($d->year, $d->month);
        }

        if ($request->filled('issued')) {
            $query->where('is_issued', $request->boolean('issued'));
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('client_name', 'like', "%{$s}%")
                    ->orWhere('document', 'like', "%{$s}%")
                    ->orWhere('receivable_description', 'like', "%{$s}%");
            });
        }

        $notes = $query->get();

        $applyMonthToStats = function ($builder) use ($monthFilter) {
            if ($monthFilter && preg_match('/^\d{4}-\d{2}$/', $monthFilter)) {
                $d = Carbon::createFromFormat('Y-m', $monthFilter);
                $builder->receivedInMonth($d->year, $d->month);
            }
        };
        $stats = [
            'total' => tap(FiscalExitNote::forCompany($company->id), $applyMonthToStats)->count(),
            'pending_issue' => tap(FiscalExitNote::forCompany($company->id), $applyMonthToStats)->notIssued()->count(),
            'issued' => tap(FiscalExitNote::forCompany($company->id), $applyMonthToStats)->where('is_issued', true)->count(),
        ];
        $statsMonthLabel = ($monthFilter && preg_match('/^\d{4}-\d{2}$/', $monthFilter))
            ? Carbon::createFromFormat('Y-m', $monthFilter)->format('m/Y')
            : 'Todos os períodos';

        $pendingSyncCount = FiscalExitNote::countPaymentsPendingSyncForCompany($company->id);

        return view('company.accounting.fiscal-exit-notes.index', compact(
            'company',
            'notes',
            'monthFilter',
            'stats',
            'statsMonthLabel',
            'pendingSyncCount'
        ));
    }

    public function syncFromReceivables(): RedirectResponse
    {
        $company = $this->getCurrentCompany();
        $result = FiscalExitNote::syncFromReceivablePaymentsForCompany($company->id);
        $created = $result['created'];
        $updated = $result['updated'];
        $skippedIssued = $result['skipped_issued'];

        if ($created === 0 && $updated === 0) {
            if ($skippedIssued > 0) {
                return back()->with('info', 'Nenhuma alteração: só há notas já emitidas vinculadas aos recebimentos (esses registros não são atualizados na sincronização).');
            }

            return back()->with('info', 'Nenhuma alteração: não há recebimentos elegíveis, ou os dados já estavam alinhados com cliente e contas a receber.');
        }

        $parts = [];
        if ($created > 0) {
            $parts[] = "{$created} nota(s) criada(s)";
        }
        if ($updated > 0) {
            $parts[] = "{$updated} nota(s) pendente(s) atualizada(s) com dados atuais do cliente e do recebimento";
        }
        $msg = 'Sincronização: '.implode('; ', $parts).'.';
        if ($skippedIssued > 0) {
            $msg .= " {$skippedIssued} nota(s) já emitida(s) mantida(s) sem alteração.";
        }

        return back()->with('success', $msg);
    }

    public function edit(FiscalExitNote $fiscal_exit_note): View
    {
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_exit_note, $company);
        $fiscal_exit_note->load(['receivable', 'receivablePayment', 'client']);
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get();

        return view('company.accounting.fiscal-exit-notes.edit', [
            'company' => $company,
            'note' => $fiscal_exit_note,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, FiscalExitNote $fiscal_exit_note): RedirectResponse
    {
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_exit_note, $company);
        $fiscal_exit_note->update($this->validatedData($request));

        return redirect()
            ->route('company.accounting.fiscal-exit-notes.index')
            ->with('success', 'Nota de saída atualizada com sucesso.');
    }

    public function destroy(FiscalExitNote $fiscal_exit_note): RedirectResponse
    {
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_exit_note, $company);
        $fiscal_exit_note->delete();

        return redirect()
            ->route('company.accounting.fiscal-exit-notes.index')
            ->with('success', 'Lançamento de NF de saída removido.');
    }

    public function toggleIssued(FiscalExitNote $fiscal_exit_note): RedirectResponse
    {
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_exit_note, $company);

        $fiscal_exit_note->is_issued = ! $fiscal_exit_note->is_issued;
        $fiscal_exit_note->issued_at = $fiscal_exit_note->is_issued ? now()->toDateString() : null;
        $fiscal_exit_note->save();

        return back()->with('success', $fiscal_exit_note->is_issued
            ? 'Marcado como nota emitida.'
            : 'Marcado como não emitida.');
    }

    protected function authorizeNote(FiscalExitNote $note, Company $company): void
    {
        if ((int) $note->company_id !== (int) $company->id) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'person_type' => ['required', 'in:pf,pj'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'document' => ['nullable', 'string', 'max:20'],
            'document_type' => ['nullable', 'in:cpf,cnpj'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:4'],
            'zip_code' => ['nullable', 'string', 'max:16'],
            'country' => ['nullable', 'string', 'max:120'],
            'amount_received' => ['required', 'numeric', 'min:0'],
            'received_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'receivable_description' => ['nullable', 'string', 'max:255'],
            'is_issued' => ['sometimes', 'boolean'],
            'issued_at' => ['nullable', 'date'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $validated['is_issued'] = $request->boolean('is_issued');
        if (! $validated['is_issued']) {
            $validated['issued_at'] = null;
        } elseif (empty($validated['issued_at'])) {
            $validated['issued_at'] = now()->toDateString();
        }

        return $validated;
    }
}
