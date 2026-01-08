<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'employee_id',
        'type',
        'number',
        'name',
        'description',
        'value',
        'has_down_payment',
        'down_payment_value',
        'down_payment_date',
        'installments_count',
        'payment_frequency',
        'equal_installments',
        'first_installment_date',
        'billing_period',
        'recurring_due_date_type',
        'recurring_due_date_day',
        'start_date',
        'end_date',
        'auto_renew',
        'status',
        'terms',
        'notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'down_payment_value' => 'decimal:2',
        'has_down_payment' => 'boolean',
        'equal_installments' => 'boolean',
        'down_payment_date' => 'date',
        'first_installment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
        'terms' => 'array',
        'deleted_at' => 'datetime',
    ];

    /**
     * Empresa do contrato
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Cliente do contrato (se for contrato com cliente)
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Funcionário do contrato (se for contrato com funcionário)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Projetos vinculados ao contrato
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Parcelas do contrato (ContractInstallment)
     */
    public function installments()
    {
        return $this->hasMany(ContractInstallment::class)->orderBy('installment_number');
    }

    /**
     * Contas a receber vinculadas ao contrato
     */
    public function receivables()
    {
        return $this->hasMany(Receivable::class)->orderBy('due_date');
    }

    /**
     * Parcelas pendentes
     */
    public function pendingInstallments()
    {
        return $this->installments()->where('status', 'pending');
    }

    /**
     * Parcelas pagas
     */
    public function paidInstallments()
    {
        return $this->installments()->where('status', 'paid');
    }

    /**
     * Verifica se o contrato está ativo
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && ($this->end_date === null || $this->end_date >= now()->toDateString());
    }

    /**
     * Calcula o valor restante a receber
     */
    public function getRemainingAmountAttribute(): float
    {
        $paid = $this->paidInstallments()->sum('value');
        return $this->value - $paid;
    }

    /**
     * Calcula o percentual pago
     */
    public function getPaidPercentageAttribute(): float
    {
        if ($this->value == 0) {
            return 0;
        }
        $paid = $this->paidInstallments()->sum('value');
        return ($paid / $this->value) * 100;
    }
}
