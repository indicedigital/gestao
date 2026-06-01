<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    protected $guarded = [
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Empresas do usuário
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_company')
            ->withPivot('role', 'is_active', 'joined_at', 'client_id', 'employee_id', 'permission_profile_id')
            ->withTimestamps();
    }

    public function companyRole(?int $companyId = null): ?string
    {
        $companyId ??= session('current_company_id');
        if (! $companyId) {
            return null;
        }

        $membership = \App\Support\CurrentCompanyContext::membership();
        if ($membership && (int) $membership->id === (int) $companyId) {
            return $membership->pivot?->role;
        }

        $pivot = $this->companies()->where('companies.id', $companyId)->first();

        return $pivot?->pivot?->role;
    }

    public function isClientUser(?int $companyId = null): bool
    {
        return $this->companyRole($companyId) === 'client';
    }

    /**
     * Empresa atual do usuário (contexto de tenant)
     */
    public function currentCompany()
    {
        return $this->companies()->wherePivot('is_active', true)->first();
    }

    /**
     * Empresas que o usuário é owner
     */
    public function ownedCompanies()
    {
        return $this->hasMany(Company::class, 'owner_id');
    }

    /**
     * Verifica se o usuário é admin de uma empresa
     */
    public function isCompanyAdmin(Company $company): bool
    {
        $pivot = $this->companies()->where('company_id', $company->id)->first();
        return $pivot && in_array($pivot->pivot->role, ['owner', 'admin']);
    }
}
