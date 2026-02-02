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

    {{-- Parcelas do contrato - detalhado (pagas e não pagas) --}}
    <div class="card shadow mt-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 font-weight-bold"><i class="fas fa-receipt me-2 text-primary"></i>Parcelas do contrato – detalhado</h6>
            @php
                $totalParcelas = $contract->installments->count() ?: $contract->receivables->count();
                $pagas = $contract->installments->count()
                    ? $contract->installments->where('status', 'paid')->count()
                    : $contract->receivables->whereIn('status', ['paid'])->count();
                $pendentes = $totalParcelas - $pagas;
            @endphp
            <span class="badge bg-success">{{ $pagas }} paga(s)</span>
            <span class="badge bg-warning">{{ $pendentes }} pendente(s)</span>
        </div>
        <div class="card-body">
            @if($contract->installments->count() > 0)
                {{-- Contrato com parcelas (client_fixed) --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nº Parcela</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Data de vencimento</th>
                                <th>Status</th>
                                <th>Valor pago</th>
                                <th>Data(s) de recebimento</th>
                                <th>Forma de pagamento</th>
                                <th>Observações</th>
                                <th>Conta a receber</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contract->installments as $inst)
                            @php
                                $receivable = $contract->receivables->firstWhere('installment_number', $inst->installment_number);
                                $statusColors = ['pending' => 'warning', 'paid' => 'success', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                                $statusLabels = ['pending' => 'Pendente', 'paid' => 'Paga', 'overdue' => 'Vencida', 'cancelled' => 'Cancelada'];
                                $statusColor = $statusColors[$inst->status] ?? 'secondary';
                                $statusLabel = $statusLabels[$inst->status] ?? ucfirst($inst->status);
                                if ($inst->status === 'pending' && $inst->isOverdue()) {
                                    $statusLabel = 'Vencida';
                                    $statusColor = 'danger';
                                }
                            @endphp
                            <tr class="{{ ($inst->status === 'pending' && $inst->isOverdue()) ? 'table-danger' : '' }}">
                                <td><strong>{{ $inst->installment_number }}/{{ $contract->installments->count() }}</strong></td>
                                <td>{{ $inst->description ?? "Parcela {$inst->installment_number}" }}</td>
                                <td><strong>R$ {{ number_format($inst->value, 2, ',', '.') }}</strong></td>
                                <td>
                                    {{ $inst->due_date->format('d/m/Y') }}
                                    @if($inst->status === 'pending' && $inst->isOverdue())
                                        <br><small class="text-danger">{{ (int) floor(now()->diffInDays($inst->due_date, false)) }} dias atrasado</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span></td>
                                <td>
                                    @if($inst->status === 'paid')
                                        <strong class="text-success">R$ {{ number_format($inst->value, 2, ',', '.') }}</strong>
                                    @elseif($receivable && ($receivable->paid_value ?? 0) > 0)
                                        <strong class="text-info">R$ {{ number_format($receivable->paid_value, 2, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($receivable && $receivable->relationLoaded('payments') && $receivable->payments->count() > 0)
                                        @foreach($receivable->payments as $p)
                                            <div><strong>{{ $p->paid_date->format('d/m/Y') }}</strong> — R$ {{ number_format($p->amount, 2, ',', '.') }}</div>
                                        @endforeach
                                    @elseif($inst->paid_date)
                                        <strong>{{ $inst->paid_date->format('d/m/Y') }}</strong>
                                    @elseif($receivable && $receivable->paid_date)
                                        <strong>{{ $receivable->paid_date->format('d/m/Y') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $inst->payment_method ?? ($receivable?->payment_method ?? '-') }}</td>
                                <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($inst->notes ?? '-', 40) }}</small></td>
                                <td>
                                    @if($receivable)
                                        <a href="{{ route('company.receivables.show', $receivable) }}" class="btn btn-sm btn-outline-primary" title="Ver conta a receber"><i class="fas fa-external-link-alt"></i></a>
                                        @if($receivable->status === 'pending' || $receivable->status === 'partial')
                                            <a href="{{ route('company.receivables.edit', $receivable) }}" class="btn btn-sm btn-outline-success" title="Registrar pagamento"><i class="fas fa-check"></i></a>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 pt-3 border-top">
                    <p class="mb-0 small text-muted">
                        <strong>Resumo:</strong>
                        Total do contrato: <strong>R$ {{ number_format($contract->value, 2, ',', '.') }}</strong>
                        • Pago (parcelas): <strong class="text-success">R$ {{ number_format($contract->installments->where('status', 'paid')->sum('value'), 2, ',', '.') }}</strong>
                        • Pendente: <strong class="text-warning">R$ {{ number_format($contract->installments->where('status', '!=', 'paid')->sum('value'), 2, ',', '.') }}</strong>
                    </p>
                </div>
            @elseif($contract->receivables->count() > 0)
                {{-- Contrato sem parcelas (ex.: recorrente) – lista contas a receber --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Descrição</th>
                                <th>Valor</th>
                                <th>Valor pago</th>
                                <th>Data de vencimento</th>
                                <th>Status</th>
                                <th>Data(s) de recebimento</th>
                                <th>Forma de pagamento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contract->receivables->sortBy('due_date') as $rec)
                            @php
                                $statusColors = ['pending' => 'warning', 'paid' => 'success', 'partial' => 'info', 'overdue' => 'danger', 'cancelled' => 'secondary'];
                                $statusLabels = ['pending' => 'Pendente', 'paid' => 'Paga', 'partial' => 'Parcial', 'overdue' => 'Vencida', 'cancelled' => 'Cancelada'];
                                $statusColor = $statusColors[$rec->status] ?? 'secondary';
                                $statusLabel = $statusLabels[$rec->status] ?? ucfirst($rec->status);
                                if ($rec->status === 'partial' && $rec->paid_value) {
                                    $statusLabel .= ' (' . number_format(($rec->paid_value / $rec->value) * 100, 0) . '%)';
                                }
                            @endphp
                            <tr class="{{ $rec->isOverdue() ? 'table-danger' : '' }}">
                                <td>{{ $rec->installment_number ? $rec->installment_number . '/' . ($rec->total_installments ?? '-') : $rec->id }}</td>
                                <td><strong>{{ $rec->description }}</strong></td>
                                <td><strong>R$ {{ number_format($rec->value, 2, ',', '.') }}</strong></td>
                                <td>
                                    @if(($rec->paid_value ?? 0) > 0)
                                        <strong class="text-success">R$ {{ number_format($rec->paid_value, 2, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $rec->due_date->format('d/m/Y') }}
                                    @if($rec->isOverdue())
                                        <br><small class="text-danger">{{ (int) floor(now()->diffInDays($rec->due_date, false)) }} dias atrasado</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span></td>
                                <td>
                                    @if($rec->relationLoaded('payments') && $rec->payments->count() > 0)
                                        @foreach($rec->payments as $p)
                                            <div><strong>{{ $p->paid_date->format('d/m/Y') }}</strong> — R$ {{ number_format($p->amount, 2, ',', '.') }}</div>
                                        @endforeach
                                    @elseif($rec->paid_date)
                                        <strong>{{ $rec->paid_date->format('d/m/Y') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $rec->payment_method ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('company.receivables.show', $rec) }}" class="btn btn-sm btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                                    @if($rec->status === 'pending' || $rec->status === 'partial')
                                        <a href="{{ route('company.receivables.edit', $rec) }}" class="btn btn-sm btn-outline-success" title="Registrar pagamento"><i class="fas fa-check"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 pt-3 border-top">
                    <p class="mb-0 small text-muted">
                        <strong>Resumo:</strong>
                        Total: <strong>R$ {{ number_format($contract->receivables->sum('value'), 2, ',', '.') }}</strong>
                        • Pago: <strong class="text-success">R$ {{ number_format($contract->receivables->where('status', 'paid')->sum('value') + $contract->receivables->where('status', 'partial')->sum('paid_value'), 2, ',', '.') }}</strong>
                        • Pendente: <strong class="text-warning">R$ {{ number_format($contract->receivables->whereIn('status', ['pending', 'partial'])->sum(fn($r) => $r->value - ($r->paid_value ?? 0)), 2, ',', '.') }}</strong>
                    </p>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">Nenhuma parcela ou conta a receber registrada para este contrato.</p>
                </div>
            @endif
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
