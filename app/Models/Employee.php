<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'email',
        'phone',
        'document',
        'position',
        'role',
        'hire_date',
        'dismissal_date',
        'salary',
        'status',
        'address',
        'notes',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'hire_date' => 'date',
        'dismissal_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Contratos do funcionário
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Projetos do funcionário
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_employees')
            ->withPivot('role', 'hourly_rate', 'allocated_hours', 'start_date', 'end_date', 'is_active')
            ->withTimestamps();
    }

    /**
     * Contas a pagar (salários)
     */
    public function payables()
    {
        return $this->hasMany(Payable::class);
    }

    /**
     * Custos de projetos
     */
    public function projectCosts()
    {
        return $this->hasMany(ProjectCost::class);
    }
}
