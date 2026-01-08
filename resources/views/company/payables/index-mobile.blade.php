@extends('layouts.mobile')

@section('title', 'Contas a Pagar')

@section('content')
<div class="mobile-content">
    <!-- Filtros -->
    <div class="mobile-card" style="margin-bottom: 16px;">
        <form method="GET" action="{{ route('company.payables.index') }}" id="filterForm">
            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Mês</label>
                <input type="month" 
                       name="month" 
                       value="{{ $monthFilter ?? now()->format('Y-m') }}" 
                       style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                       onchange="document.getElementById('filterForm').submit();">
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Status</label>
                <select name="status" 
                        style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;"
                        onchange="document.getElementById('filterForm').submit();">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paga</option>
                </select>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Buscar</label>
                <div style="position: relative;">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Descrição, fornecedor ou funcionário" 
                           style="width: 100%; padding: 10px 12px 10px 40px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #64748b;"></i>
                </div>
            </div>

            <button type="submit" 
                    class="btn btn-primary" 
                    style="width: 100%; padding: 10px; border-radius: 8px; font-size: 14px;">
                <i class="fas fa-filter me-2"></i>Aplicar Filtros
            </button>
        </form>
    </div>

    <!-- Lista de Contas a Pagar -->
    <div class="mobile-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">Lista de Contas a Pagar</h5>
            <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 12px; font-size: 12px;">{{ count($payables) }} registro(s)</span>
        </div>

        @forelse($payables as $payable)
        <div class="mobile-card-item" style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; overflow: hidden; {{ $payable->isOverdue() ? 'border-left: 3px solid #f5365c;' : '' }}">
            <div class="mobile-card-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; gap: 8px;">
                <div class="mobile-card-item-title" style="font-weight: 600; font-size: 15px; color: #1a202c; flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word;">
                    #{{ $payable->id }} - {{ $payable->description }}
                </div>
                @php
                    $statusColors = [
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'secondary'
                    ];
                    $statusColor = $statusColors[$payable->status] ?? 'secondary';
                    $statusLabels = [
                        'pending' => 'Pendente',
                        'paid' => 'Paga',
                        'cancelled' => 'Cancelada'
                    ];
                    $statusLabel = $statusLabels[$payable->status] ?? ucfirst($payable->status);
                @endphp
                <span class="badge bg-{{ $statusColor }}" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="mobile-card-item-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Tipo</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        @php
                            $typeLabels = [
                                'salary' => 'Salário',
                                'service' => 'Serviço',
                                'supplier' => 'Fornecedor',
                                'other' => 'Outro'
                            ];
                            $typeLabel = $typeLabels[$payable->type] ?? $payable->type;
                        @endphp
                        <span class="badge bg-info" style="padding: 4px 8px; border-radius: 6px; font-size: 11px;">{{ $typeLabel }}</span>
                    </div>
                </div>

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Valor</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500; font-weight: 600;">R$ {{ number_format($payable->value, 2, ',', '.') }}</div>
                </div>

                <div class="mobile-card-item-field" style="grid-column: 1 / -1;">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Fornecedor/Funcionário</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        @if($payable->employee)
                            {{ $payable->employee->name }}
                        @elseif($payable->supplier_name)
                            {{ $payable->supplier_name }}
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </div>
                </div>

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Vencimento</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: {{ $payable->isOverdue() ? '#f5365c' : '#1a202c' }}; font-weight: 500;">
                        {{ $payable->due_date->format('d/m/Y') }}
                        @if($payable->isOverdue())
                            <br><small style="color: #f5365c; font-size: 11px;">
                                Venceu há {{ (int) floor(now()->diffInDays($payable->due_date, false)) }} dias
                            </small>
                        @endif
                    </div>
                </div>

                @if($payable->paid_date)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Pagamento</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        {{ $payable->paid_date->format('d/m/Y') }}
                        @if($payable->payment_method)
                            <br><small style="color: #64748b; font-size: 11px;">{{ $payable->payment_method }}</small>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="mobile-card-item-actions" style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('company.payables.show', $payable) }}" 
                   class="btn btn-info btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('company.payables.edit', $payable) }}" 
                   class="btn btn-warning btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fas fa-arrow-circle-up" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i>
            <p style="margin: 0;">Nenhuma conta a pagar encontrada</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
