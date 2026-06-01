<?php

namespace App\Http\Middleware;

use App\Services\CompanyAuthorizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictClientToPortal
{
    public function handle(Request $request, Closure $next): Response
    {
        $authz = app(CompanyAuthorizationService::class);

        if ($authz->isClient()) {
            abort(403, 'Clientes devem usar o portal em /portal.');
        }

        return $next($request);
    }
}
