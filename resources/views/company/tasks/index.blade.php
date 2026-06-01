@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
@include('company.tasks._helpers')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Tasks</h1>
            <p class="page-subtitle">Todas as tasks da empresa</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if($authz->canCreateTask())
            <a href="{{ route('company.tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova Task
            </a>
            @endif
            @if($authz->canManage())
            <a href="{{ route('company.tasks.export.excel', request()->query()) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-1"></i>Exportar
            </a>
            @endif
        </div>
    </div>

    <div class="card shadow mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-0">Projeto</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Task::STATUSES as $k => $v)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Prioridade</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach(\App\Models\Task::PRIORITIES as $k => $v)
                            <option value="{{ $k }}" @selected(request('priority') === $k)>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Categoria</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach(\App\Models\Task::CATEGORIES as $k => $v)
                            <option value="{{ $k }}" @selected(request('category') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">SLA</label>
                    <select name="sla" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="late" @selected(request('sla') === 'late')>Atrasadas</option>
                        <option value="on_time" @selected(request('sla') === 'on_time')>No prazo</option>
                    </select>
                </div>
                @if($authz->canManage())
                <div class="col-md-2">
                    <label class="form-label small mb-0">Responsável</label>
                    <select name="assignee_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(request('assignee_id') == $emp->id)>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Projeto</th>
                            <th>Categoria</th>
                            <th>Prioridade</th>
                            <th>Status</th>
                            <th>Responsável</th>
                            <th>SLA</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                            <td class="text-muted">{{ $task->id }}</td>
                            <td><strong>{{ $task->title }}</strong></td>
                            <td>{{ $task->project->name ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ \App\Models\Task::CATEGORIES[$task->category] ?? $task->category }}</span></td>
                            <td><span class="badge bg-{{ $priorityColors[$task->priority] ?? 'secondary' }}">{{ $task->priority }}</span></td>
                            <td><span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ \App\Models\Task::STATUSES[$task->status] ?? $task->status }}</span></td>
                            <td>{{ $task->assignee->name ?? '—' }}</td>
                            <td>
                                @if($task->sla_deadline)
                                    <span class="{{ $task->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ $task->sla_deadline->format('d/m H:i') }}</span>
                                @else — @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('company.tasks.show', $task) }}" class="btn btn-sm btn-info text-white" title="Ver"><i class="fas fa-eye"></i></a>
                                @if($authz->canUpdateTask($task))
                                <a href="{{ route('company.tasks.edit', $task) }}" class="btn btn-sm btn-warning text-white" title="Editar"><i class="fas fa-edit"></i></a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-tasks fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-0">Nenhuma task encontrada com os filtros aplicados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
                <div class="d-flex justify-content-center py-3">{{ $tasks->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
