<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Task;
use Carbon\Carbon;

class SlaService
{
    public function resolveHoursForTask(Task $task): int
    {
        $project = $task->project()->with('contract.slaSettings')->first();
        $priority = $task->priority ?? 'P2';

        if ($project?->contract) {
            $custom = $project->contract->slaSettings
                ->firstWhere('priority', $priority);

            if ($custom) {
                return (int) $custom->hours;
            }
        }

        return Task::DEFAULT_SLA_HOURS[$priority] ?? Task::DEFAULT_SLA_HOURS['P2'];
    }

    public function applyToTask(Task $task): Task
    {
        if ($task->sla_deadline) {
            if (! $task->sla_hours) {
                $task->sla_hours = max(1, (int) ceil(now()->diffInMinutes($task->sla_deadline) / 60));
                $task->save();
            }

            return $task;
        }

        $hours = $this->resolveHoursForTask($task);
        $deadline = Carbon::now()->addHours($hours);

        $task->sla_hours = $hours;
        $task->sla_deadline = $deadline;
        $task->save();

        return $task;
    }

    public function seedDefaultsForContract(Contract $contract): void
    {
        foreach (Task::DEFAULT_SLA_HOURS as $priority => $hours) {
            $contract->slaSettings()->updateOrCreate(
                ['priority' => $priority],
                ['hours' => $hours]
            );
        }
    }
}
