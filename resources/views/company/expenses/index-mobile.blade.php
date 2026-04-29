@extends('layouts.mobile')

@section('title', 'Despesas')

@section('content')
@php $evoYear = (int) substr($monthInput, 0, 4); @endphp
<div class="mobile-content">
    <!-- Tabs -->
    <div class="mobile-card" style="margin-bottom: 16px;">
        <div style="display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0;">
            <a href="{{ route('company.expenses.index', $tabQueryFixed) }}" 
               class="btn {{ $type === 'fixed' ? 'btn-primary' : 'btn-outline-primary' }}" 
               style="flex: 1; padding: 10px; border-radius: 8px 8px 0 0; font-size: 13px; text-align: center; text-decoration: none; border: none; border-bottom: 3px solid {{ $type === 'fixed' ? '#5e72e4' : 'transparent' }};">
                Fixas ({{ $fixedCount }})
            </a>
            <a href="{{ route('company.expenses.index', $tabQueryVariable) }}" 
               class="btn {{ $type === 'variable' ? 'btn-primary' : 'btn-outline-primary' }}" 
               style="flex: 1; padding: 10px; border-radius: 8px 8px 0 0; font-size: 13px; text-align: center; text-decoration: none; border: none; border-bottom: 3px solid {{ $type === 'variable' ? '#5e72e4' : 'transparent' }};">
                Variáveis ({{ $variableCount }})
            </a>
        </div>
    </div>

    <!-- Botão Nova Despesa -->
    <div style="margin-bottom: 16px;">
        <a href="{{ route('company.expenses.create') }}" 
           class="btn btn-primary" 
           style="width: 100%; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-plus"></i> Nova Despesa
        </a>
    </div>

    <!-- Filtro categoria -->
    <div class="mobile-card" style="margin-bottom: 16px;">
        <form method="get" action="{{ route('company.expenses.index') }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <label class="form-label small text-muted mb-1">Mês (vencimento)</label>
            <input type="month" name="month" class="form-control form-control-sm mb-2" value="{{ $monthInput }}" onchange="this.form.submit()">
            <label class="form-label small text-muted mb-1">Categoria</label>
            <select name="category_id" class="form-select form-select-sm mb-2" onchange="this.form.submit()">
                <option value="" @selected($selectedCategoryKey === '')>Todas</option>
                @if($hasUncategorized)
                    <option value="none" @selected($selectedCategoryKey === 'none')>Sem categoria</option>
                @endif
                @foreach($filterCategories as $cat)
                    <option value="{{ $cat->id }}" @selected($selectedCategoryKey === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            @if($hasCategoryFilter)
                <a href="{{ route('company.expenses.index', ['type' => $type, 'month' => $monthInput]) }}" class="small">Limpar categoria</a>
            @endif
        </form>
    </div>

    <!-- KPIs (acompanham filtro) -->
    <div class="mobile-card" style="margin-bottom: 16px;">
        <p class="small text-muted mb-2" style="margin: 0;">{{ ucfirst($selectedMonthLabel) }} — totais com filtros (lista completa)</p>
        <p class="small text-primary mb-2" style="margin: 0;"><i class="fas fa-chart-line me-1"></i>Toque nos cards para evolução no ano.</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div class="expense-kpi-trigger" tabindex="0" role="button"
                 data-expense-evolution="sum"
                 data-evolution-year="{{ $evoYear }}"
                 data-evolution-type="{{ $type }}"
                 data-category-id="{{ $selectedCategoryKey }}"
                 data-evolution-title="Soma — {{ $type === 'fixed' ? 'Fixas' : 'Variáveis' }} ({{ $evoYear }})"
                 style="cursor:pointer;background: #ecfdf5; border-radius: 10px; padding: 12px; border: 1px solid #a7f3d0;">
                <div style="font-size: 10px; color: #047857; font-weight: 600; text-transform: uppercase;">Soma</div>
                <div style="font-size: 15px; font-weight: 700; color: #065f46;">R$ {{ number_format($filteredTotal, 2, ',', '.') }}</div>
            </div>
            <div class="expense-kpi-trigger" tabindex="0" role="button"
                 data-expense-evolution="count"
                 data-evolution-year="{{ $evoYear }}"
                 data-evolution-type="{{ $type }}"
                 data-category-id="{{ $selectedCategoryKey }}"
                 data-evolution-title="Lançamentos ({{ $evoYear }})"
                 style="cursor:pointer;background: #eef2ff; border-radius: 10px; padding: 12px; border: 1px solid #c7d2fe;">
                <div style="font-size: 10px; color: #4338ca; font-weight: 600; text-transform: uppercase;">Lançamentos</div>
                <div style="font-size: 15px; font-weight: 700; color: #3730a3;">{{ $filteredCount }}</div>
            </div>
            <div class="expense-kpi-trigger" tabindex="0" role="button"
                 data-expense-evolution="average"
                 data-evolution-year="{{ $evoYear }}"
                 data-evolution-type="{{ $type }}"
                 data-category-id="{{ $selectedCategoryKey }}"
                 data-evolution-title="Média ({{ $evoYear }})"
                 style="cursor:pointer;background: #ecfeff; border-radius: 10px; padding: 12px; border: 1px solid #a5f3fc;">
                <div style="font-size: 10px; color: #0e7490; font-weight: 600; text-transform: uppercase;">Média</div>
                <div style="font-size: 14px; font-weight: 700; color: #155e75;">R$ {{ number_format($filteredAverage, 2, ',', '.') }}</div>
            </div>
            <div class="expense-kpi-trigger" tabindex="0" role="button"
                 data-expense-evolution="{{ $hasCategoryFilter && $typeTotal > 0 ? 'share' : 'type_total' }}"
                 data-evolution-year="{{ $evoYear }}"
                 data-evolution-type="{{ $type }}"
                 data-category-id="{{ $selectedCategoryKey }}"
                 data-evolution-title="{{ $hasCategoryFilter && $typeTotal > 0 ? 'Participação % ('.$evoYear.')' : 'Total do tipo ('.$evoYear.')' }}"
                 style="background: #fff7ed; border-radius: 10px; padding: 12px; border: 1px solid #fed7aa;">
                <div style="font-size: 10px; color: #c2410c; font-weight: 600; text-transform: uppercase;">
                    {{ $hasCategoryFilter && $typeTotal > 0 ? '% no tipo' : 'Grupos' }}</div>
                <div style="font-size: 15px; font-weight: 700; color: #9a3412;">
                    @if($hasCategoryFilter && $typeTotal > 0)
                        {{ number_format(($filteredTotal / $typeTotal) * 100, 1, ',', '.') }}%
                    @else
                        {{ $categoryBreakdown->count() }}
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($categoryBreakdown->isNotEmpty())
    <div class="mobile-card" style="margin-bottom: 12px; padding: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase;">Por categoria</span>
            <span style="font-size: 11px; color: #94a3b8;">{{ $type === 'fixed' ? 'Fixas' : 'Variáveis' }}</span>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
            @foreach($categoryBreakdown as $row)
            <div class="expense-kpi-trigger" tabindex="0" role="button"
                 data-expense-evolution="category"
                 data-evolution-year="{{ $evoYear }}"
                 data-evolution-type="{{ $type }}"
                 data-category-id="{{ $row['id'] === null ? 'none' : $row['id'] }}"
                 data-evolution-title="Evolução: {{ $row['name'] }} ({{ $evoYear }})"
                 style="cursor:pointer;border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; background: #fafbfc;">
                <span class="badge text-truncate d-inline-block mb-1" style="max-width: 100%; font-size: 10px; background-color: {{ $row['color'] }};" title="{{ $row['name'] }}">{{ $row['name'] }}</span>
                <div style="font-weight: 700; font-size: 13px; color: #1a202c;">R$ {{ number_format($row['total'], 2, ',', '.') }}</div>
                <div style="font-size: 10px; color: #64748b;">{{ $row['count'] }} lanç.</div>
                <a href="{{ route('company.expenses.index', ['type' => $type, 'month' => $monthInput, 'category_id' => $row['id'] === null ? 'none' : $row['id']]) }}" style="font-size: 11px;" onclick="event.stopPropagation()">Filtrar</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Lista de Despesas -->
    <div class="mobile-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">Lista de Despesas {{ $type === 'fixed' ? 'Fixas' : 'Variáveis' }}</h5>
            <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 12px; font-size: 12px;">{{ $expenses->total() }} registro(s)</span>
        </div>

        @forelse($expenses as $expense)
        <div class="mobile-card-item" style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; overflow: hidden;">
            <div class="mobile-card-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; gap: 8px;">
                <div class="mobile-card-item-title" style="font-weight: 600; font-size: 15px; color: #1a202c; flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word;">
                    #{{ $expense->id }} - {{ $expense->description }}
                </div>
                <span class="badge bg-{{ $expense->is_active ? 'success' : 'secondary' }}" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                    {{ $expense->is_active ? 'Ativa' : 'Inativa' }}
                </span>
            </div>

            <div class="mobile-card-item-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Valor</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500; font-weight: 600;">R$ {{ number_format($expense->value, 2, ',', '.') }}</div>
                </div>

                @if($expense->category)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Categoria</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        <span class="badge" style="background-color: {{ $expense->category->color ?? '#5e72e4' }}; padding: 4px 8px; border-radius: 6px; font-size: 11px;">{{ $expense->category->name }}</span>
                    </div>
                </div>
                @endif

                @if($expense->supplier)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Fornecedor</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">{{ $expense->supplier->name }}</div>
                </div>
                @endif

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">
                        {{ $type === 'fixed' ? 'Dia do Vencimento' : 'Data de Vencimento' }}
                    </div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        @if($type === 'fixed')
                            <span class="badge bg-primary" style="padding: 4px 8px; border-radius: 6px; font-size: 11px;">Dia {{ $expense->due_date_day }}</span>
                        @else
                            {{ $expense->due_date ? \Carbon\Carbon::parse($expense->due_date)->format('d/m/Y') : '-' }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="mobile-card-item-actions" style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('company.expenses.edit', $expense) }}" 
                   class="btn btn-warning btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form action="{{ route('company.expenses.destroy', $expense) }}" 
                      method="POST" 
                      class="d-inline delete-form" 
                      data-message="Tem certeza que deseja remover esta despesa?"
                      style="flex: 1; margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="btn btn-danger btn-sm" 
                            style="width: 100%; padding: 8px 12px; border-radius: 8px; font-size: 12px;">
                        <i class="fas fa-trash"></i> Remover
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fas fa-receipt" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i>
            <p style="margin: 0;">Nenhuma despesa cadastrada</p>
            <a href="{{ route('company.expenses.create') }}" class="btn btn-primary" style="margin-top: 16px; padding: 10px 20px; border-radius: 8px;">
                Cadastrar Primeira Despesa
            </a>
        </div>
        @endforelse

        @if($expenses->hasPages())
        <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px; padding: 16px;">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>
</div>

@include('company.expenses.partials.evolution-modal')
@endsection
