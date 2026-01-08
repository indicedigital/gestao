@extends('layouts.mobile')

@section('title', 'Contas a Receber')

@section('content')
<div class="mobile-content">
    <!-- Filtros -->
    <div class="mobile-card" style="margin-bottom: 16px;">
        <form method="GET" action="{{ route('company.receivables.index') }}" id="filterForm">
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
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Parcial</option>
                </select>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px; display: block;">Buscar</label>
                <div style="position: relative;">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Descrição ou cliente" 
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

    <!-- Lista de Contas a Receber -->
    <div class="mobile-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">Lista de Contas a Receber</h5>
            <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 12px; font-size: 12px;">{{ count($receivables) }} registro(s)</span>
        </div>

        @forelse($receivables as $receivable)
        <div class="mobile-card-item" style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; overflow: hidden; {{ $receivable->isOverdue() ? 'border-left: 3px solid #f5365c;' : '' }}">
            <div class="mobile-card-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; gap: 8px;">
                <div class="mobile-card-item-title" style="font-weight: 600; font-size: 15px; color: #1a202c; flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word;">
                    #{{ $receivable->id }} - {{ $receivable->description }}
                </div>
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
                <span class="badge bg-{{ $statusColor }}" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="mobile-card-item-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Cliente</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">{{ $receivable->client->name ?? '-' }}</div>
                </div>

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Valor</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500; font-weight: 600;">R$ {{ number_format($receivable->value, 2, ',', '.') }}</div>
                </div>

                @if($receivable->paid_value && $receivable->paid_value > 0)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Valor Pago</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #2dce89; font-weight: 600;">R$ {{ number_format($receivable->paid_value, 2, ',', '.') }}</div>
                </div>
                @endif

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Vencimento</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: {{ $receivable->isOverdue() ? '#f5365c' : '#1a202c' }}; font-weight: 500;">
                        {{ $receivable->due_date->format('d/m/Y') }}
                        @if($receivable->isOverdue())
                            <br><small style="color: #f5365c; font-size: 11px;">
                                Venceu há {{ (int) floor(now()->diffInDays($receivable->due_date, false)) }} dias
                            </small>
                        @endif
                    </div>
                </div>

                @if($receivable->paid_date)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Pagamento</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        {{ $receivable->paid_date->format('d/m/Y') }}
                        @if($receivable->payment_method)
                            <br><small style="color: #64748b; font-size: 11px;">{{ $receivable->payment_method }}</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($receivable->contract)
                <div class="mobile-card-item-field" style="grid-column: 1 / -1;">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Contrato</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">{{ $receivable->contract->name }}</div>
                </div>
                @endif
            </div>

            <div class="mobile-card-item-actions" style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('company.receivables.show', $receivable) }}" 
                   class="btn btn-info btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('company.receivables.edit', $receivable) }}" 
                   class="btn btn-warning btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-edit"></i> Editar
                </a>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fas fa-arrow-circle-down" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i>
            <p style="margin: 0;">Nenhuma conta a receber encontrada</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
