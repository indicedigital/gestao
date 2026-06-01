<?php

namespace App\Models;

use App\Support\PermissionModules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CompanyPermissionProfile extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'permissions',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function normalizedPermissions(): array
    {
        return PermissionModules::normalizePermissions($this->permissions);
    }

    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }
}
