<div class="row g-4">
    <div class="col-lg-4">
        <div class="prod-section h-100">
            <div class="prod-section-head">Metas da empresa</div>
            <div class="prod-section-body">
                <div class="prod-goal-ring mb-4">
                    <div class="prod-goal-pct">{{ $goals['achievement_pct'] }}%</div>
                    <div class="text-muted">colaboradores atingiram a meta</div>
                    <div class="small mt-2">{{ $goals['met_count'] }} de {{ $goals['total_count'] }}</div>
                </div>
                <div class="prod-progress mb-4" style="height:10px;">
                    <div class="prod-progress-fill" style="width:{{ $goals['achievement_pct'] }}%"></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1"><span>Meta diária (horas)</span><strong>{{ number_format($goals['daily_hours'], 1, ',', '.') }}h</strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Meta semanal (tasks)</span><strong>{{ $goals['weekly_tasks'] }}</strong></div>
                    <div class="d-flex justify-content-between small mb-1"><span>Meta mensal (taxa)</span><strong>{{ $goals['monthly_rate'] }}%</strong></div>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="fas fa-chart-line me-1"></i>
                    Previsão de atingimento: <strong>{{ $goals['forecast'] }}%</strong> da meta operacional.
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="prod-section">
            <div class="prod-section-head">Performance individual vs. meta</div>
            <div class="prod-section-body p-0">
                <div class="prod-table-wrap">
                    <table class="prod-table">
                        <thead>
                            <tr><th>Colaborador</th><th>Produtividade</th><th>Meta</th><th>Progresso</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($table as $row)
                            @php $pct = min(100, $row['productivity']); @endphp
                            <tr>
                                <td class="fw-semibold">{{ $row['name'] }}</td>
                                <td>{{ $row['productivity'] }}%</td>
                                <td>80%</td>
                                <td style="min-width:140px;">
                                    <div class="prod-progress"><div class="prod-progress-fill" style="width:{{ $pct }}%;{{ $pct < 60 ? 'background:#f5365c' : ($pct < 80 ? 'background:#fb6340' : '') }}"></div></div>
                                </td>
                                <td>
                                    @if($row['goal_met'])
                                        <span class="prod-badge high"><i class="fas fa-check"></i> Atingida</span>
                                    @else
                                        <span class="prod-badge attention"><i class="fas fa-exclamation"></i> Pendente</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="prod-chart-box wide mt-4">
    <div class="prod-chart-title">Evolução de metas ao longo do período</div>
    <canvas id="chartGoalsTab" height="70"></canvas>
</div>
