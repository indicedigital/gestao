@extends('layouts.app')

@section('title', $task->title)

@section('content')
@php
    $statusLabels = \App\Models\Task::STATUSES;
    $categoryLabels = \App\Models\Task::CATEGORIES;
    $priorityLabels = \App\Models\Task::PRIORITIES;
@endphp

<div class="portal-shell portal-task-wrap py-2">

    <nav class="portal-breadcrumb" aria-label="Navegação">
        <a href="{{ route('portal.dashboard') }}"><i class="fas fa-home me-1"></i>Portal</a>
        <span class="sep">/</span>
        <a href="{{ route('portal.kanban', $task->project) }}">{{ $task->project->name }}</a>
        <span class="sep">/</span>
        <span class="current">{{ Str::limit($task->title, 48) }}</span>
    </nav>

    @if($task->status === 'homologation')
        <div class="portal-homolog-card">
            <div>
                <strong><i class="fas fa-clipboard-check me-2 text-warning"></i>Entrega pronta para homologação</strong>
                <p>Revise a solicitação abaixo. Se estiver de acordo, aprove para concluir a entrega.</p>
            </div>
            <form action="{{ route('portal.tasks.approve', $task) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-2"></i>Aprovar homologação
                </button>
            </form>
        </div>
    @endif

    @if($task->status === 'waiting_client')
        <div class="portal-alert-banner portal-alert-waiting mb-3">
            <div class="portal-alert-icon"><i class="fas fa-user-clock"></i></div>
            <div class="flex-grow-1">
                <strong>A equipe aguarda sua resposta</strong>
                <div class="small text-muted">Envie um comentário ou anexo abaixo para dar continuidade.</div>
            </div>
        </div>
    @endif

    <div class="task-detail-layout">
        {{-- Coluna principal --}}
        <div class="task-detail-main">
            <div class="task-type-badge">
                <i class="fas fa-ticket-alt"></i> Solicitação
            </div>
            <h1 class="task-detail-title">{{ $task->title }}</h1>

            <div class="portal-task-meta-bar">
                <span class="work-status-pill status-{{ $task->status }}">
                    {{ $statusLabels[$task->status] ?? $task->status }}
                </span>
                <span class="kanban-priority {{ $task->priority }}">{{ $task->priority }}</span>
                @if($task->isOverdue())
                    <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>SLA vencido</span>
                @endif
            </div>

            <div class="task-actions-row">
                <a href="{{ route('portal.kanban', $task->project) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-columns me-1"></i>Quadro do projeto
                </a>
                <a href="{{ route('portal.tasks.create', ['project_id' => $task->project_id]) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i>Nova solicitação
                </a>
            </div>

            <div class="work-props">
                <div>
                    <div class="work-prop-label">Projeto</div>
                    <div class="work-prop-value">{{ $task->project->name }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Categoria</div>
                    <div class="work-prop-value">{{ $categoryLabels[$task->category] ?? $task->category ?? '—' }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Prioridade</div>
                    <div class="work-prop-value">{{ $priorityLabels[$task->priority] ?? $task->priority }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Responsável</div>
                    <div class="work-prop-value {{ $task->assignee ? '' : 'empty' }}">{{ $task->assignee->name ?? 'A definir' }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Aberta em</div>
                    <div class="work-prop-value">{{ $task->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Prazo (SLA)</div>
                    <div class="work-prop-value {{ $task->isOverdue() ? 'text-danger' : '' }}">
                        @if($task->sla_deadline)
                            {{ $task->sla_deadline->format('d/m/Y H:i') }}
                        @else
                            <span class="empty">Sem prazo definido</span>
                        @endif
                    </div>
                </div>
                @if($task->completed_at)
                <div>
                    <div class="work-prop-label">Concluída em</div>
                    <div class="work-prop-value text-success">{{ $task->completed_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>

            <div class="task-section-title">Descrição</div>
            <p class="mb-0" style="color:var(--text-secondary); line-height:1.65;">
                {{ $task->description ?: 'Nenhuma descrição informada.' }}
            </p>

            @if($task->subtasks->count())
            <div class="task-section-title">Subtarefas ({{ $task->subtasks->count() }})</div>
            @foreach($task->subtasks as $subtask)
                <div class="subtask-row">
                    <div>
                        <strong style="color:var(--text-primary);">{{ $subtask->title }}</strong>
                        <div class="small text-muted">
                            {{ \App\Models\Subtask::STATUSES[$subtask->status] ?? $subtask->status }}
                        </div>
                    </div>
                    <span class="work-status-pill status-{{ $subtask->status === 'completed' ? 'completed' : 'in_progress' }}" style="font-size:10px;">
                        {{ \App\Models\Subtask::STATUSES[$subtask->status] ?? $subtask->status }}
                    </span>
                </div>
            @endforeach
            @endif

            <div class="task-section-title">
                <i class="fas fa-paperclip me-2"></i>Anexos
                @if($task->attachments->count())
                    <span class="badge bg-secondary ms-1">{{ $task->attachments->count() }}</span>
                @endif
            </div>

            @if($task->attachments->count())
            <div class="portal-attachment-grid mb-0">
                @foreach($task->attachments as $att)
                    <a href="{{ route('portal.tasks.attachments.download', [$task, $att]) }}" class="attachment-link">
                        <i class="fas fa-file-alt text-primary"></i>
                        <span class="text-truncate">{{ $att->filename }}</span>
                        <i class="fas fa-download ms-auto text-muted" style="font-size:11px;"></i>
                    </a>
                @endforeach
            </div>
            @else
                <p class="text-muted small mb-0">Nenhum anexo ainda.</p>
            @endif

            <div class="portal-attachment-upload">
                <form action="{{ route('portal.tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <label class="form-label small fw-semibold">Enviar arquivo</label>
                    <div class="input-group input-group-sm">
                        <input type="file" name="attachment" class="form-control" required
                               accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.zip,.txt">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-upload me-1"></i>Enviar
                        </button>
                    </div>
                    <div class="form-text">PDF, imagens, documentos ou ZIP — máx. 10 MB</div>
                </form>
            </div>
        </div>

        {{-- Sidebar: comentários --}}
        <div class="task-detail-sidebar">
            <div class="task-activity-header">
                <span><i class="fas fa-comments me-2"></i>Comentários</span>
                <span class="text-muted small">{{ $task->publicComments->count() }}</span>
            </div>
            <div class="task-activity-feed">
                @forelse($task->publicComments->sortByDesc('created_at') as $comment)
                    @php
                        $name = $comment->user->name ?? 'Usuário';
                        $commentInitials = collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    @endphp
                    <div class="portal-comment-item">
                        <div class="portal-comment-avatar">{{ $commentInitials }}</div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="portal-comment-head">
                                <span class="portal-comment-author">{{ $name }}</span>
                                <span class="portal-comment-date">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="portal-comment-body">{!! nl2br(e($comment->body)) !!}</div>
                        </div>
                    </div>
                @empty
                    <div class="portal-empty py-4">
                        <i class="fas fa-comment-dots d-block"></i>
                        <p class="text-muted small mb-0">Nenhum comentário ainda.<br>Seja o primeiro a interagir.</p>
                    </div>
                @endforelse
            </div>
            <div class="task-comment-box">
                <form action="{{ route('portal.tasks.comments.store', $task) }}" method="POST">
                    @csrf
                    <textarea name="body" class="form-control mb-2" rows="3" placeholder="Escreva um comentário para a equipe..." required></textarea>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-paper-plane me-1"></i>Enviar comentário
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
