@extends('layouts.app')

@section('title', 'Editar Task')

@section('content')
@php
    $fmtDateTime = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '';
    $oldSubtasks = old('subtasks', [['title' => '', 'assignee_id' => '', 'due_date' => '']]);
@endphp

<div class="container-fluid py-4 task-create-page work-shell">

    @if($contextProject)
    <nav class="work-breadcrumb" aria-label="breadcrumb">
        <a href="{{ route('company.projects.index') }}">Projetos</a>
        <span class="sep">/</span>
        <a href="{{ route('company.projects.kanban', $contextProject) }}">{{ $contextProject->name }}</a>
        <span class="sep">/</span>
        <a href="{{ route('company.tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a>
        <span class="sep">/</span>
        <span>Editar</span>
    </nav>
    @endif

    <header class="task-create-hero">
        <div>
            <h1>Editar Task</h1>
            @if($canEditPlanning)
                <p class="mb-0">Atualize a demanda, prazo de entrega e adicione novas subtasks.</p>
            @else
                <p class="mb-0 text-muted">Como responsável, você pode editar título, descrição, status e tempo estimado.</p>
            @endif
        </div>
        <div class="task-create-hero-actions">
            <a href="{{ route('company.tasks.show', $task) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Voltar
            </a>
            <button type="submit" form="task-edit-form" class="btn btn-primary btn-sm">
                <i class="fas fa-check me-1"></i>Salvar
            </button>
        </div>
    </header>

    <form action="{{ route('company.tasks.update', $task) }}" method="POST" id="task-edit-form">
        @csrf
        @method('PUT')

        <div class="task-create-sections-grid">

            <section class="task-form-section work-panel h-100">
                <div class="task-form-section-head">
                    <div class="task-form-section-icon primary"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <h2 class="task-form-section-title">Identificação</h2>
                        <p class="task-form-section-sub">Título, projeto e descrição</p>
                    </div>
                </div>
                <div class="work-panel-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="task-form-label">Título <span class="req">*</span></label>
                            <input type="text" name="title" class="form-control task-title-input @error('title') is-invalid @enderror"
                                   value="{{ old('title', $task->title) }}" required autofocus>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Projeto</label>
                            @if($isManager)
                                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" @selected(old('project_id', $task->project_id) == $p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @else
                                <input type="hidden" name="project_id" value="{{ $task->project_id }}">
                                <div class="project-pill">
                                    <i class="fas fa-folder-open"></i>
                                    <span>{{ $contextProject?->name ?? '—' }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(\App\Models\Task::STATUSES as $k => $v)
                                    <option value="{{ $k }}" @selected(old('status', $task->status) === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="task-form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="5">{{ old('description', $task->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </section>

            @if($canEditPlanning)
            <section class="task-form-section work-panel h-100">
                <div class="task-form-section-head">
                    <div class="task-form-section-icon success"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <h2 class="task-form-section-title">Planejamento</h2>
                        <p class="task-form-section-sub">Prioridade, prazo e responsável</p>
                    </div>
                </div>
                <div class="work-panel-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="task-form-label">Categoria</label>
                            <select name="category" class="form-select">
                                @foreach(\App\Models\Task::CATEGORIES as $k => $v)
                                    <option value="{{ $k }}" @selected(old('category', $task->category) === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Prioridade</label>
                            <select name="priority" class="form-select">
                                @foreach(\App\Models\Task::PRIORITIES as $k => $v)
                                    <option value="{{ $k }}" @selected(old('priority', $task->priority) === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Responsável</label>
                            <select name="assignee_id" class="form-select">
                                <option value="">Sem responsável</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" @selected(old('assignee_id', $task->assignee_id) == $emp->id)>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Tempo estimado</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control"
                                       value="{{ old('estimated_hours', $task->estimated_hours) }}" placeholder="0">
                                <span class="input-group-text">h</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="task-form-label">Prazo de entrega</label>
                            <input type="datetime-local" name="sla_deadline"
                                   class="form-control @error('sla_deadline') is-invalid @enderror"
                                   value="{{ $fmtDateTime(old('sla_deadline', $task->sla_deadline)) }}">
                            <div class="form-text">Vazio = SLA automático pela prioridade</div>
                            @error('sla_deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <hr class="my-1 border-secondary opacity-25">
                        </div>
                        <div class="col-sm-5">
                            <label class="task-form-label">Solicitante</label>
                            <select name="requester_type" class="form-select">
                                <option value="internal" @selected(old('requester_type', $task->requester_type) === 'internal')>Interno</option>
                                <option value="client" @selected(old('requester_type', $task->requester_type) === 'client')>Cliente</option>
                            </select>
                        </div>
                        <div class="col-sm-7">
                            <label class="task-form-label">Nome do solicitante</label>
                            <input type="text" name="requester_name" class="form-control"
                                   value="{{ old('requester_name', $task->requester_name) }}" placeholder="Nome de quem solicitou">
                        </div>
                    </div>
                </div>
            </section>
            @else
            <section class="task-form-section work-panel h-100">
                <div class="task-form-section-head">
                    <div class="task-form-section-icon success"><i class="fas fa-clock"></i></div>
                    <div>
                        <h2 class="task-form-section-title">Execução</h2>
                        <p class="task-form-section-sub">Status e tempo estimado</p>
                    </div>
                </div>
                <div class="work-panel-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="task-form-label">Tempo estimado</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control"
                                       value="{{ old('estimated_hours', $task->estimated_hours) }}" placeholder="0">
                                <span class="input-group-text">h</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @endif

            @if($canEditPlanning)
            @if($task->subtasks->isNotEmpty())
            <section class="task-form-section work-panel task-form-section--full">
                <div class="task-form-section-head">
                    <div class="task-form-section-icon info"><i class="fas fa-list"></i></div>
                    <div>
                        <h2 class="task-form-section-title">Subtasks existentes</h2>
                        <p class="task-form-section-sub">Altere status e detalhes na página da task</p>
                    </div>
                </div>
                <div class="work-panel-body">
                    <ul class="list-unstyled mb-0">
                        @foreach($task->subtasks as $subtask)
                        <li class="d-flex align-items-center justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                            <span>
                                <strong>{{ $subtask->title }}</strong>
                                <span class="text-muted small ms-2">
                                    {{ $subtask->assignee->name ?? 'Sem responsável' }}
                                    @if($subtask->due_date) · {{ $subtask->due_date->format('d/m/Y H:i') }}@endif
                                </span>
                            </span>
                            <span class="badge bg-secondary">{{ \App\Models\Subtask::STATUSES[$subtask->status] ?? $subtask->status }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </section>
            @endif

            <section class="task-form-section work-panel task-subtasks-panel task-form-section--full">
                <div class="task-form-section-head">
                    <div class="task-form-section-icon purple"><i class="fas fa-list-check"></i></div>
                    <div class="flex-grow-1">
                        <h2 class="task-form-section-title">Novas subtasks</h2>
                        <p class="task-form-section-sub">Opcional — adicione entregas parciais com prazo próprio</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary" id="add-subtask-row">
                        <i class="fas fa-plus me-1"></i>Adicionar
                    </button>
                </div>
                <div class="work-panel-body">
                    <div id="subtask-rows" class="row g-3">
                        @foreach($oldSubtasks as $index => $sub)
                        <div class="col-md-6 col-xl-4 subtask-row-wrap">
                            <div class="task-subtask-card subtask-row h-100" data-index="{{ $index + 1 }}">
                                <span class="task-subtask-index">{{ $index + 1 }}</span>
                                <button type="button" class="btn btn-outline-danger btn-sm task-subtask-remove remove-subtask-row" title="Remover">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="task-form-label">Título</label>
                                        <input type="text" data-name="title" name="subtasks[{{ $index }}][title]" class="form-control form-control-sm"
                                               value="{{ $sub['title'] ?? '' }}" placeholder="Etapa ou entrega parcial">
                                    </div>
                                    <div class="col-12">
                                        <label class="task-form-label">Responsável</label>
                                        <select data-name="assignee_id" name="subtasks[{{ $index }}][assignee_id]" class="form-select form-select-sm">
                                            <option value="">Mesmo da task</option>
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->id }}" @selected(($sub['assignee_id'] ?? '') == $emp->id)>{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="task-form-label">Prazo de entrega</label>
                                        <input type="datetime-local" data-name="due_date" name="subtasks[{{ $index }}][due_date]"
                                               class="form-control form-control-sm" value="{{ $fmtDateTime($sub['due_date'] ?? null) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-muted small mb-0 mt-2">
                        <i class="fas fa-info-circle me-1"></i>Linhas sem título são ignoradas ao salvar.
                    </p>
                </div>
            </section>
            @endif
        </div>

        <div class="task-form-actions mt-3">
            <a href="{{ route('company.tasks.show', $task) }}" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Salvar alterações</button>
        </div>
    </form>
</div>

@if($canEditPlanning)
<template id="subtask-row-template">
    <div class="col-md-6 col-xl-4 subtask-row-wrap">
        <div class="task-subtask-card subtask-row h-100" data-index="">
            <span class="task-subtask-index">1</span>
            <button type="button" class="btn btn-outline-danger btn-sm task-subtask-remove remove-subtask-row" title="Remover">
                <i class="fas fa-times"></i>
            </button>
            <div class="row g-2">
                <div class="col-12">
                    <label class="task-form-label">Título</label>
                    <input type="text" data-name="title" class="form-control form-control-sm" placeholder="Etapa ou entrega parcial">
                </div>
                <div class="col-12">
                    <label class="task-form-label">Responsável</label>
                    <select data-name="assignee_id" class="form-select form-select-sm">
                        <option value="">Mesmo da task</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="task-form-label">Prazo de entrega</label>
                    <input type="datetime-local" data-name="due_date" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
(function () {
    const container = document.getElementById('subtask-rows');
    const template = document.getElementById('subtask-row-template');
    if (!container || !template) return;

    function updateIndexes() {
        container.querySelectorAll('.subtask-row').forEach((row, index) => {
            const num = index + 1;
            row.dataset.index = num;
            const badge = row.querySelector('.task-subtask-index');
            if (badge) badge.textContent = num;
            row.querySelectorAll('[data-name]').forEach((el) => {
                el.name = `subtasks[${index}][${el.dataset.name}]`;
            });
        });
    }

    document.getElementById('add-subtask-row')?.addEventListener('click', () => {
        container.appendChild(template.content.cloneNode(true));
        updateIndexes();
    });

    container.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-subtask-row');
        if (!btn) return;
        const wraps = container.querySelectorAll('.subtask-row-wrap');
        if (wraps.length <= 1) {
            wraps[0].querySelectorAll('input, select').forEach((el) => {
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
                else el.value = '';
            });
            return;
        }
        btn.closest('.subtask-row-wrap')?.remove();
        updateIndexes();
    });

    updateIndexes();
})();
</script>
@endpush
@endif
@endsection
