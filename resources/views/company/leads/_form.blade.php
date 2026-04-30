@php
    $selectedScopes = collect(old('project_scopes', $lead->project_scopes ?? []))->map(fn($v) => (string) $v)->all();
    $selectedPlatforms = collect(old('app_platforms', $lead->app_platforms ?? []))->map(fn($v) => (string) $v)->all();
    $hasDomainValue = old('has_domain', isset($lead) ? (int) $lead->has_domain : 0);
    $hasServerValue = old('has_server', isset($lead) ? (int) $lead->has_server : 0);
@endphp

<style>
    .lead-option-card {
        border: 1px solid #dbe3ee;
        border-radius: 10px;
        padding: 10px 12px;
        background: #fff;
        transition: all .2s ease;
    }
    .lead-option-card:has(input:checked) {
        border-color: #0d6efd;
        background: #eef5ff;
        box-shadow: 0 0 0 1px rgba(13, 110, 253, .15);
    }
    .lead-section-title {
        font-weight: 600;
        margin-bottom: 6px;
        color: #334155;
    }
</style>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="meeting_date" class="form-label">Data da reunião</label>
        <input type="date" class="form-control @error('meeting_date') is-invalid @enderror" id="meeting_date" name="meeting_date" value="{{ old('meeting_date', isset($lead->meeting_date) ? $lead->meeting_date->format('Y-m-d') : '') }}">
        @error('meeting_date')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="project_name" class="form-label">Nome do projeto <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('project_name') is-invalid @enderror" id="project_name" name="project_name" value="{{ old('project_name', $lead->project_name ?? '') }}" required>
        @error('project_name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mb-3">
    <label for="brief_description" class="form-label">Descrição breve</label>
    <textarea class="form-control @error('brief_description') is-invalid @enderror" id="brief_description" name="brief_description" rows="3">{{ old('brief_description', $lead->brief_description ?? '') }}</textarea>
    @error('brief_description')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <div class="lead-section-title">Tipo de projeto <span class="text-danger">*</span></div>
    <small class="text-muted d-block mb-2">Pode marcar mais de uma opção.</small>
    <div class="row g-2">
        @foreach(['aplicativo' => 'Aplicativo', 'site' => 'Site', 'sistema' => 'Sistema', 'landing_page' => 'Landing page', 'automacao' => 'Automação', 'outro' => 'Outro'] as $scopeValue => $scopeLabel)
            <div class="col-md-4">
                <div class="form-check lead-option-card">
                    <input class="form-check-input js-scope-check" type="checkbox" value="{{ $scopeValue }}" id="scope_{{ $scopeValue }}" name="project_scopes[]" {{ in_array($scopeValue, $selectedScopes, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="scope_{{ $scopeValue }}">{{ $scopeLabel }}</label>
                </div>
            </div>
        @endforeach
    </div>
    @error('project_scopes')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error('project_scopes.*')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<div id="project_scope_other_wrap" class="mb-3" style="display: none;">
    <label for="project_scope_other" class="form-label">Detalhe do tipo "Outro" <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('project_scope_other') is-invalid @enderror" id="project_scope_other" name="project_scope_other" value="{{ old('project_scope_other', $lead->project_scope_other ?? '') }}">
    @error('project_scope_other')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div id="app_platforms_wrap" class="mb-3" style="display: none;">
    <div class="lead-section-title">Plataformas do aplicativo <span class="text-danger">*</span></div>
    <div class="row g-2">
        @foreach(['android' => 'Android', 'iphone' => 'iPhone'] as $platformValue => $platformLabel)
            <div class="col-md-3">
                <div class="form-check lead-option-card">
                    <input class="form-check-input" type="checkbox" value="{{ $platformValue }}" id="platform_{{ $platformValue }}" name="app_platforms[]" {{ in_array($platformValue, $selectedPlatforms, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="platform_{{ $platformValue }}">{{ $platformLabel }}</label>
                </div>
            </div>
        @endforeach
    </div>
    @error('app_platforms')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error('app_platforms.*')
    <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="project_kind" class="form-label">Tipo de trabalho <span class="text-danger">*</span></label>
        <select class="form-select @error('project_kind') is-invalid @enderror" id="project_kind" name="project_kind" required>
            <option value="">Selecione</option>
            <option value="desenvolvimento" {{ old('project_kind', $lead->project_kind ?? '') === 'desenvolvimento' ? 'selected' : '' }}>Desenvolvimento</option>
            <option value="correcoes" {{ old('project_kind', $lead->project_kind ?? '') === 'correcoes' ? 'selected' : '' }}>Correções</option>
            <option value="melhorias" {{ old('project_kind', $lead->project_kind ?? '') === 'melhorias' ? 'selected' : '' }}>Melhorias</option>
        </select>
        @error('project_kind')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="project_stage" class="form-label">Estágio do projeto</label>
        <input type="text" class="form-control @error('project_stage') is-invalid @enderror" id="project_stage" name="project_stage" value="{{ old('project_stage', $lead->project_stage ?? '') }}" placeholder="Ex.: briefing, proposta, desenvolvimento">
        @error('project_stage')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 mb-3">
        <label for="is_online" class="form-label">Já está online?</label>
        <select class="form-select @error('is_online') is-invalid @enderror" id="is_online" name="is_online">
            <option value="0" {{ (string) old('is_online', isset($lead) ? (int) $lead->is_online : 0) === '0' ? 'selected' : '' }}>Não</option>
            <option value="1" {{ (string) old('is_online', isset($lead) ? (int) $lead->is_online : 0) === '1' ? 'selected' : '' }}>Sim</option>
        </select>
        @error('is_online')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-2 mb-3">
        <label for="is_active" class="form-label">Já está ativo?</label>
        <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
            <option value="0" {{ (string) old('is_active', isset($lead) ? (int) $lead->is_active : 0) === '0' ? 'selected' : '' }}>Não</option>
            <option value="1" {{ (string) old('is_active', isset($lead) ? (int) $lead->is_active : 0) === '1' ? 'selected' : '' }}>Sim</option>
        </select>
        @error('is_active')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label for="has_domain" class="form-label">Já possui domínio?</label>
        <select class="form-select @error('has_domain') is-invalid @enderror js-has-domain" id="has_domain" name="has_domain">
            <option value="0" {{ (string) $hasDomainValue === '0' ? 'selected' : '' }}>Não</option>
            <option value="1" {{ (string) $hasDomainValue === '1' ? 'selected' : '' }}>Sim</option>
        </select>
        @error('has_domain')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-9 mb-3" id="domain_info_wrap" style="display: none;">
        <label for="domain_info" class="form-label">Informações do domínio <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('domain_info') is-invalid @enderror" id="domain_info" name="domain_info" value="{{ old('domain_info', $lead->domain_info ?? '') }}" placeholder="Ex.: dominio.com.br, registrador e acesso">
        @error('domain_info')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label for="has_server" class="form-label">Já possui servidor?</label>
        <select class="form-select @error('has_server') is-invalid @enderror js-has-server" id="has_server" name="has_server">
            <option value="0" {{ (string) $hasServerValue === '0' ? 'selected' : '' }}>Não</option>
            <option value="1" {{ (string) $hasServerValue === '1' ? 'selected' : '' }}>Sim</option>
        </select>
        @error('has_server')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-9 mb-3" id="server_info_wrap" style="display: none;">
        <label for="server_info" class="form-label">Informações do servidor <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('server_info') is-invalid @enderror" id="server_info" name="server_info" value="{{ old('server_info', $lead->server_info ?? '') }}" placeholder="Ex.: provedor, plano, acesso e observações">
        @error('server_info')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="expected_budget" class="form-label">Expectativa de orçamento</label>
        <input type="number" step="0.01" min="0" class="form-control @error('expected_budget') is-invalid @enderror" id="expected_budget" name="expected_budget" value="{{ old('expected_budget', $lead->expected_budget ?? '') }}">
        @error('expected_budget')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="expected_deadline" class="form-label">Prazo esperado</label>
        <input type="date" class="form-control @error('expected_deadline') is-invalid @enderror" id="expected_deadline" name="expected_deadline" value="{{ old('expected_deadline', isset($lead->expected_deadline) ? $lead->expected_deadline->format('Y-m-d') : '') }}">
        @error('expected_deadline')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        function toggleConditionalFields() {
            const checkedScopes = Array.from(document.querySelectorAll('.js-scope-check:checked')).map((el) => el.value);
            const hasApp = checkedScopes.includes('aplicativo');
            const hasOutro = checkedScopes.includes('outro');
            const hasDomain = document.getElementById('has_domain')?.value === '1';
            const hasServer = document.getElementById('has_server')?.value === '1';

            const appWrap = document.getElementById('app_platforms_wrap');
            const otherWrap = document.getElementById('project_scope_other_wrap');
            const domainWrap = document.getElementById('domain_info_wrap');
            const serverWrap = document.getElementById('server_info_wrap');

            if (appWrap) appWrap.style.display = hasApp ? '' : 'none';
            if (otherWrap) otherWrap.style.display = hasOutro ? '' : 'none';
            if (domainWrap) domainWrap.style.display = hasDomain ? '' : 'none';
            if (serverWrap) serverWrap.style.display = hasServer ? '' : 'none';
        }

        document.querySelectorAll('.js-scope-check').forEach((checkbox) => {
            checkbox.addEventListener('change', toggleConditionalFields);
        });
        document.querySelector('.js-has-domain')?.addEventListener('change', toggleConditionalFields);
        document.querySelector('.js-has-server')?.addEventListener('change', toggleConditionalFields);

        toggleConditionalFields();
    })();
</script>
@endpush

