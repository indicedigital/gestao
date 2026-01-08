@extends('layouts.app')

@section('title', 'Detalhes do Projeto')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">{{ $project->name }}</h1>
            <p class="page-subtitle">Detalhes do projeto</p>
        </div>
        <div>
            <a href="{{ route('company.projects.edit', $project) }}" class="btn btn-warning text-white">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <a href="{{ route('company.projects.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Informações do Projeto</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Cliente:</dt>
                        <dd class="col-sm-9">{{ $project->client->name ?? '-' }}</dd>

                        <dt class="col-sm-3">Tipo:</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-{{ $project->type === 'fixed' ? 'primary' : 'info' }}">
                                {{ $project->type === 'fixed' ? 'Fechado' : 'Recorrente' }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Valor Total:</dt>
                        <dd class="col-sm-9">R$ {{ number_format($project->total_value, 2, ',', '.') }}</dd>

                        <dt class="col-sm-3">Parcelas:</dt>
                        <dd class="col-sm-9">{{ $project->installments }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            @php
                                $statusColors = [
                                    'planning' => 'secondary',
                                    'in_progress' => 'primary',
                                    'paused' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $statusColor = $statusColors[$project->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($project->status) }}</span>
                        </dd>

                        @if($project->start_date)
                        <dt class="col-sm-3">Data de Início:</dt>
                        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') }}</dd>
                        @endif

                        @if($project->deadline)
                        <dt class="col-sm-3">Prazo:</dt>
                        <dd class="col-sm-9">{{ \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') }}</dd>
                        @endif

                        @if($project->description)
                        <dt class="col-sm-3">Descrição:</dt>
                        <dd class="col-sm-9">{{ $project->description }}</dd>
                        @endif

                        @if($project->scope)
                        <dt class="col-sm-3">Escopo:</dt>
                        <dd class="col-sm-9">{{ $project->scope }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Estatísticas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Custo</div>
                        <div class="h5 mb-0">R$ {{ number_format($project->cost ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Lucro</div>
                        <div class="h5 mb-0 text-success">R$ {{ number_format($project->profit ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Margem de Lucro</div>
                        <div class="h5 mb-0">{{ number_format($project->profit_margin_percent ?? 0, 2, ',', '.') }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
