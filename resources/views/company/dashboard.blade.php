@extends('layouts.app')

@section('title', 'Dashboard Financeiro')

@push('styles')
<style>
    body {
        background: #f7fafc;
    }
    
    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s;
        height: 100%;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        border-color: transparent;
    }
    .kpi-card.primary { border-top: 4px solid #5e72e4; }
    .kpi-card.success { border-top: 4px solid #2dce89; }
    .kpi-card.danger { border-top: 4px solid #f5365c; }
    .kpi-card.warning { border-top: 4px solid #fb6340; }
    .kpi-card.info { border-top: 4px solid #11cdef; }
    .kpi-card.secondary { border-top: 4px solid #6c757d; }
    .kpi-card.clickable { cursor: pointer; }
    .cash-report-modal .modal-dialog {
        max-width: 1650px;
    }
    .cash-indicators-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .cash-indicator-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 12px;
        background: #f8fafc;
        min-height: 74px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .cash-indicator-card .label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .3px;
        margin-bottom: 3px;
    }
    .cash-indicator-card .value {
        font-size: 18px;
        font-weight: 700;
        color: #1a202c;
        line-height: 1.2;
    }
    .cash-indicator-card.highlight {
        background: linear-gradient(135deg, #eef2ff 0%, #e0f2fe 100%);
        border-color: #c7d2fe;
    }
    
    .kpi-card h6 {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 12px;
    }
    
    .kpi-card h3 {
        font-size: 32px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }
    
    .variation-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .table-overdue tbody tr td {
        background-color: var(--table-danger-bg);
        color: var(--table-danger-text);
    }
    
    .card-modern {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    
    .card-modern .card-header {
        background: white;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 24px;
        border-radius: 16px 16px 0 0;
        font-weight: 600;
        font-size: 16px;
    }
    
    .card-modern .card-body {
        padding: 24px;
    }
    
    .page-header-modern {
        margin-bottom: 32px;
    }
    
    .page-header-modern h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 8px;
    }
    
    .page-header-modern p {
        color: #64748b;
        font-size: 14px;
    }

    /* Indicadores Operacionais */
    .operational-indicator-item {
        padding: 16px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        height: 100%;
        transition: all 0.2s;
    }

    .operational-indicator-item:hover {
        background: #ffffff;
        border-color: #dee2e6;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .indicator-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .indicator-icon i {
        font-size: 16px;
    }

    .indicator-stats {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
    }

    .stat-label {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
    }

    .stat-value {
        font-size: 14px;
        font-weight: 700;
        color: #1a202c;
    }

    .indicator-expenses {
        margin-top: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .expense-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .expense-category {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
    }

    .expense-value {
        font-size: 13px;
        font-weight: 700;
        color: #1a202c;
    }
</style>
@endpush

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Dashboard Financeiro</h1>
                <p>Visão geral da saúde financeira da empresa • 
                   @if($monthFilter !== now()->format('Y-m'))
                       Período: {{ $selectedMonth->locale('pt_BR')->translatedFormat('F \d\e Y') }} • 
                   @endif
                   Atualizado em {{ now()->format('d/m/Y \à\s H:i') }}
                </p>
            </div>
            <div>
                <form method="GET" action="{{ route('company.dashboard') }}" class="d-flex align-items-center gap-2" id="month-filter-form">
                    <label for="month_filter" class="form-label mb-0 small text-muted">Filtrar por mês:</label>
                    <input type="month" 
                           class="form-control form-control-sm" 
                           id="month_filter" 
                           name="month" 
                           value="{{ $monthFilter }}"
                           style="width: 180px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <button type="submit" class="btn btn-sm btn-primary" style="border-radius: 8px;">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    @if($monthFilter !== now()->format('Y-m'))
                    <a href="{{ route('company.dashboard') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;" title="Voltar ao mês atual">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                    @endif
                </form>
            </div>
        </div>
    </div>


    <!-- 1. LINHA 1 - INDICADORES-CHAVE (KPIs) -->
    <div class="row mb-4">
        <!-- Card 1 - Faturamento do Mês -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card primary">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Faturamento Realizado</h6>
                        <h3>R$ {{ number_format($revenueRealized, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-chart-line fa-lg text-primary"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small">Previsto: R$ {{ number_format($totalRevenueForecast, 0, ',', '.') }}</span>
                    @if($revenueVariation >= 0)
                        <span class="badge bg-success variation-badge">
                            <i class="fas fa-arrow-up"></i> {{ number_format(abs($revenueVariation), 1) }}%
                        </span>
                    @else
                        <span class="badge bg-danger variation-badge">
                            <i class="fas fa-arrow-down"></i> {{ number_format(abs($revenueVariation), 1) }}%
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 2 - Despesas do Mês -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card danger">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Despesas Realizadas</h6>
                        <h3>R$ {{ number_format($expensesRealized, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-arrow-trend-down fa-lg text-danger"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small">Previsto: R$ {{ number_format($totalExpensesForecast, 0, ',', '.') }}</span>
                    @if($expensesRealized <= $expensesForecast)
                        <span class="badge bg-success variation-badge">
                            <i class="fas fa-check"></i> Dentro
                        </span>
                    @else
                        <span class="badge bg-danger variation-badge">
                            <i class="fas fa-exclamation"></i> Estouro
                        </span>
                    @endif
                </div>
                <p class="mb-0 mt-1 small text-muted" style="font-size: 11px;">
                </p>
            </div>
        </div>

        <!-- Card 3 - Lucro do Mês -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card {{ $profitRealized >= 0 ? 'success' : 'danger' }}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Lucro Realizado</h6>
                        <h3>R$ {{ number_format($profitRealized, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-{{ $profitRealized >= 0 ? 'success' : 'danger' }} bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-coins fa-lg text-{{ $profitRealized >= 0 ? 'success' : 'danger' }}"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge bg-{{ $profitMargin >= 20 ? 'success' : ($profitMargin >= 10 ? 'warning' : 'danger') }} variation-badge">
                        Margem: {{ number_format($profitMargin, 1) }}%
                    </span>
                    <span class="text-muted small">Previsto: R$ {{ number_format($profitForecast, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Card 4 - Caixa Atual -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card info clickable" data-bs-toggle="modal" data-bs-target="#cashReportModal" title="Clique para ver relatório de caixa">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Caixa Disponível</h6>
                        <h3>R$ {{ number_format($availableCash, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-wallet fa-lg text-info"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted small">Fôlego: {{ number_format($monthsOfRunway, 1) }} meses</span>
                    <button type="button"
                            class="btn btn-sm btn-outline-info"
                            data-bs-toggle="modal"
                            data-bs-target="#updateCashModal"
                            onclick="event.stopPropagation();"
                            title="Atualizar valor do caixa">
                        <i class="fas fa-edit me-1"></i>Editar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Card 5 - Contas Vencidas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card danger">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Vencidas a Receber</h6>
                        <h3>R$ {{ number_format($totalOverdueReceivables, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-exclamation-circle fa-lg text-danger"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge bg-danger variation-badge">{{ $countOverdueReceivables }} títulos</span>
                    @if($maxOverdueDays > 0)
                        <span class="text-muted small">Maior atraso: {{ $maxOverdueDays }}d</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 6 - Vencidas a Pagar -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card warning">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Vencidas a Pagar</h6>
                        <h3>R$ {{ number_format($totalOverduePayables, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-exclamation-triangle fa-lg text-warning"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-warning variation-badge">{{ $countOverduePayables }} títulos</span>
                </div>
            </div>
        </div>

        <!-- Card 7 - MRR (Receita Recorrente Mensal) -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card success">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>MRR (Recorrente)</h6>
                        <h3>R$ {{ number_format($mrrValue, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-sync-alt fa-lg text-success"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="text-muted small">{{ $mrrActiveContractsCount }} contratos recorrentes ativos</span>
                </div>
            </div>
        </div>

        <!-- Card 8 - Burn Rate -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card secondary">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Burn Rate Mensal</h6>
                        <h3>R$ {{ number_format($burnRate, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-fire fa-lg text-secondary"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="text-muted small">Consumo médio/mês</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. LINHA 2 - GRÁFICOS PRINCIPAIS -->
    <div class="row mb-4">
        <!-- Gráfico 1 - Faturamento x Despesas x Lucro -->
        <div class="col-xl-8 mb-4">
            <div class="card-modern">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">Faturamento x Despesas x Lucro (Últimos 6 meses + Projeções)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueExpensesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Despesas por Categoria -->
        <div class="col-xl-4 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">Despesas por Categoria ({{ $selectedMonth->locale('pt_BR')->translatedFormat('F \d\e Y') }})</h6>
                </div>
                <div class="card-body">
                    @if($expensesByCategoryChart->count() > 0)
                    <div class="chart-container" style="height: 240px;">
                        <canvas id="expensesByCategoryChart"></canvas>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <p class="text-muted small">Nenhuma despesa registrada neste mês</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 3. LINHA 3 - PROJEÇÕES -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">Previsão Financeira - Próximos 3 Meses</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="projectionsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. LINHA 4 - CONTAS A PAGAR E RECEBER -->
    <div class="row mb-4">
        <!-- Próximos Vencimentos - A Receber -->
        <div class="col-xl-6 mb-4">
            <div class="card-modern">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#receivables-7">7 dias</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#receivables-15">15 dias</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#receivables-30">30 dias</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-arrow-circle-down text-primary me-2"></i>Próximas Contas a Receber</h6>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="receivables-7">
                            @include('company.dashboard.partials.receivables-table', ['receivables' => $upcomingReceivables7])
                        </div>
                        <div class="tab-pane fade" id="receivables-15">
                            @include('company.dashboard.partials.receivables-table', ['receivables' => $upcomingReceivables15])
                        </div>
                        <div class="tab-pane fade" id="receivables-30">
                            @include('company.dashboard.partials.receivables-table', ['receivables' => $upcomingReceivables30])
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximos Vencimentos - A Pagar -->
        <div class="col-xl-6 mb-4">
            <div class="card-modern">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#payables-7">7 dias</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#payables-15">15 dias</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#payables-30">30 dias</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-arrow-circle-up text-danger me-2"></i>Próximas Contas a Pagar</h6>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="payables-7">
                            @include('company.dashboard.partials.payables-table', ['payables' => $upcomingPayables7])
                        </div>
                        <div class="tab-pane fade" id="payables-15">
                            @include('company.dashboard.partials.payables-table', ['payables' => $upcomingPayables15])
                        </div>
                        <div class="tab-pane fade" id="payables-30">
                            @include('company.dashboard.partials.payables-table', ['payables' => $upcomingPayables30])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contas Vencidas -->
    <div class="row mb-4">
        <div class="col-xl-6 mb-4">
            <div class="card-modern" style="border-top: 4px solid #f5365c;">
                <div class="card-header" style="background: linear-gradient(135deg, #f5365c 0%, #dc2626 100%); color: white;">
                    <h6 class="mb-0"><i class="fas fa-exclamation-circle me-2"></i>Contas Vencidas a Receber</h6>
                </div>
                <div class="card-body">
                    @if($overdueReceivables->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-overdue">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Atraso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueReceivables as $receivable)
                                <tr>
                                    <td>{{ $receivable->client->name ?? '-' }}</td>
                                    <td>
                                        {{ $receivable->description }}
                                        @if($receivable->status === 'partial')
                                            <span class="badge bg-info ms-2">Parcial</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>R$ {{ number_format($receivable->overdue_value ?? ($receivable->value - ($receivable->paid_value ?? 0)), 2, ',', '.') }}</strong>
                                        @if($receivable->status === 'partial')
                                            <br><small class="text-muted">
                                                Total: R$ {{ number_format($receivable->value, 2, ',', '.') }} | 
                                                Pago: R$ {{ number_format($receivable->paid_value ?? 0, 2, ',', '.') }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $receivable->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-danger">
                                            {{ (int) floor(now()->diffInDays($receivable->due_date, false)) }} dias
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('company.receivables.show', $receivable) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-3">Nenhuma conta vencida a receber</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-6 mb-4">
            <div class="card-modern" style="border-top: 4px solid #fb6340;">
                <div class="card-header" style="background: linear-gradient(135deg, #fb6340 0%, #f59e0b 100%); color: white;">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Contas Vencidas a Pagar</h6>
                </div>
                <div class="card-body">
                    @if($overduePayables->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-overdue">
                            <thead>
                                <tr>
                                    <th>Descrição</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Vencimento</th>
                                    <th>Atraso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overduePayables as $payable)
                                <tr>
                                    <td>{{ $payable->description }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($payable->type) }}</span>
                                    </td>
                                    <td><strong>R$ {{ number_format($payable->value, 2, ',', '.') }}</strong></td>
                                    <td>{{ $payable->due_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-warning">
                                            {{ now()->diffInDays($payable->due_date) }} dias
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('company.payables.show', $payable) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-3">Nenhuma conta vencida a pagar</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 5. LINHA 5 - OPERACIONAL E CONTRATOS -->
    <div class="row mb-4">
        <!-- Indicadores Operacionais -->
        <div class="col-xl-6 mb-4">
            <div class="card-modern">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-chart-bar me-2 text-primary"></i>Indicadores Operacionais</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Clientes -->
                        <div class="col-md-6">
                            <div class="operational-indicator-item">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="indicator-icon bg-primary bg-opacity-10 me-2">
                                        <i class="fas fa-users text-primary"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold text-dark">Clientes</h6>
                                </div>
                                <div class="indicator-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Total</span>
                                        <span class="stat-value">{{ $totalClients }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Ativos</span>
                                        <span class="stat-value text-success">{{ $activeClients }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Inadimplentes</span>
                                        <span class="stat-value text-danger">{{ $overdueClients }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contratos -->
                        <div class="col-md-6">
                            <div class="operational-indicator-item">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="indicator-icon bg-info bg-opacity-10 me-2">
                                        <i class="fas fa-file-contract text-info"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold text-dark">Contratos</h6>
                                </div>
                                <div class="indicator-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Ativos</span>
                                        <span class="stat-value">{{ $totalContracts }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Vencem 30d</span>
                                        <span class="stat-value text-warning">{{ $contractsExpiring30 }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Vencem 60d</span>
                                        <span class="stat-value text-info">{{ $contractsExpiring60 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipe -->
                        <div class="col-md-6">
                            <div class="operational-indicator-item">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="indicator-icon bg-success bg-opacity-10 me-2">
                                        <i class="fas fa-user-tie text-success"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold text-dark">Equipe</h6>
                                </div>
                                <div class="indicator-stats">
                                    <div class="stat-item">
                                        <span class="stat-label">Total</span>
                                        <span class="stat-value">{{ $totalEmployees }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">CLT</span>
                                        <span class="stat-value">{{ $cltEmployees }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">PJ</span>
                                        <span class="stat-value">{{ $pjEmployees }}</span>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        Custo mensal: <strong class="ms-1">R$ {{ number_format($monthlyPersonnelCost, 2, ',', '.') }}</strong>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Categorias de Despesas -->
                        <div class="col-md-6">
                            <div class="operational-indicator-item">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="indicator-icon bg-warning bg-opacity-10 me-2">
                                        <i class="fas fa-tags text-warning"></i>
                                    </div>
                                    <h6 class="mb-0 fw-semibold text-dark">Despesas (Mês)</h6>
                                </div>
                                <div class="indicator-expenses">
                                    @if(count($expensesByCategory) > 0)
                                        @foreach($expensesByCategory as $category => $total)
                                        <div class="expense-item">
                                            <span class="expense-category">{{ ucfirst($category) }}</span>
                                            <span class="expense-value">R$ {{ number_format($total, 2, ',', '.') }}</span>
                                        </div>
                                        @endforeach
                                    @else
                                        <small class="text-muted">Nenhuma despesa registrada</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contratos a Vencer -->
        <div class="col-xl-6 mb-4">
            <div class="card-modern">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold">Contratos a Vencer (Próximos 90 dias)</h6>
                </div>
                <div class="card-body">
                    @if($contractsExpiringList->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Contrato</th>
                                    <th>Valor Mensal</th>
                                    <th>Vencimento</th>
                                    <th>Dias</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contractsExpiringList as $contract)
                                <tr>
                                    <td>{{ $contract->client->name ?? '-' }}</td>
                                    <td>{{ $contract->name }}</td>
                                    <td>R$ {{ number_format($contract->value, 2, ',', '.') }}</td>
                                    <td>{{ $contract->end_date->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $daysLeft = (int) floor(now()->diffInDays($contract->end_date, false));
                                        @endphp
                                        <span class="badge bg-{{ $daysLeft <= 30 ? 'danger' : ($daysLeft <= 60 ? 'warning' : 'info') }}">
                                            {{ $daysLeft }} dias
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('company.contracts.show', $contract) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center py-3">Nenhum contrato a vencer nos próximos 90 dias</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateCashModal" tabindex="-1" aria-labelledby="updateCashModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('company.dashboard.update-cash') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateCashModalLabel">Atualizar Caixa Disponível</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="month" value="{{ $monthFilter }}">
                        <label for="current_cash_balance" class="form-label">Valor atual do caixa (R$)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="current_cash_balance" name="current_cash_balance" value="{{ number_format($availableCash, 2, '.', '') }}" required>
                        <div class="form-text">Use o saldo real da conta para manter o sistema sincronizado.</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar caixa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade cash-report-modal" id="cashReportModal" tabindex="-1" aria-labelledby="cashReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashReportModalLabel">Relatório de Caixa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-4">
                            <label for="cash_report_start_date" class="form-label small text-muted">Data inicial</label>
                            <input type="date" class="form-control form-control-sm" id="cash_report_start_date" value="{{ now()->subDays(89)->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="cash_report_end_date" class="form-label small text-muted">Data final</label>
                            <input type="date" class="form-control form-control-sm" id="cash_report_end_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary btn-sm" id="cash_report_refresh_btn">
                                <i class="fas fa-sync-alt me-1"></i>Atualizar relatório
                            </button>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Fluxo de Caixa Líquido</div>
                                <div class="fw-bold" id="cash_metric_net_cash_flow">R$ 0,00</div>
                                <div class="small text-muted">Entradas - Saídas</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Burn Rate</div>
                                <div class="fw-bold text-danger" id="cash_metric_burn_rate">R$ 0,00</div>
                                <div class="small text-muted">Saídas - Entradas (quando negativo)</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Runway</div>
                                <div class="fw-bold text-info" id="cash_metric_runway">N/A</div>
                                <div class="small text-muted">Caixa atual / Burn rate</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">% Comprometimento do Caixa</div>
                                <div class="fw-bold text-warning" id="cash_metric_commitment">N/A</div>
                                <div class="small text-muted">Obrigações curtas / Caixa</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="mb-2">Evolução de Caixa</h6>
                                <div style="height: 260px;">
                                    <canvas id="cashEvolutionChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="mb-2">Entradas x Saídas (período)</h6>
                                <div style="height: 260px;">
                                    <canvas id="cashFlowBarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <div class="border rounded p-3">
                                <h6 class="mb-2">Previsão de Caixa (próximos 15 dias)</h6>
                                <div style="height: 240px;">
                                    <canvas id="cashForecast15Chart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-2">Indicadores</h6>
                                <div class="cash-indicators-grid" id="cash_metrics_list">
                                    <div class="cash-indicator-card highlight">
                                        <div class="label">Entradas</div>
                                        <div class="value text-success" data-cash-key="inflows">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card highlight">
                                        <div class="label">Saídas</div>
                                        <div class="value text-danger" data-cash-key="outflows">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card">
                                        <div class="label">Média entradas</div>
                                        <div class="value" data-cash-key="avg_inflows">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card">
                                        <div class="label">Média saídas</div>
                                        <div class="value" data-cash-key="avg_outflows">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card">
                                        <div class="label">Resultado diário</div>
                                        <div class="value" data-cash-key="daily_result">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card">
                                        <div class="label">Resultado semanal</div>
                                        <div class="value" data-cash-key="weekly_result">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card">
                                        <div class="label">Resultado mensal</div>
                                        <div class="value" data-cash-key="monthly_result">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card">
                                        <div class="label">Obrigações curto prazo</div>
                                        <div class="value" data-cash-key="short_term_obligations">R$ 0,00</div>
                                    </div>
                                    <div class="cash-indicator-card">
                                        <div class="label">Liquidez de caixa</div>
                                        <div class="value text-info" data-cash-key="liquidity_index">N/A</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3">
                        <h6 class="mb-2">Projeções de Caixa e Entrada Necessária</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Horizonte</th>
                                        <th class="text-end">Caixa atual</th>
                                        <th class="text-end">Entradas previstas</th>
                                        <th class="text-end">Saídas previstas</th>
                                        <th class="text-end">Caixa projetado</th>
                                    </tr>
                                </thead>
                                <tbody id="cash_projection_summary_tbody">
                                    <tr><td colspan="5" class="text-center text-muted">Carregando...</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Metas por horizonte</span>
                                <div class="d-flex align-items-center gap-2">
                                    <label for="cash_growth_horizon_filter" class="small text-muted mb-0">Filtrar:</label>
                                    <select id="cash_growth_horizon_filter" class="form-select form-select-sm" style="width: 120px;">
                                        <option value="15">15 dias</option>
                                        <option value="30">30 dias</option>
                                        <option value="60">60 dias</option>
                                    </select>
                                </div>
                            </div>
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Meta</th>
                                        <th class="text-end">Caixa alvo</th>
                                        <th class="text-end">Entrada adicional necessária</th>
                                    </tr>
                                </thead>
                                <tbody id="cash_growth_targets_tbody">
                                    <tr><td colspan="3" class="text-center text-muted">Carregando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Gráfico 1 - Faturamento x Despesas x Lucro
    const revenueExpensesCtx = document.getElementById('revenueExpensesChart');
    if (revenueExpensesCtx) {
        const financialData = @json($financialHistory);
        
        new Chart(revenueExpensesCtx, {
            type: 'bar',
            data: {
                labels: financialData.map(item => item.month),
                datasets: [
                    {
                        label: 'Faturamento',
                        data: financialData.map(item => item.revenue),
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Despesas',
                        data: financialData.map(item => item.expenses),
                        backgroundColor: 'rgba(245, 54, 92, 0.7)',
                        borderColor: 'rgba(245, 54, 92, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Lucro',
                        data: financialData.map(item => item.profit),
                        type: 'line',
                        backgroundColor: 'rgba(94, 114, 228, 0.1)',
                        borderColor: 'rgba(94, 114, 228, 1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico 2 - Projeções
    const projectionsCtx = document.getElementById('projectionsChart');
    if (projectionsCtx) {
        const projectionsData = @json($projections);
        
        new Chart(projectionsCtx, {
            type: 'bar',
            data: {
                labels: projectionsData.map(item => item.month),
                datasets: [
                    {
                        label: 'Faturamento Previsto',
                        data: projectionsData.map(item => item.revenue),
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Despesa Prevista',
                        data: projectionsData.map(item => item.expenses),
                        backgroundColor: 'rgba(245, 54, 92, 0.7)',
                        borderColor: 'rgba(245, 54, 92, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Lucro Previsto',
                        data: projectionsData.map(item => item.profit),
                        backgroundColor: 'rgba(94, 114, 228, 0.7)',
                        borderColor: 'rgba(94, 114, 228, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Pizza - Despesas por Categoria
    const expensesByCategoryCtx = document.getElementById('expensesByCategoryChart');
    if (expensesByCategoryCtx) {
        const expensesData = @json($expensesByCategoryChart);
        
        if (expensesData.length > 0) {
            new Chart(expensesByCategoryCtx, {
                type: 'doughnut',
                data: {
                    labels: expensesData.map(item => item.label),
                    datasets: [{
                        data: expensesData.map(item => item.value),
                        backgroundColor: expensesData.map(item => item.color),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                },
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        const dataset = data.datasets[0];
                                        const total = dataset.data.reduce((a, b) => a + b, 0);
                                        return data.labels.map((label, i) => {
                                            const value = dataset.data[i];
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return {
                                                text: label + ': R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + percentage + '%)',
                                                fillStyle: dataset.backgroundColor[i],
                                                strokeStyle: dataset.borderColor,
                                                lineWidth: dataset.borderWidth,
                                                hidden: false,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    const cashReportModal = document.getElementById('cashReportModal');
    const cashReportEndpoint = @json(route('company.dashboard.cash-report-data'));
    const cashStartInput = document.getElementById('cash_report_start_date');
    const cashEndInput = document.getElementById('cash_report_end_date');
    const cashRefreshBtn = document.getElementById('cash_report_refresh_btn');
    const cashGrowthHorizonFilter = document.getElementById('cash_growth_horizon_filter');

    let cashEvolutionChart = null;
    let cashFlowBarChart = null;
    let cashForecast15Chart = null;
    let cashReportDataCache = null;

    const brl = (v) => 'R$ ' + Number(v || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const pct = (v) => v === null || v === undefined ? 'N/A' : Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + '%';

    function renderGrowthTargetsTable(data) {
        const growthTbody = document.getElementById('cash_growth_targets_tbody');
        if (!growthTbody) return;

        const horizons = data.forecast_horizons || [];
        const selectedHorizon = Number(cashGrowthHorizonFilter?.value || 15);
        const selected = horizons.find(h => Number(h.horizon_days) === selectedHorizon);

        if (!selected || !selected.growth_targets || !selected.growth_targets.length) {
            growthTbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Sem projeções para o horizonte selecionado</td></tr>';
            return;
        }

        growthTbody.innerHTML = selected.growth_targets.map(item => `
            <tr>
                <td>${item.growth_percent === 0 ? 'Ficar positivo' : 'Crescer ' + item.growth_percent + '%'}</td>
                <td class="text-end">${brl(item.target_cash)}</td>
                <td class="text-end fw-bold ${Number(item.needed_inflow) > 0 ? 'text-danger' : 'text-success'}">${brl(item.needed_inflow)}</td>
            </tr>
        `).join('');
    }

    async function loadCashReport() {
        if (!cashStartInput || !cashEndInput) return;
        const params = new URLSearchParams({
            start_date: cashStartInput.value,
            end_date: cashEndInput.value
        });

        const res = await fetch(`${cashReportEndpoint}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        if (!res.ok) return;

        const data = await res.json();
        cashReportDataCache = data;
        const summary = data.summary || {};
        const evolution = data.evolution || [];
        const forecast = data.forecast_15_days || [];
        const horizons = data.forecast_horizons || [];

        const netEl = document.getElementById('cash_metric_net_cash_flow');
        if (netEl) {
            netEl.textContent = brl(summary.net_cash_flow);
            netEl.classList.toggle('text-success', Number(summary.net_cash_flow) >= 0);
            netEl.classList.toggle('text-danger', Number(summary.net_cash_flow) < 0);
        }
        const burnEl = document.getElementById('cash_metric_burn_rate');
        if (burnEl) burnEl.textContent = brl(summary.burn_rate);
        const runwayEl = document.getElementById('cash_metric_runway');
        if (runwayEl) runwayEl.textContent = summary.runway_months === null ? 'N/A' : Number(summary.runway_months).toLocaleString('pt-BR', {minimumFractionDigits: 1, maximumFractionDigits: 1}) + ' meses';
        const commEl = document.getElementById('cash_metric_commitment');
        if (commEl) commEl.textContent = pct(summary.cash_commitment_percent);

        document.querySelectorAll('#cash_metrics_list [data-cash-key]').forEach((el) => {
            const k = el.getAttribute('data-cash-key');
            const val = summary[k];
            if (k === 'liquidity_index') {
                el.textContent = val === null || val === undefined ? 'N/A' : Number(val).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            } else {
                el.textContent = brl(val);
            }
        });

        const projectionSummaryTbody = document.getElementById('cash_projection_summary_tbody');
        if (projectionSummaryTbody) {
            const rows = horizons.map(h => `
                <tr>
                    <td>${h.horizon_days} dias</td>
                    <td class="text-end">${brl(h.start_cash)}</td>
                    <td class="text-end text-success">${brl(h.projected_inflows)}</td>
                    <td class="text-end text-danger">${brl(h.projected_outflows)}</td>
                    <td class="text-end fw-bold ${Number(h.projected_end_cash) >= Number(h.start_cash) ? 'text-success' : 'text-danger'}">${brl(h.projected_end_cash)}</td>
                </tr>
            `);
            const nm = data.next_month_projection || null;
            if (nm) {
                rows.push(`
                    <tr>
                        <td>Próximo mês (${nm.month_label})</td>
                        <td class="text-end">${brl(nm.current_cash)}</td>
                        <td class="text-end text-success">${brl(nm.projected_inflows)}</td>
                        <td class="text-end text-danger">${brl(nm.projected_outflows)}</td>
                        <td class="text-end fw-bold ${Number(nm.projected_end_cash) >= Number(nm.current_cash) ? 'text-success' : 'text-danger'}">${brl(nm.projected_end_cash)}</td>
                    </tr>
                `);
            }
            projectionSummaryTbody.innerHTML = rows.length ? rows.join('') : '<tr><td colspan="5" class="text-center text-muted">Sem projeções</td></tr>';
        }

        renderGrowthTargetsTable(data);

        const evoCanvas = document.getElementById('cashEvolutionChart');
        if (evoCanvas) {
            if (cashEvolutionChart) cashEvolutionChart.destroy();
            cashEvolutionChart = new Chart(evoCanvas, {
                type: 'line',
                data: {
                    labels: evolution.map(i => i.label),
                    datasets: [
                        {
                            label: 'Saldo de Caixa',
                            data: evolution.map(i => i.balance),
                            borderColor: 'rgba(17, 205, 239, 1)',
                            backgroundColor: 'rgba(17, 205, 239, 0.15)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Fluxo Líquido',
                            data: evolution.map(i => i.net),
                            borderColor: 'rgba(94, 114, 228, 1)',
                            backgroundColor: 'rgba(94, 114, 228, 0.10)',
                            fill: false,
                            tension: 0.25
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const barCanvas = document.getElementById('cashFlowBarChart');
        if (barCanvas) {
            if (cashFlowBarChart) cashFlowBarChart.destroy();
            cashFlowBarChart = new Chart(barCanvas, {
                type: 'bar',
                data: {
                    labels: evolution.map(i => i.label),
                    datasets: [
                        {
                            label: 'Entradas',
                            data: evolution.map(i => i.inflows),
                            backgroundColor: 'rgba(46, 204, 113, 0.7)'
                        },
                        {
                            label: 'Saídas',
                            data: evolution.map(i => i.outflows),
                            backgroundColor: 'rgba(245, 54, 92, 0.7)'
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        const fcCanvas = document.getElementById('cashForecast15Chart');
        if (fcCanvas) {
            if (cashForecast15Chart) cashForecast15Chart.destroy();
            cashForecast15Chart = new Chart(fcCanvas, {
                type: 'line',
                data: {
                    labels: forecast.map(i => i.label),
                    datasets: [
                        {
                            label: 'Previsão Saldo',
                            data: forecast.map(i => i.balance),
                            borderColor: 'rgba(251, 99, 64, 1)',
                            backgroundColor: 'rgba(251, 99, 64, 0.15)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Previsão Fluxo Líquido',
                            data: forecast.map(i => i.net),
                            borderColor: 'rgba(136, 132, 216, 1)',
                            fill: false,
                            tension: 0.25
                        }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    }

    if (cashReportModal) {
        cashReportModal.addEventListener('shown.bs.modal', loadCashReport);
    }
    if (cashRefreshBtn) {
        cashRefreshBtn.addEventListener('click', loadCashReport);
    }
    if (cashGrowthHorizonFilter) {
        cashGrowthHorizonFilter.addEventListener('change', function () {
            if (cashReportDataCache) {
                renderGrowthTargetsTable(cashReportDataCache);
            }
        });
    }
</script>
@endpush
