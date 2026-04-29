@php
    $note = $note ?? null;
@endphp

<div class="alert alert-light border mb-4">
    <div class="row">
        <div class="col-md-6">
            <strong>Origem:</strong>
            <a href="{{ route('company.receivables.show', $note->receivable_id) }}">Conta a receber #{{ $note->receivable_id }}</a>
            @if($note->receivablePayment)
                <br><small class="text-muted">Pagamento registrado em {{ $note->receivablePayment->paid_date->format('d/m/Y') }} — ID #{{ $note->receivable_payment_id }}</small>
            @endif
        </div>
        <div class="col-md-6">
            <small class="text-muted">Os vínculos com contas a receber não podem ser alterados. Ajuste apenas os dados fiscais do tomador, se necessário.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label for="person_type" class="form-label">Tipo tomador <span class="text-danger">*</span></label>
        <select class="form-select @error('person_type') is-invalid @enderror" id="person_type" name="person_type" required>
            <option value="pf" @selected(old('person_type', $note->person_type ?? 'pf') === 'pf')>Pessoa física</option>
            <option value="pj" @selected(old('person_type', $note->person_type ?? '') === 'pj')>Pessoa jurídica</option>
        </select>
        @error('person_type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="document_type" class="form-label">Documento</label>
        <select class="form-select @error('document_type') is-invalid @enderror" id="document_type" name="document_type">
            <option value="">—</option>
            <option value="cpf" @selected(old('document_type', $note->document_type ?? '') === 'cpf')>CPF</option>
            <option value="cnpj" @selected(old('document_type', $note->document_type ?? '') === 'cnpj')>CNPJ</option>
        </select>
        @error('document_type')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="receivable_description" class="form-label">Descrição (referência da cobrança)</label>
        <input type="text" class="form-control @error('receivable_description') is-invalid @enderror" id="receivable_description" name="receivable_description" value="{{ old('receivable_description', $note->receivable_description ?? '') }}">
        @error('receivable_description')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="client_name" class="form-label">Nome / razão social <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name', $note->client_name ?? '') }}" required>
        @error('client_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="document" class="form-label">CPF / CNPJ</label>
        <input type="text" class="form-control @error('document') is-invalid @enderror" id="document" name="document" value="{{ old('document', $note->document ?? '') }}">
        @error('document')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="client_phone" class="form-label">Telefone</label>
        <input type="text" class="form-control @error('client_phone') is-invalid @enderror" id="client_phone" name="client_phone" value="{{ old('client_phone', $note->client_phone ?? '') }}">
        @error('client_phone')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="client_email" class="form-label">E-mail</label>
        <input type="email" class="form-control @error('client_email') is-invalid @enderror" id="client_email" name="client_email" value="{{ old('client_email', $note->client_email ?? '') }}">
        @error('client_email')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Endereço</label>
    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $note->address ?? '') }}</textarea>
    @error('address')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="city" class="form-label">Cidade</label>
        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $note->city ?? '') }}">
        @error('city')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 mb-3">
        <label for="state" class="form-label">UF</label>
        <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $note->state ?? '') }}" maxlength="4">
        @error('state')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="zip_code" class="form-label">CEP</label>
        <input type="text" class="form-control @error('zip_code') is-invalid @enderror" id="zip_code" name="zip_code" value="{{ old('zip_code', $note->zip_code ?? '') }}">
        @error('zip_code')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="country" class="form-label">País</label>
        <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $note->country ?? 'Brasil') }}">
        @error('country')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="amount_received" class="form-label">Valor do recebimento (R$) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" class="form-control @error('amount_received') is-invalid @enderror" id="amount_received" name="amount_received" value="{{ old('amount_received', isset($note) ? $note->amount_received : '') }}" required>
        @error('amount_received')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="received_date" class="form-label">Data do recebimento <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('received_date') is-invalid @enderror" id="received_date" name="received_date" value="{{ old('received_date', isset($note) && $note->received_date ? $note->received_date->format('Y-m-d') : '') }}" required>
        @error('received_date')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="payment_method" class="form-label">Forma de pagamento</label>
        <input type="text" class="form-control @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" value="{{ old('payment_method', $note->payment_method ?? '') }}">
        @error('payment_method')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row align-items-end">
    <div class="col-md-4 mb-3">
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="is_issued" id="is_issued" value="1" @checked(old('is_issued', $note->is_issued ?? false))>
            <label class="form-check-label" for="is_issued">Nota fiscal já emitida</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="issued_at" class="form-label">Data da emissão (NF)</label>
        <input type="date" class="form-control @error('issued_at') is-invalid @enderror" id="issued_at" name="issued_at" value="{{ old('issued_at', isset($note) && $note->issued_at ? $note->issued_at->format('Y-m-d') : '') }}">
        @error('issued_at')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="internal_notes" class="form-label">Observações internas</label>
    <textarea class="form-control @error('internal_notes') is-invalid @enderror" id="internal_notes" name="internal_notes" rows="2">{{ old('internal_notes', $note->internal_notes ?? '') }}</textarea>
    @error('internal_notes')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
