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
        'parent_receivable_id',
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
     * Conta a receber original (quando esta é a duplicata do restante)
     */
    public function parentReceivable()
    {
        return $this->belongsTo(Receivable::class, 'parent_receivable_id');
    }

    /**
     * Duplicata criada para o valor restante (quando houve pagamento parcial)
     */
    public function remainderReceivable()
    {
        return $this->hasOne(Receivable::class, 'parent_receivable_id');
    }

    /**
     * Pagamentos registrados (múltiplas datas)
     */
    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class)->orderBy('paid_date');
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
     * Marca como paga (total ou parcial) – registra um pagamento e atualiza totais.
     * Se for parcial e $createRemainderDuplicata = true, cria nova conta a receber para o restante.
     */
    public function markAsPaid(string $paidDate, ?string $paymentMethod = null, ?float $paidValue = null, bool $createRemainderDuplicata = true): ?self
    {
        $paidValue = $paidValue ?? ($this->value - (float) ($this->paid_value ?? 0));
        $paidValue = min($paidValue, (float) $this->value - (float) ($this->paid_value ?? 0));

        if ($paidValue <= 0) {
            return null;
        }

        $this->payments()->create([
            'amount' => $paidValue,
            'paid_date' => $paidDate,
            'payment_method' => $paymentMethod ?? $this->payment_method,
        ]);

        $this->syncPaidFromPayments();

        $remainder = (float) $this->value - (float) $this->paid_value;
        if ($remainder > 0 && $createRemainderDuplicata && ! $this->remainderReceivable()->exists()) {
            return $this->createRemainderReceivable();
        }

        return null;
    }

    /**
     * Atualiza paid_value e paid_date a partir da soma dos pagamentos.
     */
    public function syncPaidFromPayments(): void
    {
        $totalPaid = (float) $this->payments()->sum('amount');
        $lastPayment = $this->payments()->orderByDesc('paid_date')->first();

        $status = $totalPaid >= (float) $this->value ? 'paid' : ($totalPaid > 0 ? 'partial' : 'pending');

        $this->update([
            'paid_value' => $totalPaid,
            'paid_date' => $lastPayment?->paid_date,
            'payment_method' => $lastPayment?->payment_method ?? $this->payment_method,
            'status' => $status,
        ]);
    }

    /**
     * Cria duplicata pendente para o valor restante.
     */
    public function createRemainderReceivable(): self
    {
        $remainder = (float) $this->value - (float) $this->paid_value;
        if ($remainder <= 0) {
            throw new \InvalidArgumentException('Não há valor restante para criar duplicata.');
        }

        return self::create([
            'company_id' => $this->company_id,
            'client_id' => $this->client_id,
            'project_id' => $this->project_id,
            'contract_id' => $this->contract_id,
            'parent_receivable_id' => $this->id,
            'type' => $this->type,
            'description' => 'Restante: ' . $this->description,
            'value' => $remainder,
            'due_date' => $this->due_date->copy()->addDays(30),
            'status' => 'pending',
        ]);
    }
}
