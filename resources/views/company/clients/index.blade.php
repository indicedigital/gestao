@extends('layouts.app')

@section('title', 'Clientes')

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
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .badge-modern {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
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
    .icon-circle.secondary { background: #f1f5f9; color: #64748b; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Clientes</h1>
                <p>Gerencie seus clientes e parceiros de negócio</p>
            </div>
            <a href="{{ route('company.clients.create') }}" class="btn btn-primary btn-modern">
                <i class="fas fa-plus me-2"></i>Novo Cliente
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Total de Clientes</h6>
                        <h3>{{ $stats['total'] }}</h3>
                    </div>
                    <div class="icon-circle primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card success">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Clientes Ativos</h6>
                        <h3>{{ $stats['active'] }}</h3>
                    </div>
                    <div class="icon-circle success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card secondary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Clientes Inativos</h6>
                        <h3>{{ $stats['inactive'] }}</h3>
                    </div>
                    <div class="icon-circle secondary">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card danger">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Clientes Bloqueados</h6>
                        <h3>{{ $stats['blocked'] }}</h3>
                    </div>
                    <div class="icon-circle danger">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="card-modern">
        <div class="card-body" style="padding: 16px 24px;">
            <form method="GET" action="{{ route('company.clients.index') }}" id="filterForm">
                <div class="row g-2 align-items-end">
                    <!-- Campo de Busca -->
                    <div class="col-md-6">
                        <label for="search" class="form-label small text-muted mb-1" style="font-size: 12px;">Buscar</label>
                        <div class="search-box-modern">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nome, e-mail, telefone ou documento...">
                        </div>
                    </div>
                    
                    <!-- Botão de Filtros Dropdown -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1 d-block" style="font-size: 12px;">&nbsp;</label>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary w-100 dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="padding: 8px 12px; font-size: 13px; height: 38px; border-radius: 8px;">
                                <i class="fas fa-filter me-1"></i>Filtros
                                @if(request()->hasAny(['type', 'status', 'city']))
                                    <span class="badge bg-primary ms-1" style="font-size: 10px;">{{ collect([request('type'), request('status'), request('city')])->filter()->count() }}</span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-3 shadow-lg" style="min-width: 320px; max-height: 500px; overflow-y: auto;" aria-labelledby="filterDropdown" onclick="event.stopPropagation();">
                                <li>
                                    <div class="mb-3">
                                        <label for="type" class="form-label small">Tipo</label>
                                        <select class="form-select form-select-sm" id="type" name="type">
                                            <option value="">Todos</option>
                                            <option value="pf" {{ request('type') === 'pf' ? 'selected' : '' }}>PF</option>
                                            <option value="pj" {{ request('type') === 'pj' ? 'selected' : '' }}>PJ</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label for="status" class="form-label small">Status</label>
                                        <select class="form-select form-select-sm" id="status" name="status">
                                            <option value="">Todos</option>
                                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                                            <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                                        </select>
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label for="city" class="form-label small">Cidade</label>
                                        <input type="text" class="form-control form-control-sm" id="city" name="city" 
                                               value="{{ request('city') }}" 
                                               placeholder="Digite a cidade...">
                                    </div>
                                </li>
                                <li>
                                    <div class="mb-3">
                                        <label for="per_page" class="form-label small">Registros por Página</label>
                                        <select class="form-select form-select-sm" id="per_page" name="per_page">
                                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-check me-2"></i>Aplicar Filtros
                                        </button>
                                        <a href="{{ route('company.clients.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-redo me-2"></i>Limpar Filtros
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Ordenação -->
                    <div class="col-md-2">
                        <label for="sort_by" class="form-label small text-muted mb-1" style="font-size: 12px;">Ordenar</label>
                        <select class="form-select" id="sort_by" name="sort_by" style="padding: 8px 12px; font-size: 13px; height: 38px; border-radius: 8px;" onchange="document.getElementById('sort_order').value = (document.getElementById('sort_by').value === '{{ request('sort_by', 'created_at') }}' && document.getElementById('sort_order').value === 'asc') ? 'desc' : 'asc'; document.getElementById('filterForm').submit();">
                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Data Cadastro</option>
                            <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Nome</option>
                            <option value="id" {{ request('sort_by') === 'id' ? 'selected' : '' }}>ID</option>
                        </select>
                        <input type="hidden" id="sort_order" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                    </div>
                    
                    <!-- Botões de Exportação -->
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1 d-block" style="font-size: 12px;">&nbsp;</label>
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('company.clients.export.excel', request()->query()) }}" class="btn btn-success btn-export" title="Exportar Excel">
                                <i class="fas fa-file-excel"></i>
                            </a>
                            <a href="{{ route('company.clients.export.pdf', request()->query()) }}" class="btn btn-danger btn-export" title="Exportar PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card-modern">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Lista de Clientes</h5>
            <span class="badge bg-primary badge-modern">{{ $clients->total() }} registro(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">
                                <a href="{{ route('company.clients.index', array_merge(request()->query(), ['sort_by' => 'id', 'sort_order' => request('sort_by') === 'id' && request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    ID
                                    @if(request('sort_by') === 'id')
                                        <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('company.clients.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-decoration-none text-dark">
                                    Nome
                                    @if(request('sort_by') === 'name')
                                        <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Tipo</th>
                            <th>Documento</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Cidade/Estado</th>
                            <th>Status</th>
                            <th style="width: 150px;" class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                        <tr>
                            <td><strong class="text-muted">#{{ $client->id }}</strong></td>
                            <td>
                                <strong>{{ $client->name }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $client->type === 'pj' ? 'primary' : 'info' }} badge-modern">
                                    {{ $client->type === 'pj' ? 'PJ' : 'PF' }}
                                </span>
                            </td>
                            <td><span class="text-muted">{{ $client->document ?? '-' }}</span></td>
                            <td>
                                @if($client->email)
                                    <a href="mailto:{{ $client->email }}" class="text-decoration-none text-primary">
                                        {{ $client->email }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($client->phone)
                                    <a href="tel:{{ $client->phone }}" class="text-decoration-none text-primary">
                                        {{ $client->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($client->city || $client->state)
                                    <span class="text-muted">{{ $client->city ?? '' }}{{ $client->city && $client->state ? '/' : '' }}{{ $client->state ?? '' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'blocked' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$client->status] ?? 'secondary';
                                    $statusLabels = [
                                        'active' => 'Ativo',
                                        'inactive' => 'Inativo',
                                        'blocked' => 'Bloqueado'
                                    ];
                                    $statusLabel = $statusLabels[$client->status] ?? ucfirst($client->status);
                                @endphp
                                <span class="badge bg-{{ $statusColor }} badge-modern">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('company.clients.show', $client) }}" class="btn btn-info text-white btn-table" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('company.clients.edit', $client) }}" class="btn btn-warning text-white btn-table" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('company.clients.destroy', $client) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este cliente?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-table" title="Remover">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-3">Nenhum cliente encontrado</p>
                                    @if(request()->hasAny(['search', 'type', 'status', 'city']))
                                        <a href="{{ route('company.clients.index') }}" class="btn btn-primary btn-modern">Limpar Filtros</a>
                                    @else
                                        <a href="{{ route('company.clients.create') }}" class="btn btn-primary btn-modern">Cadastrar Primeiro Cliente</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($clients->hasPages())
        <div class="card-footer bg-white border-top" style="padding: 20px 24px; border-radius: 0 0 16px 16px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-0 text-muted small">
                        Mostrando {{ $clients->firstItem() }} até {{ $clients->lastItem() }} de {{ $clients->total() }} registros
                    </p>
                </div>
                <div>
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Auto-submit ao pressionar Enter no campo de busca
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('filterForm').submit();
    }
});

// Prevenir que o dropdown feche ao clicar dentro dele
var filterDropdown = document.getElementById('filterDropdown');
if (filterDropdown) {
    var dropdownMenu = filterDropdown.nextElementSibling;
    
    // Prevenir fechamento ao clicar dentro do dropdown
    if (dropdownMenu) {
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}
</script>
@endpush
@endsection
