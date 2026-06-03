@extends('layouts.app')

@section('title', 'Tasks excluídas')

@section('content')
@include('company.tasks._helpers')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Tasks excluídas</h1>
            <p class="page-subtitle mb-0">Histórico de exclusões — não aparecem no quadro Kanban</p>
        </div>
        <a href="{{ route('company.tasks.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar às tasks
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Projeto</th>
                            <th>Excluída em</th>
                            <th>Excluída por</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        @php
                            $deleteLog = $task->histories->first();
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $task->id }}</td>
                            <td><strong>{{ $task->title }}</strong></td>
                            <td>{{ $task->project->name ?? '—' }}</td>
                            <td>{{ $task->deleted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                @if($deleteLog)
                                    {{ $deleteLog->user->name ?? $deleteLog->new_value ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('company.tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary" title="Ver histórico">
                                    <i class="fas fa-eye me-1"></i>Detalhes
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-trash-alt fa-3x mb-3 d-block opacity-25"></i>
                                Nenhuma task excluída.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tasks->hasPages())
                <div class="d-flex justify-content-center py-3">{{ $tasks->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
