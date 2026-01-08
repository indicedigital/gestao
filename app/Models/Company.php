<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'status',
        'owner_id',
    ];

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
}
