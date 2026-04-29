<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalEntryNote extends Model
{
    protected $fillable = [
        'company_id',
        'client_id',
        'person_type',
        'client_name',
        'client_email',
        'client_phone',
        'document',
        'document_type',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'amount_received',
        'received_date',
        'is_issued',
        'issued_at',
        'internal_notes',
    ];

    protected $casts = [
        'amount_received' => 'decimal:2',
        'received_date' => 'date',
        'is_issued' => 'boolean',
        'issued_at' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeNotIssued($query)
    {
        return $query->where('is_issued', false);
    }

    public function scopeReceivedInMonth($query, int $year, int $month)
    {
        return $query->whereYear('received_date', $year)->whereMonth('received_date', $month);
    }
}
