<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receivable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'project_id',
        'contract_id',
        'type',
        'description',
        'value',
        'paid_value',
        'due_date',
        'paid_date',
        'status',
        'installment_number',
        'total_installments',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'paid_value' => 'decimal:2',
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
     * Cliente
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Projeto
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Contrato
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
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
     * Marca como paga (total ou parcial)
     */
    public function markAsPaid(string $paidDate, ?string $paymentMethod = null, ?float $paidValue = null): void
    {
        $paidValue = $paidValue ?? $this->value;
        $status = ($paidValue >= $this->value) ? 'paid' : 'partial';
        
        $this->update([
            'status' => $status,
            'paid_date' => $paidDate,
            'paid_value' => $paidValue,
            'payment_method' => $paymentMethod ?? $this->payment_method,
        ]);
    }
}
