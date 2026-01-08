@extends('layouts.app')

@section('title', 'Editar Contrato')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Editar Contrato</h1>
            <p class="page-subtitle">Atualize os dados do contrato</p>
        </div>
        <a href="{{ route('company.contracts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.contracts.update', $contract) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Selecione</option>
                            <option value="client_recurring" {{ old('type', $contract->type) === 'client_recurring' ? 'selected' : '' }}>Cliente - Recorrente</option>
                            <option value="client_fixed" {{ old('type', $contract->type) === 'client_fixed' ? 'selected' : '' }}>Cliente - Fechado</option>
                            <option value="employee_clt" {{ old('type', $contract->type) === 'employee_clt' ? 'selected' : '' }}>Funcionário - CLT</option>
                            <option value="employee_pj" {{ old('type', $contract->type) === 'employee_pj' ? 'selected' : '' }}>Funcionário - PJ</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nome do Contrato <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $contract->name) }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">Valor <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" id="value" name="value" value="{{ old('value', $contract->value) }}" required>
                        @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="billing_period" class="form-label">Período de Cobrança</label>
                        <select class="form-select @error('billing_period') is-invalid @enderror" id="billing_period" name="billing_period">
                            <option value="">Selecione</option>
                            <option value="monthly" {{ old('billing_period', $contract->billing_period) === 'monthly' ? 'selected' : '' }}>Mensal</option>
                            <option value="yearly" {{ old('billing_period', $contract->billing_period) === 'yearly' ? 'selected' : '' }}>Anual</option>
                        </select>
                        @error('billing_period')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Configuração de Data de Vencimento para Contratos Recorrentes -->
                @if($contract->type === 'client_recurring' && $contract->billing_period === 'monthly')
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="recurring_due_date_type" class="form-label">Tipo de Data de Vencimento</label>
                        <select class="form-select @error('recurring_due_date_type') is-invalid @enderror" id="recurring_due_date_type" name="recurring_due_date_type">
                            <option value="fixed_day" {{ old('recurring_due_date_type', $contract->recurring_due_date_type ?? 'fixed_day') === 'fixed_day' ? 'selected' : '' }}>Dia Fixo do Mês</option>
                            <option value="first_business_day" {{ old('recurring_due_date_type', $contract->recurring_due_date_type) === 'first_business_day' ? 'selected' : '' }}>Primeiro Dia Útil</option>
                            <option value="fifth_business_day" {{ old('recurring_due_date_type', $contract->recurring_due_date_type) === 'fifth_business_day' ? 'selected' : '' }}>Quinto Dia Útil</option>
                            <option value="last_business_day" {{ old('recurring_due_date_type', $contract->recurring_due_date_type) === 'last_business_day' ? 'selected' : '' }}>Último Dia Útil</option>
                        </select>
                        @error('recurring_due_date_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3" id="recurring-due-date-day-field" style="display: {{ (old('recurring_due_date_type', $contract->recurring_due_date_type ?? 'fixed_day') === 'fixed_day') ? 'block' : 'none' }};">
                        <label for="recurring_due_date_day" class="form-label">Dia do Mês <span class="text-danger">*</span></label>
                        <input type="number" min="1" max="31" class="form-control @error('recurring_due_date_day') is-invalid @enderror" 
                               id="recurring_due_date_day" name="recurring_due_date_day" 
                               value="{{ old('recurring_due_date_day', $contract->recurring_due_date_day ?? 5) }}" 
                               placeholder="Ex: 5"
                               {{ (old('recurring_due_date_type', $contract->recurring_due_date_type ?? 'fixed_day') === 'fixed_day') ? 'required' : '' }}>
                        @error('recurring_due_date_day')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Dia fixo do mês para vencimento (1-31)</small>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Data de Início <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required>
                        @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">Data de Término</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', $contract->end_date ? $contract->end_date->format('Y-m-d') : '') }}">
                        @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Descrição</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $contract->description) }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('company.contracts.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar Contrato</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($contract->type === 'client_recurring' && $contract->billing_period === 'monthly')
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const recurringDueDateType = document.getElementById('recurring_due_date_type');
        const recurringDueDateDayField = document.getElementById('recurring-due-date-day-field');
        const recurringDueDateDayInput = document.getElementById('recurring_due_date_day');
        
        function toggleRecurringDueDateDay() {
            if (recurringDueDateType && recurringDueDateDayField) {
                if (recurringDueDateType.value === 'fixed_day') {
                    recurringDueDateDayField.style.display = 'block';
                    if (recurringDueDateDayInput) recurringDueDateDayInput.required = true;
                } else {
                    recurringDueDateDayField.style.display = 'none';
                    if (recurringDueDateDayInput) {
                        recurringDueDateDayInput.required = false;
                    }
                }
            }
        }
        
        if (recurringDueDateType) {
            recurringDueDateType.addEventListener('change', toggleRecurringDueDateDay);
        }
    });
</script>
@endpush
@endif
@endsection
