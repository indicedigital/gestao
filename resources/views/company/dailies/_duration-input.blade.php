@php
    $durationUnit = old('duration_unit', 'minutes');
    $durationValue = old('duration_value');

    if ($durationValue === null && old('hours') !== null) {
        $durationValue = $durationUnit === 'hours'
            ? old('hours')
            : (int) round((float) old('hours') * 60);
    }
@endphp

<div class="daily-duration-field mb-3" data-daily-duration-field>
    <label class="form-label">Tempo gasto</label>
    <div class="btn-group w-100 mb-2 daily-duration-toggle" role="group" aria-label="Unidade de tempo">
        <input type="radio" class="btn-check" name="duration_unit" id="duration_unit_minutes" value="minutes" @checked($durationUnit === 'minutes') autocomplete="off">
        <label class="btn btn-outline-primary btn-sm" for="duration_unit_minutes">Minutos</label>
        <input type="radio" class="btn-check" name="duration_unit" id="duration_unit_hours" value="hours" @checked($durationUnit === 'hours') autocomplete="off">
        <label class="btn btn-outline-primary btn-sm" for="duration_unit_hours">Horas</label>
    </div>
    <input type="number"
           name="duration_value"
           id="duration_value"
           class="form-control @error('duration_value') is-invalid @enderror @error('hours') is-invalid @enderror"
           value="{{ $durationValue }}"
           required
           data-daily-duration-input>
    <div class="form-text" data-daily-duration-hint>Ex: 30 para meia hora</div>
    @error('duration_value')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error('hours')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@once
@push('scripts')
<script>
(function () {
    function syncDailyDurationField(field) {
        const unit = field.querySelector('input[name="duration_unit"]:checked')?.value || 'minutes';
        const input = field.querySelector('[data-daily-duration-input]');
        const hint = field.querySelector('[data-daily-duration-hint]');

        if (!input) return;

        if (unit === 'minutes') {
            input.min = '15';
            input.max = '1440';
            input.step = '1';
            input.placeholder = 'Ex: 30';
            if (hint) hint.textContent = 'Informe de 15 a 1440 minutos (até 24 h).';
        } else {
            input.min = '0.25';
            input.max = '24';
            input.step = '0.25';
            input.placeholder = 'Ex: 2';
            if (hint) hint.textContent = 'Informe de 0,25 a 24 horas (mínimo 15 min).';
        }
    }

    document.querySelectorAll('[data-daily-duration-field]').forEach(function (field) {
        syncDailyDurationField(field);
        field.querySelectorAll('input[name="duration_unit"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                syncDailyDurationField(field);
            });
        });
    });
})();
</script>
@endpush
@endonce
