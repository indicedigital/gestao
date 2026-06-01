<div class="row g-4">
    <div class="col-lg-7">
        <div class="prod-section">
            <div class="prod-section-head">
                <span><i class="fas fa-bell me-2 text-danger"></i>Alertas inteligentes</span>
                <span class="badge bg-danger">{{ count($alerts) }}</span>
            </div>
            <div class="prod-section-body">
                @forelse($alerts as $alert)
                <div class="prod-alert {{ $alert['severity'] }}">
                    <div class="prod-alert-icon">
                        <i class="fas fa-{{ $alert['severity'] === 'danger' ? 'exclamation-triangle' : ($alert['severity'] === 'warning' ? 'exclamation-circle' : 'info-circle') }}"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $alert['title'] }}</div>
                        <div class="small text-muted">{{ $alert['message'] }}</div>
                        @if($alert['employee_id'])
                        <a href="{{ route('company.dailies.productivity', array_merge(request()->query(), ['tab' => 'collaborators', 'selected_employee_id' => $alert['employee_id']])) }}" class="small">Ver colaborador →</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="mb-0">Nenhum alerta crítico no período.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="prod-section">
            <div class="prod-section-head">
                <span><i class="fas fa-lightbulb me-2 text-warning"></i>Pontos de atenção</span>
            </div>
            <div class="prod-section-body">
                @foreach($insights as $insight)
                <div class="prod-insight">
                    <i class="fas fa-chart-line"></i>
                    <span>{{ $insight }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
