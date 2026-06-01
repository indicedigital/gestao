@extends('layouts.app')

@section('title', 'Nova Solicitação')

@section('content')
<div class="container-fluid py-4">
    <h1 class="page-title mb-4">Nova Solicitação</h1>
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('portal.tasks.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Projeto</label>
                    <select name="project_id" class="form-select" required>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="category" class="form-select">
                            @foreach(\App\Models\Task::CATEGORIES as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prioridade</label>
                        <select name="priority" class="form-select">
                            @foreach(\App\Models\Task::PRIORITIES as $k => $v)
                                <option value="{{ $k }}" @selected($k === 'P2')>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Enviar solicitação</button>
            </form>
        </div>
    </div>
</div>
@endsection
