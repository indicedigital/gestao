@extends('layouts.app')

@section('title', 'Fornecedores')

@push('styles')
<style>
    body {
        background: #f7fafc;
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
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    
    .table-modern tbody tr:hover {
        background: #f8fafc;
    }
    
    .btn-modern {
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .btn-table {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Fornecedores</h1>
                <p>Gerencie seus fornecedores</p>
            </div>
            <a href="{{ route('company.suppliers.create') }}" class="btn btn-primary btn-modern">
                <i class="fas fa-plus me-2"></i>Novo Fornecedor
            </a>
        </div>
    </div>

    <div class="card-modern">
        <div class="card-body">
            @if($suppliers->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Contato</th>
                            <th>Documento</th>
                            <th>Cidade/Estado</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                        <tr>
                            <td><strong>{{ $supplier->name }}</strong></td>
                            <td>
                                @if($supplier->email)
                                <div><i class="fas fa-envelope me-2 text-muted"></i>{{ $supplier->email }}</div>
                                @endif
                                @if($supplier->phone)
                                <div><i class="fas fa-phone me-2 text-muted"></i>{{ $supplier->phone }}</div>
                                @endif
                                @if(!$supplier->email && !$supplier->phone)
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($supplier->document)
                                <span>{{ $supplier->document_type === 'cnpj' ? 'CNPJ' : 'CPF' }}: {{ $supplier->document }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($supplier->city)
                                {{ $supplier->city }}{{ $supplier->state ? '/' . $supplier->state : '' }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($supplier->is_active)
                                <span class="badge bg-success">Ativo</span>
                                @else
                                <span class="badge bg-secondary">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('company.suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning btn-table" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('company.suppliers.destroy', $supplier) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este fornecedor?">
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
                {{ $suppliers->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                <p class="text-muted">Nenhum fornecedor cadastrado</p>
                <a href="{{ route('company.suppliers.create') }}" class="btn btn-primary">Cadastrar Primeiro Fornecedor</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
