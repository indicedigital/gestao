<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Support\Facades\Auth;

class TaskHistoryService
{
    public function log(Task $task, string $action, ?string $field = null, mixed $oldValue = null, mixed $newValue = null): TaskHistory
    {
        return TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'field' => $field,
            'old_value' => $this->stringify($oldValue),
            'new_value' => $this->stringify($newValue),
            'action' => $action,
        ]);
    }

    public function logChanges(Task $task, array $original, array $changes): void
    {
        foreach ($changes as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            if ((string) $oldValue === (string) $newValue) {
                continue;
            }

            $action = $field === 'status' ? 'status_changed' : 'updated';
            $this->log($task, $action, $field, $oldValue, $newValue);
        }
    }

    protected function stringify(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
