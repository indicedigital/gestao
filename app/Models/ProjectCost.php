<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'type',
        'description',
        'value',
        'date',
        'employee_id',
        'hours',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'date' => 'date',
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
     * Projeto
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Funcionário (se for custo de mão de obra)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
