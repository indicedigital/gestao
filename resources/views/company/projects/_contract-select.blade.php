@php
    $selectedContractId = old('contract_id', $selectedContractId ?? ($project->contract_id ?? null));
    $contractsPayload = $contracts->map(function ($c) {
        return [
            'id' => $c->id,
            'label' => $c->name.($c->client ? ' — '.$c->client->name : ''),
            'client' => $c->client?->name ?? '—',
            'financial_type' => $c->type === 'client_recurring' ? 'Recorrente (Mensal)' : 'Fechado (Parcelado)',
            'category' => $c->type === 'client_recurring' ? 'Suporte' : 'Desenvolvimento',
            'value' => number_format((float) $c->value, 2, ',', '.'),
            'installments' => $c->type === 'client_recurring' ? 1 : max(1, (int) ($c->installments_count ?: 1)),
            'start' => $c->start_date?->format('d/m/Y') ?? '—',
            'end' => $c->end_date?->format('d/m/Y') ?? '—',
        ];
    })->values();
@endphp

<div class="mb-4">
    <label for="contract_id" class="form-label">Contrato vinculado <span class="text-danger">*</span></label>
    <select class="form-select @error('contract_id') is-invalid @enderror" id="contract_id" name="contract_id" required>
        <option value="">Selecione o contrato</option>
        @foreach($contracts as $contract)
            <option value="{{ $contract->id }}" @selected((string) $selectedContractId === (string) $contract->id)>
                {{ $contract->name }} — {{ $contract->client->name ?? 'Sem cliente' }}
            </option>
        @endforeach
    </select>
    @error('contract_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Cliente, valores e tipo financeiro serão herdados automaticamente do contrato.</small>
</div>

<div id="contract-preview" class="card bg-light border mb-4 d-none">
    <div class="card-body py-3">
        <h6 class="mb-3 text-muted"><i class="fas fa-file-contract me-1"></i> Dados do contrato</h6>
        <div class="row g-2 small">
            <div class="col-md-4"><span class="text-muted">Cliente</span><div class="fw-semibold" id="pv-client">—</div></div>
            <div class="col-md-4"><span class="text-muted">Tipo financeiro</span><div class="fw-semibold" id="pv-type">—</div></div>
            <div class="col-md-4"><span class="text-muted">Tipo operacional</span><div class="fw-semibold" id="pv-category">—</div></div>
            <div class="col-md-4"><span class="text-muted">Valor</span><div class="fw-semibold" id="pv-value">—</div></div>
            <div class="col-md-4"><span class="text-muted">Parcelas</span><div class="fw-semibold" id="pv-installments">—</div></div>
            <div class="col-md-4"><span class="text-muted">Vigência</span><div class="fw-semibold" id="pv-period">—</div></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const contracts = @json($contractsPayload);
    const select = document.getElementById('contract_id');
    const preview = document.getElementById('contract-preview');

    function renderPreview(id) {
        const c = contracts.find(x => String(x.id) === String(id));
        if (!c) {
            preview.classList.add('d-none');
            return;
        }
        preview.classList.remove('d-none');
        document.getElementById('pv-client').textContent = c.client;
        document.getElementById('pv-type').textContent = c.financial_type;
        document.getElementById('pv-category').textContent = c.category;
        document.getElementById('pv-value').textContent = 'R$ ' + c.value;
        document.getElementById('pv-installments').textContent = c.installments;
        document.getElementById('pv-period').textContent = c.start + ' → ' + c.end;
    }

    select?.addEventListener('change', () => renderPreview(select.value));
    if (select?.value) renderPreview(select.value);
});
</script>
