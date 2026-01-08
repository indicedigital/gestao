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
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps();
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
