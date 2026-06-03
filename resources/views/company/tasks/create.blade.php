@extends('layouts.app')

@section('title', 'Nova Task')

@section('content')
@php
    $fmtDateTime = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '';
    $oldSubtasks = old('subtasks', [['title' => '', 'assignee_id' => '', 'due_date' => '']]);
    $cancelUrl = ($selectedProject && request('redirect_to') === 'kanban')
        ? route('company.projects.kanban', $selectedProject)
        : route('company.tasks.index');
    $singleProject = $projects->count() === 1 ? $projects->first() : null;
@endphp

<div class="container-fluid py-4 task-create-page work-shell">

    @if($contextProject)
    <nav class="work-breadcrumb" aria-label="breadcrumb">
        <a href="{{ route('company.projects.index') }}">Projetos</a>
        <span class="sep">/</span>
        <a href="{{ route('company.projects.kanban', $contextProject) }}">{{ $contextProject->name }}</a>
        <span class="sep">/</span>
        <span>Nova task</span>
    </nav>
    @endif

    <header class="task-create-hero">
        <div>
            <h1>Nova Task</h1>
            <p class="mb-0">Cadastre a demanda com prazo de entrega e subtasks no mesmo fluxo.</p>
        </div>
        <div class="task-create-hero-actions">
            <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Voltar
            </a>
            <button type="submit" form="task-create-form" class="btn btn-primary btn-sm">
                <i class="fas fa-check me-1"></i>Criar Task
            </button>
        </div>
    </header>

    <form action="{{ route('company.tasks.store') }}" method="POST" id="task-create-form">
        @csrf
        @if(request('redirect_to'))
            <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">
        @endif

        <div class="task-create-sections-grid">

            {{-- Coluna 1: Identificação --}}
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
                                   value="{{ old('title') }}" placeholder="Ex.: Implementar login com SSO" required autofocus>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Projeto <span class="req">*</span></label>
                            @if($singleProject)
                                <input type="hidden" name="project_id" value="{{ $singleProject->id }}">
                                <div class="project-pill">
                                    <i class="fas fa-folder-open"></i>
                                    <span>{{ $singleProject->name }}</span>
                                </div>
                            @else
                                <select name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
                                    <option value="">Selecione</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" @selected(old('project_id', $selectedProject) == $p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Status inicial</label>
                            <select name="status" class="form-select">
                                @foreach(\App\Models\Task::STATUSES as $k => $v)
                                    <option value="{{ $k }}" @selected(old('status', request('status', 'backlog')) === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="task-form-label">Descrição</label>
                            <textarea name="description" class="form-control" rows="5"
                                      placeholder="Contexto, critérios de aceite, links...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Coluna 2: Planejamento --}}
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
                                    <option value="{{ $k }}" @selected(old('category', 'support') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Prioridade</label>
                            <select name="priority" class="form-select">
                                @foreach(\App\Models\Task::PRIORITIES as $k => $v)
                                    <option value="{{ $k }}" @selected(old('priority', 'P2') === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Responsável</label>
                            <select name="assignee_id" class="form-select">
                                <option value="">Sem responsável</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" @selected(old('assignee_id') == $emp->id)>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="task-form-label">Tempo estimado</label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0" name="estimated_hours" class="form-control"
                                       value="{{ old('estimated_hours') }}" placeholder="0">
                                <span class="input-group-text">h</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="task-form-label">Prazo de entrega</label>
                            <input type="datetime-local" name="sla_deadline"
                                   class="form-control @error('sla_deadline') is-invalid @enderror"
                                   value="{{ $fmtDateTime(old('sla_deadline')) }}">
                            <div class="form-text">Vazio = SLA automático pela prioridade</div>
                            @error('sla_deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <hr class="my-1 border-secondary opacity-25">
                        </div>
                        <div class="col-sm-5">
                            <label class="task-form-label">Solicitante</label>
                            <select name="requester_type" class="form-select">
                                <option value="internal" @selected(old('requester_type', 'internal') === 'internal')>Interno</option>
                                <option value="client" @selected(old('requester_type') === 'client')>Cliente</option>
                            </select>
                        </div>
                        <div class="col-sm-7">
                            <label class="task-form-label">Nome do solicitante</label>
                            <input type="text" name="requester_name" class="form-control"
                                   value="{{ old('requester_name', auth()->user()->name) }}" placeholder="Nome de quem solicitou">
                        </div>
                    </div>
                </div>
            </section>

            {{-- Largura total: Subtasks --}}
            <section class="task-form-section work-panel task-subtasks-panel task-form-section--full">
                <div class="task-form-section-head">
                    <div class="task-form-section-icon purple"><i class="fas fa-list-check"></i></div>
                    <div class="flex-grow-1">
                        <h2 class="task-form-section-title">Subtasks</h2>
                        <p class="task-form-section-sub">Opcional — entregas parciais com prazo próprio</p>
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
        </div>

        <div class="task-form-actions mt-3">
            <span class="task-form-actions-hint"><i class="fas fa-asterisk" style="font-size:8px;"></i> Obrigatórios: título e projeto</span>
            <div class="d-flex gap-2">
                <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i>Criar Task</button>
            </div>
        </div>
    </form>
</div>

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
@endsection
