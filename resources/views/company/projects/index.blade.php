@extends('layouts.app')

@section('title', 'Projetos')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Projetos</h1>
            <p class="page-subtitle">Gerencie seus projetos</p>
        </div>
        <a href="{{ route('company.projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Projeto
        </a>
    </div>


    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Cliente</th>
                            <th>Tipo</th>
                            <th>Valor Total</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td>{{ $project->id }}</td>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->client->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $project->type === 'fixed' ? 'primary' : 'info' }}">
                                    {{ $project->type === 'fixed' ? 'Fechado' : 'Recorrente' }}
                                </span>
                            </td>
                            <td>R$ {{ number_format($project->total_value, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'planning' => 'secondary',
                                        'in_progress' => 'primary',
                                        'paused' => 'warning',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$project->status] ?? 'secondary';
                                    $statusLabels = [
                                        'planning' => 'Planejamento',
                                        'in_progress' => 'Em Andamento',
                                        'paused' => 'Pausado',
                                        'completed' => 'Finalizado',
                                        'cancelled' => 'Cancelado'
                                    ];
                                    $statusLabel = $statusLabels[$project->status] ?? ucfirst($project->status);
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <a href="{{ route('company.projects.show', $project) }}" class="btn btn-sm btn-info text-white" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('company.projects.edit', $project) }}" class="btn btn-sm btn-warning text-white" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('company.projects.destroy', $project) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este projeto?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum projeto cadastrado</p>
                                <a href="{{ route('company.projects.create') }}" class="btn btn-primary">Cadastrar Primeiro Projeto</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($projects->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $projects->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
