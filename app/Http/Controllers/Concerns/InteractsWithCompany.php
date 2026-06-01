<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Company;
use App\Services\CompanyAuthorizationService;
use App\Support\CurrentCompanyContext;
use Illuminate\Support\Facades\Auth;

trait InteractsWithCompany
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

        $membership = CurrentCompanyContext::membership()
            ?? $user->companies()
                ->where('companies.id', $companyId)
                ->wherePivot('is_active', true)
                ->first();

        if (! $membership) {
            session()->forget('current_company_id');
            abort(403, 'Você não possui acesso ativo a esta empresa.');
        }

        return $membership;
    }

    protected function authz(): CompanyAuthorizationService
    {
        return app(CompanyAuthorizationService::class);
    }
}
