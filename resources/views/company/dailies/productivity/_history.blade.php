<div class="prod-chart-box wide mb-4">
    <div class="prod-chart-title"><i class="fas fa-history me-2"></i>Evolução mensal (últimos 6 meses)</div>
    <canvas id="chartHistory" height="80"></canvas>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="prod-section">
            <div class="prod-section-head">Produtividade mensal</div>
            <div class="prod-section-body p-0">
                <table class="prod-table">
                    <thead><tr><th>Mês</th><th>Horas</th><th>Concluídas</th><th>Produtividade</th></tr></thead>
                    <tbody>
                        @foreach($history['monthly'] as $m)
                        <tr>
                            <td>{{ $m['label'] }}</td>
                            <td>{{ number_format($m['hours'], 1, ',', '.') }}h</td>
                            <td>{{ $m['completed'] }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="prod-progress flex-grow-1"><div class="prod-progress-fill" style="width:{{ min(100, $m['productivity']) }}%"></div></div>
                                    <span class="small">{{ $m['productivity'] }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="prod-section">
            <div class="prod-section-head">Comparativo entre períodos</div>
            <div class="prod-section-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="prod-kpi">
                            <div class="prod-kpi-label">Produtividade anterior</div>
                            <div class="prod-kpi-value">{{ $comparatives['productivity_previous'] }}%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="prod-kpi success">
                            <div class="prod-kpi-label">Produtividade atual</div>
                            <div class="prod-kpi-value">{{ $comparatives['productivity_current'] }}%</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="prod-kpi">
                            <div class="prod-kpi-label">Entregas anteriores</div>
                            <div class="prod-kpi-value">{{ $comparatives['completed_previous'] }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="prod-kpi success">
                            <div class="prod-kpi-label">Entregas atuais</div>
                            <div class="prod-kpi-value">{{ $comparatives['completed_current'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
