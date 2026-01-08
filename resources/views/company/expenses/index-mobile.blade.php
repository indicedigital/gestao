@extends('layouts.mobile')

@section('title', 'Despesas')

@section('content')
<div class="mobile-content">
    <!-- Tabs -->
    <div class="mobile-card" style="margin-bottom: 16px;">
        <div style="display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0;">
            <a href="{{ route('company.expenses.index', ['type' => 'fixed']) }}" 
               class="btn {{ $type === 'fixed' ? 'btn-primary' : 'btn-outline-primary' }}" 
               style="flex: 1; padding: 10px; border-radius: 8px 8px 0 0; font-size: 13px; text-align: center; text-decoration: none; border: none; border-bottom: 3px solid {{ $type === 'fixed' ? '#5e72e4' : 'transparent' }};">
                Fixas ({{ $fixedCount }})
            </a>
            <a href="{{ route('company.expenses.index', ['type' => 'variable']) }}" 
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
@endsection
