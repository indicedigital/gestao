@extends('layouts.app')

@section('title', 'Meu Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/developer-dashboard.css') }}">
@endpush

@section('content')
@php
    $nameParts = preg_split('/\s+/u', trim($user->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $firstName = $nameParts[0] ?? $user->name;
    $initials = strtoupper(substr($nameParts[0] ?? '?', 0, 1) . substr($nameParts[count($nameParts) - 1] ?? '', 0, 1));
    $priorityMax = max(1, $priorityDistribution->max() ?? 1);
    $urgentCount = (int) ($priorityDistribution['P0'] ?? 0) + (int) ($priorityDistribution['P1'] ?? 0);
    $ringPct = min(100, $todayProgress);
    $hoursRemaining = max(0, $dailyTarget - $todayHours);
@endphp

<div class="dev-dashboard">

    {{-- Hero --}}
    <header class="dev-hero">
        <div class="dev-hero-col">
            <div class="dev-hero-main">
                <div class="dev-avatar" aria-hidden="true">{{ $initials }}</div>
                <div class="dev-hero-text">
                    <div class="dev-hero-eyebrow">Workspace · Programador</div>
                    <h1>{{ $greeting }}, {{ $firstName }}</h1>
                    <p class="dev-hero-sub">
                        Panorama do seu dia — foque nas entregas com maior impacto.
                    </p>
                    <div class="dev-pills">
                        <span class="dev-pill dev-pill-date">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="dev-pill-long">{{ $dateLabel }}</span>
                            <span class="dev-pill-short">{{ $dateLabelShort }}</span>
                        </span>
                        <span class="dev-pill accent">
                            <i class="fas fa-folder-open"></i>
                            {{ $activeProjects }} {{ $activeProjects === 1 ? 'projeto' : 'projetos' }}
                        </span>
                        @if($pendingCount > 0)
                            <span class="dev-pill">
                                <i class="fas fa-list-check"></i> {{ $pendingCount }} pendentes
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="dev-quick-actions">
                <a href="{{ route('company.dailies.index') }}" class="dev-quick-btn primary">
                    <i class="fas fa-plus"></i>
                    <span class="dev-btn-txt-full">Registrar Daily</span>
                    <span class="dev-btn-txt-short">Daily</span>
                </a>
                <a href="{{ route('company.tasks.index') }}" class="dev-quick-btn">
                    <i class="fas fa-tasks"></i>
                    <span class="dev-btn-txt-full">Minhas Tasks</span>
                    <span class="dev-btn-txt-short">Tasks</span>
                </a>
                <a href="{{ route('company.projects.index') }}" class="dev-quick-btn">
                    <i class="fas fa-project-diagram"></i>
                    <span class="dev-btn-txt-full">Projetos</span>
                    <span class="dev-btn-txt-short">Projetos</span>
                </a>
            </div>
        </div>

        <aside class="dev-focus-card" aria-label="Meta diária de horas">
            <div class="dev-focus-top">
                <span class="dev-focus-label">Foco de hoje</span>
                <span class="dev-focus-clock"><i class="fas fa-clock"></i> {{ $timeLabel }} · Brasília</span>
            </div>
            <div class="dev-focus-body">
                <div class="dev-ring-md">
                    <svg viewBox="0 0 36 36" aria-hidden="true">
                        <defs>
                            <linearGradient id="devRingGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#5e72e4"/>
                                <stop offset="100%" stop-color="#9d8df7"/>
                            </linearGradient>
                        </defs>
                        <path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="ring-fill" stroke-dasharray="{{ $ringPct }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <div class="dev-ring-center">
                        <strong>{{ number_format($todayHours, 1, ',', '.') }}</strong>
                        <span>/ {{ number_format($dailyTarget, 0) }}h</span>
                    </div>
                </div>
                <div class="dev-focus-stats">
                    <div class="dev-focus-pct-lg">{{ $todayProgress }}%</div>
                    <div class="dev-focus-pct-label">da meta diária</div>
                    @if($hoursRemaining > 0)
                        <div class="dev-focus-hint">
                            <i class="fas fa-hourglass-half"></i>
                            Faltam <strong>{{ number_format($hoursRemaining, 1, ',', '.') }}h</strong>
                        </div>
                    @else
                        <div class="dev-focus-hint success">
                            <i class="fas fa-check-circle"></i> Meta atingida!
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('company.dailies.index') }}" class="dev-focus-cta">
                <i class="fas fa-plus me-1"></i> Lançar horas agora
            </a>
            </aside>
    </header>

    {{-- KPIs --}}
    <section class="dev-kpi-grid" aria-label="Indicadores principais">
        <article class="dev-kpi primary">
            <div class="dev-kpi-icon"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="dev-kpi-value">{{ $pendingCount }}</div>
                <div class="dev-kpi-label">Tarefas pendentes</div>
                <div class="dev-kpi-hint">{{ $inProgressCount }} em execução · {{ $urgentCount }} urgentes</div>
            </div>
        </article>
        <article class="dev-kpi info">
            <div class="dev-kpi-icon"><i class="fas fa-bolt"></i></div>
            <div>
                <div class="dev-kpi-value">{{ $inProgressCount }}</div>
                <div class="dev-kpi-label">Em progresso</div>
                <div class="dev-kpi-hint">{{ $reviewCount }} aguardando revisão</div>
            </div>
        </article>
        <article class="dev-kpi {{ $slaRate >= 80 ? 'success' : ($slaRate >= 50 ? 'warning' : 'danger') }}">
            <div class="dev-kpi-icon"><i class="fas fa-shield-alt"></i></div>
            <div>
                <div class="dev-kpi-value">{{ $slaRate }}%</div>
                <div class="dev-kpi-label">SLA no prazo</div>
                <div class="dev-kpi-hint">{{ $slaOnTime }} de {{ $slaClosed }} no prazo</div>
            </div>
        </article>
        <article class="dev-kpi success">
            <div class="dev-kpi-icon"><i class="fas fa-check-double"></i></div>
            <div>
                <div class="dev-kpi-value">{{ $completedWeek }}</div>
                <div class="dev-kpi-label">Concluídas na semana</div>
                <div class="dev-kpi-hint">{{ $completedMonth }} no mês corrente</div>
            </div>
        </article>
        <article class="dev-kpi warning">
            <div class="dev-kpi-icon"><i class="fas fa-clock"></i></div>
            <div>
                <div class="dev-kpi-value">{{ number_format($weekHours, 1, ',', '.') }}<span style="font-size:16px">h</span></div>
                <div class="dev-kpi-label">Horas na semana</div>
                <div class="dev-kpi-hint">{{ number_format($monthHours, 1, ',', '.') }}h · {{ $monthWorkDays }} dias ativos</div>
            </div>
        </article>
        <article class="dev-kpi danger">
            <div class="dev-kpi-icon"><i class="fas fa-calendar-times"></i></div>
            <div>
                <div class="dev-kpi-value">{{ $overdueCount }}</div>
                <div class="dev-kpi-label">Prazo vencido</div>
                <div class="dev-kpi-hint">
                    @if($overdueCount > 0)
                        Tasks abertas com entrega atrasada
                    @else
                        Nenhuma task com prazo vencido
                    @endif
                </div>
            </div>
        </article>
    </section>

    {{-- Gráficos --}}
    <section class="dev-charts-row">
        <div class="dev-section">
            <div class="dev-section-head">
                <div>
                    <h2 class="dev-section-title"><i class="fas fa-chart-bar"></i> Produtividade semanal</h2>
                    <p class="dev-section-desc">Horas registradas nos últimos 7 dias</p>
                </div>
                <span class="dev-pill accent">{{ number_format(array_sum($hoursChartData), 1, ',', '.') }}h total</span>
            </div>
            <div class="dev-section-body padded">
                <div class="dev-chart-box">
                    <canvas id="hoursChart" aria-label="Gráfico de horas por dia"></canvas>
                </div>
            </div>
        </div>
        <div class="dev-section">
            <div class="dev-section-head">
                <div>
                    <h2 class="dev-section-title"><i class="fas fa-chart-pie"></i> Pipeline de tasks</h2>
                    <p class="dev-section-desc">Distribuição por status (abertas)</p>
                </div>
            </div>
            <div class="dev-section-body padded">
                @if(count($statusChartData) > 0)
                    <div class="dev-chart-box sm">
                        <canvas id="statusChart" aria-label="Gráfico de tasks por status"></canvas>
                    </div>
                @else
                    <div class="dev-empty">
                        <div class="dev-empty-icon"><i class="fas fa-inbox"></i></div>
                        <div class="dev-empty-title">Pipeline vazio</div>
                        <p class="dev-empty-text">Nenhuma task aberta no momento.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Fila + sidebar --}}
    <div class="dev-layout">
        <section class="dev-section">
            <div class="dev-section-head">
                <div>
                    <h2 class="dev-section-title"><i class="fas fa-stream"></i> Fila de execução</h2>
                    <p class="dev-section-desc">Ordenadas por prioridade e prazo — clique para abrir</p>
                </div>
                <a href="{{ route('company.tasks.index') }}" class="dev-section-action">Ver todas →</a>
            </div>
            <div class="dev-section-body">
                @forelse($upcomingTasks as $index => $task)
                    @php
                        $isOverdue = $task->sla_deadline && $task->sla_deadline->isPast();
                    @endphp
                    <a href="{{ route('company.tasks.show', $task) }}" class="dev-task-row {{ $isOverdue ? 'overdue' : '' }}">
                        <span class="dev-task-rank">{{ $index + 1 }}</span>
                        <div class="dev-task-content">
                            <div class="dev-task-name">{{ $task->title }}</div>
                            <div class="dev-task-tags">
                                <span class="dev-tag priority-{{ $task->priority }}">{{ $task->priority }}</span>
                                <span class="dev-tag status-{{ $task->status }}">{{ \App\Models\Task::STATUSES[$task->status] ?? $task->status }}</span>
                                @if($task->project)
                                    <span class="dev-tag project">{{ $task->project->name }}</span>
                                @endif
                                @if($task->sla_deadline)
                                    <span class="dev-tag deadline {{ $isOverdue ? 'overdue' : '' }}">
                                        <i class="far fa-clock me-1"></i>{{ $task->sla_deadline->format('d/m H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="dev-task-side">
                            @if($task->estimated_hours)
                                <span class="dev-task-est">{{ $task->estimated_hours }}h</span>
                            @endif
                            <i class="fas fa-chevron-right dev-task-chevron"></i>
                        </div>
                    </a>
                @empty
                    <div class="dev-empty">
                        <div class="dev-empty-icon"><i class="fas fa-champagne-glasses"></i></div>
                        <div class="dev-empty-title">Tudo em dia!</div>
                        <p class="dev-empty-text">Você não tem tarefas pendentes no momento.</p>
                        <a href="{{ route('company.tasks.index') }}" class="dev-section-action">Explorar tasks</a>
                    </div>
                @endforelse
            </div>
        </section>

        <aside>
            {{-- Timeline do dia --}}
            <section class="dev-section">
                <div class="dev-section-head">
                    <div>
                        <h2 class="dev-section-title"><i class="fas fa-sun"></i> Horas de hoje</h2>
                        <p class="dev-section-desc">{{ number_format($todayHours, 1, ',', '.') }}h registradas</p>
                    </div>
                </div>
                <div class="dev-section-body">
                    @forelse($todayDailies as $daily)
                        <div class="dev-timeline-item">
                            <div class="dev-timeline-dot"></div>
                            <div class="dev-timeline-body">
                                <div class="dev-timeline-title">{{ $daily->task->title ?? 'Task removida' }}</div>
                                <div class="dev-timeline-meta">{{ $daily->project->name ?? '' }}</div>
                                @if($daily->description)
                                    <div class="dev-timeline-desc">{{ Str::limit($daily->description, 70) }}</div>
                                @endif
                            </div>
                            <span class="dev-timeline-hours">{{ number_format($daily->hours, 2, ',', '.') }}h</span>
                        </div>
                    @empty
                        <div class="dev-empty" style="padding:32px 20px">
                            <div class="dev-empty-icon" style="width:48px;height:48px;font-size:18px"><i class="fas fa-pen"></i></div>
                            <div class="dev-empty-title">Nenhum registro</div>
                            <p class="dev-empty-text mb-0">Comece registrando sua primeira daily.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Prioridades --}}
            <section class="dev-section">
                <div class="dev-section-head">
                    <div>
                        <h2 class="dev-section-title"><i class="fas fa-signal"></i> Backlog por prioridade</h2>
                    </div>
                </div>
                <div class="dev-section-body dev-priority-list">
                    @foreach(['P0', 'P1', 'P2', 'P3'] as $p)
                        @php
                            $count = (int) ($priorityDistribution[$p] ?? 0);
                            $colors = ['P0' => '#f5365c', 'P1' => '#fb6340', 'P2' => '#5e72e4', 'P3' => '#8b949e'];
                        @endphp
                        <div class="dev-priority-row">
                            <span class="dev-priority-label {{ $p }}">{{ $p }}</span>
                            <div class="dev-priority-track">
                                <div class="dev-priority-fill" style="width:{{ $count ? round(($count / $priorityMax) * 100) : 0 }}%; background:{{ $colors[$p] }}"></div>
                            </div>
                            <span class="dev-priority-count">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Carga --}}
            <section class="dev-section">
                <div class="dev-section-head">
                    <div>
                        <h2 class="dev-section-title"><i class="fas fa-weight-hanging"></i> Carga de trabalho</h2>
                    </div>
                </div>
                <div class="dev-workload-grid">
                    <div class="dev-workload-item">
                        <div class="dev-workload-val">{{ number_format($estimatedOpenHours, 0, ',', '.') }}h</div>
                        <div class="dev-workload-lbl">Estimadas (abertas)</div>
                    </div>
                    <div class="dev-workload-item">
                        <div class="dev-workload-val">{{ number_format($actualOpenHours, 0, ',', '.') }}h</div>
                        <div class="dev-workload-lbl">Registradas (abertas)</div>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    {{-- Rodapé: projetos + concluídas --}}
    <section class="dev-bottom-row">
        <div class="dev-section">
            <div class="dev-section-head">
                <div>
                    <h2 class="dev-section-title"><i class="fas fa-folder-tree"></i> Horas por projeto</h2>
                    <p class="dev-section-desc">Esta semana — onde você investiu tempo</p>
                </div>
            </div>
            <div class="dev-section-body padded">
                @if($hoursByProject->isNotEmpty())
                    <div class="dev-chart-box lg">
                        <canvas id="projectChart" aria-label="Horas por projeto"></canvas>
                    </div>
                @else
                    <div class="dev-empty">
                        <div class="dev-empty-icon"><i class="fas fa-chart-simple"></i></div>
                        <div class="dev-empty-title">Sem dados ainda</div>
                        <p class="dev-empty-text">Registre dailies para ver a distribuição por projeto.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="dev-section">
            <div class="dev-section-head">
                <div>
                    <h2 class="dev-section-title"><i class="fas fa-medal"></i> Entregas recentes</h2>
                    <p class="dev-section-desc">Últimas tasks concluídas por você</p>
                </div>
            </div>
            <div class="dev-section-body">
                @forelse($recentCompleted as $task)
                    <a href="{{ route('company.tasks.show', $task) }}" class="dev-done-row">
                        <div class="dev-done-check"><i class="fas fa-check"></i></div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="dev-done-title">{{ Str::limit($task->title, 45) }}</div>
                            <div class="dev-done-meta">
                                {{ $task->project->name ?? '—' }}
                                @if($task->completed_at)
                                    · {{ $task->completed_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                        <i class="fas fa-chevron-right dev-task-chevron"></i>
                    </a>
                @empty
                    <div class="dev-empty">
                        <div class="dev-empty-icon"><i class="fas fa-flag-checkered"></i></div>
                        <div class="dev-empty-title">Nenhuma entrega recente</div>
                        <p class="dev-empty-text mb-0">Suas conclusões aparecerão aqui.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(15,23,42,0.06)';
    const textColor = isDark ? '#8b949e' : '#64748b';
    const fontFamily = "'Inter', system-ui, sans-serif";

    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = gridColor;
    Chart.defaults.font.family = fontFamily;

    const tooltipDefaults = {
        backgroundColor: isDark ? '#21262d' : '#1a202c',
        titleFont: { size: 13, weight: '600' },
        bodyFont: { size: 12 },
        padding: 12,
        cornerRadius: 10,
        displayColors: true,
        boxPadding: 4,
    };

    function makeGradient(ctx, c1, c2) {
        const g = ctx.createLinearGradient(0, 0, 0, 220);
        g.addColorStop(0, c1);
        g.addColorStop(1, c2);
        return g;
    }

    const hoursCtx = document.getElementById('hoursChart');
    if (hoursCtx) {
        const ctx = hoursCtx.getContext('2d');
        const grad = makeGradient(ctx, 'rgba(94,114,228,0.9)', 'rgba(157,141,247,0.5)');
        const todayIdx = @json(count($hoursChartLabels) - 1);

        new Chart(hoursCtx, {
            type: 'bar',
            data: {
                labels: @json($hoursChartLabels),
                datasets: [{
                    label: 'Horas',
                    data: @json($hoursChartData),
                    backgroundColor: @json($hoursChartData).map((_, i) => i === todayIdx ? grad : 'rgba(94,114,228,0.45)'),
                    hoverBackgroundColor: 'rgba(94,114,228,0.95)',
                    borderRadius: { topLeft: 10, topRight: 10, bottomLeft: 4, bottomRight: 4 },
                    borderSkipped: false,
                    maxBarThickness: 44,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            label: ctx => ' ' + ctx.parsed.y + ' horas registradas',
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { padding: 8, callback: v => v + 'h' },
                        border: { display: false },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { padding: 8, maxRotation: 0 },
                        border: { display: false },
                    }
                }
            }
        });
    }

    const statusCtx = document.getElementById('statusChart');
    if (statusCtx && @json(count($statusChartData)) > 0) {
        const total = @json(array_sum($statusChartData));
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: @json($statusChartLabels),
                datasets: [{
                    data: @json($statusChartData),
                    backgroundColor: @json($statusChartColors),
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, boxHeight: 10, padding: 14, usePointStyle: true, pointStyle: 'circle' }
                    },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            label: ctx => {
                                const pct = total ? Math.round((ctx.parsed / total) * 100) : 0;
                                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw(chart) {
                    const { width, height, ctx } = chart;
                    ctx.save();
                    ctx.font = 'bold 22px ' + fontFamily;
                    ctx.fillStyle = isDark ? '#e6edf3' : '#1a202c';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(total, width / 2, height / 2 - 6);
                    ctx.font = '11px ' + fontFamily;
                    ctx.fillStyle = textColor;
                    ctx.fillText('abertas', width / 2, height / 2 + 14);
                    ctx.restore();
                }
            }]
        });
    }

    @if($hoursByProject->isNotEmpty())
    const projectCtx = document.getElementById('projectChart');
    if (projectCtx) {
        const colors = ['#5e72e4','#2dce89','#11cdef','#9d8df7','#fb6340','#f5365c'];
        new Chart(projectCtx, {
            type: 'bar',
            data: {
                labels: @json($hoursByProject->map(fn ($r) => Str::limit($r->name ?? 'Sem projeto', 28))),
                datasets: [{
                    label: 'Horas',
                    data: @json($hoursByProject->pluck('total')->map(fn ($v) => round((float) $v, 2))),
                    backgroundColor: colors.map(c => c + 'cc'),
                    hoverBackgroundColor: colors,
                    borderRadius: 8,
                    maxBarThickness: 28,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: { label: ctx => ' ' + ctx.parsed.x + ' horas' }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { callback: v => v + 'h', padding: 8 },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false },
                        ticks: { padding: 8, font: { size: 12, weight: '500' } },
                        border: { display: false },
                    }
                }
            }
        });
    }
    @endif
})();
</script>
@endpush
