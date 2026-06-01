<?php

namespace App\Services;

use App\Models\Task;

class TaskWorkflowService
{
    public function __construct(protected TaskHistoryService $historyService) {}

    public function transition(Task $task, string $newStatus, ?int $position = null): Task
    {
        if ($newStatus === 'completed' && ! $task->allSubtasksCompleted()) {
            throw new \InvalidArgumentException('Conclua todas as subtasks antes de finalizar a task.');
        }

        $original = $task->status;
        $task->status = $newStatus;

        if ($position !== null) {
            $task->position = $position;
        }

        if ($newStatus === 'completed') {
            $task->completed_at = now();
        } elseif ($original === 'completed') {
            $task->completed_at = null;
        }

        $task->save();
        $this->historyService->log($task, 'status_changed', 'status', $original, $newStatus);

        return $task;
    }
}
