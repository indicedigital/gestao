<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'contract_id',
        'name',
        'description',
        'type',
        'total_value',
        'installments',
        'status',
        'start_date',
        'end_date',
        'deadline',
        'cost',
        'profit_margin',
        'scope',
        'deliverables',
    ];

    protected $casts = [
        'total_value' => 'decimal:2',
        'cost' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'date',
        'deliverables' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Empresa do projeto
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Cliente do projeto
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Contrato vinculado
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Colaboradores do projeto
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'project_employees')
            ->withPivot('role', 'hourly_rate', 'allocated_hours', 'start_date', 'end_date', 'is_active')
            ->withTimestamps();
    }

    /**
     * Custos do projeto
     */
    public function costs()
    {
        return $this->hasMany(ProjectCost::class);
    }

    /**
     * Contas a receber do projeto
     */
    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    /**
     * Calcula o lucro do projeto
     */
    public function getProfitAttribute(): float
    {
        return $this->total_value - $this->cost;
    }

    /**
     * Calcula a margem de lucro percentual
     */
    public function getProfitMarginPercentAttribute(): float
    {
        if ($this->total_value == 0) {
            return 0;
        }
        return (($this->total_value - $this->cost) / $this->total_value) * 100;
    }
}
