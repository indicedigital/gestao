<div class="work-stats">
    <div class="work-stat-card">
        <div class="work-stat-label">Tasks abertas</div>
        <div class="work-stat-value">{{ $openTasks }}</div>
    </div>
    <div class="work-stat-card">
        <div class="work-stat-label">Concluídas</div>
        <div class="work-stat-value text-success">{{ $closedTasks }}</div>
    </div>
    <div class="work-stat-card">
        <div class="work-stat-label">SLA estourado</div>
        <div class="work-stat-value {{ $overdueTasks ? 'text-danger' : '' }}">{{ $overdueTasks }}</div>
    </div>
    @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProjectFinancial())
    <div class="work-stat-card">
        <div class="work-stat-label">Valor do contrato</div>
        <div class="work-stat-value" style="font-size:18px;">R$ {{ number_format($project->total_value, 2, ',', '.') }}</div>
    </div>
    @endif
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="work-panel">
            <div class="work-panel-header">Informações do projeto</div>
            <div class="work-panel-body">
                <div class="work-props">
                    <div>
                        <div class="work-prop-label">Status</div>
                        <div class="work-prop-value">
                            <span class="work-status-pill status-{{ $project->status === 'active' || $project->status === 'in_progress' ? 'in_progress' : ($project->status === 'completed' ? 'completed' : 'todo') }}">
                                {{ $project->statusLabel() }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="work-prop-label">Cliente</div>
                        <div class="work-prop-value">{{ $project->client->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="work-prop-label">Contrato</div>
                        <div class="work-prop-value">{{ $project->contract->name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="work-prop-label">Tipo operacional</div>
                        <div class="work-prop-value">{{ $project->categoryLabel() }}</div>
                    </div>
                    @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProjectFinancial())
                    <div>
                        <div class="work-prop-label">Tipo financeiro</div>
                        <div class="work-prop-value">{{ $project->type === 'fixed' ? 'Fechado' : 'Recorrente' }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="work-prop-label">Prazo</div>
                        <div class="work-prop-value {{ $project->deadline ? '' : 'empty' }}">
                            {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') : 'Não definido' }}
                        </div>
                    </div>
                </div>

                @if($project->description)
                <div class="task-section-title">Descrição</div>
                <p class="mb-0">{{ $project->description }}</p>
                @endif

                @if($project->scope)
                <div class="task-section-title">Escopo</div>
                <p class="mb-0" style="white-space:pre-line;">{{ $project->scope }}</p>
                @endif

                @if(is_array($project->deliverables) && count($project->deliverables))
                <div class="task-section-title">Entregas</div>
                <ul class="mb-0 ps-3">
                    @foreach($project->deliverables as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        @if($project->employees->count())
        <div class="work-panel">
            <div class="work-panel-header">
                <span>Equipe alocada</span>
                <a href="{{ route('company.projects.team', $project) }}" class="small text-decoration-none project-tab-link" data-tab="team">Gerenciar</a>
            </div>
            <div class="work-panel-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th class="ps-3">Nome</th><th>Função</th><th>Horas</th></tr></thead>
                        <tbody>
                            @foreach($project->employees as $member)
                                <tr>
                                    <td class="ps-3">{{ $member->name }}</td>
                                    <td>{{ $member->pivot->role ?? '—' }}</td>
                                    <td>{{ $member->pivot->allocated_hours ?? '—' }}h</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="work-panel">
            <div class="work-panel-header">Acesso rápido</div>
            <div class="work-panel-body">
                <a href="{{ route('company.projects.kanban', $project) }}" class="btn btn-primary w-100 mb-2 project-tab-link" data-tab="kanban">
                    <i class="fas fa-columns me-2"></i>Abrir Quadro Kanban
                </a>
                @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProjectDashboard())
                <a href="{{ route('company.projects.dashboard', $project) }}" class="btn btn-outline-secondary w-100 project-tab-link" data-tab="dashboard">
                    <i class="fas fa-chart-bar me-2"></i>Ver Dashboard
                </a>
                @endif
            </div>
        </div>

        @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProjectFinancial())
        <div class="work-panel">
            <div class="work-panel-header">Financeiro</div>
            <div class="work-panel-body">
                <div class="work-props" style="grid-template-columns:1fr;">
                    <div>
                        <div class="work-prop-label">Custo</div>
                        <div class="work-prop-value">R$ {{ number_format($project->cost ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="work-prop-label">Lucro</div>
                        <div class="work-prop-value text-success">R$ {{ number_format($project->profit ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="work-prop-label">Margem</div>
                        <div class="work-prop-value">{{ number_format($project->profit_margin_percent ?? 0, 2, ',', '.') }}%</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
