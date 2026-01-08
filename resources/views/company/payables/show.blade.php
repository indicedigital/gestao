@extends('layouts.app')

@section('title', 'Detalhes da Conta a Pagar')

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
                <h1>{{ $payable->description }}</h1>
                <p class="text-muted mb-0">
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            'cancelled' => 'secondary'
                        ];
                        $statusColor = $statusColors[$payable->status] ?? 'secondary';
                        $statusLabels = [
                            'pending' => 'Pendente',
                            'paid' => 'Paga',
                            'overdue' => 'Vencida',
                            'cancelled' => 'Cancelada'
                        ];
                        $statusLabel = $statusLabels[$payable->status] ?? ucfirst($payable->status);
                    @endphp
                    <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                    @php
                        $typeLabels = [
                            'salary' => 'Salário',
                            'service' => 'Serviço',
                            'supplier' => 'Fornecedor',
                            'other' => 'Outro'
                        ];
                        $typeLabel = $typeLabels[$payable->type] ?? $payable->type;
                    @endphp
                    • Tipo: <span class="badge bg-info">{{ $typeLabel }}</span>
                </p>
            </div>
            <div>
                @if($payable->status === 'pending')
                <a href="{{ route('company.payables.edit', $payable) }}" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Registrar Pagamento
                </a>
                @endif
                <a href="{{ route('company.payables.edit', $payable) }}" class="btn btn-warning text-white">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <a href="{{ route('company.payables.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Resumido -->
    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="kpi-mini">
                <div class="kpi-mini-label">Valor</div>
                <div class="kpi-mini-value text-primary">R$ {{ number_format($payable->value, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="kpi-mini">
                <div class="kpi-mini-label">Status</div>
                <div class="kpi-mini-value">
                    <span class="badge bg-{{ $statusColor }} fs-6">{{ $statusLabel }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="kpi-mini">
                <div class="kpi-mini-label">Situação</div>
                <div class="kpi-mini-value">
                    @if($payable->isOverdue())
                        <span class="badge bg-danger fs-6">
                            <i class="fas fa-exclamation-circle"></i> Vencida
                        </span>
                    @elseif($payable->status === 'pending')
                        <span class="badge bg-warning fs-6">
                            <i class="fas fa-clock"></i> Pendente
                        </span>
                    @else
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-check-circle"></i> Paga
                        </span>
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
                        <div class="info-value">#{{ $payable->id }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Descrição</div>
                        <div class="info-value">{{ $payable->description }}</div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            <span class="badge bg-info">{{ $typeLabel }}</span>
                            @if($payable->category)
                                <span class="badge bg-secondary ms-1">{{ ucfirst($payable->category) }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Valor</div>
                        <div class="info-value">
                            <strong style="font-size: 18px; color: #1a202c;">R$ {{ number_format($payable->value, 2, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            @if($payable->isOverdue())
                                <span class="badge bg-danger ms-2">Vencida</span>
                            @endif
                        </div>
                    </div>

                    @if($payable->employee)
                    <div class="info-item">
                        <div class="info-label">Funcionário</div>
                        <div class="info-value">
                            <a href="{{ route('company.employees.show', $payable->employee) }}" class="text-decoration-none">
                                {{ $payable->employee->name }}
                            </a>
                            @if($payable->employee->email)
                                <br><small class="text-muted">{{ $payable->employee->email }}</small>
                            @endif
                            @if($payable->employee->phone)
                                <br><small class="text-muted">{{ $payable->employee->phone }}</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($payable->supplier_name)
                    <div class="info-item">
                        <div class="info-label">Fornecedor</div>
                        <div class="info-value">{{ $payable->supplier_name }}</div>
                    </div>
                    @endif

                    <div class="info-item">
                        <div class="info-label">Data de Criação</div>
                        <div class="info-value">{{ $payable->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    @if($payable->updated_at != $payable->created_at)
                    <div class="info-item">
                        <div class="info-label">Última Atualização</div>
                        <div class="info-value">{{ $payable->updated_at->format('d/m/Y H:i') }}</div>
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
                            {{ $payable->due_date->format('d/m/Y') }}
                            @if($payable->isOverdue())
                                <br><span class="badge bg-danger mt-1">
                                    {{ (int) floor(now()->diffInDays($payable->due_date, false)) }} dias atrasado
                                </span>
                            @elseif($payable->status === 'pending' && $payable->due_date > now())
                                <br><span class="badge bg-info mt-1">
                                    Vence em {{ (int) floor(now()->diffInDays($payable->due_date, false)) }} dias
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($payable->paid_date)
                    <div class="info-item">
                        <div class="info-label">Data de Pagamento</div>
                        <div class="info-value">
                            {{ $payable->paid_date->format('d/m/Y') }}
                            @php
                                $daysDifference = $payable->paid_date->diffInDays($payable->due_date, false);
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

                    @if($payable->payment_method)
                    <div class="info-item">
                        <div class="info-label">Forma de Pagamento</div>
                        <div class="info-value">{{ $payable->payment_method }}</div>
                    </div>
                    @endif

                    @if($payable->status === 'pending')
                    <div class="info-item">
                        <div class="info-label">Ações Disponíveis</div>
                        <div class="info-value">
                            <a href="{{ route('company.payables.edit', $payable) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-check me-1"></i>Registrar Pagamento
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Relacionamentos -->
    <div class="row mb-4">
        @if($payable->project)
        <div class="col-xl-6 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-folder me-2 text-primary"></i>Projeto Vinculado</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Nome do Projeto</div>
                        <div class="info-value">
                            <a href="{{ route('company.projects.show', $payable->project) }}" class="text-decoration-none">
                                {{ $payable->project->name }}
                            </a>
                        </div>
                    </div>
                    @if($payable->project->description)
                    <div class="info-item">
                        <div class="info-label">Descrição</div>
                        <div class="info-value">{{ $payable->project->description }}</div>
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
                                $projectStatusColor = $projectStatusColors[$payable->project->status] ?? 'secondary';
                                $projectStatusLabels = [
                                    'active' => 'Ativo',
                                    'paused' => 'Pausado',
                                    'completed' => 'Concluído',
                                    'cancelled' => 'Cancelado'
                                ];
                                $projectStatusLabel = $projectStatusLabels[$payable->project->status] ?? ucfirst($payable->project->status);
                            @endphp
                            <span class="badge bg-{{ $projectStatusColor }}">{{ $projectStatusLabel }}</span>
                        </div>
                    </div>
                    @if($payable->project->total_value)
                    <div class="info-item">
                        <div class="info-label">Valor Total do Projeto</div>
                        <div class="info-value">R$ {{ number_format($payable->project->total_value, 2, ',', '.') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($payable->employee && $payable->type === 'salary')
        <div class="col-xl-6 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-user-tie me-2 text-primary"></i>Funcionário</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Nome</div>
                        <div class="info-value">
                            <a href="{{ route('company.employees.show', $payable->employee) }}" class="text-decoration-none">
                                {{ $payable->employee->name }}
                            </a>
                        </div>
                    </div>
                    @if($payable->employee->position)
                    <div class="info-item">
                        <div class="info-label">Cargo</div>
                        <div class="info-value">{{ $payable->employee->position }}</div>
                    </div>
                    @endif
                    @if($payable->employee->email)
                    <div class="info-item">
                        <div class="info-label">E-mail</div>
                        <div class="info-value">{{ $payable->employee->email }}</div>
                    </div>
                    @endif
                    @if($payable->employee->phone)
                    <div class="info-item">
                        <div class="info-label">Telefone</div>
                        <div class="info-value">{{ $payable->employee->phone }}</div>
                    </div>
                    @endif
                    @if($payable->employee->type)
                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            @php
                                $employeeTypeLabels = [
                                    'clt' => 'CLT',
                                    'pj' => 'PJ',
                                    'freelancer' => 'Freelancer'
                                ];
                                $employeeTypeLabel = $employeeTypeLabels[$payable->employee->type] ?? ucfirst($payable->employee->type);
                            @endphp
                            <span class="badge bg-info">{{ $employeeTypeLabel }}</span>
                        </div>
                    </div>
                    @endif
                    @if($payable->employee->salary)
                    <div class="info-item">
                        <div class="info-label">Salário</div>
                        <div class="info-value">R$ {{ number_format($payable->employee->salary, 2, ',', '.') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Observações -->
    @if($payable->notes)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-sticky-note me-2 text-primary"></i>Observações</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0" style="white-space: pre-wrap;">{{ $payable->notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
