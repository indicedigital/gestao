<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    public const STATUSES = [
        'todo' => 'A Fazer',
        'in_progress' => 'Em Progresso',
        'completed' => 'Concluída',
    ];

    protected $fillable = [
        'task_id',
        'assignee_id',
        'title',
        'status',
        'due_date',
        'hours_spent',
        'position',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'hours_spent' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assignee_id');
    }

    public function dailies()
    {
        return $this->hasMany(Daily::class);
    }

    public function recalculateHoursSpent(): void
    {
        $hours = (float) $this->dailies()->sum('hours');
        $this->update(['hours_spent' => $hours]);
    }
}
