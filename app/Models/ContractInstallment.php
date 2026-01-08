<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractInstallment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_id',
        'installment_number',
        'description',
        'value',
        'due_date',
        'paid_date',
        'status', // pending, paid, overdue, cancelled
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Contrato da parcela
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Conta a receber relacionada
     */
    public function receivable()
    {
        return $this->hasOne(Receivable::class, 'contract_id')
            ->where('installment_number', $this->installment_number);
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

        // Atualiza a conta a receber relacionada se existir
        if ($this->receivable) {
            $this->receivable->markAsPaid($paidDate, $paymentMethod);
        }
    }
}
