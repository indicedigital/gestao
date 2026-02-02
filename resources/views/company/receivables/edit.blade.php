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

    {{-- Pagamentos registrados (múltiplas datas) --}}
    <div class="card shadow mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i>Pagamentos registrados</h6>
        </div>
        <div class="card-body">
            @if($receivable->payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Data do recebimento</th>
                                <th>Valor</th>
                                <th>Forma de pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receivable->payments as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $p->paid_date->format('d/m/Y') }}</td>
                                <td><strong>R$ {{ number_format($p->amount, 2, ',', '.') }}</strong></td>
                                <td>{{ $p->payment_method ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mb-0 text-muted small">
                    Total pago: <strong>R$ {{ number_format($receivable->paid_value ?? 0, 2, ',', '.') }}</strong>
                    @if((float)($receivable->value - ($receivable->paid_value ?? 0)) > 0)
                        • Restante: <strong class="text-warning">R$ {{ number_format($receivable->value - ($receivable->paid_value ?? 0), 2, ',', '.') }}</strong>
                    @endif
                </p>
            @else
                <p class="text-muted mb-0">Nenhum pagamento registrado nesta conta.</p>
            @endif
        </div>
    </div>

    {{-- Adicionar novo pagamento (parcial ou total) --}}
    @if($receivable->status !== 'paid' && (float)($receivable->value - ($receivable->paid_value ?? 0)) > 0)
    <div class="card shadow mt-4">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="fas fa-plus me-2"></i>Registrar novo pagamento (parcial ou total)</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('company.receivables.mark-as-paid', $receivable) }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label for="add_paid_date" class="form-label">Data do recebimento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="add_paid_date" name="paid_date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label for="add_paid_value" class="form-label">Valor <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control add-payment-mask" id="add_paid_value" name="paid_value" placeholder="0,00" required>
                    </div>
                    <small class="text-muted">Máx. R$ {{ number_format($receivable->value - ($receivable->paid_value ?? 0), 2, ',', '.') }}</small>
                </div>
                <div class="col-md-3">
                    <label for="add_payment_method" class="form-label">Forma de pagamento</label>
                    <input type="text" class="form-control" id="add_payment_method" name="payment_method" placeholder="PIX, Boleto...">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="partial_payment" value="1" id="add_partial" checked>
                        <label class="form-check-label" for="add_partial">Pagamento parcial</label>
                    </div>
                    <button type="submit" class="btn btn-success ms-2"><i class="fas fa-check me-1"></i>Registrar</button>
                </div>
            </form>
            <p class="text-muted small mt-2 mb-0">
                Se o valor for menor que o restante, será criada automaticamente uma <strong>duplicata pendente</strong> para o valor restante, com outra data de vencimento.
            </p>
        </div>
    </div>
    @endif

    @if($receivable->remainderReceivable)
    <div class="card shadow mt-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Duplicata do restante</h6>
        </div>
        <div class="card-body">
            <p class="mb-2">Foi criada uma conta a receber para o valor restante:</p>
            <p class="mb-0">
                <a href="{{ route('company.receivables.show', $receivable->remainderReceivable) }}" class="btn btn-outline-primary btn-sm">
                    Ver duplicata #{{ $receivable->remainderReceivable->id }} — R$ {{ number_format($receivable->remainderReceivable->value, 2, ',', '.') }} (venc. {{ $receivable->remainderReceivable->due_date->format('d/m/Y') }})
                </a>
            </p>
        </div>
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.money-mask, .add-payment-mask').forEach(function(input) {
        new Cleave(input, {
            numeral: true,
            numeralDecimalMark: ',',
            delimiter: '.',
            numeralDecimalScale: 2
        });
    });
});
</script>
@endsection
