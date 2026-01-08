<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'project_id',
        'type',
        'category',
        'description',
        'value',
        'due_date',
        'paid_date',
        'status',
        'payment_method',
        'supplier_name',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
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
     * Funcionário (se for salário)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Projeto (se for custo de projeto)
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Verifica se está vencida
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' 
            && $this->due_date < now()->toDateString();
    }

    /**
     * Marca como paga
     */
    public function markAsPaid(string $paidDate, ?string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => $paidDate,
            'payment_method' => $paymentMethod ?? $this->payment_method,
        ]);
    }
}
