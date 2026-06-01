@extends('layouts.app')

@section('title', 'Novo Projeto')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Novo Projeto</h1>
            <p class="page-subtitle">Vincule um contrato e preencha as informações operacionais do projeto</p>
        </div>
        <a href="{{ route('company.projects.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ route('company.projects.store') }}" method="POST">
                        @csrf

                        @include('company.projects._contract-select')

                        <hr class="my-4">

                        <h6 class="text-muted mb-3">Informações do projeto</h6>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nome do projeto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="Ex: Portal do Cliente v2">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    @foreach(\App\Models\Project::STATUSES as $key => $label)
                                        @if(!in_array($key, ['planning', 'in_progress', 'completed', 'cancelled']))
                                            <option value="{{ $key }}" @selected(old('status', 'implementing') === $key)>{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="deadline" class="form-label">Prazo de entrega</label>
                                <input type="date" class="form-control @error('deadline') is-invalid @enderror" id="deadline" name="deadline" value="{{ old('deadline') }}">
                                @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descrição</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Resumo do que será entregue neste projeto">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="scope" class="form-label">Escopo</label>
                            <textarea class="form-control @error('scope') is-invalid @enderror" id="scope" name="scope" rows="4" placeholder="Detalhe módulos, funcionalidades e limites do escopo">{{ old('scope') }}</textarea>
                            @error('scope')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="deliverables" class="form-label">Entregas (uma por linha)</label>
                            <textarea class="form-control" id="deliverables" name="deliverables" rows="3" placeholder="Ex:&#10;Módulo financeiro&#10;App mobile&#10;Documentação técnica">{{ old('deliverables') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('company.projects.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Criar projeto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-info-circle text-primary me-1"></i> Como funciona</h6>
                    <p class="small text-muted mb-0">
                        Selecione o contrato e o sistema preenche automaticamente cliente, valor, parcelas e tipo financeiro.
                        Nesta tela você define apenas o que é específico da execução do projeto: nome, escopo, entregas e prazos operacionais.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
