<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivablePayment extends Model
{
    protected $fillable = [
        'receivable_id',
        'amount',
        'paid_date',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }
}
