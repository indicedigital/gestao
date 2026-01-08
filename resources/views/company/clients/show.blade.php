@extends('layouts.app')

@section('title', 'Detalhes do Cliente')

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
    
    .card-modern {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 24px;
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
    
    .table-modern {
        margin-bottom: 0;
    }
    
    .table-modern thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 16px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
    }
    
    .table-modern tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .table-modern tbody tr:hover {
        background: #f8fafc;
    }
    
    .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .icon-circle.primary { background: #eef2ff; color: #5e72e4; }
    .icon-circle.success { background: #d1fae5; color: #2dce89; }
    .icon-circle.danger { background: #fee2e2; color: #f5365c; }
    .icon-circle.warning { background: #fef3c7; color: #fb6340; }
    .icon-circle.info { background: #dbeafe; color: #11cdef; }
    .icon-circle.secondary { background: #f1f5f9; color: #64748b; }
    
    .badge-modern {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
    }
    
    .info-item {
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .info-value {
        font-size: 15px;
        color: #1a202c;
        font-weight: 500;
    }
    
    .chart-container {
        position: relative;
        height: 250px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>{{ $client->name }}</h1>
                <p>
                    @php
                        $statusLabels = [
                            'active' => 'Ativo',
                            'inactive' => 'Inativo',
                            'blocked' => 'Bloqueado'
                        ];
                        $statusColors = [
                            'active' => 'success',
                            'inactive' => 'secondary',
                            'blocked' => 'danger'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$client->status] ?? 'secondary' }} badge-modern me-2">
                        {{ $statusLabels[$client->status] ?? ucfirst($client->status) }}
                    </span>
                    <span class="badge bg-{{ $client->type === 'pj' ? 'primary' : 'info' }} badge-modern">
                        {{ $client->type === 'pj' ? 'Pessoa Jurídica' : 'Pessoa Física' }}
                    </span>
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('company.clients.edit', $client) }}" class="btn btn-warning text-white" style="border-radius: 12px;">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <a href="{{ route('company.clients.index') }}" class="btn btn-secondary" style="border-radius: 12px;">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Filtro de Período -->
    <div class="card-modern mb-4">
        <div class="card-body" style="padding: 16px 24px;">
            <form method="GET" action="{{ route('company.clients.show', $client) }}" class="d-flex align-items-center gap-3">
                <div>
                    <label for="year" class="form-label small text-muted mb-1">Ano:</label>
                    <select class="form-select" id="year" name="year" style="width: 120px; padding: 8px 12px; font-size: 13px; height: 38px; border-radius: 8px;" onchange="this.form.submit()">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="semester" class="form-label small text-muted mb-1">Período:</label>
                    <select class="form-select" id="semester" name="semester" style="width: 150px; padding: 8px 12px; font-size: 13px; height: 38px; border-radius: 8px;" onchange="this.form.submit()">
                        <option value="">Ano Todo</option>
                        <option value="1" {{ $semester == 1 ? 'selected' : '' }}>1º Semestre</option>
                        <option value="2" {{ $semester == 2 ? 'selected' : '' }}>2º Semestre</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs Financeiros -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Total Faturado (Histórico)</h6>
                        <h3>R$ {{ number_format($totalRevenue, 2, ',', '.') }}</h3>
                    </div>
                    <div class="icon-circle success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Faturado no Período</h6>
                        <h3>R$ {{ number_format($revenueRealized, 2, ',', '.') }}</h3>
                    </div>
                    <div class="icon-circle primary">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card info">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Previsto no Período</h6>
                        <h3>R$ {{ number_format($revenueForecast, 2, ',', '.') }}</h3>
                    </div>
                    <div class="icon-circle info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card {{ $overdueValue > 0 ? 'danger' : 'success' }}">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Contas Vencidas</h6>
                        <h3>R$ {{ number_format($overdueValue, 2, ',', '.') }}</h3>
                        <small class="text-muted">{{ $stats['overdue_count'] }} título(s)</small>
                    </div>
                    <div class="icon-circle {{ $overdueValue > 0 ? 'danger' : 'success' }}">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informações do Cliente -->
        <div class="col-lg-4 mb-4">
            <div class="card-modern">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informações do Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">E-mail</div>
                        <div class="info-value">
                            @if($client->email)
                                <a href="mailto:{{ $client->email }}" class="text-primary text-decoration-none">{{ $client->email }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Telefone</div>
                        <div class="info-value">
                            @if($client->phone)
                                <a href="tel:{{ $client->phone }}" class="text-primary text-decoration-none">{{ $client->phone }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>
                    @if($client->document)
                    <div class="info-item">
                        <div class="info-label">{{ $client->document_type === 'cnpj' ? 'CNPJ' : 'CPF' }}</div>
                        <div class="info-value">{{ $client->document }}</div>
                    </div>
                    @endif
                    @if($client->address)
                    <div class="info-item">
                        <div class="info-label">Endereço</div>
                        <div class="info-value">{{ $client->address }}</div>
                    </div>
                    @endif
                    @if($client->city)
                    <div class="info-item">
                        <div class="info-label">Cidade/Estado</div>
                        <div class="info-value">{{ $client->city }}{{ $client->state ? ' / ' . $client->state : '' }}</div>
                    </div>
                    @endif
                    @if($client->zip_code)
                    <div class="info-item">
                        <div class="info-label">CEP</div>
                        <div class="info-value">{{ $client->zip_code }}</div>
                    </div>
                    @endif
                    @if($client->notes)
                    <div class="info-item">
                        <div class="info-label">Observações</div>
                        <div class="info-value">{{ $client->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="card-modern">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Estatísticas</h5>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Contratos</div>
                        <div class="info-value">
                            <span class="badge bg-primary badge-modern">{{ $stats['total_contracts'] }} Total</span>
                            <span class="badge bg-success badge-modern">{{ $stats['active_contracts'] }} Ativos</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Projetos</div>
                        <div class="info-value">
                            <span class="badge bg-primary badge-modern">{{ $stats['total_projects'] }} Total</span>
                            <span class="badge bg-info badge-modern">{{ $stats['active_projects'] }} Em Andamento</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Adimplência</div>
                        <div class="info-value">
                            @if($client->isAdimplente())
                                <span class="badge bg-success badge-modern">Adimplente</span>
                            @else
                                <span class="badge bg-danger badge-modern">Inadimplente</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico e Tabelas -->
        <div class="col-lg-8 mb-4">
            <!-- Gráfico de Faturamento Mensal -->
            <div class="card-modern mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Faturamento Mensal (Últimos 12 Meses)</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Contratos Ativos -->
            <div class="card-modern mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Contratos Ativos</h5>
                    <span class="badge bg-primary badge-modern">{{ $activeContracts->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($activeContracts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Contrato</th>
                                    <th>Tipo</th>
                                    <th>Valor</th>
                                    <th>Início</th>
                                    <th>Término</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeContracts as $contract)
                                <tr>
                                    <td><strong>{{ $contract->name }}</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $contract->type === 'client_recurring' ? 'info' : 'primary' }} badge-modern">
                                            {{ $contract->type === 'client_recurring' ? 'Recorrente' : 'Fixo' }}
                                        </span>
                                    </td>
                                    <td>R$ {{ number_format($contract->value, 2, ',', '.') }}</td>
                                    <td>{{ $contract->start_date->format('d/m/Y') }}</td>
                                    <td>{{ $contract->end_date ? $contract->end_date->format('d/m/Y') : '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('company.contracts.show', $contract) }}" class="btn btn-sm btn-info text-white" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-contract fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Nenhum contrato ativo</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Projetos -->
            <div class="card-modern mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Projetos</h5>
                    <span class="badge bg-primary badge-modern">{{ $projects->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($projects->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern mb-0">
                            <thead>
                                <tr>
                                    <th>Projeto</th>
                                    <th>Status</th>
                                    <th>Valor</th>
                                    <th>Início</th>
                                    <th>Término</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                <tr>
                                    <td><strong>{{ $project->name }}</strong></td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'planning' => 'secondary',
                                                'in_progress' => 'info',
                                                'paused' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $statusLabels = [
                                                'planning' => 'Planejamento',
                                                'in_progress' => 'Em Andamento',
                                                'paused' => 'Pausado',
                                                'completed' => 'Concluído',
                                                'cancelled' => 'Cancelado'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }} badge-modern">
                                            {{ $statusLabels[$project->status] ?? ucfirst($project->status) }}
                                        </span>
                                    </td>
                                    <td>R$ {{ number_format($project->total_value, 2, ',', '.') }}</td>
                                    <td>{{ $project->start_date ? $project->start_date->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $project->end_date ? $project->end_date->format('d/m/Y') : '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('company.projects.show', $project) }}" class="btn btn-sm btn-info text-white" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-project-diagram fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Nenhum projeto cadastrado</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Contas a Receber -->
    <div class="card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-arrow-circle-down me-2"></i>Contas a Receber</h5>
            <span class="badge bg-primary badge-modern">{{ $receivables->count() }} título(s)</span>
        </div>
        <div class="card-body p-0">
            @if($receivables->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receivables as $receivable)
                        <tr class="{{ $receivable->isOverdue() ? 'table-danger' : '' }}">
                            <td>{{ $receivable->description }}</td>
                            <td><strong>R$ {{ number_format($receivable->value, 2, ',', '.') }}</strong></td>
                            <td>{{ $receivable->due_date->format('d/m/Y') }}</td>
                            <td>{{ $receivable->paid_date ? $receivable->paid_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'overdue' => 'danger',
                                        'cancelled' => 'secondary'
                                    ];
                                    $statusLabels = [
                                        'paid' => 'Pago',
                                        'pending' => 'Pendente',
                                        'overdue' => 'Vencido',
                                        'cancelled' => 'Cancelado'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$receivable->status] ?? 'secondary' }} badge-modern">
                                    {{ $statusLabels[$receivable->status] ?? ucfirst($receivable->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <i class="fas fa-arrow-circle-down fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">Nenhuma conta a receber no período selecionado</p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyHistory = @json($monthlyHistory);
        
        const ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthlyHistory.map(item => item.month),
                    datasets: [{
                        label: 'Faturamento',
                        data: monthlyHistory.map(item => item.revenue),
                        backgroundColor: 'rgba(94, 114, 228, 0.8)',
                        borderColor: 'rgba(94, 114, 228, 1)',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
    });
</script>
@endpush
@endsection
