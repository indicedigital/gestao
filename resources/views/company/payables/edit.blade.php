@extends('layouts.app')

@section('title', 'Editar Conta a Pagar')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Editar Conta a Pagar</h1>
            <p class="page-subtitle">Atualize os dados da conta</p>
        </div>
        <a href="{{ route('company.payables.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>Corrija os erros abaixo:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif
            <form action="{{ route('company.payables.update', $payable) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Selecione</option>
                            <option value="salary" {{ old('type', $payable->type) === 'salary' ? 'selected' : '' }}>Salário</option>
                            <option value="service" {{ old('type', $payable->type) === 'service' ? 'selected' : '' }}>Serviço</option>
                            <option value="supplier" {{ old('type', $payable->type) === 'supplier' ? 'selected' : '' }}>Fornecedor</option>
                            <option value="other" {{ old('type', $payable->type) === 'other' ? 'selected' : '' }}>Outro</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Descrição <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $payable->description) }}" required>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">Valor <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" id="value" name="value" value="{{ old('value', $payable->value) }}" required>
                        @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Data de Vencimento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $payable->due_date->format('Y-m-d')) }}" required>
                        @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="supplier_name" class="form-label">Nome do Fornecedor</label>
                        <input type="text" class="form-control @error('supplier_name') is-invalid @enderror" id="supplier_name" name="supplier_name" value="{{ old('supplier_name', $payable->supplier_name) }}">
                        @error('supplier_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="pending" {{ old('status', $payable->status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ old('status', $payable->status) === 'paid' ? 'selected' : '' }}>Paga</option>
                            <option value="overdue" {{ old('status', $payable->status) === 'overdue' ? 'selected' : '' }}>Vencida</option>
                            <option value="cancelled" {{ old('status', $payable->status) === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row" id="paid-date-row" style="display: {{ old('status', $payable->status) === 'paid' ? 'block' : 'none' }};">
                    <div class="col-md-6 mb-3">
                        <label for="paid_date" class="form-label">Data de Pagamento <span class="text-danger" id="paid-date-required" style="display: {{ old('status', $payable->status) === 'paid' ? 'inline' : 'none' }};">*</span></label>
                        <input type="date" class="form-control @error('paid_date') is-invalid @enderror" id="paid_date" name="paid_date" value="{{ old('paid_date', $payable->paid_date ? $payable->paid_date->format('Y-m-d') : '') }}">
                        @error('paid_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="payment_method" class="form-label">Forma de Pagamento</label>
                        <input type="text" class="form-control @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" value="{{ old('payment_method', $payable->payment_method) }}">
                        @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Observações</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $payable->notes) }}</textarea>
                    @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('company.payables.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar Conta a Pagar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const paidDateRow = document.getElementById('paid-date-row');
    const paidDateInput = document.getElementById('paid_date');
    const paidDateRequired = document.getElementById('paid-date-required');
    
    if (statusSelect && paidDateRow) {
        statusSelect.addEventListener('change', function() {
            if (this.value === 'paid') {
                paidDateRow.style.display = 'block';
                if (paidDateInput) {
                    paidDateInput.required = true;
                    if (!paidDateInput.value) {
                        paidDateInput.value = new Date().toISOString().split('T')[0];
                    }
                }
                if (paidDateRequired) {
                    paidDateRequired.style.display = 'inline';
                }
            } else {
                paidDateRow.style.display = 'none';
                if (paidDateInput) {
                    paidDateInput.required = false;
                }
                if (paidDateRequired) {
                    paidDateRequired.style.display = 'none';
                }
            }
        });
    }
});
</script>
@endsection
