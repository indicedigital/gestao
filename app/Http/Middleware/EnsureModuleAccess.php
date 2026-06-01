<?php

namespace App\Http\Middleware;

use App\Services\CompanyAuthorizationService;
use App\Support\PermissionRouteMapper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function __construct(
        protected CompanyAuthorizationService $authz
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $module = PermissionRouteMapper::moduleForRoute($routeName);

        if ($module === null) {
            return $next($request);
        }

        if ($module === 'permission_profiles') {
            abort_unless($this->authz->canManageProfiles(), 403, 'Sem permissão para gerenciar perfis.');
        } else {
            abort_unless($this->authz->canAccessModule($module), 403, 'Você não tem permissão para acessar este módulo.');
        }

        return $next($request);
    }
}
