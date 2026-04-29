<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FiscalEntryNote;
use App\Models\FiscalExitNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountingReportController extends Controller
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

    public function monthly(Request $request): View
    {
        $company = $this->getCurrentCompany();
        $monthFilter = $request->input('month', now()->format('Y-m'));
        $direction = $request->input('direction', 'entrada');

        if (! in_array($direction, ['entrada', 'saida'], true)) {
            $direction = 'entrada';
        }

        $d = Carbon::createFromFormat('Y-m', $monthFilter);

        if ($direction === 'entrada') {
            $entryNotes = FiscalEntryNote::forCompany($company->id)
                ->notIssued()
                ->receivedInMonth($d->year, $d->month)
                ->orderBy('received_date')
                ->orderBy('id')
                ->get();

            $exitNotes = collect();
            $totalAmount = $entryNotes->sum('amount_received');
        } else {
            $exitNotes = FiscalExitNote::forCompany($company->id)
                ->with(['receivable', 'receivablePayment'])
                ->notIssued()
                ->receivedInMonth($d->year, $d->month)
                ->orderBy('received_date')
                ->orderBy('id')
                ->get();

            $entryNotes = collect();
            $totalAmount = $exitNotes->sum('amount_received');
        }

        return view('company.accounting.report', compact(
            'company',
            'monthFilter',
            'direction',
            'entryNotes',
            'exitNotes',
            'totalAmount'
        ));
    }
}
