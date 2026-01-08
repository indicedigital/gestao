@extends('layouts.mobile')

@section('title', 'Fornecedores')

@section('content')
<div class="mobile-content">
    <!-- Botão Novo Fornecedor -->
    <div style="margin-bottom: 16px;">
        <a href="{{ route('company.suppliers.create') }}" 
           class="btn btn-primary" 
           style="width: 100%; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-plus"></i> Novo Fornecedor
        </a>
    </div>

    <!-- Lista de Fornecedores -->
    <div class="mobile-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">Lista de Fornecedores</h5>
            <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 12px; font-size: 12px;">{{ $suppliers->total() }} registro(s)</span>
        </div>

        @forelse($suppliers as $supplier)
        <div class="mobile-card-item" style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; overflow: hidden;">
            <div class="mobile-card-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; gap: 8px;">
                <div class="mobile-card-item-title" style="font-weight: 600; font-size: 15px; color: #1a202c; flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word;">
                    {{ $supplier->name }}
                </div>
                <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                    {{ $supplier->is_active ? 'Ativo' : 'Inativo' }}
                </span>
            </div>

            <div class="mobile-card-item-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                @if($supplier->email)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">E-mail</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        <a href="mailto:{{ $supplier->email }}" style="color: #5e72e4; text-decoration: none;">{{ $supplier->email }}</a>
                    </div>
                </div>
                @endif

                @if($supplier->phone)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Telefone</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        <a href="tel:{{ $supplier->phone }}" style="color: #5e72e4; text-decoration: none;">{{ $supplier->phone }}</a>
                    </div>
                </div>
                @endif

                @if($supplier->document)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Documento</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        {{ $supplier->document_type === 'cnpj' ? 'CNPJ' : 'CPF' }}: {{ $supplier->document }}
                    </div>
                </div>
                @endif

                @if($supplier->city)
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Cidade/Estado</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        {{ $supplier->city }}{{ $supplier->state ? '/' . $supplier->state : '' }}
                    </div>
                </div>
                @endif
            </div>

            <div class="mobile-card-item-actions" style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('company.suppliers.show', $supplier) }}" 
                   class="btn btn-info btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('company.suppliers.edit', $supplier) }}" 
                   class="btn btn-warning btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form action="{{ route('company.suppliers.destroy', $supplier) }}" 
                      method="POST" 
                      class="d-inline delete-form" 
                      data-message="Tem certeza que deseja remover este fornecedor?"
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
            <i class="fas fa-truck" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i>
            <p style="margin: 0;">Nenhum fornecedor cadastrado</p>
            <a href="{{ route('company.suppliers.create') }}" class="btn btn-primary" style="margin-top: 16px; padding: 10px 20px; border-radius: 8px;">
                Cadastrar Primeiro Fornecedor
            </a>
        </div>
        @endforelse

        @if($suppliers->hasPages())
        <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px; padding: 16px;">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
