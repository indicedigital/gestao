@php
    $canManageAccess = app(\App\Services\CompanyAuthorizationService::class)->canManageAccess();
    $roleLabels = [
        'client' => 'Cliente (Portal)',
        'user' => 'Colaborador',
        'freelancer' => 'Freelancer',
    ];
    $company = session('current_company_id') ? \App\Models\Company::find(session('current_company_id')) : null;
    $permissionProfiles = $company
        ? \App\Models\CompanyPermissionProfile::where('company_id', $company->id)->orderBy('name')->get()
        : collect();
    $storeRoute = $type === 'client'
        ? route('company.clients.access.store', $entity)
        : route('company.employees.access.store', $entity);
    $updateRoute = $type === 'client'
        ? route('company.clients.access.update', $entity)
        : route('company.employees.access.update', $entity);
    $destroyRoute = $type === 'client'
        ? route('company.clients.access.destroy', $entity)
        : route('company.employees.access.destroy', $entity);
    $defaultEmail = $entity->email ?? '';
    $defaultName = $entity->name ?? '';
    $suggestedRole = $type === 'employee' ? ($entity->type === 'freelancer' ? 'freelancer' : 'user') : 'client';
@endphp

<div class="work-panel mt-4" id="member-access-panel">
    <div class="work-panel-header">
        <span><i class="fas fa-key me-2"></i>Acesso ao sistema</span>
        @if($access)
            <span class="badge {{ $access['is_active'] ? 'bg-success' : 'bg-secondary' }}">{{ $access['is_active'] ? 'Ativo' : 'Inativo' }}</span>
        @else
            <span class="badge bg-warning text-dark">Sem acesso</span>
        @endif
    </div>
    <div class="work-panel-body">
        @error('access')
            <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
        @enderror

        @if($access)
            <div class="work-props mb-3" style="grid-template-columns:1fr 1fr;">
                <div>
                    <div class="work-prop-label">Usuário</div>
                    <div class="work-prop-value">{{ $access['user']->name }}</div>
                    <small class="text-muted">{{ $access['user']->email }}</small>
                </div>
                <div>
                    <div class="work-prop-label">Perfil de permissão</div>
                    <div class="work-prop-value">
                        @if($access['permission_profile'] ?? null)
                            <span class="badge bg-info text-dark">{{ $access['permission_profile']->name }}</span>
                        @else
                            <span class="text-muted small">Padrão do role</span>
                        @endif
                        <span class="badge bg-secondary ms-1">{{ $roleLabels[$access['role']] ?? $access['role'] }}</span>
                    </div>
                </div>
            </div>
            <p class="small text-muted mb-3">
                @if($type === 'client')
                    Acesso ao <strong>Portal do Cliente</strong> via <code>/login</code> — projetos e tasks vinculados ao cliente.
                @else
                    Acesso interno a tasks, dailies e projetos conforme o perfil selecionado.
                @endif
            </p>

            @if($canManageAccess)
            <form action="{{ $updateRoute }}" method="POST" class="border-top pt-3">
                @csrf
                @method('PUT')
                <h6 class="mb-3 small fw-semibold">Alterar senha ou status</h6>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input type="password" name="password" class="form-control form-control-sm" placeholder="Nova senha (mín. 8)" minlength="8">
                    </div>
                    <div class="col-md-6">
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirmar senha">
                    </div>
                </div>

                @if($type === 'employee')
                <div class="mb-3">
                    <label class="form-label small">Tipo de vínculo</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="user" @selected($access['role'] === 'user')>Colaborador</option>
                        <option value="freelancer" @selected($access['role'] === 'freelancer')>Freelancer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Perfil de permissão</label>
                    <select name="permission_profile_id" class="form-select form-select-sm">
                        <option value="">— Padrão do role —</option>
                        @foreach($permissionProfiles as $profile)
                            <option value="{{ $profile->id }}" @selected(($access['permission_profile_id'] ?? null) == $profile->id)>{{ $profile->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Define módulos visíveis e escopo de projetos/tasks.</div>
                </div>
                @endif

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="access_active_{{ $type }}_{{ $entity->id }}" @checked($access['is_active'])>
                    <label class="form-check-label" for="access_active_{{ $type }}_{{ $entity->id }}">Acesso ativo</label>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save me-1"></i>Salvar alterações
                </button>
            </form>

            <form action="{{ $destroyRoute }}" method="POST" class="mt-3 pt-3 border-top delete-form" data-message="Revogar o acesso? O usuário não poderá mais entrar.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-unlink me-1"></i>Revogar acesso
                </button>
            </form>
            @else
            <p class="text-muted small mb-0">Somente administradores (owner/admin) podem gerenciar acessos.</p>
            @endif

        @elseif($canManageAccess)
            <p class="text-muted small mb-3">
                @if($type === 'client')
                    Gere login e senha para o cliente acessar o portal.
                @else
                    Gere login e senha para o colaborador acessar o sistema.
                @endif
            </p>

            @if(!$defaultEmail)
                <div class="alert alert-warning py-2 small">Informe um e-mail abaixo ou cadastre no registro do {{ $type === 'client' ? 'cliente' : 'colaborador' }}.</div>
            @endif

            <form action="{{ $storeRoute }}" method="POST">
                @csrf
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label small">Nome</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $defaultName) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">E-mail de login</label>
                        <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $defaultEmail) }}" required>
                    </div>
                </div>

                @if($type === 'employee')
                <div class="mb-2">
                    <label class="form-label small">Tipo de vínculo</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="user" @selected(old('role', $suggestedRole) === 'user')>Colaborador</option>
                        <option value="freelancer" @selected(old('role', $suggestedRole) === 'freelancer')>Freelancer</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Perfil de permissão</label>
                    <select name="permission_profile_id" class="form-select form-select-sm">
                        <option value="">Programador (padrão)</option>
                        @foreach($permissionProfiles as $profile)
                            <option value="{{ $profile->id }}" @selected(old('permission_profile_id') == $profile->id || ($profile->slug === 'programador' && !old('permission_profile_id')))>{{ $profile->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small">Senha inicial</label>
                        <input type="password" name="password" class="form-control form-control-sm" required minlength="8">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Confirmar senha</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-sm" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus me-1"></i>Gerar acesso
                </button>
            </form>
        @else
            <p class="text-muted small mb-0">Nenhum acesso configurado.</p>
        @endif
    </div>
</div>
