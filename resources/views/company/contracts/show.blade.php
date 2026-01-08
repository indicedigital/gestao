@extends('layouts.app')

@section('title', 'Detalhes do Contrato')

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
        padding: 12px 0;
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
        margin-bottom: 4px;
    }
    
    .info-value {
        font-size: 15px;
        color: #1a202c;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>{{ $contract->name }}</h1>
                <p class="text-muted mb-0">
                    @php
                        $typeLabels = [
                            'client_recurring' => 'Cliente Recorrente',
                            'client_fixed' => 'Cliente Fechado',
                            'employee_clt' => 'Funcionário CLT',
                            'employee_pj' => 'Funcionário PJ'
                        ];
                        $typeLabel = $typeLabels[$contract->type] ?? $contract->type;
                    @endphp
                    {{ $typeLabel }} • 
                    @if($contract->client)
                        Cliente: {{ $contract->client->name }}
                    @elseif($contract->employee)
                        Funcionário: {{ $contract->employee->name }}
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('company.contracts.edit', $contract) }}" class="btn btn-warning text-white">
                    <i class="fas fa-edit me-2"></i>Editar
                </a>
                <a href="{{ route('company.contracts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card primary">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Valor Total</h6>
                        <h3>R$ {{ number_format($contract->value, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-money-bill-wave fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card success">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Recebido</h6>
                        <h3>R$ {{ number_format($paidReceivables, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-success">{{ number_format($paidPercentage, 1) }}%</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card warning">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>Pendente</h6>
                        <h3>R$ {{ number_format($pendingReceivables + $partialReceivables, 2, ',', '.') }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-clock fa-lg text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card {{ $overdueReceivables > 0 ? 'danger' : 'info' }}">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6>{{ $overdueReceivables > 0 ? 'Vencido' : 'Projetos' }}</h6>
                        <h3>
                            @if($overdueReceivables > 0)
                                R$ {{ number_format($overdueReceivables, 2, ',', '.') }}
                            @else
                                {{ $totalProjects }}
                            @endif
                        </h3>
                    </div>
                    <div class="bg-{{ $overdueReceivables > 0 ? 'danger' : 'info' }} bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-{{ $overdueReceivables > 0 ? 'exclamation-circle' : 'folder' }} fa-lg text-{{ $overdueReceivables > 0 ? 'danger' : 'info' }}"></i>
                    </div>
                </div>
                @if($overdueReceivables == 0)
                <div class="d-flex align-items-center">
                    <span class="text-muted small">{{ $activeProjects }} ativos</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Informações do Contrato -->
        <div class="col-xl-4 mb-4">
            <div class="card-modern h-100">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-file-contract me-2 text-primary"></i>Informações do Contrato</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'suspended' => 'warning',
                                    'cancelled' => 'danger',
                                    'expired' => 'secondary'
                                ];
                                $statusColor = $statusColors[$contract->status] ?? 'secondary';
                                $statusLabels = [
                                    'active' => 'Ativo',
                                    'suspended' => 'Suspenso',
                                    'cancelled' => 'Cancelado',
                                    'expired' => 'Expirado'
                                ];
                                $statusLabel = $statusLabels[$contract->status] ?? ucfirst($contract->status);
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Tipo</div>
                        <div class="info-value">
                            <span class="badge bg-info">{{ $typeLabel }}</span>
                        </div>
                    </div>

                    @if($contract->number)
                    <div class="info-item">
                        <div class="info-label">Número</div>
                        <div class="info-value">{{ $contract->number }}</div>
                    </div>
                    @endif

                    <div class="info-item">
                        <div class="info-label">Data de Início</div>
                        <div class="info-value">{{ $contract->start_date->format('d/m/Y') }}</div>
                    </div>

                    @if($contract->end_date)
                    <div class="info-item">
                        <div class="info-label">Data de Término</div>
                        <div class="info-value">
                            {{ $contract->end_date->format('d/m/Y') }}
                            @php
                                $daysLeft = (int) floor(now()->diffInDays($contract->end_date, false));
                            @endphp
                            @if($daysLeft >= 0 && $daysLeft <= 90)
                                <span class="badge bg-{{ $daysLeft <= 30 ? 'danger' : ($daysLeft <= 60 ? 'warning' : 'info') }} ms-2">
                                    {{ $daysLeft }} dias
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($contract->billing_period)
                    <div class="info-item">
                        <div class="info-label">Período de Cobrança</div>
                        <div class="info-value">{{ $contract->billing_period === 'monthly' ? 'Mensal' : 'Anual' }}</div>
                    </div>
                    @endif

                    @if($contract->auto_renew)
                    <div class="info-item">
                        <div class="info-label">Renovação Automática</div>
                        <div class="info-value">
                            <span class="badge bg-success">Sim</span>
                        </div>
                    </div>
                    @endif

                    @if($contract->description)
                    <div class="info-item">
                        <div class="info-label">Descrição</div>
                        <div class="info-value">{{ $contract->description }}</div>
                    </div>
                    @endif

                    @if($contract->notes)
                    <div class="info-item">
                        <div class="info-label">Observações</div>
                        <div class="info-value">{{ $contract->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Projetos Vinculados -->
        <div class="col-xl-8 mb-4">
            <div class="card-modern h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-folder me-2 text-primary"></i>Projetos Vinculados</h6>
                    <span class="badge bg-primary">{{ $totalProjects }} projeto(s)</span>
                </div>
                <div class="card-body">
                    @if($contract->projects->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Status</th>
                                    <th>Início</th>
                                    <th>Término</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contract->projects as $project)
                                <tr>
                                    <td>
                                        <strong>{{ $project->name }}</strong>
                                        @if($project->description)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($project->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $projectStatusColors = [
                                                'active' => 'success',
                                                'paused' => 'warning',
                                                'completed' => 'info',
                                                'cancelled' => 'danger'
                                            ];
                                            $projectStatusColor = $projectStatusColors[$project->status] ?? 'secondary';
                                            $projectStatusLabels = [
                                                'active' => 'Ativo',
                                                'paused' => 'Pausado',
                                                'completed' => 'Concluído',
                                                'cancelled' => 'Cancelado'
                                            ];
                                            $projectStatusLabel = $projectStatusLabels[$project->status] ?? ucfirst($project->status);
                                        @endphp
                                        <span class="badge bg-{{ $projectStatusColor }}">{{ $projectStatusLabel }}</span>
                                    </td>
                                    <td>{{ $project->start_date ? $project->start_date->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $project->end_date ? $project->end_date->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('company.projects.show', $project) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhum projeto vinculado a este contrato</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Parcelas/Contas a Receber -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-receipt me-2 text-primary"></i>Parcelas/Contas a Receber</h6>
                    <span class="badge bg-primary">{{ $contract->receivables->count() }} parcela(s)</span>
                </div>
                <div class="card-body">
                    @if($contract->receivables->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Valor Pago</th>
                                    <th>Vencimento</th>
                                    <th>Pagamento</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contract->receivables->sortBy('due_date') as $receivable)
                                <tr class="{{ $receivable->isOverdue() ? 'table-danger' : '' }}">
                                    <td>
                                        @if($receivable->installment_number)
                                            {{ $receivable->installment_number }}/{{ $receivable->total_installments ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $receivable->description }}</strong>
                                        @if($receivable->notes)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($receivable->notes, 50) }}</small>
                                        @endif
                                    </td>
                                    <td><strong>R$ {{ number_format($receivable->value, 2, ',', '.') }}</strong></td>
                                    <td>
                                        @if($receivable->paid_value && $receivable->paid_value > 0)
                                            <strong class="text-success">R$ {{ number_format($receivable->paid_value, 2, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $receivable->due_date->format('d/m/Y') }}
                                        @if($receivable->isOverdue())
                                            <br><small class="text-danger">
                                                {{ (int) floor(now()->diffInDays($receivable->due_date, false)) }} dias atrasado
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($receivable->paid_date)
                                            {{ $receivable->paid_date->format('d/m/Y') }}
                                            @if($receivable->payment_method)
                                                <br><small class="text-muted">{{ $receivable->payment_method }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
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
                                            if ($receivable->status === 'partial' && $receivable->paid_value) {
                                                $percentage = ($receivable->paid_value / $receivable->value) * 100;
                                                $statusLabel .= ' (' . number_format($percentage, 0) . '%)';
                                            }
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('company.receivables.show', $receivable) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($receivable->status === 'pending' || $receivable->status === 'partial')
                                        <a href="{{ route('company.receivables.edit', $receivable) }}" class="btn btn-sm btn-outline-success" title="Marcar como Paga">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhuma parcela registrada para este contrato</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
