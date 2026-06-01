@extends('layouts.app')

@section('title', 'Editar Task')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header mb-4">
        <h1 class="page-title">Editar Task</h1>
        @unless($isManager)
        <p class="page-subtitle text-muted">Como responsável, você pode editar campos limitados desta task.</p>
        @endunless
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.tasks.update', $task) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
                    </div>
                    @if($isManager)
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Projeto</label>
                        <select name="project_id" class="form-select" required>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id', $task->project_id) == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $task->description) }}</textarea>
                </div>

                <div class="row">
                    @if($isManager)
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="category" class="form-select">
                            @foreach(\App\Models\Task::CATEGORIES as $k => $v)
                                <option value="{{ $k }}" @selected(old('category', $task->category) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Prioridade</label>
                        <select name="priority" class="form-select">
                            @foreach(\App\Models\Task::PRIORITIES as $k => $v)
                                <option value="{{ $k }}" @selected(old('priority', $task->priority) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Responsável</label>
                        <select name="assignee_id" class="form-select">
                            <option value="">Sem responsável</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" @selected(old('assignee_id', $task->assignee_id) == $emp->id)>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach(\App\Models\Task::STATUSES as $k => $v)
                                <option value="{{ $k }}" @selected(old('status', $task->status) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tempo estimado (h)</label>
                        <input type="number" step="0.5" name="estimated_hours" class="form-control" value="{{ old('estimated_hours', $task->estimated_hours) }}">
                    </div>
                </div>

                @if($isManager)
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Solicitante</label>
                        <select name="requester_type" class="form-select">
                            <option value="internal" @selected(old('requester_type', $task->requester_type) === 'internal')>Interno</option>
                            <option value="client" @selected(old('requester_type', $task->requester_type) === 'client')>Cliente</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nome do solicitante</label>
                        <input type="text" name="requester_name" class="form-control" value="{{ old('requester_name', $task->requester_name) }}">
                    </div>
                </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('company.tasks.show', $task) }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
