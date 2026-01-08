@extends('layouts.app')

@section('title', 'Novo Contrato')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Novo Contrato</h1>
            <p class="page-subtitle">Cadastre um novo contrato</p>
        </div>
        <a href="{{ route('company.contracts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.contracts.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Selecione</option>
                            <option value="client_recurring">Cliente - Recorrente</option>
                            <option value="client_fixed">Cliente - Fechado</option>
                            <option value="employee_clt">Funcionário - CLT</option>
                            <option value="employee_pj">Funcionário - PJ</option>
                        </select>
                        @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Nome do Contrato <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row" id="client-field" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label for="client_id" class="form-label">Cliente</label>
                        <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id">
                            <option value="">Selecione um cliente</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row" id="employee-field" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label for="employee_id" class="form-label">Funcionário</label>
                        <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id">
                            <option value="">Selecione um funcionário</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">Valor Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control money-mask @error('value') is-invalid @enderror" id="value" placeholder="0,00" required>
                            <input type="hidden" id="value_raw" name="value" value="{{ old('value', '0') }}">
                        </div>
                        @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="billing-period-field" style="display: none;">
                        <label for="billing_period" class="form-label">Período de Cobrança</label>
                        <select class="form-select @error('billing_period') is-invalid @enderror" id="billing_period" name="billing_period">
                            <option value="">Selecione</option>
                            <option value="monthly" {{ old('billing_period') === 'monthly' ? 'selected' : '' }}>Mensal</option>
                            <option value="yearly" {{ old('billing_period') === 'yearly' ? 'selected' : '' }}>Anual</option>
                        </select>
                        @error('billing_period')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Configuração de Data de Vencimento para Contratos Recorrentes -->
                    <div id="recurring-due-date-config" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="recurring_due_date_type" class="form-label">Tipo de Data de Vencimento</label>
                                <select class="form-select @error('recurring_due_date_type') is-invalid @enderror" id="recurring_due_date_type" name="recurring_due_date_type">
                                    <option value="fixed_day" {{ old('recurring_due_date_type', 'fixed_day') === 'fixed_day' ? 'selected' : '' }}>Dia Fixo do Mês</option>
                                    <option value="first_business_day" {{ old('recurring_due_date_type') === 'first_business_day' ? 'selected' : '' }}>Primeiro Dia Útil</option>
                                    <option value="fifth_business_day" {{ old('recurring_due_date_type') === 'fifth_business_day' ? 'selected' : '' }}>Quinto Dia Útil</option>
                                    <option value="last_business_day" {{ old('recurring_due_date_type') === 'last_business_day' ? 'selected' : '' }}>Último Dia Útil</option>
                                </select>
                                @error('recurring_due_date_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3" id="recurring-due-date-day-field" style="display: none;">
                                <label for="recurring_due_date_day" class="form-label">Dia do Mês <span class="text-danger">*</span></label>
                                <input type="number" min="1" max="31" class="form-control @error('recurring_due_date_day') is-invalid @enderror" 
                                       id="recurring_due_date_day" name="recurring_due_date_day" 
                                       value="{{ old('recurring_due_date_day', 5) }}" 
                                       placeholder="Ex: 5">
                                @error('recurring_due_date_day')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Dia fixo do mês para vencimento (1-31)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuração de Pagamento para Contratos Fixos -->
                <div id="payment-config" style="display: none;">
                    <hr class="my-4">
                    <h5 class="mb-3"><i class="fas fa-credit-card me-2"></i>Configuração de Pagamento</h5>
                    
                    <!-- Entrada -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="has_down_payment" name="has_down_payment" value="1" {{ old('has_down_payment') ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_down_payment">
                                    Contrato possui entrada
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="down-payment-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="down_payment_value" class="form-label">Valor da Entrada</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" class="form-control money-mask @error('down_payment_value') is-invalid @enderror" id="down_payment_value" placeholder="0,00">
                                    <input type="hidden" id="down_payment_value_raw" name="down_payment_value" value="{{ old('down_payment_value', '0') }}">
                                </div>
                                @error('down_payment_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="down_payment_date" class="form-label">Data da Entrada</label>
                                <input type="date" class="form-control @error('down_payment_date') is-invalid @enderror" id="down_payment_date" name="down_payment_date" value="{{ old('down_payment_date') }}">
                                @error('down_payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Parcelas -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="installments_count" class="form-label">Número de Parcelas <span class="text-danger">*</span></label>
                            <input type="number" min="1" class="form-control @error('installments_count') is-invalid @enderror" id="installments_count" name="installments_count" value="{{ old('installments_count', 1) }}" required>
                            @error('installments_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="payment_frequency" class="form-label">Frequência de Pagamento <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_frequency') is-invalid @enderror" id="payment_frequency" name="payment_frequency" required>
                                <option value="">Selecione</option>
                                <option value="daily" {{ old('payment_frequency') === 'daily' ? 'selected' : '' }}>Diária</option>
                                <option value="weekly" {{ old('payment_frequency') === 'weekly' ? 'selected' : '' }}>Semanal</option>
                                <option value="biweekly" {{ old('payment_frequency', 'monthly') === 'biweekly' ? 'selected' : '' }}>Quinzenal</option>
                                <option value="monthly" {{ old('payment_frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Mensal</option>
                                <option value="quarterly" {{ old('payment_frequency') === 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                                <option value="yearly" {{ old('payment_frequency') === 'yearly' ? 'selected' : '' }}>Anual</option>
                            </select>
                            @error('payment_frequency')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="first_installment_date" class="form-label">Data da Primeira Parcela <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('first_installment_date') is-invalid @enderror" id="first_installment_date" name="first_installment_date" value="{{ old('first_installment_date') }}" required>
                            @error('first_installment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="equal_installments" name="equal_installments" value="1" {{ old('equal_installments', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="equal_installments">
                                    Parcelas com valores iguais (desmarque para definir valores diferentes manualmente)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview/Simulação das Parcelas -->
                <div id="installments-preview" style="display: none;">
                    <hr class="my-4">
                    <h5 class="mb-3"><i class="fas fa-list-alt me-2"></i>Preview das Parcelas</h5>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Edite os valores, datas e status das parcelas abaixo. A soma deve ser igual ao valor total do contrato.</strong>
                    </div>
                    <div id="preview-validation-alert" class="alert alert-danger" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção:</strong> A soma das parcelas (<span id="preview-sum">R$ 0,00</span>) não corresponde ao valor total do contrato (<span id="preview-total-value">R$ 0,00</span>). Diferença: <span id="preview-diff">R$ 0,00</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">#</th>
                                    <th>Descrição</th>
                                    <th style="width: 180px;">Valor</th>
                                    <th style="width: 180px;">Vencimento</th>
                                    <th style="width: 150px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="installments-preview-body">
                                <!-- Será preenchido via JavaScript -->
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2" class="text-end">Total Calculado:</th>
                                    <th id="preview-total">R$ 0,00</th>
                                    <th colspan="2"></th>
                                </tr>
                                <tr>
                                    <th colspan="2" class="text-end">Valor do Contrato:</th>
                                    <th id="preview-contract-total">R$ 0,00</th>
                                    <th colspan="2"></th>
                                </tr>
                                <tr id="preview-validation-row" style="display: none;">
                                    <th colspan="2" class="text-end">Diferença:</th>
                                    <th id="preview-difference" class="text-danger">R$ 0,00</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Data de Início <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                        @error('start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">Data de Término</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                        @error('end_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Descrição</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('company.contracts.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Contrato</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
<script>
(function() {
    // Máscara de dinheiro
    function formatMoney(value) {
        if (!value || value === 0 || value === '0') return '0,00';
        // Se já está formatado, retorna
        if (typeof value === 'string' && value.includes(',')) {
            return value;
        }
        // Converte número para formato brasileiro
        value = parseFloat(value) || 0;
        return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function parseMoney(value) {
        if (!value) return 0;
        // Remove formatação e converte para número
        const num = value.toString().replace(/\./g, '').replace(',', '.');
        return parseFloat(num) || 0;
    }

    // Aplica máscara de dinheiro
    function applyMoneyMask(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value) {
                value = (parseInt(value) / 100).toFixed(2);
                value = value.replace('.', ',');
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            } else {
                value = '0,00';
            }
            e.target.value = value;
            
            // Atualiza campo hidden
            const hiddenField = document.getElementById(e.target.id + '_raw');
            if (hiddenField) {
                hiddenField.value = parseMoney(value);
            }
        });

        input.addEventListener('blur', function(e) {
            if (!e.target.value || e.target.value === '0,00') {
                e.target.value = '';
            }
        });
    }

    // Inicializa valores dos campos de dinheiro
    function initMoneyFields() {
        const valueField = document.getElementById('value');
        const valueRaw = document.getElementById('value_raw');
        if (valueField && valueRaw && valueRaw.value) {
            valueField.value = formatMoney(valueRaw.value);
        }
        
        const downPaymentField = document.getElementById('down_payment_value');
        const downPaymentRaw = document.getElementById('down_payment_value_raw');
        if (downPaymentField && downPaymentRaw && downPaymentRaw.value) {
            downPaymentField.value = formatMoney(downPaymentRaw.value);
        }
    }
    
    // Aplica máscaras após inicialização
    function setupMoneyMasks() {
        const moneyInputs = document.querySelectorAll('.money-mask');
        moneyInputs.forEach(function(input) {
            applyMoneyMask(input);
        });
    }

    function toggleFields() {
        const type = document.getElementById('type').value;
        const clientField = document.getElementById('client-field');
        const employeeField = document.getElementById('employee-field');
        const paymentConfig = document.getElementById('payment-config');
        const billingPeriodField = document.getElementById('billing-period-field');
        const installmentsPreview = document.getElementById('installments-preview');
        
        if (type === 'client_recurring' || type === 'client_fixed') {
            clientField.style.display = 'block';
            employeeField.style.display = 'none';
            const clientId = document.getElementById('client_id');
            const employeeId = document.getElementById('employee_id');
            if (clientId) clientId.required = true;
            if (employeeId) employeeId.required = false;
            
            // Mostra configuração de pagamento apenas para contratos fixos
            if (type === 'client_fixed') {
                if (paymentConfig) paymentConfig.style.display = 'block';
                if (billingPeriodField) billingPeriodField.style.display = 'none';
                const recurringConfig = document.getElementById('recurring-due-date-config');
                if (recurringConfig) recurringConfig.style.display = 'none';
            } else if (type === 'client_recurring') {
                if (paymentConfig) paymentConfig.style.display = 'none';
                if (billingPeriodField) billingPeriodField.style.display = 'block';
                if (installmentsPreview) installmentsPreview.style.display = 'none';
                const recurringConfig = document.getElementById('recurring-due-date-config');
                if (recurringConfig) recurringConfig.style.display = 'block';
                toggleRecurringDueDateDay();
            }
        } else if (type === 'employee_clt' || type === 'employee_pj') {
            clientField.style.display = 'none';
            employeeField.style.display = 'block';
            if (paymentConfig) paymentConfig.style.display = 'none';
            if (billingPeriodField) billingPeriodField.style.display = 'none';
            if (installmentsPreview) installmentsPreview.style.display = 'none';
            const clientId = document.getElementById('client_id');
            const employeeId = document.getElementById('employee_id');
            if (clientId) clientId.required = false;
            if (employeeId) employeeId.required = true;
        } else {
            clientField.style.display = 'none';
            employeeField.style.display = 'none';
            if (paymentConfig) paymentConfig.style.display = 'none';
            if (billingPeriodField) billingPeriodField.style.display = 'block';
            if (installmentsPreview) installmentsPreview.style.display = 'none';
            const clientId = document.getElementById('client_id');
            const employeeId = document.getElementById('employee_id');
            if (clientId) clientId.required = false;
            if (employeeId) employeeId.required = false;
        }
        
        if (type === 'client_fixed') {
            updatePreview();
        }
    }

    function toggleDownPayment() {
        const hasDownPayment = document.getElementById('has_down_payment');
        const downPaymentFields = document.getElementById('down-payment-fields');
        if (hasDownPayment && downPaymentFields) {
            if (hasDownPayment.checked) {
                downPaymentFields.style.display = 'block';
            } else {
                downPaymentFields.style.display = 'none';
            }
        }
        updatePreview();
    }

    function syncDates() {
        const startDate = document.getElementById('start_date');
        const firstInstallmentDate = document.getElementById('first_installment_date');
        if (startDate && firstInstallmentDate && !firstInstallmentDate.value) {
            firstInstallmentDate.value = startDate.value;
        }
        updatePreview();
    }
    
    function toggleRecurringDueDateDay() {
        const dueDateType = document.getElementById('recurring_due_date_type');
        const dueDateDayField = document.getElementById('recurring-due-date-day-field');
        const dueDateDayInput = document.getElementById('recurring_due_date_day');
        
        if (dueDateType && dueDateDayField) {
            if (dueDateType.value === 'fixed_day') {
                dueDateDayField.style.display = 'block';
                if (dueDateDayInput) dueDateDayInput.required = true;
            } else {
                dueDateDayField.style.display = 'none';
                if (dueDateDayInput) {
                    dueDateDayInput.required = false;
                    dueDateDayInput.value = '';
                }
            }
        }
    }

    // Calcula e exibe preview das parcelas
    function updatePreview() {
        const type = document.getElementById('type')?.value;
        if (type !== 'client_fixed') {
            const preview = document.getElementById('installments-preview');
            if (preview) preview.style.display = 'none';
            return;
        }

        const valueRaw = document.getElementById('value_raw');
        const downPaymentValueRaw = document.getElementById('down_payment_value_raw');
        const totalValue = parseFloat(valueRaw?.value || 0);
        const hasDownPayment = document.getElementById('has_down_payment')?.checked || false;
        const downPaymentValue = parseFloat(downPaymentValueRaw?.value || 0);
        const installmentsCount = parseInt(document.getElementById('installments_count')?.value || 1);
        const paymentFrequency = document.getElementById('payment_frequency')?.value || 'monthly';
        const firstInstallmentDate = document.getElementById('first_installment_date')?.value;
        const downPaymentDate = document.getElementById('down_payment_date')?.value;
        const equalInstallments = document.getElementById('equal_installments')?.checked !== false;
        const startDate = document.getElementById('start_date')?.value;

        if (!totalValue || !firstInstallmentDate) {
            const preview = document.getElementById('installments-preview');
            if (preview) preview.style.display = 'none';
            return;
        }

        const preview = document.getElementById('installments-preview');
        const previewBody = document.getElementById('installments-preview-body');
        const manualInstallments = document.getElementById('manual-installments');
        const manualInstallmentsList = document.getElementById('manual-installments-list');

        if (!preview || !previewBody) return;

        preview.style.display = 'block';

        let html = '';
        let totalCalculated = 0;
        const installmentValue = hasDownPayment ? (totalValue - downPaymentValue) : totalValue;
        const valuePerInstallment = equalInstallments ? (installmentValue / installmentsCount) : 0;

        // Entrada
        if (hasDownPayment && downPaymentValue > 0) {
            totalCalculated += downPaymentValue;
            const downPaymentDateValue = downPaymentDate || startDate;
            html += `
                <tr data-installment="0">
                    <td><strong>0</strong></td>
                    <td>Entrada</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control form-control-sm preview-value money-mask-preview" 
                                   data-installment="0" 
                                   value="${formatMoney(downPaymentValue)}"
                                   placeholder="0,00">
                            <input type="hidden" name="preview_installment_values[0]" class="preview-value-raw" value="${downPaymentValue}">
                        </div>
                    </td>
                    <td>
                        <input type="date" class="form-control form-control-sm preview-date" 
                               data-installment="0"
                               name="preview_installment_dates[0]" 
                               value="${downPaymentDateValue}">
                    </td>
                    <td>
                        <select class="form-select form-select-sm preview-status" 
                                data-installment="0"
                                name="preview_installment_status[0]">
                            <option value="pending" selected>Pendente</option>
                            <option value="paid">Pago</option>
                            <option value="overdue">Vencido</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </td>
                </tr>
            `;
        }

        // Parcelas
        let currentDate = new Date(firstInstallmentDate);
        const frequencyDays = getFrequencyDays(paymentFrequency);
        
        // Ajusta diferença de arredondamento na última parcela
        let lastValueAdjustment = 0;
        if (equalInstallments && installmentsCount > 0) {
            const diff = installmentValue - (valuePerInstallment * installmentsCount);
            if (Math.abs(diff) > 0.01) {
                lastValueAdjustment = diff;
            }
        }

        for (let i = 0; i < installmentsCount; i++) {
            const installmentNumber = i + 1;
            let value = equalInstallments ? valuePerInstallment : (installmentValue / installmentsCount);
            
            // Ajusta última parcela se necessário
            if (i === installmentsCount - 1 && lastValueAdjustment !== 0) {
                value += lastValueAdjustment;
            }
            
            if (i > 0) {
                currentDate = addDays(currentDate, frequencyDays);
            }

            const dueDate = currentDate.toISOString().split('T')[0];
            totalCalculated += value;

            html += `
                <tr data-installment="${installmentNumber}">
                    <td><strong>${installmentNumber}</strong></td>
                    <td>Parcela ${installmentNumber} de ${installmentsCount}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">R$</span>
                            <input type="text" class="form-control form-control-sm preview-value money-mask-preview" 
                                   data-installment="${installmentNumber}" 
                                   value="${formatMoney(value)}"
                                   placeholder="0,00">
                            <input type="hidden" name="preview_installment_values[${installmentNumber}]" class="preview-value-raw" value="${value}">
                        </div>
                    </td>
                    <td>
                        <input type="date" class="form-control form-control-sm preview-date" 
                               data-installment="${installmentNumber}"
                               name="preview_installment_dates[${installmentNumber}]" 
                               value="${dueDate}">
                    </td>
                    <td>
                        <select class="form-select form-select-sm preview-status" 
                                data-installment="${installmentNumber}"
                                name="preview_installment_status[${installmentNumber}]">
                            <option value="pending" selected>Pendente</option>
                            <option value="paid">Pago</option>
                            <option value="overdue">Vencido</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </td>
                </tr>
            `;
        }

        previewBody.innerHTML = html;
        updatePreviewTotal();
        setupPreviewEditors();
    }

    // Atualiza os totais no preview
    function updatePreviewTotal() {
        const valueRaw = document.getElementById('value_raw');
        const totalValue = parseFloat(valueRaw?.value || 0);
        
        // Calcula total das parcelas
        let totalCalculated = 0;
        const previewValueInputs = document.querySelectorAll('.preview-value-raw');
        previewValueInputs.forEach(function(input) {
            const value = parseFloat(input.value || 0);
            totalCalculated += value;
        });
        
        // Atualiza elementos do preview
        const previewTotal = document.getElementById('preview-total');
        const previewContractTotal = document.getElementById('preview-contract-total');
        const previewDifference = document.getElementById('preview-difference');
        const previewValidationRow = document.getElementById('preview-validation-row');
        const previewValidationAlert = document.getElementById('preview-validation-alert');
        const previewSum = document.getElementById('preview-sum');
        const previewTotalValue = document.getElementById('preview-total-value');
        const previewDiff = document.getElementById('preview-diff');
        
        if (previewTotal) {
            previewTotal.textContent = 'R$ ' + formatMoney(totalCalculated);
        }
        
        if (previewContractTotal) {
            previewContractTotal.textContent = 'R$ ' + formatMoney(totalValue);
        }
        
        // Valida diferença
        const difference = Math.abs(totalCalculated - totalValue);
        if (difference > 0.01) {
            if (previewDifference) {
                previewDifference.textContent = 'R$ ' + formatMoney(difference);
            }
            if (previewValidationRow) {
                previewValidationRow.style.display = '';
            }
            if (previewValidationAlert) {
                previewValidationAlert.style.display = 'block';
            }
            if (previewSum) {
                previewSum.textContent = 'R$ ' + formatMoney(totalCalculated);
            }
            if (previewTotalValue) {
                previewTotalValue.textContent = 'R$ ' + formatMoney(totalValue);
            }
            if (previewDiff) {
                previewDiff.textContent = 'R$ ' + formatMoney(difference);
            }
        } else {
            if (previewValidationRow) {
                previewValidationRow.style.display = 'none';
            }
            if (previewValidationAlert) {
                previewValidationAlert.style.display = 'none';
            }
        }
    }

    // Configura editores do preview (máscaras e listeners)
    function setupPreviewEditors() {
        // Aplica máscara de dinheiro nos campos de preview
        const previewMoneyInputs = document.querySelectorAll('.money-mask-preview');
        previewMoneyInputs.forEach(function(input) {
            // Remove listeners anteriores se existirem
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            
            // Aplica máscara
            newInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value) {
                    value = (parseInt(value) / 100).toFixed(2);
                    value = value.replace('.', ',');
                    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                } else {
                    value = '0,00';
                }
                e.target.value = value;
                
                // Atualiza campo hidden correspondente
                const row = e.target.closest('tr');
                if (row) {
                    const hiddenInput = row.querySelector('.preview-value-raw');
                    if (hiddenInput) {
                        hiddenInput.value = parseMoney(value);
                    }
                }
                
                // Atualiza totais
                updatePreviewTotal();
            });
        });
        
        // Listener para mudanças nos valores (quando editados manualmente)
        const previewValueRaws = document.querySelectorAll('.preview-value-raw');
        previewValueRaws.forEach(function(input) {
            input.addEventListener('change', function() {
                updatePreviewTotal();
            });
        });
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    }

    function getFrequencyDays(frequency) {
        const frequencies = {
            'daily': 1,
            'weekly': 7,
            'biweekly': 15,
            'monthly': 30,
            'quarterly': 90,
            'yearly': 365
        };
        return frequencies[frequency] || 30;
    }

    function addDays(date, days) {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    }

    // Event listeners
    function init() {
        initMoneyFields();
        setupMoneyMasks();
        
        const typeSelect = document.getElementById('type');
        if (typeSelect) {
            typeSelect.addEventListener('change', toggleFields);
            if (typeSelect.value) toggleFields();
        }

        const hasDownPayment = document.getElementById('has_down_payment');
        if (hasDownPayment) {
            hasDownPayment.addEventListener('change', toggleDownPayment);
            if (hasDownPayment.checked) toggleDownPayment();
        }

        const startDate = document.getElementById('start_date');
        if (startDate) {
            startDate.addEventListener('change', syncDates);
        }
        
        // Listener para tipo de data de vencimento recorrente
        const recurringDueDateType = document.getElementById('recurring_due_date_type');
        if (recurringDueDateType) {
            recurringDueDateType.addEventListener('change', toggleRecurringDueDateDay);
        }

        // Atualiza preview quando campos mudam
        ['installments_count', 'payment_frequency', 'first_installment_date', 'down_payment_date', 'equal_installments', 'value_raw', 'down_payment_value_raw'].forEach(function(id) {
            const field = document.getElementById(id);
            if (field) {
                field.addEventListener('change', function() {
                    updatePreview();
                    updatePreviewTotal();
                });
                field.addEventListener('input', function() {
                    updatePreview();
                    updatePreviewTotal();
                });
            }
        });
        
        // Atualiza totais quando valor do contrato muda (sem recriar preview)
        const valueRaw = document.getElementById('value_raw');
        if (valueRaw) {
            valueRaw.addEventListener('change', updatePreviewTotal);
            valueRaw.addEventListener('input', updatePreviewTotal);
        }

        // Validação antes de submeter formulário
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('submit-contract-btn');
        if (form && submitBtn) {
            form.addEventListener('submit', function(e) {
                const preview = document.getElementById('installments-preview');
                if (preview && preview.style.display !== 'none') {
                    const validationAlert = document.getElementById('preview-validation-alert');
                    if (validationAlert && validationAlert.style.display !== 'none') {
                        e.preventDefault();
                        showAlert('Atenção', 'Por favor, ajuste os valores das parcelas para que a soma seja igual ao valor total do contrato antes de salvar.', 'warning');
                        return false;
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endsection
