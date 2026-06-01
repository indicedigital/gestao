<?php

namespace App\Http\Middleware;

use App\Services\CompanyAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $authz = app(CompanyAuthorizationService::class);

        if (! $authz->isClient()) {
            abort(403, 'Acesso restrito ao portal do cliente.');
        }

        return $next($request);
    }
}
