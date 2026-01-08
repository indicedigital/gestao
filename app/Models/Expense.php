<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'type',
        'description',
        'value',
        'expense_category_id',
        'supplier_id',
        'due_date_day',
        'due_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'due_date' => 'date',
        'due_date_day' => 'integer',
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
     * Categoria
     */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Fornecedor
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
