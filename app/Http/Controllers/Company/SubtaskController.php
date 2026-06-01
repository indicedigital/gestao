<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\Subtask;
use App\Models\Task;
use App\Rules\BelongsToCompany;
use App\Services\TaskHistoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubtaskController extends Controller
{
    use InteractsWithCompany;

    public function __construct(protected TaskHistoryService $historyService) {}

    public function store(Request $request, Task $task)
    {
        abort_unless($this->authz()->canManageSubtasks($task), 403);

        $company = $this->getCurrentCompany();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'assignee_id' => ['nullable', new BelongsToCompany('employees', $company->id)],
            'due_date' => ['nullable', 'date'],
        ]);

        $position = (int) $task->subtasks()->max('position') + 1;
        $subtask = $task->subtasks()->create($validated + ['position' => $position]);
        $this->historyService->log($task, 'subtask_created', 'subtask', null, $subtask->title);

        return back()->with('success', 'Subtask adicionada.');
    }

    public function update(Request $request, Task $task, Subtask $subtask)
    {
        abort_unless($this->authz()->canManageSubtasks($task), 403);
        abort_unless($subtask->task_id === $task->id, 404);

        $company = $this->getCurrentCompany();
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'assignee_id' => ['nullable', new BelongsToCompany('employees', $company->id)],
            'status' => ['sometimes', Rule::in(array_keys(Subtask::STATUSES))],
            'due_date' => ['nullable', 'date'],
        ]);

        $subtask->update($validated);
        $this->historyService->log($task, 'subtask_updated', 'subtask', null, $subtask->title);

        return back()->with('success', 'Subtask atualizada.');
    }

    public function destroy(Task $task, Subtask $subtask)
    {
        abort_unless($this->authz()->canManageSubtasks($task), 403);
        abort_unless($subtask->task_id === $task->id, 404);

        $title = $subtask->title;
        $subtask->delete();
        $this->historyService->log($task, 'subtask_deleted', 'subtask', $title, null);

        return back()->with('success', 'Subtask removida.');
    }
}
