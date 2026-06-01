<?php

namespace App\Http\Controllers\Company\Concerns;

use App\Services\CompanyAuthorizationService;

trait AuthorizesCompanyManagement
{
    protected function authorizeManage(string $message = 'Sem permissão para esta operação.'): void
    {
        abort_unless(app(CompanyAuthorizationService::class)->canManage(), 403, $message);
    }
}
