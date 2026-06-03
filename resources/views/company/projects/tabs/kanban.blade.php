@php $categoryLabels = \App\Models\Task::CATEGORIES; @endphp

<div class="kanban-toolbar">
    <form method="GET" action="{{ route('company.projects.kanban', $project) }}" class="row g-2 align-items-end project-tab-form">
        <div class="col-md-2">
            <label class="form-label small mb-1">Colaborador</label>
            <select name="assignee_id" class="form-select form-select-sm">
                <option value="">Todos</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" @selected(request('assignee_id') == $emp->id)>{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Prioridade</label>
            <select name="priority" class="form-select form-select-sm">
                <option value="">Todas</option>
                @foreach(\App\Models\Task::PRIORITIES as $k => $v)
                    <option value="{{ $k }}" @selected(request('priority') === $k)>{{ $k }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Categoria</label>
            <select name="category" class="form-select form-select-sm">
                <option value="">Todas</option>
                @foreach($categoryLabels as $k => $v)
                    <option value="{{ $k }}" @selected(request('category') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">SLA</label>
            <select name="sla" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="late" @selected(request('sla') === 'late')>Atrasadas</option>
                <option value="on_time" @selected(request('sla') === 'on_time')>No prazo</option>
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="compact" value="1" id="compact" @checked($compactView)>
                <label class="form-check-label small" for="compact">Compacta</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="subtasks" value="1" id="subtasks" @checked($showSubtasks)>
                <label class="form-check-label small" for="subtasks">Subtasks</label>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-primary w-100">Filtrar</button>
        </div>
    </form>
</div>

<div class="kanban-board" id="kanban-board" data-can-move="{{ $canMove ? '1' : '0' }}">
    @foreach($kanbanColumns as $column)
        <div class="kanban-column col-head-{{ $column['key'] }}" data-status="{{ $column['key'] }}">
            <div class="kanban-col-head">
                <span class="kanban-col-badge">{{ $column['label'] }}</span>
                <span class="kanban-col-count">{{ $column['tasks']->count() }}</span>
            </div>
            <div class="kanban-col-body kanban-tasks" data-status="{{ $column['key'] }}">
                @foreach($column['tasks'] as $task)
                    @php
                        $slaClass = $task->isOverdue() ? 'overdue' : ($task->slaAlertLevel() === 'warning' ? 'sla-warning' : ($task->slaAlertLevel() === 'info' ? 'sla-info' : ''));
                    @endphp
                    <div class="kanban-task {{ $compactView ? 'compact' : '' }} {{ $slaClass }}"
                         draggable="{{ $canMove ? 'true' : 'false' }}"
                         data-task-id="{{ $task->id }}">
                        <a href="{{ route('company.tasks.show', $task) }}" class="kanban-task-title">{{ $task->title }}</a>
                        <div class="kanban-task-meta">
                            <span class="kanban-priority {{ $task->priority }}">{{ $task->priority }}</span>
                            @if($task->assignee)
                                <span><i class="fas fa-user me-1"></i>{{ Str::limit($task->assignee->name, 14) }}</span>
                            @endif
                            @if($task->sla_deadline)
                                <span class="{{ $task->isOverdue() ? 'text-danger' : '' }}">
                                    <i class="fas fa-clock me-1"></i>{{ $task->sla_deadline->format('d/m') }}
                                </span>
                            @endif
                            @if($task->comments_count)
                                <span><i class="fas fa-comment me-1"></i>{{ $task->comments_count }}</span>
                            @endif
                        </div>
                        @if($showSubtasks && $task->subtasks->count())
                            @foreach($task->subtasks->take(3) as $st)
                                <div class="subtask-mini">{{ Str::limit($st->title, 30) }}</div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
            @if(app(\App\Services\CompanyAuthorizationService::class)->canCreateTaskOnProject($project))
            <a href="{{ route('company.tasks.create', ['project_id' => $project->id, 'status' => $column['key'], 'redirect_to' => 'kanban']) }}" class="kanban-add-task">
                <i class="fas fa-plus me-1"></i> Adicionar Tarefa
            </a>
            @endif
        </div>
    @endforeach
</div>
