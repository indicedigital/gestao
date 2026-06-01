@extends('layouts.app')

@section('title', 'Quadro — '.$project->name)

@section('content')
@php
    $allTasks = collect($columns)->flatMap(fn ($col) => $col['tasks']);
    $totalTasks = $allTasks->count();
    $openTasks = $allTasks->where('status', '!=', 'completed')->count();
    $completedTasks = $allTasks->where('status', 'completed')->count();
    $homologTasks = $allTasks->where('status', 'homologation')->count();
    $waitingTasks = $allTasks->where('status', 'waiting_client')->count();
    $categoryLabels = \App\Models\Task::CATEGORIES;
@endphp

<div class="portal-shell py-2">

    <nav class="portal-breadcrumb" aria-label="Navegação">
        <a href="{{ route('portal.dashboard') }}"><i class="fas fa-home me-1"></i>Portal</a>
        <span class="sep">/</span>
        <span class="current">{{ $project->name }}</span>
    </nav>

    <div class="portal-hero">
        <div>
            <h1 class="portal-hero-title">{{ $project->name }}</h1>
            <p class="portal-hero-sub">
                Quadro Kanban · {{ $totalTasks }} solicitação(ões)
                @if($project->client)
                    · {{ $project->client->name }}
                @endif
            </p>
        </div>
        <div class="portal-hero-actions">
            <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar ao portal
            </a>
            <a href="{{ route('portal.tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nova solicitação
            </a>
        </div>
    </div>

    <div class="portal-kpi-grid">
        <div class="portal-kpi portal-kpi-accent-primary">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-layer-group"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $totalTasks }}</div>
            <div class="portal-kpi-label">Total no projeto</div>
        </div>
        <div class="portal-kpi portal-kpi-accent-info">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-spinner"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $openTasks }}</div>
            <div class="portal-kpi-label">Em andamento</div>
            <div class="portal-kpi-sub">Abertas no quadro</div>
        </div>
        <div class="portal-kpi portal-kpi-accent-warning">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-clipboard-check"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $homologTasks }}</div>
            <div class="portal-kpi-label">Homologação</div>
            <div class="portal-kpi-sub">Aguardam sua aprovação</div>
        </div>
        <div class="portal-kpi portal-kpi-accent-success">
            <div class="portal-kpi-top">
                <div class="portal-kpi-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="portal-kpi-value">{{ $completedTasks }}</div>
            <div class="portal-kpi-label">Concluídas</div>
            @if($waitingTasks)
            <div class="portal-kpi-sub">{{ $waitingTasks }} aguardando você</div>
            @endif
        </div>
    </div>

    @if($homologTasks)
    <div class="portal-alert-banner portal-alert-homolog">
        <div class="portal-alert-icon"><i class="fas fa-star"></i></div>
        <div class="flex-grow-1">
            <strong>{{ $homologTasks }} entrega(s) pronta(s) para homologação</strong>
            <div class="small text-muted">Clique no card na coluna Homologação para revisar e aprovar.</div>
        </div>
    </div>
    @endif

    <div class="portal-kanban-toolbar">
        <span class="portal-kanban-badge">
            <i class="fas fa-eye"></i> Visualização somente leitura
        </span>
        <span class="small text-muted">Clique em um card para ver detalhes, comentários e anexos.</span>
    </div>

    <div class="kanban-board portal-kanban-board">
        @foreach($columns as $column)
            <div class="kanban-column col-head-{{ $column['key'] }}" data-status="{{ $column['key'] }}">
                <div class="kanban-col-head">
                    <span class="kanban-col-badge">{{ $column['label'] }}</span>
                    <span class="kanban-col-count">{{ $column['tasks']->count() }}</span>
                </div>
                <div class="kanban-col-body">
                    @forelse($column['tasks'] as $task)
                        @php
                            $slaClass = $task->isOverdue() ? 'overdue' : ($task->slaAlertLevel() === 'warning' ? 'sla-warning' : ($task->slaAlertLevel() === 'info' ? 'sla-info' : ''));
                        @endphp
                        <a href="{{ route('portal.tasks.show', $task) }}" class="kanban-task {{ $slaClass }} text-decoration-none d-block">
                            <span class="kanban-task-title">{{ $task->title }}</span>
                            <div class="kanban-task-meta">
                                <span class="kanban-priority {{ $task->priority }}">{{ $task->priority }}</span>
                                @if($task->category)
                                    <span title="Categoria">
                                        <i class="fas fa-tag me-1"></i>{{ $categoryLabels[$task->category] ?? $task->category }}
                                    </span>
                                @endif
                                @if($task->assignee)
                                    <span><i class="fas fa-user me-1"></i>{{ Str::limit($task->assignee->name, 14) }}</span>
                                @endif
                                @if($task->sla_deadline)
                                    <span class="{{ $task->isOverdue() ? 'text-danger' : '' }}">
                                        <i class="fas fa-clock me-1"></i>{{ $task->sla_deadline->format('d/m') }}
                                    </span>
                                @endif
                                @if($task->comments_count)
                                    <span><i class="fas fa-comment me-1"></i>{{ $task->comments_count }}</span>
                                @endif
                                @if($task->attachments_count)
                                    <span><i class="fas fa-paperclip me-1"></i>{{ $task->attachments_count }}</span>
                                @endif
                            </div>
                            @if($task->subtasks->count())
                                @foreach($task->subtasks->take(2) as $st)
                                    <div class="subtask-mini">{{ Str::limit($st->title, 28) }}</div>
                                @endforeach
                                @if($task->subtasks->count() > 2)
                                    <div class="subtask-mini text-muted">+{{ $task->subtasks->count() - 2 }} subtask(s)</div>
                                @endif
                            @endif
                            @if($task->status === 'homologation')
                                <div class="mt-2">
                                    <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Aprovar</span>
                                </div>
                            @elseif($task->status === 'waiting_client')
                                <div class="mt-2">
                                    <span class="badge bg-info text-dark"><i class="fas fa-reply me-1"></i>Sua resposta</span>
                                </div>
                            @endif
                        </a>
                    @empty
                        <div class="portal-kanban-empty-col">
                            <i class="fas fa-inbox"></i>
                            Nenhum item
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
