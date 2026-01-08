@extends('layouts.app')

@section('title', 'Editar Conta a Receber')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Editar Conta a Receber</h1>
            <p class="page-subtitle">Atualize os dados da conta</p>
        </div>
        <a href="{{ route('company.receivables.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.receivables.update', $receivable) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="client_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                            <option value="">Selecione um cliente</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id', $receivable->client_id) == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Selecione</option>
                            <option value="project" {{ old('type', $receivable->type) === 'project' ? 'selected' : '' }}>Projeto</option>
                            <option value="recurring" {{ old('type', $receivable->type) === 'recurring' ? 'selected' : '' }}>Recorrente</option>
                            <option value="other" {{ old('type', $receivable->type) === 'other' ? 'selected' : '' }}>Outro</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_id" class="form-label">Projeto</label>
                        <select class="form-select @error('project_id') is-invalid @enderror" id="project_id" name="project_id">
                            <option value="">Selecione um projeto (opcional)</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $receivable->project_id) == $project->id ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('project_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="contract_id" class="form-label">Contrato</label>
                        <select class="form-select @error('contract_id') is-invalid @enderror" id="contract_id" name="contract_id">
                            <option value="">Selecione um contrato (opcional)</option>
                            @foreach($contracts as $contract)
                                <option value="{{ $contract->id }}" {{ old('contract_id', $receivable->contract_id) == $contract->id ? 'selected' : '' }}>
                                    {{ $contract->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('contract_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Descrição <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $receivable->description) }}" required>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">Valor <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('value') is-invalid @enderror" id="value" name="value" value="{{ old('value', $receivable->value) }}" required>
                        @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="due_date" class="form-label">Data de Vencimento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $receivable->due_date->format('Y-m-d')) }}" required>
                        @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" onchange="togglePaidFields()">
                            <option value="pending" {{ old('status', $receivable->status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="paid" {{ old('status', $receivable->status) === 'paid' ? 'selected' : '' }}>Paga</option>
                            <option value="partial" {{ old('status', $receivable->status) === 'partial' ? 'selected' : '' }}>Parcial</option>
                            <option value="overdue" {{ old('status', $receivable->status) === 'overdue' ? 'selected' : '' }}>Vencida</option>
                            <option value="cancelled" {{ old('status', $receivable->status) === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row" id="paid-date-row" style="display: {{ in_array(old('status', $receivable->status), ['paid', 'partial']) ? 'flex' : 'none' }};">
                    <div class="col-md-4 mb-3">
                        <label for="paid_date" class="form-label">Data de Pagamento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('paid_date') is-invalid @enderror" id="paid_date" name="paid_date" value="{{ old('paid_date', $receivable->paid_date ? $receivable->paid_date->format('Y-m-d') : '') }}">
                        @error('paid_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3" id="paid-value-row" style="display: {{ (old('status', $receivable->status) === 'partial') ? 'block' : 'none' }};">
                        <label for="paid_value" class="form-label">Valor Pago <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control money-mask @error('paid_value') is-invalid @enderror" id="paid_value" name="paid_value" value="{{ old('paid_value', $receivable->paid_value ? number_format($receivable->paid_value, 2, ',', '.') : '') }}">
                        </div>
                        <small class="text-muted">Valor total: R$ {{ number_format($receivable->value, 2, ',', '.') }}</small>
                        @error('paid_value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="payment_method" class="form-label">Forma de Pagamento</label>
                        <input type="text" class="form-control @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" value="{{ old('payment_method', $receivable->payment_method) }}">
                        @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Observações</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $receivable->notes) }}</textarea>
                    @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('company.receivables.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar Conta a Receber</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
<script>
function togglePaidFields() {
    const status = document.getElementById('status').value;
    const paidDateRow = document.getElementById('paid-date-row');
    const paidValueRow = document.getElementById('paid-value-row');
    const paidValueInput = document.getElementById('paid_value');
    
    if (status === 'paid' || status === 'partial') {
        paidDateRow.style.display = 'flex';
        if (status === 'partial') {
            paidValueRow.style.display = 'block';
            paidValueInput.required = true;
        } else {
            paidValueRow.style.display = 'none';
            paidValueInput.required = false;
        }
    } else {
        paidDateRow.style.display = 'none';
        paidValueRow.style.display = 'none';
        paidValueInput.required = false;
    }
}

// Aplica máscara de dinheiro
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.money-mask').forEach(function(input) {
        new Cleave(input, {
            numeral: true,
            numeralDecimalMark: ',',
            delimiter: '.',
            numeralDecimalScale: 2
        });
    });
    
    togglePaidFields();
});
</script>
@endsection
