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
    $isPreview = ($preview ?? false) || ! ($entity->exists ?? true);
    $entityLabel = $type === 'client' ? 'cliente' : 'colaborador';
    $defaultEmail = $entity->email ?? '';
    $defaultName = $entity->name ?? '';
    $suggestedRole = $type === 'employee' ? ($entity->type === 'freelancer' ? 'freelancer' : 'user') : 'client';
    $userInitial = $access ? mb_strtoupper(mb_substr($access['user']->name, 0, 1)) : mb_strtoupper(mb_substr($defaultName ?: '?', 0, 1));

    $storeRoute = $updateRoute = $destroyRoute = null;
    if (! $isPreview) {
        $storeRoute = $type === 'client'
            ? route('company.clients.access.store', $entity)
            : route('company.employees.access.store', $entity);
        $updateRoute = $type === 'client'
            ? route('company.clients.access.update', $entity)
            : route('company.employees.access.update', $entity);
        $destroyRoute = $type === 'client'
            ? route('company.clients.access.destroy', $entity)
            : route('company.employees.access.destroy', $entity);
    }
@endphp

<div class="member-access-card" id="member-access-panel">
    <div class="member-access-header">
        <div class="member-access-header-main">
            <div class="member-access-icon" aria-hidden="true">
                <i class="fas fa-shield-halved"></i>
            </div>
            <div>
                <h2 class="member-access-title">Acesso ao sistema</h2>
                <p class="member-access-subtitle">
                    @if($type === 'client')
                        Login no portal do cliente para acompanhar projetos e tasks.
                    @else
                        Credenciais para tasks, dailies e projetos conforme o perfil.
                    @endif
                </p>
            </div>
        </div>

        @if($isPreview)
            <span class="member-access-status member-access-status--pending">
                <i class="fas fa-clock"></i> Após salvar
            </span>
        @elseif($access)
            <span class="member-access-status {{ $access['is_active'] ? 'member-access-status--active' : 'member-access-status--inactive' }}">
                <i class="fas fa-circle"></i>
                {{ $access['is_active'] ? 'Ativo' : 'Inativo' }}
            </span>
        @else
            <span class="member-access-status member-access-status--none">
                <i class="fas fa-lock"></i> Sem acesso
            </span>
        @endif
    </div>

    <div class="member-access-body">
        @error('access')
            <div class="alert alert-danger py-2 mb-3">{{ $message }}</div>
        @enderror

        @if($isPreview)
            <div class="member-access-preview">
                <div class="member-access-preview-icon">
                    <i class="fas fa-user-lock"></i>
                </div>
                <div>
                    <strong>Configure o acesso depois de salvar</strong>
                    <p class="mb-0">Salve o {{ $entityLabel }} primeiro. Na tela de edição você poderá gerar login, senha e perfil de permissão.</p>
                </div>
            </div>

        @elseif($access)
            <div class="member-access-user-card">
                <div class="member-access-avatar">{{ $userInitial }}</div>
                <div class="member-access-user-info">
                    <div class="member-access-user-name">{{ $access['user']->name }}</div>
                    <div class="member-access-user-email">{{ $access['user']->email }}</div>
                </div>
                <div class="member-access-badges">
                    <span class="member-access-badge member-access-badge--role">
                        {{ $roleLabels[$access['role']] ?? $access['role'] }}
                    </span>
                    @if($access['permission_profile'] ?? null)
                        <span class="member-access-badge member-access-badge--profile">
                            <i class="fas fa-id-badge me-1"></i>{{ $access['permission_profile']->name }}
                        </span>
                    @else
                        <span class="member-access-badge member-access-badge--muted">Perfil padrão do role</span>
                    @endif
                </div>
            </div>

            @if($canManageAccess)
                <div class="member-access-section">
                    <div class="member-access-section-head">
                        <i class="fas fa-sliders"></i>
                        <div>
                            <h3>Configurações</h3>
                            <p>Altere senha, perfil ou status do acesso.</p>
                        </div>
                    </div>

                    <form action="{{ $updateRoute }}" method="POST">
                        @csrf
                        @method('PUT')

                        @if($type === 'employee')
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de vínculo</label>
                                <select name="role" class="form-select">
                                    <option value="user" @selected($access['role'] === 'user')>Colaborador</option>
                                    <option value="freelancer" @selected($access['role'] === 'freelancer')>Freelancer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Perfil de permissão</label>
                                <select name="permission_profile_id" class="form-select">
                                    <option value="">— Padrão do role —</option>
                                    @foreach($permissionProfiles as $profile)
                                        <option value="{{ $profile->id }}" @selected(($access['permission_profile_id'] ?? null) == $profile->id)>{{ $profile->name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Define módulos visíveis e escopo de projetos/tasks.</div>
                            </div>
                        </div>
                        @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nova senha</label>
                                <input type="password" name="password" class="form-control" placeholder="Mínimo 8 caracteres" minlength="8" autocomplete="new-password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar senha</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Repita a nova senha" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="member-access-switch-row">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="access_active_{{ $type }}_{{ $entity->id }}" @checked($access['is_active'])>
                                <label class="form-check-label" for="access_active_{{ $type }}_{{ $entity->id }}">Acesso ativo</label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Salvar alterações
                            </button>
                        </div>
                    </form>
                </div>

                <div class="member-access-danger">
                    <div>
                        <strong>Revogar acesso</strong>
                        <p class="mb-0">O usuário não poderá mais entrar no sistema.</p>
                    </div>
                    <form action="{{ $destroyRoute }}" method="POST" class="delete-form" data-message="Revogar o acesso? O usuário não poderá mais entrar.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-unlink me-1"></i>Revogar
                        </button>
                    </form>
                </div>
            @else
                <div class="member-access-readonly">
                    <i class="fas fa-info-circle me-2"></i>
                    Somente administradores (owner/admin) podem gerenciar acessos.
                </div>
            @endif

        @elseif($canManageAccess)
            <div class="member-access-empty">
                <div class="member-access-empty-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <strong>Nenhum acesso configurado</strong>
                    <p class="mb-0">
                        @if($type === 'client')
                            Gere login e senha para o cliente acessar o portal.
                        @else
                            Gere login e senha para o colaborador acessar o sistema.
                        @endif
                    </p>
                </div>
            </div>

            @if(!$defaultEmail)
                <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
                    <i class="fas fa-exclamation-triangle mt-1"></i>
                    <div>Informe um e-mail no cadastro do {{ $entityLabel }} ou no formulário abaixo.</div>
                </div>
            @endif

            <div class="member-access-section">
                <div class="member-access-section-head">
                    <i class="fas fa-key"></i>
                    <div>
                        <h3>Gerar acesso</h3>
                        <p>Crie as credenciais de login para este {{ $entityLabel }}.</p>
                    </div>
                </div>

                <form action="{{ $storeRoute }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $defaultName) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail de login</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $defaultEmail) }}" required>
                        </div>
                    </div>

                    @if($type === 'employee')
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de vínculo</label>
                            <select name="role" class="form-select">
                                <option value="user" @selected(old('role', $suggestedRole) === 'user')>Colaborador</option>
                                <option value="freelancer" @selected(old('role', $suggestedRole) === 'freelancer')>Freelancer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perfil de permissão</label>
                            <select name="permission_profile_id" class="form-select">
                                <option value="">Programador (padrão)</option>
                                @foreach($permissionProfiles as $profile)
                                    <option value="{{ $profile->id }}" @selected(old('permission_profile_id') == $profile->id || ($profile->slug === 'programador' && !old('permission_profile_id')))>{{ $profile->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Senha inicial</label>
                            <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar senha</label>
                            <input type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i>Gerar acesso
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="member-access-readonly">
                <i class="fas fa-lock me-2"></i>
                Nenhum acesso configurado.
            </div>
        @endif
    </div>
</div>
