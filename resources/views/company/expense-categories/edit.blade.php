@extends('layouts.app')

@section('title', 'Editar Categoria de Despesa')

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
    
    .form-label {
        font-weight: 500;
        color: #1a202c;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 10px 16px;
    }
    
    .color-input-wrapper {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .color-preview {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 2px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Editar Categoria de Despesa</h1>
                <p>Atualize os dados da categoria</p>
            </div>
            <a href="{{ route('company.expense-categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <div class="card-modern">
        <div class="card-body">
            <form action="{{ route('company.expense-categories.update', $expenseCategory) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $expenseCategory->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="color" class="form-label">Cor</label>
                        <div class="color-input-wrapper">
                            <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" id="color" name="color" value="{{ old('color', $expenseCategory->color) }}" style="width: 70px; height: 40px; padding: 2px;">
                            <span class="color-preview" id="colorPreview" style="background-color: {{ old('color', $expenseCategory->color) }};"></span>
                        </div>
                        @error('color')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $expenseCategory->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="is_active" class="form-label">Status</label>
                        <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                            <option value="1" {{ old('is_active', $expenseCategory->is_active) ? 'selected' : '' }}>Ativa</option>
                            <option value="0" {{ !old('is_active', $expenseCategory->is_active) ? 'selected' : '' }}>Inativa</option>
                        </select>
                        @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('company.expense-categories.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar Categoria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('color').addEventListener('input', function(e) {
    document.getElementById('colorPreview').style.backgroundColor = e.target.value;
});
</script>
@endsection
