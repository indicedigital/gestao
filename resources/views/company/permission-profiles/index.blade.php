@extends('layouts.app')

@section('title', 'Perfis de Permissão')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h1 class="page-title">Perfis de Permissão</h1>
            <p class="page-subtitle">Defina quais módulos cada perfil pode acessar e o escopo de projetos/tasks.</p>
        </div>
        <a href="{{ route('company.permission-profiles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Novo perfil
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Perfis cadastrados</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Módulos</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profiles as $profile)
                                @php
                                    $perms = $profile->normalizedPermissions();
                                    $enabled = collect($perms['modules'])->filter()->count();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $profile->name }}</strong>
                                        <div class="small text-muted">{{ $profile->slug }}</div>
                                    </td>
                                    <td>
                                        @if($profile->is_system)
                                            <span class="badge bg-secondary">Sistema</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Customizado</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-primary">{{ $enabled }} módulos</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('company.permission-profiles.edit', $profile) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$profile->is_system)
                                        <form action="{{ route('company.permission-profiles.destroy', $profile) }}" method="POST" class="d-inline delete-form" data-message="Excluir este perfil?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Nenhum perfil cadastrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Colaboradores e perfis</div>
            <div class="card-body">
                <p class="small text-muted">Atribua um perfil a cada colaborador com login. Owner/admin têm acesso total.</p>
                @forelse($members as $member)
                    <form action="{{ route('company.permission-profiles.assign') }}" method="POST" class="border-bottom py-3">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $member->user_id }}">
                        <div class="fw-semibold">{{ $member->name }}</div>
                        <div class="small text-muted mb-2">{{ $member->email }} · {{ ucfirst($member->role) }}</div>
                        <select name="permission_profile_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">— Padrão do role —</option>
                            @foreach($profiles as $profile)
                                <option value="{{ $profile->id }}" @selected($member->permission_profile_id == $profile->id)>{{ $profile->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @empty
                    <p class="text-muted small mb-0">Nenhum colaborador com login encontrado.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
