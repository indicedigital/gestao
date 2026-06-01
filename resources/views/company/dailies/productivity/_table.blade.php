<div class="prod-section">
    <div class="prod-section-head">
        <span><i class="fas fa-table me-2"></i>Tabela analítica</span>
        <span class="text-muted small">{{ count($table) }} colaborador(es)</span>
    </div>
    <div class="prod-section-body p-0">
        <div class="prod-table-wrap">
            <table class="prod-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Colaborador</th>
                        <th>Equipe</th>
                        <th>Score</th>
                        <th>Produtiv.</th>
                        <th>Eficiência</th>
                        <th>Concl.</th>
                        <th>Pend.</th>
                        <th>Atras.</th>
                        <th>Taxa</th>
                        <th>Tempo méd.</th>
                        <th>Evolução</th>
                        <th>Status</th>
                        <th>Meta</th>
                        <th>Alertas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($table as $row)
                    <tr>
                        <td>{{ $row['rank'] }}</td>
                        <td>
                            <a href="{{ route('company.dailies.productivity', array_merge(request()->query(), ['tab' => 'collaborators', 'selected_employee_id' => $row['id']])) }}" class="text-decoration-none fw-semibold">
                                {{ $row['name'] }}
                            </a>
                        </td>
                        <td>{{ $row['team'] }}</td>
                        <td><strong>{{ $row['score'] }}</strong></td>
                        <td>{{ $row['productivity'] }}%</td>
                        <td>{{ $row['efficiency'] }}</td>
                        <td class="text-success">{{ $row['completed'] }}</td>
                        <td class="text-warning">{{ $row['pending'] }}</td>
                        <td class="text-danger">{{ $row['overdue'] }}</td>
                        <td>{{ $row['completion_rate'] }}%</td>
                        <td>{{ number_format($row['avg_hours'], 1, ',', '.') }}h</td>
                        <td class="prod-trend {{ $row['trend'] }}">{{ $row['growth'] >= 0 ? '+' : '' }}{{ $row['growth'] }}%</td>
                        <td><span class="prod-badge {{ $row['status'] }}">{{ ucfirst($row['status']) }}</span></td>
                        <td>@if($row['goal_met'])<i class="fas fa-check-circle text-success"></i>@else<i class="fas fa-times-circle text-danger"></i>@endif</td>
                        <td>@if($row['alerts'] > 0)<span class="badge bg-danger">{{ $row['alerts'] }}</span>@else—@endif</td>
                    </tr>
                    @empty
                    <tr><td colspan="15" class="text-center py-4 text-muted">Nenhum dado para os filtros selecionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
