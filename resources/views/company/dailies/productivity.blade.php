@extends('layouts.app')

@section('title', 'Produtividade')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/productivity-dashboard.css') }}">
@endpush

@section('content')
@php
    $activeTab = $filters['tab'] ?? 'overview';
    $initialPayload = array_filter([
        'charts' => $charts ?? null,
        'comparatives' => $comparatives ?? null,
        'history' => $history ?? null,
        'employeeCharts' => isset($employeeDetail) ? ($employeeDetail['charts'] ?? null) : null,
        'employeeMetrics' => isset($employeeDetail) ? [
            'productivity_pct' => $employeeDetail['productivity_pct'] ?? 0,
            'team_avg_productivity' => $employeeDetail['team_avg_productivity'] ?? 0,
        ] : null,
    ], fn ($v) => $v !== null);
@endphp

<div class="container-fluid py-4 prod-dashboard"
     id="prod-dashboard"
     data-tab-url="{{ route('company.dailies.productivity.tab') }}"
     data-active-tab="{{ $activeTab }}"
     data-company-name="{{ $company->name }}"
     data-initial-payload="@json($initialPayload)">

    <header class="prod-hero">
        <div class="prod-hero-inner">
            <div>
                <div class="prod-hero-eyebrow">Centro de Análise Operacional</div>
                <h1>Dashboard de Produtividade</h1>
                <p class="prod-hero-sub" data-prod-period-label>{{ $period['label'] }} · {{ $period['business_days'] }} dias úteis · {{ $company->name }}</p>
            </div>
            <div class="prod-hero-actions">
                <a href="{{ route('company.dailies.index') }}" class="prod-action-btn">
                    <i class="fas fa-book"></i><span>{{ app(\App\Services\CompanyAuthorizationService::class)->canViewTeamDailies() ? 'Daily equipe' : 'Daily' }}</span>
                </a>
                <a href="{{ route('company.dailies.export.excel') }}" class="prod-action-btn success">
                    <i class="fas fa-file-excel"></i><span>Exportar</span>
                </a>
            </div>
        </div>
    </header>

    @include('company.dailies.productivity._filters')

    <nav class="prod-tabs-sticky">
        <div class="prod-tabs" role="tablist">
            @foreach([
                'overview' => ['Visão Geral', 'fa-th-large'],
                'collaborators' => ['Colaboradores', 'fa-users'],
                'ranking' => ['Rankings', 'fa-trophy'],
                'alerts' => ['Alertas', 'fa-bell'],
                'insights' => ['Insights', 'fa-brain'],
                'history' => ['Histórico', 'fa-history'],
                'table' => ['Tabela', 'fa-table'],
                'goals' => ['Metas', 'fa-bullseye'],
            ] as $key => [$label, $icon])
            <button type="button"
                    role="tab"
                    class="prod-tab {{ $activeTab === $key ? 'active' : '' }}"
                    data-prod-tab="{{ $key }}"
                    aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}">
                <i class="fas {{ $icon }}"></i>
                <span>{{ $label }}</span>
                @if($key === 'alerts')
                    <span class="prod-tab-badge {{ ($alert_count ?? count($alerts ?? [])) > 0 ? '' : 'd-none' }}" data-prod-alert-badge>{{ $alert_count ?? count($alerts ?? []) }}</span>
                @endif
            </button>
            @endforeach
        </div>
    </nav>

    <div class="prod-panel" data-prod-panel aria-live="polite" aria-busy="false">
        <div class="prod-panel-inner" data-prod-panel-inner>
            @include('company.dailies.productivity._'.$activeTab)
        </div>
    </div>
</div>

<template id="prod-skeleton-template">
    @include('company.dailies.productivity._skeleton')
</template>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
<script src="{{ asset('js/productivity-dashboard.js') }}" defer></script>
@endpush
