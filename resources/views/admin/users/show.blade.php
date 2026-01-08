@extends('layouts.app')

@section('title', 'Detalhes do Usuário')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Detalhes do Usuário</h1>
        <p class="page-subtitle">Informações completas do usuário</p>
    </div>
    <div>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning text-white">
            <i class="fas fa-edit me-2"></i>Editar
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações do Usuário</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="user-avatar me-3" style="width: 80px; height: 80px; font-size: 32px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                    </div>
                </div>
                
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $user->id }}</td>
                    </tr>
                    <tr>
                        <th>Nome:</th>
                        <td><strong>{{ $user->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>E-mail:</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'pending' => 'warning',
                                    'blocked' => 'danger'
                                ];
                                $statusColor = $statusColors[$user->status ?? 'pending'] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">
                                {{ ucfirst($user->status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Super Admin:</th>
                        <td>
                            @if($user->is_super_admin ?? false)
                                <span class="badge bg-primary">Sim</span>
                            @else
                                <span class="badge bg-secondary">Não</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>E-mail Verificado:</th>
                        <td>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Sim - {{ $user->email_verified_at->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="badge bg-warning">Não</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Criado em:</th>
                        <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Atualizado em:</th>
                        <td>{{ $user->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ações</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning text-white">
                        <i class="fas fa-edit me-2"></i>Editar Usuário
                    </a>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="delete-form" data-message="Tem certeza que deseja excluir este usuário?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-2"></i>Excluir Usuário
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
