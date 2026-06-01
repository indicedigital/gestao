<?php

namespace App\Support;

use App\Models\Company;

class CurrentCompanyContext
{
    public const MEMBERSHIP_KEY = 'current_company_membership';

    public static function setMembership(Company $company): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        request()->attributes->set(self::MEMBERSHIP_KEY, $company);
    }

    public static function membership(): ?Company
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return null;
        }

        $membership = request()->attributes->get(self::MEMBERSHIP_KEY);

        return $membership instanceof Company ? $membership : null;
    }
}
