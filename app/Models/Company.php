<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'logo_path',
        'current_cash_balance',
        'status',
        'owner_id',
    ];

    /**
     * URL pública do logotipo (disco public), ou null.
     */
    public function logoPublicUrl(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * Iniciais para exibição quando não há logo.
     */
    public function logoInitials(): string
    {
        $name = trim((string) $this->name);
        if ($name === '') {
            return '?';
        }
        $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (function_exists('mb_strtoupper')) {
            if (count($parts) === 1) {
                return mb_strtoupper(mb_substr($parts[0], 0, 2));
            }

            return mb_strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[count($parts) - 1], 0, 1));
        }

        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 2));
        }

        return strtoupper(substr($parts[0], 0, 1).substr($parts[count($parts) - 1], 0, 1));
    }

    /**
     * Owner da empresa
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Usuários da empresa
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_company')
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Usuários ativos da empresa
     */
    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true);
    }

    /**
     * Administradores da empresa
     */
    public function admins()
    {
        return $this->users()->wherePivot('role', 'admin');
    }

    /**
     * Clientes da empresa
     */
    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Projetos da empresa
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Leads da empresa
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Contratos da empresa
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Funcionários da empresa
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Contas a receber da empresa
     */
    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    /**
     * Contas a pagar da empresa
     */
    public function payables()
    {
        return $this->hasMany(Payable::class);
    }

    /**
     * Lançamentos de notas fiscais de entrada (contabilidade)
     */
    public function fiscalEntryNotes()
    {
        return $this->hasMany(FiscalEntryNote::class);
    }

    public function fiscalExitNotes()
    {
        return $this->hasMany(FiscalExitNote::class);
    }
}
