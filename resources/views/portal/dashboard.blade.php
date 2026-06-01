@extends('layouts.app')

@section('title', 'Portal do Cliente')

@section('content')
@php
    $openCount = (int) ($stats->open_count ?? 0);
    $completedCount = (int) ($stats->completed_count ?? 0);
    $homologCount = (int) ($stats->homologation_count ?? 0);
    $waitingCount = (int) ($stats->waiting_client_count ?? 0);
    $overdueCount = (int) ($stats->overdue_count ?? 0);
    $inProgressCount = (int) ($stats->in_progress_count ?? 0);
    $statusLabels = \App\Models\Task::STATUSES;
@endphp

<div class="portal-shell py-2">

    {{-- Hero --}}
    <div class="portal-hero">
        <div>
            <h1 class="portal-hero-title">
                Olá, {{ auth()->user()->name }}!
            </h1>
            <p class="portal-hero-sub">
                @if($client)
                    {{ $client->name }} · Acompanhe projetos, solicitações e homologações
                @else
                    Acompanhe seus projetos e solicitações
                @endif
            </p>
        </div>
        <div class="portal-hero-actions">
            <a href="{{ route('portal.tasks.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova solicitação
            </a>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="portal-kpi-grid">
        <div class="portal-kpi portal-kpi-accent-primary">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-tasks"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $openCount }}</div>
            <div class="portal-kpi-label">Solicitações abertas</div>
            <div class="portal-kpi-sub">{{ $inProgressCount }} em progresso</div>
        </div>
        <div class="portal-kpi portal-kpi-accent-success">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $completionRate }}%</div>
            <div class="portal-kpi-label">Taxa de conclusão</div>
            <div class="portal-kpi-sub">{{ $completedCount }} concluída(s)</div>
        </div>
        <div class="portal-kpi portal-kpi-accent-warning">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $homologCount }}</div>
            <div class="portal-kpi-label">Aguardando homologação</div>
            <div class="portal-kpi-sub">Sua aprovação necessária</div>
        </div>
        <div class="portal-kpi portal-kpi-accent-info">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-folder-open"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $projects->count() }}</div>
            <div class="portal-kpi-label">Projetos ativos</div>
            <div class="portal-kpi-sub">{{ $waitingCount }} aguardando você</div>
        </div>
    </div>

    {{-- Alertas de ação --}}
    @if($homologationTasks->count())
    <div class="portal-alert-banner portal-alert-homolog">
        <div class="portal-alert-icon"><i class="fas fa-exclamation-circle"></i></div>
        <div class="flex-grow-1">
            <strong>{{ $homologationTasks->count() }} solicitação(ões) aguardando homologação</strong>
            <div class="small text-muted">Revise e aprove para concluir as entregas.</div>
        </div>
        <a href="#homolog-section" class="btn btn-warning btn-sm text-dark">Revisar agora</a>
    </div>
    @endif

    @if($waitingTasks->count())
    <div class="portal-alert-banner portal-alert-waiting">
        <div class="portal-alert-icon"><i class="fas fa-user-clock"></i></div>
        <div class="flex-grow-1">
            <strong>{{ $waitingTasks->count() }} aguardando sua resposta</strong>
            <div class="small text-muted">A equipe precisa de informações ou validação sua.</div>
        </div>
    </div>
    @endif

    <div class="portal-main-grid">
        <div class="portal-main-col">
            {{-- Projetos --}}
            <div class="portal-panel">
                <div class="portal-panel-head">
                    <h6><i class="fas fa-project-diagram me-2 text-primary"></i>Meus projetos</h6>
                    <span class="badge bg-secondary">{{ $projects->count() }}</span>
                </div>
                <div class="portal-panel-body">
                    @if($projects->count())
                    <div class="portal-project-grid">
                        @foreach($projects as $project)
                            @php
                                $pct = $project->total_tasks > 0
                                    ? round(($project->completed_tasks / $project->total_tasks) * 100)
                                    : 0;
                            @endphp
                            <div class="portal-project-card">
                                <div class="portal-project-name">{{ $project->name }}</div>
                                <div class="portal-project-stats">
                                    <span><i class="fas fa-circle-notch"></i> {{ $project->open_tasks }} abertas</span>
                                    <span><i class="fas fa-check"></i> {{ $project->completed_tasks }} concluídas</span>
                                    @if($project->homologation_tasks)
                                    <span class="text-warning"><i class="fas fa-star"></i> {{ $project->homologation_tasks }} homologação</span>
                                    @endif
                                </div>
                                @if($project->total_tasks > 0)
                                <div class="portal-progress">
                                    <div class="portal-progress-bar" style="width:{{ $pct }}%"></div>
                                </div>
                                <div class="small text-muted mb-2">{{ $pct }}% concluído</div>
                                @endif
                                <div class="portal-project-actions">
                                    <a href="{{ route('portal.kanban', $project) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-columns me-1"></i>Quadro
                                    </a>
                                    <a href="{{ route('portal.tasks.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-plus me-1"></i>Solicitar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @else
                    <div class="portal-empty">
                        <i class="fas fa-folder-open d-block"></i>
                        <p class="text-muted mb-3">Nenhum projeto vinculado à sua conta.</p>
                        <a href="{{ route('portal.tasks.create') }}" class="btn btn-primary btn-sm">Enviar primeira solicitação</a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tasks recentes --}}
            <div class="portal-panel">
                <div class="portal-panel-head">
                    <h6><i class="fas fa-history me-2"></i>Solicitações recentes</h6>
                    <a href="{{ route('portal.tasks.create') }}" class="btn btn-sm btn-primary">Nova</a>
                </div>
                <div class="portal-panel-body flush">
                    @forelse($recentTasks as $task)
                        <div class="portal-task-row">
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('portal.tasks.show', $task) }}" class="portal-task-title d-block text-truncate">{{ $task->title }}</a>
                                <div class="portal-task-meta">
                                    {{ $task->project->name ?? '—' }}
                                    · <span class="portal-status-badge status-{{ $task->status }}">{{ $statusLabels[$task->status] ?? $task->status }}</span>
                                    · {{ $task->created_at->format('d/m/Y') }}
                                </div>
                            </div>
                            <span class="portal-priority {{ $task->priority }}">{{ $task->priority }}</span>
                        </div>
                    @empty
                        <div class="portal-empty py-4">
                            <p class="text-muted mb-0">Nenhuma solicitação ainda.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="portal-sidebar-stack">
            {{-- Distribuição por status --}}
            <div class="portal-panel">
                <div class="portal-panel-head">
                    <h6><i class="fas fa-chart-pie me-2"></i>Status geral</h6>
                </div>
                <div class="portal-panel-body">
                    @php $hasStatus = false; @endphp
                    <div class="portal-status-pills">
                        @foreach($statusLabels as $key => $label)
                            @if(($byStatus[$key] ?? 0) > 0)
                                @php $hasStatus = true; @endphp
                                <span class="portal-status-pill">
                                    {{ $label }}
                                    <span class="count">{{ $byStatus[$key] }}</span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                    @if(!$hasStatus)
                        <p class="text-muted small mb-0 text-center py-2">Sem solicitações registradas.</p>
                    @endif
                </div>
            </div>

            {{-- Homologação --}}
            @if($homologationTasks->count())
            <div class="portal-panel" id="homolog-section">
                <div class="portal-panel-head">
                    <h6><i class="fas fa-clipboard-check me-2 text-warning"></i>Homologar</h6>
                </div>
                <div class="portal-panel-body">
                    @foreach($homologationTasks as $task)
                        <div class="portal-homolog-item">
                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('portal.tasks.show', $task) }}" class="fw-semibold text-decoration-none">{{ Str::limit($task->title, 40) }}</a>
                                <div class="small text-muted">{{ $task->project->name ?? '' }}</div>
                            </div>
                            <form action="{{ route('portal.tasks.approve', $task) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-check me-1"></i>Aprovar
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Aguardando cliente --}}
            @if($waitingTasks->count())
            <div class="portal-panel">
                <div class="portal-panel-head">
                    <h6><i class="fas fa-reply me-2 text-info"></i>Aguardando você</h6>
                </div>
                <div class="portal-panel-body flush">
                    @foreach($waitingTasks as $task)
                        <div class="portal-task-row">
                            <div>
                                <a href="{{ route('portal.tasks.show', $task) }}" class="portal-task-title">{{ Str::limit($task->title, 35) }}</a>
                                <div class="portal-task-meta">{{ $task->project->name ?? '' }}</div>
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Ajuda rápida --}}
            <div class="portal-panel">
                <div class="portal-panel-head">
                    <h6><i class="fas fa-info-circle me-2"></i>Como funciona</h6>
                </div>
                <div class="portal-panel-body">
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-2">Use <strong>Nova solicitação</strong> para reportar bugs, melhorias ou pedidos.</li>
                        <li class="mb-2">Acompanhe o andamento no <strong>Quadro</strong> de cada projeto.</li>
                        <li class="mb-0">Quando uma entrega estiver pronta, você receberá para <strong>homologar</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
