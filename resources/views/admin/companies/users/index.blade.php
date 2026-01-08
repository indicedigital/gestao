@extends('layouts.app')

@section('title', 'Usuários da Empresa')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Usuários da Empresa</h1>
        <p class="page-subtitle">{{ $company->name }}</p>
    </div>
    <div>
        <button type="button" class="btn btn-info text-white me-2" data-bs-toggle="modal" data-bs-target="#attachUserModal">
            <i class="fas fa-link me-2"></i>Vincular Usuário Existente
        </button>
        <a href="{{ route('admin.companies.users.create', $company) }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Novo Usuário
        </a>
        <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Papel</th>
                        <th>Status</th>
                        <th>Vinculado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($company->users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 12px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <strong>{{ $user->name }}</strong>
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
                                    {{ \Carbon\Carbon::parse($user->pivot->joined_at)->format('d/m/Y H:i') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($user->pivot->role !== 'owner')
                                        <button type="button" class="btn btn-sm btn-warning text-white" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editRoleModal{{ $user->id }}"
                                                title="Alterar Papel">
                                            <i class="fas fa-user-tag"></i>
                                        </button>
                                        <form action="{{ route('admin.companies.users.detach', [$company, $user]) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              class="delete-form" data-message="Tem certeza que deseja remover este usuário da empresa?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Proprietário</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Modal para editar papel -->
                        @if($user->pivot->role !== 'owner')
                        <div class="modal fade" id="editRoleModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Alterar Papel do Usuário</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.companies.users.update-role', [$company, $user]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="role{{ $user->id }}" class="form-label">Papel</label>
                                                <select class="form-select" id="role{{ $user->id }}" name="role" required>
                                                    <option value="admin" {{ $user->pivot->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                                                    <option value="manager" {{ $user->pivot->role === 'manager' ? 'selected' : '' }}>Gerente</option>
                                                    <option value="user" {{ $user->pivot->role === 'user' ? 'selected' : '' }}>Usuário</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Salvar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum usuário vinculado à empresa</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para vincular usuário existente -->
<div class="modal fade" id="attachUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vincular Usuário Existente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.companies.users.attach', $company) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="user_id" class="form-label">Usuário</label>
                        <select class="form-select" id="user_id" name="user_id" required>
                            <option value="">Selecione um usuário</option>
                            @php
                                $linkedUserIds = $company->users->pluck('id')->toArray();
                                $availableUsers = \App\Models\User::whereNotIn('id', $linkedUserIds)->get();
                            @endphp
                            @foreach($availableUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Papel</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin">Administrador</option>
                            <option value="manager">Gerente</option>
                            <option value="user" selected>Usuário</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Vincular</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
