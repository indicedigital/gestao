<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractSlaSetting extends Model
{
    protected $fillable = [
        'contract_id',
        'priority',
        'hours',
    ];

    protected $casts = [
        'hours' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
