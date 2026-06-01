@php
    $priorityColors = ['P0' => '#f5365c', 'P1' => '#fb6340', 'P2' => '#5e72e4', 'P3' => '#8b949e'];
    $categoryMax = $byCategory->max() ?: 1;
    $priorityMax = max(1, $byPriority->max() ?: 1);
    $statusMax = max(1, $byStatus->max() ?: 1);
@endphp

<div class="project-dashboard">

    {{-- KPIs principais --}}
    <div class="dash-kpi-grid mb-4">
        <div class="dash-kpi-card dash-kpi-primary">
            <div class="dash-kpi-icon"><i class="fas fa-layer-group"></i></div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-value">{{ $totalTasks }}</div>
                <div class="dash-kpi-label">Total de tasks</div>
                <div class="dash-kpi-sub">{{ $openTasks }} abertas · {{ $closedTasks }} concluídas</div>
            </div>
        </div>
        <div class="dash-kpi-card dash-kpi-success">
            <div class="dash-kpi-icon"><i class="fas fa-check-circle"></i></div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-value">{{ $completionRate }}%</div>
                <div class="dash-kpi-label">Taxa de conclusão</div>
                <div class="dash-kpi-progress mt-2">
                    <div class="dash-kpi-progress-bar" style="width:{{ $completionRate }}%"></div>
                </div>
            </div>
        </div>
        <div class="dash-kpi-card dash-kpi-info">
            <div class="dash-kpi-icon"><i class="fas fa-spinner"></i></div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-value">{{ $inProgressTasks }}</div>
                <div class="dash-kpi-label">Em progresso</div>
                <div class="dash-kpi-sub">{{ $waitingClientTasks }} aguardando cliente</div>
            </div>
        </div>
        <div class="dash-kpi-card {{ $overdueTasks > 0 ? 'dash-kpi-danger' : 'dash-kpi-muted' }}">
            <div class="dash-kpi-icon"><i class="fas fa-clock"></i></div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-value">{{ $slaComplianceRate }}%</div>
                <div class="dash-kpi-label">SLA no prazo</div>
                <div class="dash-kpi-sub {{ $overdueTasks ? 'text-danger' : '' }}">{{ $overdueTasks }} estourada(s)</div>
            </div>
        </div>
        <div class="dash-kpi-card dash-kpi-warning">
            <div class="dash-kpi-icon"><i class="fas fa-hourglass-half"></i></div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-value">{{ $avgDeliveryHours ? round($avgDeliveryHours, 1).'h' : '—' }}</div>
                <div class="dash-kpi-label">Tempo médio entrega</div>
                <div class="dash-kpi-sub">Tasks concluídas</div>
            </div>
        </div>
        <div class="dash-kpi-card dash-kpi-purple">
            <div class="dash-kpi-icon"><i class="fas fa-users"></i></div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-value">{{ $teamCount }}</div>
                <div class="dash-kpi-label">Membros no time</div>
                <div class="dash-kpi-sub">{{ number_format($hoursUsed, 1, ',', '.') }}h registradas</div>
            </div>
        </div>
    </div>

    {{-- Financeiro + Horas --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="dash-panel h-100">
                <div class="dash-panel-head">
                    <span><i class="fas fa-coins me-2 text-warning"></i>Financeiro</span>
                </div>
                <div class="dash-panel-body">
                    <div class="dash-finance-row">
                        <span class="dash-finance-label">Valor do contrato</span>
                        <span class="dash-finance-value">R$ {{ number_format($project->total_value, 2, ',', '.') }}</span>
                    </div>
                    <div class="dash-finance-row">
                        <span class="dash-finance-label">Custo acumulado</span>
                        <span class="dash-finance-value">R$ {{ number_format($project->cost ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="dash-finance-row">
                        <span class="dash-finance-label">Lucro estimado</span>
                        <span class="dash-finance-value text-success">R$ {{ number_format($project->profit ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="dash-finance-row mb-0">
                        <span class="dash-finance-label">Margem</span>
                        <span class="dash-finance-badge">{{ number_format($project->profit_margin_percent ?? 0, 1, ',', '.') }}%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="dash-panel h-100">
                <div class="dash-panel-head">
                    <span><i class="fas fa-business-time me-2 text-primary"></i>Pacote de horas</span>
                    @if($allocatedHours > 0)
                        <span class="dash-panel-badge {{ $hoursUsagePct > 90 ? 'badge-danger' : ($hoursUsagePct > 70 ? 'badge-warning' : 'badge-ok') }}">
                            {{ $hoursUsagePct }}% utilizado
                        </span>
                    @endif
                </div>
                <div class="dash-panel-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="dash-hours-ring" style="--pct: {{ $hoursUsagePct }}">
                                <svg viewBox="0 0 36 36">
                                    <path class="dash-ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    <path class="dash-ring-fill" stroke-dasharray="{{ $hoursUsagePct }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                </svg>
                                <div class="dash-hours-ring-label">
                                    <strong>{{ number_format($hoursUsed, 0, ',', '.') }}h</strong>
                                    <small>de {{ number_format($allocatedHours, 0, ',', '.') }}h</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="dash-hours-stats">
                                <div class="dash-hours-stat">
                                    <div class="dash-hours-stat-val">{{ number_format($hoursUsed, 1, ',', '.') }}h</div>
                                    <div class="dash-hours-stat-lbl">Registradas (Dailies)</div>
                                </div>
                                <div class="dash-hours-stat">
                                    <div class="dash-hours-stat-val">{{ number_format($allocatedHours, 0, ',', '.') }}h</div>
                                    <div class="dash-hours-stat-lbl">Alocadas ao time</div>
                                </div>
                                <div class="dash-hours-stat">
                                    <div class="dash-hours-stat-val {{ $hoursRemaining <= 0 && $allocatedHours > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($hoursRemaining, 1, ',', '.') }}h
                                    </div>
                                    <div class="dash-hours-stat-lbl">Saldo restante</div>
                                </div>
                            </div>
                            @if($allocatedHours > 0)
                            <div class="dash-bar-track mt-3">
                                <div class="dash-bar-fill {{ $hoursUsagePct > 90 ? 'bar-danger' : ($hoursUsagePct > 70 ? 'bar-warning' : 'bar-primary') }}" style="width:{{ $hoursUsagePct }}%"></div>
                            </div>
                            @else
                            <p class="text-muted small mb-0 mt-2">Nenhuma hora alocada ao time. Configure na aba Equipe.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficos e distribuições --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="dash-panel">
                <div class="dash-panel-head">
                    <span><i class="fas fa-chart-line me-2"></i>Fluxo do mês</span>
                    <small class="text-muted">{{ now()->translatedFormat('F Y') }}</small>
                </div>
                <div class="dash-panel-body">
                    <canvas id="burnDownChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dash-panel h-100">
                <div class="dash-panel-head">
                    <span><i class="fas fa-stream me-2"></i>Pipeline (abertas)</span>
                </div>
                <div class="dash-panel-body">
                    @php $hasStatus = false; @endphp
                    @foreach(\App\Models\Task::STATUSES as $key => $label)
                        @if($key !== 'completed' && ($byStatus[$key] ?? 0) > 0)
                            @php $hasStatus = true; $cnt = $byStatus[$key]; @endphp
                            <div class="dash-distribution-row">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="work-status-pill status-{{ $key }}" style="font-size:10px;padding:2px 8px;">{{ $label }}</span>
                                    <strong>{{ $cnt }}</strong>
                                </div>
                                <div class="dash-bar-track">
                                    <div class="dash-bar-fill bar-muted" style="width:{{ round(($cnt / $statusMax) * 100) }}%"></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasStatus)
                        <p class="text-muted small mb-0 text-center py-3">Nenhuma task aberta no pipeline.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="dash-panel h-100">
                <div class="dash-panel-head">
                    <span><i class="fas fa-tags me-2"></i>Por categoria</span>
                </div>
                <div class="dash-panel-body">
                    @forelse($byCategory as $cat => $total)
                        <div class="dash-distribution-row">
                            <div class="d-flex justify-content-between mb-1">
                                <span>{{ \App\Models\Task::CATEGORIES[$cat] ?? $cat }}</span>
                                <strong>{{ $total }}</strong>
                            </div>
                            <div class="dash-bar-track">
                                <div class="dash-bar-fill bar-primary" style="width:{{ round(($total / $categoryMax) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0 text-center py-3">Nenhuma task registrada.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dash-panel h-100">
                <div class="dash-panel-head">
                    <span><i class="fas fa-flag me-2"></i>Abertas por prioridade</span>
                </div>
                <div class="dash-panel-body">
                    @foreach(\App\Models\Task::PRIORITIES as $p => $label)
                        @php $cnt = $byPriority[$p] ?? 0; @endphp
                        <div class="dash-distribution-row">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="kanban-priority {{ $p }}">{{ $p }}</span>
                                <strong>{{ $cnt }}</strong>
                            </div>
                            <div class="dash-bar-track">
                                <div class="dash-bar-fill" style="width:{{ $cnt ? round(($cnt / $priorityMax) * 100) : 0 }}%;background:{{ $priorityColors[$p] ?? '#5e72e4' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.initProjectDashboardChart = function () {
    if (typeof Chart === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        s.onload = window.initProjectDashboardChart;
        document.head.appendChild(s);
        return;
    }
    var canvas = document.getElementById('burnDownChart');
    if (!canvas) return;
    if (canvas._chartInstance) {
        canvas._chartInstance.destroy();
    }
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    var textColor = isDark ? '#8b949e' : '#64748b';
    var opened = @json($burnDown->pluck('opened', 'day'));
    var closed = @json($burnDownClosed->pluck('closed', 'day'));
    var labels = [...new Set([...Object.keys(opened), ...Object.keys(closed)])].sort();
    if (!labels.length) {
        labels = [new Date().toISOString().slice(0, 10)];
    }
    canvas._chartInstance = new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels.map(function (d) {
                var p = d.split('-');
                return p[2] + '/' + p[1];
            }),
            datasets: [
                {
                    label: 'Abertas',
                    data: labels.map(function (d) { return opened[d] || 0; }),
                    backgroundColor: 'rgba(94, 114, 228, 0.7)',
                    borderRadius: 6,
                    barPercentage: 0.6,
                },
                {
                    label: 'Concluídas',
                    data: labels.map(function (d) { return closed[d] || 0; }),
                    backgroundColor: 'rgba(45, 206, 137, 0.7)',
                    borderRadius: 6,
                    barPercentage: 0.6,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'top', labels: { color: textColor, usePointStyle: true, boxWidth: 8 } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor, font: { size: 11 } }, grid: { color: gridColor } },
            },
        },
    });
};
</script>
