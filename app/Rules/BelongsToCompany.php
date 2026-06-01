<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BelongsToCompany implements ValidationRule
{
    public function __construct(
        protected string $table,
        protected int $companyId,
        protected string $column = 'company_id'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $exists = \DB::table($this->table)
            ->where('id', $value)
            ->where($this->column, $this->companyId)
            ->exists();

        if (! $exists) {
            $fail('O registro selecionado não pertence à sua empresa.');
        }
    }
}
