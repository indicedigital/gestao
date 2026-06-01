@extends('layouts.app')

@section('title', 'Nova Task')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header mb-4">
        <h1 class="page-title">Nova Task</h1>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.tasks.store') }}" method="POST">
                @csrf
                @if(request('redirect_to'))
                    <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
                @endif

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Projeto <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                            <option value="">Selecione</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" @selected(old('project_id', $selectedProject) == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="category" class="form-select">
                            @foreach(\App\Models\Task::CATEGORIES as $k => $v)
                                <option value="{{ $k }}" @selected(old('category', 'support') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Prioridade</label>
                        <select name="priority" class="form-select">
                            @foreach(\App\Models\Task::PRIORITIES as $k => $v)
                                <option value="{{ $k }}" @selected(old('priority', 'P2') === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Responsável</label>
                        <select name="assignee_id" class="form-select">
                            <option value="">Sem responsável</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" @selected(old('assignee_id') == $emp->id)>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tempo estimado (h)</label>
                        <input type="number" step="0.5" name="estimated_hours" class="form-control" value="{{ old('estimated_hours') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Solicitante</label>
                        <select name="requester_type" class="form-select">
                            <option value="internal" @selected(old('requester_type') === 'internal')>Interno</option>
                            <option value="client" @selected(old('requester_type') === 'client')>Cliente</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nome do solicitante</label>
                        <input type="text" name="requester_name" class="form-control" value="{{ old('requester_name', auth()->user()->name) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status inicial</label>
                        <select name="status" class="form-select">
                            @foreach(\App\Models\Task::STATUSES as $k => $v)
                                <option value="{{ $k }}" @selected(old('status', request('status', 'backlog')) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('company.tasks.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Criar Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
