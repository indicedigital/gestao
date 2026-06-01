@php
    $primaryKpis = [
        ['label' => 'Produtividade média', 'value' => $kpis['avg_productivity'].'%', 'icon' => 'fa-chart-line', 'class' => 'primary', 'hint' => 'Meta operacional'],
        ['label' => 'Taxa de conclusão', 'value' => $kpis['completion_rate'].'%', 'icon' => 'fa-check-double', 'class' => 'success', 'hint' => $kpis['total_completed'].' concluídas'],
        ['label' => 'Atrasadas', 'value' => $kpis['total_overdue'], 'icon' => 'fa-clock', 'class' => 'danger', 'hint' => 'Taxa '.$kpis['delay_rate'].'%'],
        ['label' => 'Índice eficiência', 'value' => $kpis['efficiency_index'], 'icon' => 'fa-bolt', 'class' => 'info', 'hint' => 'Consistência '.$kpis['consistency_index'].'%'],
    ];
    $secondaryKpis = [
        ['label' => 'Colaboradores ativos', 'value' => $kpis['active_employees']],
        ['label' => 'Produtivos', 'value' => $kpis['productive_employees']],
        ['label' => 'Pendentes', 'value' => $kpis['total_pending']],
        ['label' => 'Acima da meta', 'value' => $kpis['above_goal']],
        ['label' => 'Abaixo da meta', 'value' => $kpis['below_goal']],
        ['label' => 'Tempo médio', 'value' => number_format($kpis['avg_execution_hours'], 1, ',', '.').'h'],
        ['label' => 'Tasks / pessoa', 'value' => $kpis['avg_tasks_per_employee']],
        ['label' => 'Horas totais', 'value' => number_format($kpis['total_hours'], 1, ',', '.').'h'],
        ['label' => 'Volume operacional', 'value' => $kpis['operational_volume']],
        ['label' => 'Crescimento', 'value' => ($kpis['growth_pct'] >= 0 ? '+' : '').$kpis['growth_pct'].'%', 'class' => $kpis['growth_pct'] >= 0 ? 'success' : 'danger'],
    ];
@endphp

<div class="prod-kpi-grid prod-kpi-grid--hero">
    @foreach($primaryKpis as $card)
    <div class="prod-kpi prod-kpi--hero {{ $card['class'] }}">
        <div class="prod-kpi-icon"><i class="fas {{ $card['icon'] }}"></i></div>
        <div class="prod-kpi-body">
            <div class="prod-kpi-label">{{ $card['label'] }}</div>
            <div class="prod-kpi-value">{{ $card['value'] }}</div>
            <div class="prod-kpi-sub">{{ $card['hint'] }}</div>
        </div>
    </div>
    @endforeach
</div>

@if($charts['trend'] != 0)
<div class="prod-trend-banner {{ $charts['trend'] >= 0 ? 'up' : 'down' }}">
    <i class="fas fa-{{ $charts['trend'] >= 0 ? 'arrow-trend-up' : 'arrow-trend-down' }}"></i>
    <span>{{ $charts['trend'] >= 0 ? 'Crescimento' : 'Queda' }} de <strong>{{ abs($charts['trend']) }}%</strong> vs. período anterior</span>
</div>
@endif

<details class="prod-kpi-expand">
    <summary><i class="fas fa-chevron-down me-1"></i> Ver todos os indicadores ({{ count($secondaryKpis) + 2 }})</summary>
    <div class="prod-kpi-grid mt-3">
        @foreach($secondaryKpis as $card)
        <div class="prod-kpi {{ $card['class'] ?? '' }}">
            <div class="prod-kpi-label">{{ $card['label'] }}</div>
            <div class="prod-kpi-value" style="font-size:20px;">{{ $card['value'] }}</div>
        </div>
        @endforeach
        <div class="prod-kpi success">
            <div class="prod-kpi-label">Melhor performance</div>
            <div class="prod-kpi-value" style="font-size:16px;">{{ $kpis['best_performer']['name'] ?? '—' }}</div>
            @if(isset($kpis['best_performer']))<div class="prod-kpi-sub">Score {{ $kpis['best_performer']['score'] }}</div>@endif
        </div>
        <div class="prod-kpi danger">
            <div class="prod-kpi-label">Menor performance</div>
            <div class="prod-kpi-value" style="font-size:16px;">{{ $kpis['worst_performer']['name'] ?? '—' }}</div>
            @if(isset($kpis['worst_performer']))<div class="prod-kpi-sub">Score {{ $kpis['worst_performer']['score'] }}</div>@endif
        </div>
    </div>
</details>

<div class="prod-charts-grid" data-prod-charts="overview">
    <div class="prod-chart-box wide">
        <div class="prod-chart-title"><i class="fas fa-chart-area me-2"></i>Evolução de produtividade</div>
        <div class="prod-chart-canvas"><canvas id="chartEvolution" height="80"></canvas></div>
    </div>
    <div class="prod-chart-box">
        <div class="prod-chart-title"><i class="fas fa-users me-2"></i>Por colaborador</div>
        <div class="prod-chart-canvas"><canvas id="chartByEmployee" height="200"></canvas></div>
    </div>
    <div class="prod-chart-box">
        <div class="prod-chart-title"><i class="fas fa-sitemap me-2"></i>Por equipe</div>
        <div class="prod-chart-canvas"><canvas id="chartByTeam" height="200"></canvas></div>
    </div>
    <div class="prod-chart-box">
        <div class="prod-chart-title"><i class="fas fa-tasks me-2"></i>Status operacional</div>
        <div class="prod-chart-canvas"><canvas id="chartTaskStatus" height="200"></canvas></div>
    </div>
    <div class="prod-chart-box">
        <div class="prod-chart-title"><i class="fas fa-bullseye me-2"></i>Evolução de metas</div>
        <div class="prod-chart-canvas"><canvas id="chartGoals" height="200"></canvas></div>
    </div>
    <div class="prod-chart-box">
        <div class="prod-chart-title"><i class="fas fa-chart-pie me-2"></i>Distribuição por status</div>
        <div class="prod-chart-canvas"><canvas id="chartStatusDist" height="200"></canvas></div>
    </div>
    <div class="prod-chart-box wide">
        <div class="prod-chart-title"><i class="fas fa-trophy me-2 text-warning"></i>Ranking Top 10</div>
        <div class="prod-chart-canvas"><canvas id="chartRanking" height="70"></canvas></div>
    </div>
</div>
