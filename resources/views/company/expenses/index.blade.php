@extends('layouts.app')

@section('title', 'Despesas')

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
    
    .nav-tabs-modern {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 24px;
    }
    
    .nav-tabs-modern .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        padding: 12px 24px;
        margin-right: 8px;
        border-radius: 8px 8px 0 0;
        transition: all 0.2s;
    }
    
    .nav-tabs-modern .nav-link:hover {
        color: #1a202c;
        background: #f8fafc;
        border-bottom-color: #cbd5e0;
    }
    
    .nav-tabs-modern .nav-link.active {
        color: #5e72e4;
        background: white;
        border-bottom-color: #5e72e4;
        font-weight: 600;
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
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .table-modern tbody tr:hover {
        background: #f8fafc;
    }
    
    .badge-modern {
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
    }
    
    .btn-modern {
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .btn-table {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 6px;
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
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Despesas</h1>
                <p>Gerencie suas despesas fixas e variáveis</p>
            </div>
            <a href="{{ route('company.expenses.create') }}" class="btn btn-primary btn-modern">
                <i class="fas fa-plus me-2"></i>Nova Despesa
            </a>
        </div>
    </div>

    <!-- Estatísticas -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="kpi-card primary">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Despesas Fixas</h6>
                        <h3>{{ $fixedCount }}</h3>
                    </div>
                    <div class="icon-circle primary">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="kpi-card warning">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6>Despesas Variáveis</h6>
                        <h3>{{ $variableCount }}</h3>
                    </div>
                    <div class="icon-circle warning">
                        <i class="fas fa-random"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card-modern">
        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-modern" id="expenseTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'fixed' ? 'active' : '' }}" 
                       href="{{ route('company.expenses.index', ['type' => 'fixed']) }}" 
                       aria-selected="{{ $type === 'fixed' ? 'true' : 'false' }}">
                        <i class="fas fa-calendar-alt me-2"></i>Despesas Fixas
                        <span class="badge bg-primary ms-2">{{ $fixedCount }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'variable' ? 'active' : '' }}" 
                       href="{{ route('company.expenses.index', ['type' => 'variable']) }}" 
                       aria-selected="{{ $type === 'variable' ? 'true' : 'false' }}">
                        <i class="fas fa-random me-2"></i>Despesas Variáveis
                        <span class="badge bg-warning ms-2">{{ $variableCount }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <div class="tab-pane fade show active">
                    @if($expenses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Fornecedor</th>
                                    <th>Valor</th>
                                    @if($type === 'fixed')
                                    <th>Dia do Vencimento</th>
                                    @else
                                    <th>Data de Vencimento</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->id }}</td>
                                    <td>
                                        <strong>{{ $expense->description }}</strong>
                                        @if($expense->notes)
                                        <br><small class="text-muted">{{ Str::limit($expense->notes, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($expense->category)
                                        <span class="badge badge-modern" style="background-color: {{ $expense->category->color ?? '#5e72e4' }};">{{ $expense->category->name }}</span>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $expense->supplier->name ?? '-' }}</td>
                                    <td><strong>R$ {{ number_format($expense->value, 2, ',', '.') }}</strong></td>
                                    <td>
                                        @if($type === 'fixed')
                                        <span class="badge bg-primary">Dia {{ $expense->due_date_day }}</span>
                                        @else
                                        {{ $expense->due_date ? \Carbon\Carbon::parse($expense->due_date)->format('d/m/Y') : '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($expense->is_active)
                                        <span class="badge bg-success badge-modern">Ativa</span>
                                        @else
                                        <span class="badge bg-secondary badge-modern">Inativa</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('company.expenses.edit', $expense) }}" class="btn btn-sm btn-warning btn-table" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('company.expenses.destroy', $expense) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover esta despesa?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger btn-table" title="Remover">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $expenses->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Nenhuma despesa {{ $type === 'fixed' ? 'fixa' : 'variável' }} cadastrada</p>
                        <a href="{{ route('company.expenses.create') }}" class="btn btn-primary">Cadastrar Primeira Despesa</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
