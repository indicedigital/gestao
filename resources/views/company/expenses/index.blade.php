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
    .icon-circle.info { background: #e0f7ff; color: #11cdef; }

    .filter-bar-expenses {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }

    .category-breakdown-compact .category-cell {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 10px;
        background: #fafbfc;
        height: 100%;
        font-size: 12px;
        line-height: 1.35;
    }

    .category-breakdown-compact .category-cell .cat-total {
        font-weight: 700;
        font-size: 13px;
        color: #1a202c;
    }

    .category-breakdown-compact .category-cell .cat-meta {
        font-size: 11px;
        color: #64748b;
    }

    .kpi-hint {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
    }

    .expense-kpi-trigger {
        cursor: pointer;
    }
    .expense-kpi-trigger:focus-visible {
        outline: 2px solid #5e72e4;
        outline-offset: 2px;
    }
    .category-cell.expense-kpi-trigger {
        cursor: pointer;
    }
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

    <!-- Tabs -->
    <div class="card-modern">
        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-modern" id="expenseTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'fixed' ? 'active' : '' }}" 
                       href="{{ route('company.expenses.index', $tabQueryFixed) }}" 
                       aria-selected="{{ $type === 'fixed' ? 'true' : 'false' }}">
                        <i class="fas fa-calendar-alt me-2"></i>Despesas Fixas
                        <span class="badge bg-primary ms-2">{{ $fixedCount }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $type === 'variable' ? 'active' : '' }}" 
                       href="{{ route('company.expenses.index', $tabQueryVariable) }}" 
                       aria-selected="{{ $type === 'variable' ? 'true' : 'false' }}">
                        <i class="fas fa-random me-2"></i>Despesas Variáveis
                        <span class="badge bg-warning ms-2">{{ $variableCount }}</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-4">
                <div class="tab-pane fade show active">
                    <form method="get" action="{{ route('company.expenses.index') }}" class="filter-bar-expenses">
                        <input type="hidden" name="type" value="{{ $type }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4 col-lg-3">
                                <label for="expense_month_filter" class="form-label small text-muted mb-1 fw-semibold">Mês (vencimento)</label>
                                <input type="month" name="month" id="expense_month_filter" class="form-control" value="{{ $monthInput }}" onchange="this.form.submit()">
                            </div>
                            <div class="col-md-5 col-lg-4">
                                <label for="expense_category_filter" class="form-label small text-muted mb-1 fw-semibold">Categoria</label>
                                <select name="category_id" id="expense_category_filter" class="form-select" onchange="this.form.submit()">
                                    <option value="" @selected($selectedCategoryKey === '')>Todas as categorias</option>
                                    @if($hasUncategorized)
                                        <option value="none" @selected($selectedCategoryKey === 'none')>Sem categoria</option>
                                    @endif
                                    @foreach($filterCategories as $cat)
                                        <option value="{{ $cat->id }}" @selected($selectedCategoryKey === (string) $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-auto">
                                <button type="submit" class="btn btn-outline-secondary">Aplicar</button>
                                @if($hasCategoryFilter)
                                    <a href="{{ route('company.expenses.index', ['type' => $type, 'month' => $monthInput]) }}" class="btn btn-link text-decoration-none px-2">Limpar categoria</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    <p class="small text-muted mb-2">
                        Referência: <strong>{{ ucfirst($selectedMonthLabel) }}</strong>.
                        @if($type === 'variable')
                            Variáveis filtradas pela <strong>data de vencimento</strong> dentro deste mês. Totais somam todas as linhas que entram no filtro (lista completa, não só a página).
                        @else
                            <strong>Fixas</strong> não usam mês na linha (valor mensal recorrente): o mês afeta só o rótulo; listagem e totais consideram todo o cadastro de despesas fixas.
                        @endif
                    </p>
                    <p class="small text-primary mb-3"><i class="fas fa-chart-line me-1"></i>Clique nos cards ou nas categorias abaixo para ver a <strong>evolução mês a mês no ano</strong>.</p>

                    @php $evoYear = (int) substr($monthInput, 0, 4); @endphp
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="kpi-card success expense-kpi-trigger"
                                 tabindex="0" role="button"
                                 data-expense-evolution="sum"
                                 data-evolution-year="{{ $evoYear }}"
                                 data-evolution-type="{{ $type }}"
                                 data-category-id="{{ $selectedCategoryKey }}"
                                 data-evolution-title="Soma — {{ $type === 'fixed' ? 'Despesas fixas' : 'Despesas variáveis' }} ({{ $evoYear }})">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6>Soma (filtro atual)</h6>
                                        <h3>R$ {{ number_format($filteredTotal, 2, ',', '.') }}</h3>
                                        @if($hasCategoryFilter)
                                            <p class="kpi-hint mb-0">No mês, todas as categorias: R$ {{ number_format($typeTotal, 2, ',', '.') }}</p>
                                        @endif
                                    </div>
                                    <div class="icon-circle success"><i class="fas fa-coins"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="kpi-card primary expense-kpi-trigger"
                                 tabindex="0" role="button"
                                 data-expense-evolution="count"
                                 data-evolution-year="{{ $evoYear }}"
                                 data-evolution-type="{{ $type }}"
                                 data-category-id="{{ $selectedCategoryKey }}"
                                 data-evolution-title="Lançamentos — {{ $type === 'fixed' ? 'Fixas' : 'Variáveis' }} ({{ $evoYear }})">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6>Lançamentos</h6>
                                        <h3>{{ $filteredCount }}</h3>
                                        <p class="kpi-hint mb-0">Mesmo mês, todas as categorias: {{ $typeCount }}</p>
                                    </div>
                                    <div class="icon-circle primary"><i class="fas fa-list"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="kpi-card info expense-kpi-trigger"
                                 tabindex="0" role="button"
                                 data-expense-evolution="average"
                                 data-evolution-year="{{ $evoYear }}"
                                 data-evolution-type="{{ $type }}"
                                 data-category-id="{{ $selectedCategoryKey }}"
                                 data-evolution-title="Média por despesa ({{ $evoYear }})">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6>Média por despesa</h6>
                                        <h3>R$ {{ number_format($filteredAverage, 2, ',', '.') }}</h3>
                                    </div>
                                    <div class="icon-circle info"><i class="fas fa-balance-scale"></i></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="kpi-card warning expense-kpi-trigger"
                                 tabindex="0" role="button"
                                 data-expense-evolution="{{ $hasCategoryFilter && $typeTotal > 0 ? 'share' : 'type_total' }}"
                                 data-evolution-year="{{ $evoYear }}"
                                 data-evolution-type="{{ $type }}"
                                 data-category-id="{{ $selectedCategoryKey }}"
                                 data-evolution-title="{{ $hasCategoryFilter && $typeTotal > 0 ? 'Participação no tipo (%) — '.$evoYear : 'Total do tipo no ano (R$) — '.$evoYear }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        @if($hasCategoryFilter && $typeTotal > 0)
                                            <h6>Participação no tipo</h6>
                                            <h3>{{ number_format(($filteredTotal / $typeTotal) * 100, 1, ',', '.') }}%</h3>
                                            <p class="kpi-hint mb-0">Desta categoria no total do mês ({{ $type === 'fixed' ? 'fixas' : 'variáveis' }})</p>
                                        @else
                                            <h6>Grupos no resumo</h6>
                                            <h3>{{ $categoryBreakdown->count() }}</h3>
                                            <p class="kpi-hint mb-0">Categorias com lançamento</p>
                                        @endif
                                    </div>
                                    <div class="icon-circle warning"><i class="fas fa-chart-pie"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($categoryBreakdown->isNotEmpty())
                    <div class="category-breakdown-compact mb-3">
                        <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                            <span class="small text-muted fw-semibold text-uppercase" style="letter-spacing: 0.03em;">Por categoria</span>
                            <span class="small text-muted">{{ $type === 'fixed' ? 'Fixas' : 'Variáveis' }}</span>
                        </div>
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2">
                            @foreach($categoryBreakdown as $row)
                            <div class="col">
                                <div class="category-cell expense-kpi-trigger"
                                     tabindex="0" role="button"
                                     data-expense-evolution="category"
                                     data-evolution-year="{{ $evoYear }}"
                                     data-evolution-type="{{ $type }}"
                                     data-category-id="{{ $row['id'] === null ? 'none' : $row['id'] }}"
                                     data-evolution-title="Evolução: {{ $row['name'] }} ({{ $evoYear }})">
                                    <span class="badge badge-modern text-truncate d-inline-block mb-1" style="max-width: 100%; background-color: {{ $row['color'] }};" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                                    <div class="cat-total">R$ {{ number_format($row['total'], 2, ',', '.') }}</div>
                                    <div class="cat-meta mb-1">{{ $row['count'] }} lanç.</div>
                                    <a href="{{ route('company.expenses.index', ['type' => $type, 'month' => $monthInput, 'category_id' => $row['id'] === null ? 'none' : $row['id']]) }}" class="link-primary small text-decoration-none" onclick="event.stopPropagation()">Filtrar</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

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

@include('company.expenses.partials.evolution-modal')
@endsection
