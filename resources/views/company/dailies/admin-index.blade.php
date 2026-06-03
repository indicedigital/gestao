@extends('layouts.app')

@section('title', 'Daily — Equipe')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dailies.css') }}">
@endpush

@section('content')
<div class="daily-page daily-admin-page">

    <div class="daily-header">
        <div>
            <h1 class="page-title mb-1">Daily — Equipe</h1>
            <p class="page-subtitle mb-0">Acompanhe os registros diários de todos os colaboradores</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProductivity())
            <a href="{{ route('company.dailies.productivity') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i>Produtividade
            </a>
            <a href="{{ route('company.dailies.export.excel') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>Exportar mês
            </a>
            @endif
        </div>
    </div>

    <div class="daily-kpi-strip daily-admin-kpis">
        <div class="daily-kpi primary">
            <div class="daily-kpi-label">Horas no dia</div>
            <div class="daily-kpi-value">{{ number_format($teamDayTotal, 1, ',', '.') }}h</div>
        </div>
        <div class="daily-kpi success">
            <div class="daily-kpi-label">Colaboradores com registro</div>
            <div class="daily-kpi-value">{{ $teamWithEntries }} / {{ $collaborators->count() }}</div>
        </div>
        <div class="daily-kpi info">
            <div class="daily-kpi-label">Data</div>
            <div class="daily-kpi-value" style="font-size:18px;">{{ $selectedDate->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="daily-panel mb-3">
        <div class="daily-panel-body">
            <form method="GET" class="daily-admin-filters row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label">Data</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-5 col-sm-6">
                    <label class="form-label">Buscar colaborador</label>
                    <input type="search" name="q" class="form-control" value="{{ $search }}" placeholder="Nome, e-mail ou cargo...">
                </div>
                <div class="col-md-2 col-sm-6">
                    <input type="hidden" name="view" value="{{ $viewMode }}">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                </div>
                <div class="col-md-2 col-sm-6">
                    <div class="daily-view-switch btn-group w-100" role="group">
                        <a href="{{ route('company.dailies.index', ['date' => $date, 'view' => 'cards', 'q' => $search]) }}"
                           class="btn btn-sm {{ $viewMode === 'cards' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-th-large"></i>
                        </a>
                        <a href="{{ route('company.dailies.index', ['date' => $date, 'view' => 'table', 'q' => $search]) }}"
                           class="btn btn-sm {{ $viewMode === 'table' ? 'btn-primary' : 'btn-outline-primary' }}">
                            <i class="fas fa-table"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($viewMode === 'table')
        <div class="daily-panel">
            <div class="daily-panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 daily-admin-table">
                        <thead>
                            <tr>
                                <th>Colaborador</th>
                                <th>Cargo</th>
                                <th>Registros</th>
                                <th>Horas</th>
                                <th>Meta (8h)</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collaborators as $row)
                            <tr class="{{ $row['has_entries'] ? '' : 'daily-admin-row-empty' }}">
                                <td>
                                    <strong>{{ $row['employee']->name }}</strong>
                                    @if($row['employee']->email)
                                        <div class="text-muted small">{{ $row['employee']->email }}</div>
                                    @endif
                                </td>
                                <td>{{ $row['employee']->position ?? $row['employee']->role ?? '—' }}</td>
                                <td>{{ $row['entries'] }}</td>
                                <td>{{ number_format($row['total_hours'], 1, ',', '.') }}h</td>
                                <td>
                                    <div class="daily-admin-progress">
                                        <div class="daily-admin-progress-bar" style="width: {{ $row['progress'] }}%"></div>
                                    </div>
                                    <span class="small text-muted">{{ $row['progress'] }}%</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('company.dailies.collaborator', ['employee' => $row['employee'], 'date' => $date]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>Ver dailies
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    Nenhum colaborador encontrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="daily-admin-cards">
            @forelse($collaborators as $row)
            <a href="{{ route('company.dailies.collaborator', ['employee' => $row['employee'], 'date' => $date]) }}"
               class="daily-admin-card {{ $row['has_entries'] ? 'has-data' : 'no-data' }}">
                <div class="daily-admin-card-head">
                    <div>
                        <div class="daily-admin-card-name">{{ $row['employee']->name }}</div>
                        <div class="daily-admin-card-role">{{ $row['employee']->position ?? $row['employee']->role ?? 'Colaborador' }}</div>
                    </div>
                    <span class="daily-admin-card-hours">{{ number_format($row['total_hours'], 1, ',', '.') }}h</span>
                </div>
                <div class="daily-admin-card-meta">
                    {{ $row['entries'] }} {{ $row['entries'] == 1 ? 'registro' : 'registros' }}
                    · Meta {{ $row['progress'] }}%
                </div>
                <div class="daily-admin-progress mt-2">
                    <div class="daily-admin-progress-bar" style="width: {{ $row['progress'] }}%"></div>
                </div>
            </a>
            @empty
            <div class="daily-panel w-100">
                <div class="daily-empty">
                    <i class="fas fa-users"></i>
                    Nenhum colaborador encontrado.
                </div>
            </div>
            @endforelse
        </div>
    @endif
</div>
@endsection
