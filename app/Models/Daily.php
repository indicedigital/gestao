<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Daily extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'employee_id',
        'project_id',
        'task_id',
        'subtask_id',
        'work_date',
        'description',
        'hours',
        'blockers',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'decimal:2',
    ];

    public function getFormattedDurationAttribute(): string
    {
        return \App\Support\DurationFormatter::format((float) $this->hours);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function subtask()
    {
        return $this->belongsTo(Subtask::class);
    }
}
