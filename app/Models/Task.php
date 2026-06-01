<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'backlog' => 'Backlog',
        'todo' => 'A Fazer',
        'in_progress' => 'Em Progresso',
        'review' => 'Revisão',
        'waiting_client' => 'Aguardando Cliente',
        'homologation' => 'Homologação',
        'completed' => 'Concluída',
    ];

    public const CATEGORIES = [
        'bug' => 'Bug',
        'improvement' => 'Melhoria',
        'new_feature' => 'Nova Funcionalidade',
        'support' => 'Suporte',
        'documentation' => 'Documentação',
    ];

    public const PRIORITIES = [
        'P0' => 'P0 — Crítico',
        'P1' => 'P1 — Alta',
        'P2' => 'P2 — Normal',
        'P3' => 'P3 — Baixa',
    ];

    public const DEFAULT_SLA_HOURS = [
        'P0' => 6,
        'P1' => 24,
        'P2' => 60,
        'P3' => 144,
    ];

    protected $fillable = [
        'company_id',
        'project_id',
        'assignee_id',
        'created_by',
        'requester_type',
        'requester_name',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'creation_channel',
        'estimated_hours',
        'actual_hours',
        'sla_hours',
        'sla_deadline',
        'completed_at',
        'position',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'sla_hours' => 'integer',
        'sla_deadline' => 'datetime',
        'completed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assignee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class)->orderBy('position');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function publicComments()
    {
        return $this->hasMany(TaskComment::class)->where('is_internal', false)->latest();
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class)->latest();
    }

    public function histories()
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }

    public function dailies()
    {
        return $this->hasMany(Daily::class);
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'completed' || ! $this->sla_deadline) {
            return false;
        }

        return now()->greaterThan($this->sla_deadline);
    }

    public function slaProgressPercent(): ?float
    {
        if (! $this->sla_deadline || ! $this->sla_hours) {
            return null;
        }

        $created = $this->created_at ?? now();
        $totalMinutes = max(1, $created->diffInMinutes($this->sla_deadline));
        $elapsedMinutes = $created->diffInMinutes(now());

        return min(100, ($elapsedMinutes / $totalMinutes) * 100);
    }

    public function slaAlertLevel(): ?string
    {
        if ($this->status === 'completed' || ! $this->sla_deadline) {
            return null;
        }

        $percent = $this->slaProgressPercent();
        if ($percent === null) {
            return null;
        }

        if ($percent >= 100) {
            return 'danger';
        }
        if ($percent >= 80) {
            return 'warning';
        }
        if ($percent >= 50) {
            return 'info';
        }

        return null;
    }

    public function allSubtasksCompleted(): bool
    {
        if ($this->relationLoaded('subtasks')) {
            if ($this->subtasks->isEmpty()) {
                return true;
            }

            return $this->subtasks->every(fn ($st) => $st->status === 'completed');
        }

        return ! $this->subtasks()->where('status', '!=', 'completed')->exists();
    }

    public function recalculateActualHours(): void
    {
        $taskHours = (float) $this->dailies()->sum('hours');
        $this->update(['actual_hours' => $taskHours]);
    }
}
