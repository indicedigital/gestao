<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'email',
        'phone',
        'document',
        'document_type',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'status',
        'notes',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Empresa do cliente
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Projetos do cliente
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Contratos do cliente
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Contas a receber do cliente
     */
    public function receivables()
    {
        return $this->hasMany(Receivable::class);
    }

    /**
     * Verifica se o cliente está adimplente
     */
    public function isAdimplente(): bool
    {
        return $this->receivables()
            ->where('status', 'overdue')
            ->where('due_date', '<', now())
            ->doesntExist();
    }
}
