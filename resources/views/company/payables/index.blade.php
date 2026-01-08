@extends('layouts.app')

@section('title', 'Contas a Pagar')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
                <h1>Contas a Pagar</h1>
                <p class="text-muted mb-0">Gerencie suas despesas e pagamentos</p>
            </div>
            <a href="{{ route('company.payables.create') }}" class="btn btn-primary btn-modern">
                <i class="fas fa-plus me-2"></i>Nova Conta a Pagar
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card-modern mb-4">
        <div class="card-body" style="padding: 16px 24px;">
            <form method="GET" action="{{ route('company.payables.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <!-- Campo de Busca -->
                    <div class="col-md-4">
                        <label for="search" class="form-label small text-muted mb-1" style="font-size: 12px;">Buscar</label>
                        <div class="search-box-modern position-relative">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Descrição, fornecedor, funcionário...">
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
                                @if(request()->hasAny(['status', 'type']))
                                    <span class="badge bg-primary ms-1" style="font-size: 10px;">{{ collect([request('status'), request('type')])->filter()->count() }}</span>
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
                                            <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Vencida</option>
                                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label for="type" class="form-label small">Tipo</label>
                                        <select class="form-select form-select-sm" id="type" name="type">
                                            <option value="">Todos</option>
                                            <option value="salary" {{ request('type') === 'salary' ? 'selected' : '' }}>Salário</option>
                                            <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>Serviço</option>
                                            <option value="supplier" {{ request('type') === 'supplier' ? 'selected' : '' }}>Fornecedor</option>
                                            <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Outro</option>
                                        </select>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-check me-2"></i>Aplicar Filtros
                                        </button>
                                        <a href="{{ route('company.payables.index') }}" class="btn btn-outline-secondary btn-sm">
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
                <table id="payablesTable" class="table table-hover w-100">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Fornecedor/Funcionário</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payables as $payable)
                        <tr class="{{ $payable->isOverdue() ? 'table-danger' : '' }}">
                            <td>{{ $payable->id }}</td>
                            <td>
                                <strong>{{ $payable->description }}</strong>
                                @if($payable->notes)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($payable->notes, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeLabels = [
                                        'salary' => 'Salário',
                                        'service' => 'Serviço',
                                        'supplier' => 'Fornecedor',
                                        'other' => 'Outro'
                                    ];
                                    $typeLabel = $typeLabels[$payable->type] ?? $payable->type;
                                @endphp
                                <span class="badge bg-info">{{ $typeLabel }}</span>
                            </td>
                            <td>
                                @if($payable->employee)
                                    {{ $payable->employee->name }}
                                @elseif($payable->supplier_name)
                                    {{ $payable->supplier_name }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td><strong>R$ {{ number_format($payable->value, 2, ',', '.') }}</strong></td>
                            <td>
                                {{ $payable->due_date->format('d/m/Y') }}
                                @if($payable->isOverdue())
                                    <br><small class="text-danger">
                                        {{ (int) floor(now()->diffInDays($payable->due_date, false)) }} dias atrasado
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($payable->paid_date)
                                    {{ $payable->paid_date->format('d/m/Y') }}
                                    @if($payable->payment_method)
                                        <br><small class="text-muted">{{ $payable->payment_method }}</small>
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
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('company.payables.show', $payable) }}" class="btn btn-sm btn-outline-primary btn-table" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($payable->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-outline-success btn-table" title="Marcar como Paga" data-bs-toggle="modal" data-bs-target="#markPaidModal{{ $payable->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    <a href="{{ route('company.payables.edit', $payable) }}" class="btn btn-sm btn-outline-warning btn-table" title="Editar">
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
@foreach($payables as $payable)
@if($payable->status === 'pending')
<div class="modal fade" id="markPaidModal{{ $payable->id }}" tabindex="-1" aria-labelledby="markPaidModalLabel{{ $payable->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('company.payables.mark-as-paid', $payable) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="markPaidModalLabel{{ $payable->id }}">Marcar como Paga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Valor: <strong>R$ {{ number_format($payable->value, 2, ',', '.') }}</strong></label>
                    </div>
                    <div class="mb-3">
                        <label for="paid_date{{ $payable->id }}" class="form-label">Data de Pagamento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" 
                               id="paid_date{{ $payable->id }}" 
                               name="paid_date" 
                               value="{{ old('paid_date', date('Y-m-d')) }}" 
                               required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method{{ $payable->id }}" class="form-label">Forma de Pagamento</label>
                        <input type="text" class="form-control" 
                               id="payment_method{{ $payable->id }}" 
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
<script>
let payablesTable;

$(document).ready(function() {
    payablesTable = $('#payablesTable').DataTable({
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
</script>
@endpush
@endsection
