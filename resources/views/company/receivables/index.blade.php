@extends('layouts.app')

@section('title', 'Contas a Receber')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
    
    .search-box-modern {
        position: relative;
    }
    
    .search-box-modern .form-control {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 8px 14px 8px 40px;
        font-size: 14px;
        transition: all 0.2s;
        height: 38px;
    }
    
    .search-box-modern .form-control:focus {
        border-color: #5e72e4;
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
    }
    
    .search-box-modern i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }
    
    .btn-modern {
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-export {
        padding: 6px 12px;
        font-size: 13px;
        border-radius: 8px;
    }
    
    .btn-table {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 6px;
    }
    
    table.dataTable thead th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        padding: 16px;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
    }
    
    table.dataTable tbody td {
        padding: 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    
    table.dataTable tbody tr:hover {
        background: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Contas a Receber</h1>
                <p class="text-muted mb-0">Gerencie suas receitas e cobranças</p>
            </div>
            <a href="{{ route('company.receivables.create') }}" class="btn btn-primary btn-modern">
                <i class="fas fa-plus me-2"></i>Nova Conta a Receber
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card-modern mb-4">
        <div class="card-body" style="padding: 16px 24px;">
            <form method="GET" action="{{ route('company.receivables.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <!-- Campo de Busca -->
                    <div class="col-md-4">
                        <label for="search" class="form-label small text-muted mb-1" style="font-size: 12px;">Buscar</label>
                        <div class="search-box-modern position-relative">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Descrição, cliente...">
                            <div id="searchLoading" class="position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); display: none;">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtro de Mês -->
                    <div class="col-md-3">
                        <label for="month" class="form-label small text-muted mb-1" style="font-size: 12px;">Mês</label>
                        <input type="month" class="form-control" id="month" name="month" 
                               value="{{ $monthFilter }}" 
                               style="padding: 8px 12px; font-size: 13px; height: 38px; border-radius: 8px;"
                               onchange="document.getElementById('filterForm').submit();">
                    </div>
                    
                    <!-- Botão de Filtros Dropdown -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1 d-block" style="font-size: 12px;">&nbsp;</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary w-100 dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 8px 12px; font-size: 13px; height: 38px; border-radius: 8px;">
                                <i class="fas fa-filter me-1"></i>Filtros
                                @if(request()->hasAny(['status', 'client_id']))
                                    <span class="badge bg-primary ms-1" style="font-size: 10px;">{{ collect([request('status'), request('client_id')])->filter()->count() }}</span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-lg" style="min-width: 280px;" aria-labelledby="filterDropdown" onclick="event.stopPropagation();">
                                <li>
                                    <div class="mb-3">
                                        <label for="status" class="form-label small">Status</label>
                                        <select class="form-select form-select-sm" id="status" name="status">
                                            <option value="">Todos</option>
                                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paga</option>
                                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Parcial</option>
                                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencida</option>
                                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label for="client_id" class="form-label small">Cliente</label>
                                        <select class="form-select form-select-sm" id="client_id" name="client_id">
                                            <option value="">Todos</option>
                                            @foreach($clients as $client)
                                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-check me-2"></i>Aplicar Filtros
                                        </button>
                                        <a href="{{ route('company.receivables.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-redo me-2"></i>Limpar Filtros
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Botões de Exportação -->
                    <div class="col-md-3 text-end">
                        <label class="form-label small text-muted mb-1 d-block" style="font-size: 12px;">&nbsp;</label>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-success btn-export" title="Exportar Excel">
                                <i class="fas fa-file-excel"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-export" title="Exportar PDF">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="tableLoading" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Carregando...</span>
        </div>
        <p class="text-muted mt-2">Carregando dados...</p>
    </div>

    <!-- Tabela -->
    <div class="card-modern" id="tableContainer">
        <div class="card-body">
            <div class="table-responsive">
                <table id="receivablesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Valor Pago</th>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receivables as $receivable)
                        <tr class="{{ $receivable->isOverdue() ? 'table-danger' : '' }}">
                            <td>{{ $receivable->id }}</td>
                            <td>
                                <strong>{{ $receivable->description }}</strong>
                                @if($receivable->contract)
                                    <br><small class="text-muted">Contrato: {{ $receivable->contract->name }}</small>
                                @endif
                            </td>
                            <td>{{ $receivable->client->name ?? '-' }}</td>
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
                                <div class="btn-group">
                                    <a href="{{ route('company.receivables.show', $receivable) }}" class="btn btn-sm btn-outline-primary btn-table" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($receivable->status === 'pending' || $receivable->status === 'partial')
                                    <button type="button" class="btn btn-sm btn-outline-success btn-table" title="Marcar como Paga" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $receivable->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    <a href="{{ route('company.receivables.edit', $receivable) }}" class="btn btn-sm btn-outline-warning btn-table" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modais para marcar como pago -->
@foreach($receivables as $receivable)
@if($receivable->status === 'pending' || $receivable->status === 'partial')
<div class="modal fade" id="markPaidModal{{ $receivable->id }}" tabindex="-1" aria-labelledby="markPaidModalLabel{{ $receivable->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('company.receivables.mark-as-paid', $receivable) }}" method="POST" id="markPaidForm{{ $receivable->id }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="markPaidModalLabel{{ $receivable->id }}">Registrar Pagamento</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Valor Total: <strong>R$ {{ number_format($receivable->value, 2, ',', '.') }}</strong></label>
                                                @if($receivable->status === 'partial')
                                                <div class="alert alert-info">
                                                    <small>Valor já pago: R$ {{ number_format($receivable->paid_value ?? 0, 2, ',', '.') }}</small>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="partial_payment{{ $receivable->id }}" name="partial_payment" value="1" onchange="togglePartialPayment({{ $receivable->id }}, {{ $receivable->value }}, {{ $receivable->paid_value ?? 0 }})">
                                                    <label class="form-check-label" for="partial_payment{{ $receivable->id }}">
                                                        Pagamento Parcial
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3" id="paid_value_container{{ $receivable->id }}" style="display: none;">
                                                <label for="paid_value{{ $receivable->id }}" class="form-label">Valor Pago <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text">R$</span>
                                                    <input type="text" class="form-control money-mask" 
                                                           id="paid_value{{ $receivable->id }}" 
                                                           name="paid_value" 
                                                           value="{{ old('paid_value', $receivable->status === 'partial' ? number_format($receivable->paid_value, 2, ',', '.') : number_format($receivable->value, 2, ',', '.')) }}"
                                                           placeholder="0,00">
                                                </div>
                                                <small class="text-muted">Valor já pago: R$ <span id="already_paid{{ $receivable->id }}">{{ number_format($receivable->paid_value ?? 0, 2, ',', '.') }}</span></small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="paid_date{{ $receivable->id }}" class="form-label">Data de Pagamento <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" 
                                                       id="paid_date{{ $receivable->id }}" 
                                                       name="paid_date" 
                                                       value="{{ old('paid_date', date('Y-m-d')) }}" 
                                                       required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="payment_method{{ $receivable->id }}" class="form-label">Forma de Pagamento</label>
                                                <input type="text" class="form-control" 
                                                       id="payment_method{{ $receivable->id }}" 
                                                       name="payment_method" 
                                                       placeholder="Ex: PIX, Boleto, Transferência...">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success">Confirmar Pagamento</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
@endif
@endforeach

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
<script>
let receivablesTable;

$(document).ready(function() {
    receivablesTable = $('#receivablesTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        pageLength: 25,
        order: [[5, 'asc']], // Ordenar por vencimento
        columnDefs: [
            { orderable: false, targets: [8] } // Desabilitar ordenação na coluna de ações
        ],
        dom: 'lrtip', // Remove o campo de busca padrão do DataTables (sem 'f')
        searching: false // Desabilita a busca do DataTables
    });

    // Busca em tempo real com debounce
    let searchTimeout;
    $('#search').on('input', function() {
        const searchValue = $(this).val();
        const form = $('#filterForm');
        
        // Mostra loading no campo
        $('#searchLoading').show();
        
        // Limpa timeout anterior
        clearTimeout(searchTimeout);
        
        // Aguarda 500ms após parar de digitar
        searchTimeout = setTimeout(function() {
            // Mostra loading na tabela
            $('#tableLoading').show();
            $('#tableContainer').hide();
            
            // Submete o formulário
            form.submit();
        }, 500);
    });
});

function togglePartialPayment(receivableId, totalValue, alreadyPaid) {
    const checkbox = document.getElementById('partial_payment' + receivableId);
    const container = document.getElementById('paid_value_container' + receivableId);
    const paidValueInput = document.getElementById('paid_value' + receivableId);
    
    if (checkbox.checked) {
        container.style.display = 'block';
        paidValueInput.required = true;
        const remaining = totalValue - alreadyPaid;
        if (remaining > 0) {
            paidValueInput.value = remaining.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    } else {
        container.style.display = 'none';
        paidValueInput.required = false;
        paidValueInput.value = totalValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}

// Aplica máscara de dinheiro
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.money-mask').forEach(function(input) {
        new Cleave(input, {
            numeral: true,
            numeralDecimalMark: ',',
            delimiter: '.',
            numeralDecimalScale: 2
        });
    });
});
</script>
@endpush
@endsection
