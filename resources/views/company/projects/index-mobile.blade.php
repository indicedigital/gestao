@extends('layouts.mobile')

@section('title', 'Projetos')

@section('content')
<div class="mobile-content">
    <!-- Botão Novo Projeto -->
    <div style="margin-bottom: 16px;">
        <a href="{{ route('company.projects.create') }}" 
           class="btn btn-primary" 
           style="width: 100%; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-plus"></i> Novo Projeto
        </a>
    </div>

    <!-- Lista de Projetos -->
    <div class="mobile-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">Lista de Projetos</h5>
            <span class="badge bg-primary" style="padding: 6px 12px; border-radius: 12px; font-size: 12px;">{{ $projects->total() }} registro(s)</span>
        </div>

        @forelse($projects as $project)
        <div class="mobile-card-item" style="background: white; border-radius: 12px; padding: 16px; margin-bottom: 12px; border: 1px solid #e2e8f0; width: 100%; box-sizing: border-box; overflow: hidden;">
            <div class="mobile-card-item-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; gap: 8px;">
                <div class="mobile-card-item-title" style="font-weight: 600; font-size: 15px; color: #1a202c; flex: 1; min-width: 0; word-wrap: break-word; overflow-wrap: break-word;">
                    #{{ $project->id }} - {{ $project->name }}
                </div>
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
                <span class="badge bg-{{ $statusColor }}" style="padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 600;">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="mobile-card-item-body" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Tipo</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">
                        <span class="badge bg-{{ $project->type === 'fixed' ? 'primary' : 'info' }}" style="padding: 4px 8px; border-radius: 6px; font-size: 11px;">
                            {{ $project->type === 'fixed' ? 'Fechado' : 'Recorrente' }}
                        </span>
                    </div>
                </div>

                <div class="mobile-card-item-field">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Valor Total</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500; font-weight: 600;">R$ {{ number_format($project->total_value, 2, ',', '.') }}</div>
                </div>

                <div class="mobile-card-item-field" style="grid-column: 1 / -1;">
                    <div class="mobile-card-item-label" style="font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px;">Cliente</div>
                    <div class="mobile-card-item-value" style="font-size: 13px; color: #1a202c; font-weight: 500;">{{ $project->client->name ?? '-' }}</div>
                </div>
            </div>

            <div class="mobile-card-item-actions" style="display: flex; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('company.projects.show', $project) }}" 
                   class="btn btn-info btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-eye"></i> Ver
                </a>
                <a href="{{ route('company.projects.edit', $project) }}" 
                   class="btn btn-warning btn-sm" 
                   style="flex: 1; padding: 8px 12px; border-radius: 8px; font-size: 12px; text-align: center; text-decoration: none; color: white;">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <form action="{{ route('company.projects.destroy', $project) }}" 
                      method="POST" 
                      class="d-inline delete-form" 
                      data-message="Tem certeza que deseja remover este projeto?"
                      style="flex: 1; margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="btn btn-danger btn-sm" 
                            style="width: 100%; padding: 8px 12px; border-radius: 8px; font-size: 12px;">
                        <i class="fas fa-trash"></i> Remover
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fas fa-project-diagram" style="font-size: 48px; margin-bottom: 12px; opacity: 0.3;"></i>
            <p style="margin: 0;">Nenhum projeto cadastrado</p>
            <a href="{{ route('company.projects.create') }}" class="btn btn-primary" style="margin-top: 16px; padding: 10px 20px; border-radius: 8px;">
                Cadastrar Primeiro Projeto
            </a>
        </div>
        @endforelse

        @if($projects->hasPages())
        <div style="display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px; padding: 16px;">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
