<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
     * Despesas
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
