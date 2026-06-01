<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityGoal extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'period_type',
        'hours_target',
        'tasks_target',
        'completion_rate_target',
    ];

    protected $casts = [
        'hours_target' => 'decimal:2',
        'completion_rate_target' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
