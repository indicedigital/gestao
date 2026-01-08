@extends('layouts.app')

@section('title', 'Nova Despesa')

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
    
    .form-label {
        font-weight: 500;
        color: #1a202c;
        margin-bottom: 8px;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 10px 16px;
        transition: all 0.2s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #5e72e4;
        box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1>Nova Despesa</h1>
                <p>Cadastre uma nova despesa (fixa ou variável)</p>
            </div>
            <a href="{{ route('company.expenses.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <div class="card-modern">
        <div class="card-body">
            <form action="{{ route('company.expenses.store') }}" method="POST" id="expenseForm">
                @csrf
                
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Tipo de Despesa <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required onchange="toggleExpenseFields()">
                            <option value="">Selecione o tipo</option>
                            <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixa (Recorrente mensal)</option>
                            <option value="variable" {{ old('type') === 'variable' ? 'selected' : '' }}>Variável (Única)</option>
                        </select>
                        <small class="text-muted">Despesas fixas geram duplicatas mensais automaticamente. Despesas variáveis geram uma única duplicata.</small>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="description" class="form-label">Descrição <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}" required placeholder="Ex: Aluguel, Energia Elétrica, Internet...">
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="value" class="form-label">Valor <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" id="value" name="value" value="{{ old('value') }}" required min="0">
                        </div>
                        @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="expense_category_id" class="form-label">Categoria</label>
                        <select class="form-select @error('expense_category_id') is-invalid @enderror" id="expense_category_id" name="expense_category_id">
                            <option value="">Selecione uma categoria</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted"><a href="{{ route('company.expense-categories.create') }}" target="_blank">Cadastrar nova categoria</a></small>
                        @error('expense_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="supplier_id" class="form-label">Fornecedor</label>
                        <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                            <option value="">Selecione um fornecedor</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted"><a href="{{ route('company.suppliers.create') }}" target="_blank">Cadastrar novo fornecedor</a></small>
                        @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Campos para Despesa Fixa -->
                <div class="row" id="fixed-fields" style="display: {{ old('type') === 'fixed' ? 'flex' : 'none' }};">
                    <div class="col-md-6 mb-3">
                        <label for="due_date_day" class="form-label">Dia do Vencimento <span class="text-danger">*</span></label>
                        <select class="form-select @error('due_date_day') is-invalid @enderror" id="due_date_day" name="due_date_day">
                            <option value="">Selecione o dia</option>
                            @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ old('due_date_day') == $i ? 'selected' : '' }}>Dia {{ $i }}</option>
                            @endfor
                        </select>
                        <small class="text-muted">Todo mês, a duplicata será gerada para este dia</small>
                        @error('due_date_day')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Campos para Despesa Variável -->
                <div class="row" id="variable-fields" style="display: {{ old('type') === 'variable' ? 'flex' : 'none' }};">
                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Data de Vencimento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date') }}">
                        @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">Observações</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Informações adicionais sobre a despesa...">{{ old('notes') }}</textarea>
                        @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('company.expenses.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Despesa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleExpenseFields() {
    const type = document.getElementById('type').value;
    const fixedFields = document.getElementById('fixed-fields');
    const variableFields = document.getElementById('variable-fields');
    const dueDateDay = document.getElementById('due_date_day');
    const dueDate = document.getElementById('due_date');
    
    if (type === 'fixed') {
        fixedFields.style.display = 'flex';
        variableFields.style.display = 'none';
        if (dueDateDay) dueDateDay.required = true;
        if (dueDate) dueDate.required = false;
    } else if (type === 'variable') {
        fixedFields.style.display = 'none';
        variableFields.style.display = 'flex';
        if (dueDateDay) dueDateDay.required = false;
        if (dueDate) dueDate.required = true;
    } else {
        fixedFields.style.display = 'none';
        variableFields.style.display = 'none';
        if (dueDateDay) dueDateDay.required = false;
        if (dueDate) dueDate.required = false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    toggleExpenseFields();
});
</script>
@endsection
