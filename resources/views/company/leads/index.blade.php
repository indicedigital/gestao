@extends('layouts.app')

@section('title', 'Leads')

@section('content')
@php
    $scopeLabels = [
        'aplicativo' => 'Aplicativo',
        'site' => 'Site',
        'sistema' => 'Sistema',
        'landing_page' => 'Landing page',
        'automacao' => 'Automação',
        'outro' => 'Outro',
    ];
    $kindLabels = [
        'desenvolvimento' => 'Desenvolvimento',
        'correcoes' => 'Correções',
        'melhorias' => 'Melhorias',
    ];
@endphp
<style>
    .lead-view-switch .btn {
        min-width: 120px;
    }
    .kanban-wrap {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 6px;
    }
    .kanban-column {
        min-width: 280px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px;
    }
    .kanban-col-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .kanban-col-title {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
    }
    .kanban-col-count {
        font-size: 12px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #334155;
        padding: 3px 9px;
    }
    .lead-card {
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        background: #fff;
        padding: 10px;
        margin-bottom: 10px;
    }
    .lead-card-title {
        font-weight: 600;
        color: #0f172a;
    }
    .lead-card-meta {
        font-size: 12px;
        color: #64748b;
    }
</style>
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Leads</h1>
            <p class="page-subtitle">Cadastre reuniões e detalhes dos projetos em negociação.</p>
        </div>
        <div class="d-flex gap-2 align-items-center lead-view-switch">
            <a href="{{ route('company.leads.index', ['view' => 'kanban']) }}" class="btn btn-sm {{ $viewMode === 'kanban' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-columns me-1"></i>Kanban
            </a>
            <a href="{{ route('company.leads.index', ['view' => 'table']) }}" class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-table me-1"></i>Tabela
            </a>
            <a href="{{ route('company.leads.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Novo Lead
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            @if($viewMode === 'kanban')
                <div class="kanban-wrap">
                    @foreach($kanbanColumns as $column)
                        <div class="kanban-column">
                            <div class="kanban-col-header">
                                <div class="kanban-col-title">{{ $column['label'] }}</div>
                                <span class="kanban-col-count">{{ $column['leads']->count() }}</span>
                            </div>

                            @forelse($column['leads'] as $lead)
                                @php
                                    $labels = collect($lead->project_scopes ?? [])
                                        ->map(fn($scope) => $scopeLabels[$scope] ?? $scope)
                                        ->implode(', ');
                                @endphp
                                <div class="lead-card">
                                    <div class="lead-card-title">{{ $lead->project_name }}</div>
                                    <div class="lead-card-meta mb-1">{{ $labels ?: 'Sem tipo de escopo' }}</div>
                                    <div class="lead-card-meta">Tipo: {{ $kindLabels[$lead->project_kind] ?? ucfirst((string) $lead->project_kind) }}</div>
                                    <div class="lead-card-meta">Reunião: {{ optional($lead->meeting_date)->format('d/m/Y') ?: '-' }}</div>
                                    <div class="lead-card-meta">Orçamento: {{ $lead->expected_budget !== null ? 'R$ '.number_format((float) $lead->expected_budget, 2, ',', '.') : '-' }}</div>
                                    <div class="d-flex gap-1 mt-2">
                                        <a href="{{ route('company.leads.edit', $lead) }}" class="btn btn-sm btn-warning text-white" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('company.leads.destroy', $lead) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este lead?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small">Nenhum lead neste estágio.</div>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Projeto</th>
                                <th>Reunião</th>
                                <th>Tipo de trabalho</th>
                                <th>Estágio</th>
                                <th>Orçamento</th>
                                <th>Prazo</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tableLeads as $lead)
                            <tr>
                                <td>{{ $lead->id }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $lead->project_name }}</div>
                                    @php
                                        $labels = collect($lead->project_scopes ?? [])
                                            ->map(fn($scope) => $scopeLabels[$scope] ?? $scope)
                                            ->implode(', ');
                                    @endphp
                                    <small class="text-muted">{{ $labels ?: '-' }}</small>
                                </td>
                                <td>{{ optional($lead->meeting_date)->format('d/m/Y') ?: '-' }}</td>
                                <td>{{ $kindLabels[$lead->project_kind] ?? ucfirst((string) $lead->project_kind) }}</td>
                                <td>{{ $lead->project_stage ?: '-' }}</td>
                                <td>{{ $lead->expected_budget !== null ? 'R$ '.number_format((float) $lead->expected_budget, 2, ',', '.') : '-' }}</td>
                                <td>{{ optional($lead->expected_deadline)->format('d/m/Y') ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('company.leads.edit', $lead) }}" class="btn btn-sm btn-warning text-white" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('company.leads.destroy', $lead) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este lead?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Nenhum lead cadastrado.</p>
                                    <a href="{{ route('company.leads.create') }}" class="btn btn-primary">Cadastrar Primeiro Lead</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($tableLeads->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $tableLeads->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection

