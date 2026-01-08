@extends('layouts.mobile')

@section('title', 'Categorias de Despesas')

@section('content')
<div class="mobile-content">
    <!-- Botão Nova Categoria -->
    <div style="margin-bottom: 16px;">
        <a href="{{ route('company.expense-categories.create') }}" 
           class="btn btn-primary" 
           style="width: 100%; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-plus"></i> Nova Categoria
        </a>
    </div>

    <!-- Lista de Categorias -->
    <div class="mobile-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">Lista de Categorias</h5>
            <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 12px; font-size: 12px;">{{ $categories->total() }} registro(s)</span>
        </div>

        @forelse($categories as $category)
        <div class="mobile-card-item" style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; overflow: hidden;">
            <div class="mobile-card-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                    <span class="color-preview" style="width: 32px; height: 32px; border-radius: 8px; display: inline-block; border: 2px solid #e2e8f0; background-color: {{ $category->color }}; flex-shrink: 0;"></span>
                    <div class="mobile-card-item-title" style="font-weight: 600; font-size: 15px; color: #1a202c; flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word;">
                        {{ $category->name }}
                    </div>
                </div>
                <span class="badge bg-{{ $category->is_active ? 'success' : 'secondary' }}" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                    {{ $category->is_active ? 'Ativa' : 'Inativa' }}
                </span>
            </div>

            @if($category->description)
            <div class="mobile-card-item-body" style="margin-bottom: 12px;">
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Descrição</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">{{ $category->description }}</div>
                </div>
            </div>
            @endif

            <div class="mobile-card-item-actions" style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('company.expense-categories.edit', $category) }}" 
                   class="btn btn-warning btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form action="{{ route('company.expense-categories.destroy', $category) }}" 
                      method="POST" 
                      class="d-inline delete-form" 
                      data-message="Tem certeza que deseja remover esta categoria?"
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
            <i class="fas fa-tags" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i>
            <p style="margin: 0;">Nenhuma categoria cadastrada</p>
            <a href="{{ route('company.expense-categories.create') }}" class="btn btn-primary" style="margin-top: 16px; padding: 10px 20px; border-radius: 8px;">
                Cadastrar Primeira Categoria
            </a>
        </div>
        @endforelse

        @if($categories->hasPages())
        <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px; padding: 16px;">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
