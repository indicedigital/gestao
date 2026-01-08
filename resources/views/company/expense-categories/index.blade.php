@extends('layouts.app')

@section('title', 'Categorias de Despesas')

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
    
    .color-preview {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: inline-block;
        border: 2px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Categorias de Despesas</h1>
                <p>Gerencie as categorias para organizar suas despesas</p>
            </div>
            <a href="{{ route('company.expense-categories.create') }}" class="btn btn-primary btn-modern">
                <i class="fas fa-plus me-2"></i>Nova Categoria
            </a>
        </div>
    </div>

    <div class="card-modern">
        <div class="card-body">
            @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Cor</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>
                                <span class="color-preview" style="background-color: {{ $category->color }};"></span>
                            </td>
                            <td><strong>{{ $category->name }}</strong></td>
                            <td>{{ $category->description ?? '-' }}</td>
                            <td>
                                @if($category->is_active)
                                <span class="badge bg-success">Ativa</span>
                                @else
                                <span class="badge bg-secondary">Inativa</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('company.expense-categories.edit', $category) }}" class="btn btn-sm btn-warning btn-table" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('company.expense-categories.destroy', $category) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover esta categoria?">
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
                {{ $categories->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <p class="text-muted">Nenhuma categoria cadastrada</p>
                <a href="{{ route('company.expense-categories.create') }}" class="btn btn-primary">Cadastrar Primeira Categoria</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
