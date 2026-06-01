@extends('layouts.app')

@section('title', $task->title)

@section('content')
@include('company.tasks._helpers')
<div class="container-fluid py-4">
    <div class="work-breadcrumb mb-3">
        <a href="{{ route('company.projects.index') }}">Projetos</a>
        <span class="sep">/</span>
        <a href="{{ route('company.projects.kanban', $task->project) }}">{{ $task->project->name }}</a>
        <span class="sep">/</span>
        <span>{{ Str::limit($task->title, 40) }}</span>
    </div>

    <div class="task-detail-layout">
        {{-- Coluna principal --}}
        <div class="task-detail-main">
            <div class="task-type-badge">
                <i class="fas fa-check-square"></i> Tarefa
            </div>
            <h1 class="task-detail-title">{{ $task->title }}</h1>

            <div class="task-actions-row">
                <a href="{{ route('company.projects.kanban', $task->project) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-columns me-1"></i>Quadro
                </a>
                @if($canEdit)
                <a href="{{ route('company.tasks.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
                @endif
                @if($canDelete)
                <form action="{{ route('company.tasks.destroy', $task) }}" method="POST" class="d-inline delete-form" data-message="Remover esta task permanentemente?">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Excluir</button>
                </form>
                @endif
            </div>

            <div class="work-props">
                <div>
                    <div class="work-prop-label">Status</div>
                    <div class="work-prop-value">
                        <span class="work-status-pill status-{{ $task->status }}">{{ \App\Models\Task::STATUSES[$task->status] ?? $task->status }}</span>
                        @if($task->isOverdue())<span class="badge bg-danger ms-1">SLA</span>@endif
                    </div>
                </div>
                <div>
                    <div class="work-prop-label">Prioridade</div>
                    <div class="work-prop-value"><span class="kanban-priority {{ $task->priority }}">{{ $task->priority }}</span></div>
                </div>
                <div>
                    <div class="work-prop-label">Categoria</div>
                    <div class="work-prop-value">{{ \App\Models\Task::CATEGORIES[$task->category] ?? $task->category }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Responsável</div>
                    <div class="work-prop-value {{ $task->assignee ? '' : 'empty' }}">{{ $task->assignee->name ?? 'Vazio' }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Solicitante</div>
                    <div class="work-prop-value">{{ $task->requester_name ?? '—' }} ({{ $task->requester_type === 'client' ? 'Cliente' : 'Interno' }})</div>
                </div>
                <div>
                    <div class="work-prop-label">Canal</div>
                    <div class="work-prop-value">{{ $channelLabels[$task->creation_channel] ?? $task->creation_channel }}</div>
                </div>
                <div>
                    <div class="work-prop-label">Horas est. / real</div>
                    <div class="work-prop-value">{{ $task->estimated_hours ?? '—' }}h / {{ number_format($task->actual_hours, 1, ',', '.') }}h</div>
                </div>
                <div>
                    <div class="work-prop-label">SLA</div>
                    <div class="work-prop-value {{ $task->isOverdue() ? 'text-danger' : '' }}">
                        @if($task->sla_deadline)
                            {{ $task->sla_hours }}h — {{ $task->sla_deadline->format('d/m/Y H:i') }}
                        @else
                            <span class="empty">Vazio</span>
                        @endif
                    </div>
                </div>
                @if($task->completed_at)
                <div>
                    <div class="work-prop-label">Concluída em</div>
                    <div class="work-prop-value">{{ $task->completed_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>

            <div class="task-section-title">Descrição</div>
            <p class="mb-0" style="color:var(--text-secondary);">{{ $task->description ?: 'Adicione uma descrição para esta tarefa.' }}</p>

            <div class="task-section-title">Subtasks ({{ $task->subtasks->count() }})</div>
            @forelse($task->subtasks as $subtask)
                <div class="subtask-row">
                    <div>
                        <strong style="color:var(--text-primary);">{{ $subtask->title }}</strong>
                        <div class="small text-muted">
                            {{ $subtask->assignee->name ?? 'Sem responsável' }}
                            · {{ \App\Models\Subtask::STATUSES[$subtask->status] ?? $subtask->status }}
                            · {{ number_format($subtask->hours_spent, 1, ',', '.') }}h
                            @if($subtask->due_date) · {{ $subtask->due_date->format('d/m/Y') }}@endif
                        </div>
                    </div>
                    @if($canManageSubtasks)
                    <form action="{{ route('company.tasks.subtasks.update', [$task, $subtask]) }}" method="POST">
                        @csrf @method('PUT')
                        <select name="status" class="form-select form-select-sm" style="min-width:130px;" onchange="this.form.submit()">
                            @foreach(\App\Models\Subtask::STATUSES as $k => $v)
                                <option value="{{ $k }}" @selected($subtask->status === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                </div>
            @empty
                <p class="text-muted small">Nenhuma subtask.</p>
            @endforelse

            @if($canManageSubtasks)
            <form action="{{ route('company.tasks.subtasks.store', $task) }}" method="POST" class="row g-2 mt-2">
                @csrf
                <div class="col-md-4"><input type="text" name="title" class="form-control form-control-sm" placeholder="Nova subtask" required></div>
                <div class="col-md-3">
                    <select name="assignee_id" class="form-select form-select-sm">
                        <option value="">Responsável</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><input type="date" name="due_date" class="form-control form-control-sm"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary w-100">Adicionar</button></div>
            </form>
            @endif

            <div class="task-section-title">Anexos</div>
            @forelse($task->attachments as $att)
                <a href="{{ route('company.tasks.attachments.download', [$task, $att]) }}" class="attachment-link">
                    <i class="fas fa-paperclip"></i>
                    <span>{{ $att->filename }}</span>
                    <small class="text-muted ms-auto">{{ number_format($att->size / 1024, 0) }} KB</small>
                </a>
            @empty
                <p class="text-muted small">Nenhum anexo.</p>
            @endforelse
            <form action="{{ route('company.tasks.attachments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                @csrf
                <div class="input-group input-group-sm">
                    <input type="file" name="attachment" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.zip,.txt">
                    <button type="submit" class="btn btn-outline-primary">Enviar</button>
                </div>
            </form>
        </div>

        {{-- Sidebar: Activity --}}
        <div class="task-detail-sidebar">
            <div class="task-activity-header">
                <span>Activity</span>
                <span class="text-muted small">{{ $task->comments->count() + $task->histories->count() }}</span>
            </div>
            <div class="task-activity-feed">
                @foreach($task->comments->sortByDesc('created_at') as $comment)
                    <div class="task-activity-item">
                        <div class="task-activity-user">{{ $comment->user->name ?? 'Usuário' }}</div>
                        <div class="task-activity-time">
                            {{ $comment->created_at->format('d/m/Y H:i') }} — comentário
                            @if($comment->is_internal)
                                <span class="badge bg-secondary">Interno</span>
                            @endif
                        </div>
                        <div class="task-activity-text">{!! nl2br(e($comment->body)) !!}</div>
                        @if(!empty($comment->mentions))
                            <div class="small text-primary mt-1"><i class="fas fa-at"></i> {{ collect($comment->mentions)->pluck('name')->implode(', ') }}</div>
                        @endif
                    </div>
                @endforeach

                @foreach($task->histories->sortByDesc('created_at') as $history)
                    <div class="task-activity-item">
                        <div class="task-activity-user">{{ $history->user->name ?? 'Sistema' }}</div>
                        <div class="task-activity-time">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                        <div class="task-activity-text">
                            {{ $historyLabels[$history->action] ?? $history->action }}
                            @if($history->field && $history->old_value !== $history->new_value)
                                <br><span class="text-muted">{{ $history->old_value ?: '—' }} → {{ $history->new_value ?: '—' }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($task->comments->isEmpty() && $task->histories->isEmpty())
                    <p class="text-muted small text-center py-4">Nenhuma atividade ainda.</p>
                @endif
            </div>
            <div class="task-comment-box">
                <form action="{{ route('company.tasks.comments.store', $task) }}" method="POST">
                    @csrf
                    <textarea name="body" class="form-control mb-2" rows="3" placeholder="Escreva um comentário..." required></textarea>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_internal" value="1" id="comment-internal">
                        <label class="form-check-label small text-muted" for="comment-internal">Comentário interno (não visível ao cliente)</label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Enviar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
