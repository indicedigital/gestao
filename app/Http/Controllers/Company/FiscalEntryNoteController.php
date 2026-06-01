<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Company\Concerns\AuthorizesCompanyManagement;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Company;
use App\Models\FiscalEntryNote;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FiscalEntryNoteController extends Controller
{
    use AuthorizesCompanyManagement;

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

        $query = FiscalEntryNote::forCompany($company->id)
            ->with('client')
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
                    ->orWhere('document', 'like', "%{$s}%");
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
            'total' => tap(FiscalEntryNote::forCompany($company->id), $applyMonthToStats)->count(),
            'pending_issue' => tap(FiscalEntryNote::forCompany($company->id), $applyMonthToStats)->notIssued()->count(),
            'issued' => tap(FiscalEntryNote::forCompany($company->id), $applyMonthToStats)->where('is_issued', true)->count(),
        ];
        $statsMonthLabel = ($monthFilter && preg_match('/^\d{4}-\d{2}$/', $monthFilter))
            ? Carbon::createFromFormat('Y-m', $monthFilter)->format('m/Y')
            : 'Todos os períodos';

        $clients = Client::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get();

        return view('company.accounting.fiscal-entry-notes.index', compact(
            'company',
            'notes',
            'monthFilter',
            'stats',
            'statsMonthLabel',
            'clients'
        ));
    }

    public function monthlyReport(Request $request): RedirectResponse
    {
        return redirect()->route('company.accounting.report', [
            'month' => $request->input('month', now()->format('Y-m')),
            'direction' => 'entrada',
        ]);
    }

    public function create(): View
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get();

        return view('company.accounting.fiscal-entry-notes.create', compact('company', 'clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $data = $this->validatedData($request, $company);
        $data['company_id'] = $company->id;
        FiscalEntryNote::create($data);

        return redirect()
            ->route('company.accounting.fiscal-entry-notes.index')
            ->with('success', 'Lançamento de nota de entrada registrado com sucesso.');
    }

    public function edit(FiscalEntryNote $fiscal_entry_note): View
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_entry_note, $company);
        $clients = Client::where('company_id', $company->id)->where('status', 'active')->orderBy('name')->get();

        return view('company.accounting.fiscal-entry-notes.edit', [
            'company' => $company,
            'note' => $fiscal_entry_note,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, FiscalEntryNote $fiscal_entry_note): RedirectResponse
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_entry_note, $company);
        $fiscal_entry_note->update($this->validatedData($request, $company));

        return redirect()
            ->route('company.accounting.fiscal-entry-notes.index')
            ->with('success', 'Lançamento atualizado com sucesso.');
    }

    public function destroy(FiscalEntryNote $fiscal_entry_note): RedirectResponse
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_entry_note, $company);
        $fiscal_entry_note->delete();

        return redirect()
            ->route('company.accounting.fiscal-entry-notes.index')
            ->with('success', 'Lançamento removido.');
    }

    public function toggleIssued(FiscalEntryNote $fiscal_entry_note): RedirectResponse
    {
        $this->authorizeManage();
        $company = $this->getCurrentCompany();
        $this->authorizeNote($fiscal_entry_note, $company);

        $fiscal_entry_note->is_issued = ! $fiscal_entry_note->is_issued;
        $fiscal_entry_note->issued_at = $fiscal_entry_note->is_issued ? now()->toDateString() : null;
        $fiscal_entry_note->save();

        return back()->with('success', $fiscal_entry_note->is_issued
            ? 'Marcado como nota emitida.'
            : 'Marcado como não emitida.');
    }

    protected function authorizeNote(FiscalEntryNote $note, Company $company): void
    {
        if ((int) $note->company_id !== (int) $company->id) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedData(Request $request, Company $company): array
    {
        $validated = $request->validate([
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('company_id', $company->id)],
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
