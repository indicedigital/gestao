@extends('layouts.app')

@section('title', 'Projetos')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Projetos</h1>
            <p class="page-subtitle">Gerencie seus projetos</p>
        </div>
        @if($authz->canManageProjects())
        <a href="{{ route('company.projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Projeto
        </a>
        @endif
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Cliente</th>
                            <th>Categoria</th>
                            @if($authz->canViewProjectFinancial())
                            <th>Tipo</th>
                            <th>Valor</th>
                            @endif
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        @php
                            $statusColors = [
                                'active' => 'primary', 'paused' => 'warning', 'implementing' => 'info',
                                'completed' => 'success', 'cancelled' => 'danger',
                                'planning' => 'info', 'in_progress' => 'primary',
                            ];
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $project->id }}</td>
                            <td><strong>{{ $project->name }}</strong></td>
                            <td>{{ $project->client->name ?? '—' }}</td>
                            <td>{{ $project->categoryLabel() }}</td>
                            @if($authz->canViewProjectFinancial())
                            <td><span class="badge bg-{{ $project->type === 'fixed' ? 'primary' : 'info' }}">{{ $project->type === 'fixed' ? 'Fechado' : 'Recorrente' }}</span></td>
                            <td>R$ {{ number_format($project->total_value, 2, ',', '.') }}</td>
                            @endif
                            <td><span class="badge bg-{{ $statusColors[$project->status] ?? 'secondary' }}">{{ $project->statusLabel() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('company.projects.kanban', $project) }}" class="btn btn-sm btn-primary" title="Kanban"><i class="fas fa-columns"></i></a>
                                @if($authz->canViewProjectOverview())
                                <a href="{{ route('company.projects.show', $project) }}" class="btn btn-sm btn-info text-white" title="Ver"><i class="fas fa-eye"></i></a>
                                @endif
                                @if($authz->canManageProjects())
                                <a href="{{ route('company.projects.edit', $project) }}" class="btn btn-sm btn-warning text-white" title="Editar"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('company.projects.destroy', $project) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este projeto?">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remover"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $authz->canViewProjectFinancial() ? 8 : 6 }}" class="text-center py-5">
                                <i class="fas fa-project-diagram fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">Nenhum projeto encontrado.</p>
                                @if($authz->canManageProjects())
                                <a href="{{ route('company.projects.create') }}" class="btn btn-primary">Cadastrar projeto</a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($projects->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $projects->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
