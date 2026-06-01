@extends('layouts.app')

@section('title', 'Editar Projeto')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Editar Projeto</h1>
            <p class="page-subtitle">{{ $project->name }}</p>
        </div>
        <a href="{{ route('company.projects.show', $project) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')

                @include('company.projects._contract-select', ['selectedContractId' => $project->contract_id])

                <hr class="my-4">

                <h6 class="text-muted mb-3">Informações do projeto</h6>

                <div class="mb-3">
                    <label for="name" class="form-label">Nome do projeto <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $project->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            @foreach(\App\Models\Project::STATUSES as $key => $label)
                                @if(!in_array($key, ['planning', 'in_progress']))
                                    <option value="{{ $key }}" @selected(old('status', $project->status) === $key)>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="deadline" class="form-label">Prazo de entrega</label>
                        <input type="date" class="form-control @error('deadline') is-invalid @enderror" id="deadline" name="deadline" value="{{ old('deadline', $project->deadline?->format('Y-m-d')) }}">
                        @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Descrição</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $project->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="scope" class="form-label">Escopo</label>
                    <textarea class="form-control @error('scope') is-invalid @enderror" id="scope" name="scope" rows="4">{{ old('scope', $project->scope) }}</textarea>
                    @error('scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="deliverables" class="form-label">Entregas (uma por linha)</label>
                    <textarea class="form-control" id="deliverables" name="deliverables" rows="3">@php
                        $deliverables = old('deliverables', $project->deliverables);
                        echo is_array($deliverables) ? implode("\n", $deliverables) : ($deliverables ?? '');
                    @endphp</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('company.projects.show', $project) }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
