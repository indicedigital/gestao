<?php

namespace App\Http\Middleware;

use App\Support\CurrentCompanyContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ($user->is_super_admin ?? false)) {
            return $next($request);
        }

        $companyId = session('current_company_id');

        if (! $companyId) {
            $company = $user->currentCompany();
            if ($company) {
                session(['current_company_id' => $company->id]);
                CurrentCompanyContext::setMembership($company);

                return $next($request);
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
            abort(403, 'Acesso negado à empresa.');
        }

        CurrentCompanyContext::setMembership($membership);

        return $next($request);
    }
}
