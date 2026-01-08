@extends('layouts.app')

@section('title', 'Detalhes da Conta a Receber')

@push('styles')
<style>
    body {
        background: #f7fafc;
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
    
    .info-item {
        padding: 16px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 6px;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 15px;
        color: #1a202c;
        font-weight: 500;
    }
    
    .kpi-mini {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        border: 1px solid #e2e8f0;
    }
    
    .kpi-mini-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 8px;
    }
    
    .kpi-mini-value {
        font-size: 24px;
        font-weight: 700;
        color: #1a202c;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>{{ $receivable->description }}</h1>
                <p class="text-muted mb-0">
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'paid' => 'success',
                            'partial' => 'info',
                            'overdue' => 'danger',
                            'cancelled' => 'secondary'
                        ];
                        $statusColor = $statusColors[$receivable->status] ?? 'secondary';
                        $statusLabels = [
                            'pending' => 'Pendente',
                            'paid' => 'Paga',
                            'partial' => 'Parcial',
                            'overdue' => 'Vencida',
                            'cancelled' => 'Cancelada'
                        ];
                        $statusLabel = $statusLabels[$receivable->status] ?? ucfirst($receivable->status);
                    @endphp
                    <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                    @if($receivable->client)
                        • Cliente: {{ $receivable->client->name }}
                    @endif
                </p>
            </div>
            <div>
                @if($receivable->status === 'pending' || $receivable->status === 'partial')
                <a href="{{ route('company.receivables.edit', $receivable) }}" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Registrar Pagamento
                </a>
                @endif
                <a href="{{ route('company.receivables.edit', $receivable) }}" class="btn btn-warning text-white">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <a href="{{ route('company.receivables.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- KPIs Resumidos -->
    <div class="row mb-4">
        <div class="col-md-3 mb-4">
            <div class="kpi-mini">
                <div class="kpi-mini-label">Valor Total</div>
                <div class="kpi-mini-value text-primary">R$ {{ number_format($receivable->value, 2, ',', '.') }}</div>
            </div>
        </div>
        @if($receivable->paid_value && $receivable->paid_value > 0)
        <div class="col-md-3 mb-4">
            <div class="kpi-mini">
                <div class="kpi-mini-label">Valor Pago</div>
                <div class="kpi-mini-value text-success">R$ {{ number_format($receivable->paid_value, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="kpi-mini">
                <div class="kpi-mini-label">Valor Pendente</div>
                <div class="kpi-mini-value text-warning">R$ {{ number_format($receivable->value - $receivable->paid_value, 2, ',', '.') }}</div>
            </div>
        </div>
        @endif
        <div class="col-md-3 mb-4">
            <div class="kpi-mini">
                <div class="kpi-mini-label">Percentual Pago</div>
                <div class="kpi-mini-value text-info">
                    @if($receivable->paid_value)
                        {{ number_format(($receivable->paid_value / $receivable->value) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Informações Principais -->
        <div class="col-xl-6 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Informações Principais</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">ID</div>
                        <div class="info-value">#{{ $receivable->id }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Descrição</div>
                        <div class="info-value">{{ $receivable->description }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Cliente</div>
                        <div class="info-value">
                            @if($receivable->client)
                                <a href="{{ route('company.clients.show', $receivable->client) }}" class="text-decoration-none">
                                    {{ $receivable->client->name }}
                                </a>
                                @if($receivable->client->email)
                                    <br><small class="text-muted">{{ $receivable->client->email }}</small>
                                @endif
                                @if($receivable->client->phone)
                                    <br><small class="text-muted">{{ $receivable->client->phone }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            @if($receivable->isOverdue())
                                <span class="badge bg-danger ms-2">Vencida</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            @php
                                $typeLabels = [
                                    'project' => 'Projeto',
                                    'recurring' => 'Recorrente',
                                    'other' => 'Outro'
                                ];
                                $typeLabel = $typeLabels[$receivable->type] ?? ucfirst($receivable->type);
                            @endphp
                            <span class="badge bg-info">{{ $typeLabel }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Valor Total</div>
                        <div class="info-value">
                            <strong style="font-size: 18px; color: #1a202c;">R$ {{ number_format($receivable->value, 2, ',', '.') }}</strong>
                        </div>
                    </div>

                    @if($receivable->paid_value && $receivable->paid_value > 0)
                    <div class="info-item">
                        <div class="info-label">Valor Pago</div>
                        <div class="info-value">
                            <strong class="text-success" style="font-size: 18px;">R$ {{ number_format($receivable->paid_value, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                    @endif

                    @if($receivable->installment_number)
                    <div class="info-item">
                        <div class="info-label">Parcela</div>
                        <div class="info-value">
                            {{ $receivable->installment_number }}/{{ $receivable->total_installments ?? '-' }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informações de Pagamento e Vencimento -->
        <div class="col-xl-6 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Datas e Pagamento</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Data de Vencimento</div>
                        <div class="info-value">
                            {{ $receivable->due_date->format('d/m/Y') }}
                            @if($receivable->isOverdue())
                                <br><span class="badge bg-danger mt-1">
                                    {{ (int) floor(now()->diffInDays($receivable->due_date, false)) }} dias atrasado
                                </span>
                            @elseif($receivable->due_date > now())
                                <br><span class="badge bg-info mt-1">
                                    Vence em {{ (int) floor(now()->diffInDays($receivable->due_date, false)) }} dias
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($receivable->paid_date)
                    <div class="info-item">
                        <div class="info-label">Data de Pagamento</div>
                        <div class="info-value">
                            {{ $receivable->paid_date->format('d/m/Y') }}
                            @php
                                $daysDifference = $receivable->paid_date->diffInDays($receivable->due_date, false);
                            @endphp
                            @if($daysDifference > 0)
                                <br><small class="text-success">
                                    <i class="fas fa-check-circle"></i> Pago {{ abs($daysDifference) }} dias antes do vencimento
                                </small>
                            @elseif($daysDifference < 0)
                                <br><small class="text-danger">
                                    <i class="fas fa-exclamation-circle"></i> Pago {{ abs($daysDifference) }} dias após o vencimento
                                </small>
                            @else
                                <br><small class="text-info">
                                    <i class="fas fa-check-circle"></i> Pago no dia do vencimento
                                </small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($receivable->payment_method)
                    <div class="info-item">
                        <div class="info-label">Forma de Pagamento</div>
                        <div class="info-value">{{ $receivable->payment_method }}</div>
                    </div>
                    @endif

                    @if($receivable->status === 'partial' && $receivable->paid_value)
                    <div class="info-item">
                        <div class="info-label">Valor Pendente</div>
                        <div class="info-value">
                            <strong class="text-warning" style="font-size: 18px;">R$ {{ number_format($receivable->value - $receivable->paid_value, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                    @endif

                    <div class="info-item">
                        <div class="info-label">Data de Criação</div>
                        <div class="info-value">{{ $receivable->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    @if($receivable->updated_at != $receivable->created_at)
                    <div class="info-item">
                        <div class="info-label">Última Atualização</div>
                        <div class="info-value">{{ $receivable->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Relacionamentos -->
    <div class="row mb-4">
        @if($receivable->contract)
        <div class="col-xl-6 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-file-contract me-2 text-primary"></i>Contrato Vinculado</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Nome do Contrato</div>
                        <div class="info-value">
                            <a href="{{ route('company.contracts.show', $receivable->contract) }}" class="text-decoration-none">
                                {{ $receivable->contract->name }}
                            </a>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            @php
                                $contractTypeLabels = [
                                    'client_recurring' => 'Cliente Recorrente',
                                    'client_fixed' => 'Cliente Fechado',
                                ];
                                $contractTypeLabel = $contractTypeLabels[$receivable->contract->type] ?? $receivable->contract->type;
                            @endphp
                            <span class="badge bg-info">{{ $contractTypeLabel }}</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Valor do Contrato</div>
                        <div class="info-value">R$ {{ number_format($receivable->contract->value, 2, ',', '.') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @php
                                $contractStatusColors = [
                                    'active' => 'success',
                                    'suspended' => 'warning',
                                    'cancelled' => 'danger',
                                    'expired' => 'secondary'
                                ];
                                $contractStatusColor = $contractStatusColors[$receivable->contract->status] ?? 'secondary';
                                $contractStatusLabels = [
                                    'active' => 'Ativo',
                                    'suspended' => 'Suspenso',
                                    'cancelled' => 'Cancelado',
                                    'expired' => 'Expirado'
                                ];
                                $contractStatusLabel = $contractStatusLabels[$receivable->contract->status] ?? ucfirst($receivable->contract->status);
                            @endphp
                            <span class="badge bg-{{ $contractStatusColor }}">{{ $contractStatusLabel }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($receivable->project)
        <div class="col-xl-6 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-folder me-2 text-primary"></i>Projeto Vinculado</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Nome do Projeto</div>
                        <div class="info-value">
                            <a href="{{ route('company.projects.show', $receivable->project) }}" class="text-decoration-none">
                                {{ $receivable->project->name }}
                            </a>
                        </div>
                    </div>
                    @if($receivable->project->description)
                    <div class="info-item">
                        <div class="info-label">Descrição</div>
                        <div class="info-value">{{ $receivable->project->description }}</div>
                    </div>
                    @endif
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @php
                                $projectStatusColors = [
                                    'active' => 'success',
                                    'paused' => 'warning',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ];
                                $projectStatusColor = $projectStatusColors[$receivable->project->status] ?? 'secondary';
                                $projectStatusLabels = [
                                    'active' => 'Ativo',
                                    'paused' => 'Pausado',
                                    'completed' => 'Concluído',
                                    'cancelled' => 'Cancelado'
                                ];
                                $projectStatusLabel = $projectStatusLabels[$receivable->project->status] ?? ucfirst($receivable->project->status);
                            @endphp
                            <span class="badge bg-{{ $projectStatusColor }}">{{ $projectStatusLabel }}</span>
                        </div>
                    </div>
                    @if($receivable->project->total_value)
                    <div class="info-item">
                        <div class="info-label">Valor Total do Projeto</div>
                        <div class="info-value">R$ {{ number_format($receivable->project->total_value, 2, ',', '.') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Observações -->
    @if($receivable->notes)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-sticky-note me-2 text-primary"></i>Observações</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $receivable->notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
