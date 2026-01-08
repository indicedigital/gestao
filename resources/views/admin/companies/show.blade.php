@extends('layouts.app')

@section('title', 'Detalhes da Empresa')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Detalhes da Empresa</h1>
        <p class="page-subtitle">Informações completas da empresa</p>
    </div>
    <div>
        <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning text-white">
            <i class="fas fa-edit me-2"></i>Editar
        </a>
        <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações da Empresa</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $company->id }}</td>
                    </tr>
                    <tr>
                        <th>Nome:</th>
                        <td><strong>{{ $company->name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Slug:</th>
                        <td><code>{{ $company->slug ?? 'N/A' }}</code></td>
                    </tr>
                    <tr>
                        <th>E-mail:</th>
                        <td>{{ $company->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Telefone:</th>
                        <td>{{ $company->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'suspended' => 'warning',
                                    'cancelled' => 'danger'
                                ];
                                $statusColor = $statusColors[$company->status ?? 'active'] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">
                                {{ ucfirst($company->status ?? 'active') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Administrador:</th>
                        <td>
                            @if($company->owner)
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ strtoupper(substr($company->owner->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $company->owner->name }}</strong><br>
                                        <small class="text-muted">{{ $company->owner->email }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">Não definido</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Criado em:</th>
                        <td>{{ $company->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Atualizado em:</th>
                        <td>{{ $company->updated_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Ações</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-warning text-white">
                        <i class="fas fa-edit me-2"></i>Editar Empresa
                    </a>
                    <a href="{{ route('admin.companies.users.index', $company) }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>Gerenciar Usuários
                    </a>
                    <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" class="delete-form" data-message="Tem certeza que deseja excluir esta empresa?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-2"></i>Excluir Empresa
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Estatísticas</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Total de Usuários:</strong> 
                    <span class="badge bg-primary">{{ $company->users->count() }}</span>
                </p>
                <p class="mb-2">
                    <strong>Usuários Ativos:</strong> 
                    <span class="badge bg-success">{{ $company->activeUsers->count() }}</span>
                </p>
                <p class="mb-0">
                    <strong>Administradores:</strong> 
                    <span class="badge bg-warning">{{ $company->admins->count() }}</span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Usuários da Empresa</h5>
                <a href="{{ route('admin.companies.users.index', $company) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-users me-2"></i>Gerenciar Usuários
                </a>
            </div>
            <div class="card-body">
                @if($company->users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Papel</th>
                                    <th>Status</th>
                                    <th>Vinculado em</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($company->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @php
                                                $roleColors = [
                                                    'owner' => 'danger',
                                                    'admin' => 'warning',
                                                    'manager' => 'info',
                                                    'user' => 'secondary'
                                                ];
                                                $roleColor = $roleColors[$user->pivot->role] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $roleColor }}">
                                                {{ ucfirst($user->pivot->role) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $user->pivot->is_active ? 'success' : 'secondary' }}">
                                                {{ $user->pivot->is_active ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->pivot->joined_at)
                                                {{ \Carbon\Carbon::parse($user->pivot->joined_at)->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x mb-3"></i><br>
                        Nenhum usuário vinculado à empresa
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
