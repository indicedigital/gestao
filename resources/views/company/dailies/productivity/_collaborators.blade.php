<div class="prod-employee-card">
    <div>
        <div class="prod-section">
            <div class="prod-section-head">
                <span><i class="fas fa-search me-2"></i>Colaboradores</span>
            </div>
            <div class="prod-section-body p-2">
                <input type="text" class="form-control form-control-sm mb-2" placeholder="Buscar..." id="prod-emp-search">
                <div class="prod-employee-list" id="prod-emp-list">
                    @forelse($table as $row)
                    <a href="#"
                       class="prod-employee-link {{ ($selectedEmployee?->id ?? null) == $row['id'] ? 'active' : '' }}"
                       data-prod-emp-link="{{ $row['id'] }}"
                       data-name="{{ strtolower($row['name']) }}">
                        <span>{{ $row['name'] }}</span>
                        <span class="prod-badge {{ $row['status'] }}">{{ $row['score'] }}</span>
                    </a>
                    @empty
                    <p class="text-muted small p-2 mb-0">Nenhum colaborador encontrado.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div>
        @if($employeeDetail)
        <div class="prod-section mb-4">
            <div class="prod-section-head">
                <span>{{ $employeeDetail['employee']->name }}</span>
                <span class="prod-badge {{ $employeeDetail['level'] }}">#{{ $employeeDetail['rank'] }} · Score {{ $employeeDetail['score'] }}</span>
            </div>
            <div class="prod-section-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3 col-6"><div class="text-muted small">Cargo</div><strong>{{ $employeeDetail['position'] ?? '—' }}</strong></div>
                    <div class="col-md-3 col-6"><div class="text-muted small">Equipe</div><strong>{{ $employeeDetail['team'] }}</strong></div>
                    <div class="col-md-3 col-6"><div class="text-muted small">Entrada</div><strong>{{ $employeeDetail['hire_date'] ?? '—' }}</strong></div>
                    <div class="col-md-3 col-6"><div class="text-muted small">Status</div><strong>{{ ucfirst($employeeDetail['status']) }}</strong></div>
                </div>

                <div class="prod-kpi-grid mb-4">
                    @foreach([
                        ['Concluídas', $employeeDetail['completed']],
                        ['Pendentes', $employeeDetail['pending']],
                        ['Atrasadas', $employeeDetail['overdue']],
                        ['Produtividade', $employeeDetail['productivity_pct'].'%'],
                        ['Taxa conclusão', $employeeDetail['completion_rate'].'%'],
                        ['Tempo médio', number_format($employeeDetail['avg_execution_hours'], 1, ',', '.').'h'],
                        ['Consistência', $employeeDetail['consistency'].'%'],
                        ['Eficiência', $employeeDetail['efficiency']],
                        ['Dias produtivos', $employeeDetail['productive_days']],
                        ['Dias improdutivos', $employeeDetail['unproductive_days']],
                        ['Crescimento', ($employeeDetail['growth'] >= 0 ? '+' : '').$employeeDetail['growth'].'%'],
                        ['vs. Equipe', ($employeeDetail['vs_team'] >= 0 ? '+' : '').$employeeDetail['vs_team'].'%'],
                        ['Meta', $employeeDetail['goal_met'] ? 'Atingida' : 'Não atingida'],
                        ['Melhor dia', ($employeeDetail['best_day']['date'] ?? '—').' ('.($employeeDetail['best_day']['hours'] ?? 0).'h)'],
                        ['Pior dia', ($employeeDetail['worst_day']['date'] ?? '—').' ('.($employeeDetail['worst_day']['hours'] ?? 0).'h)'],
                        ['Horas totais', number_format($employeeDetail['hours'], 1, ',', '.').'h'],
                    ] as [$label, $val])
                    <div class="prod-kpi">
                        <div class="prod-kpi-label">{{ $label }}</div>
                        <div class="prod-kpi-value" style="font-size:18px;">{{ $val }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="prod-charts-grid">
                    <div class="prod-chart-box wide">
                        <div class="prod-chart-title">Evolução de produtividade</div>
                        <canvas id="chartEmpDaily" height="70"></canvas>
                    </div>
                    <div class="prod-chart-box">
                        <div class="prod-chart-title">Tipos de atividade</div>
                        <canvas id="chartEmpCategory" height="200"></canvas>
                    </div>
                    <div class="prod-chart-box">
                        <div class="prod-chart-title">Comparativo com média da equipe</div>
                        <canvas id="chartEmpVsTeam" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="prod-section">
            <div class="prod-section-body text-center py-5 text-muted">
                <i class="fas fa-user-chart fa-3x mb-3 opacity-25"></i>
                <p class="mb-0">Selecione um colaborador para ver o perfil detalhado.</p>
            </div>
        </div>
        @endif
    </div>
</div>
