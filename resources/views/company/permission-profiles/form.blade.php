@extends('layouts.app')

@php use App\Support\PermissionModules; @endphp

@section('title', $profile->exists ? 'Editar perfil' : 'Novo perfil')

@section('content')
@php
    $perms = $profile->exists ? $profile->normalizedPermissions() : PermissionModules::collaboratorDefaults();
    $isEdit = $profile->exists;
@endphp

<div class="page-header">
    <h1 class="page-title">{{ $isEdit ? 'Editar perfil' : 'Novo perfil' }}</h1>
    <p class="page-subtitle">Marque os módulos permitidos e defina o escopo de dados quando aplicável.</p>
</div>

<form action="{{ $isEdit ? route('company.permission-profiles.update', $profile) : route('company.permission-profiles.store') }}" method="POST">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="mb-4">
                <label class="form-label fw-semibold">Nome do perfil</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $profile->name) }}" required maxlength="100" placeholder="Ex: Programador, Diretor, Analista">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @foreach($moduleGroups as $group => $moduleKeys)
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted small fw-bold mb-3">{{ $group }}</h6>
                    <div class="row g-3">
                        @foreach($moduleKeys as $moduleKey)
                            @php
                                $def = PermissionModules::definitions()[$moduleKey];
                                $checked = old("modules.{$moduleKey}", $perms['modules'][$moduleKey] ?? false);
                                $scope = old("scopes.{$moduleKey}", $perms['scopes'][$moduleKey] ?? 'assigned');
                            @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="modules[{{ $moduleKey }}]" value="1" id="mod_{{ $moduleKey }}" @checked($checked)>
                                        <label class="form-check-label fw-semibold" for="mod_{{ $moduleKey }}">{{ $def['label'] }}</label>
                                    </div>
                                    @if($def['scoped'])
                                        <div class="ms-4">
                                            <label class="form-label small text-muted mb-1">Escopo de dados</label>
                                            <select name="scopes[{{ $moduleKey }}]" class="form-select form-select-sm">
                                                <option value="all" @selected($scope === 'all')>Todos (empresa)</option>
                                                <option value="assigned" @selected($scope === 'assigned')>Somente onde sou responsável</option>
                                                @if($moduleKey === 'dailies')
                                                <option value="own" @selected($scope === 'own')>Somente meus registros</option>
                                                @endif
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Salvar</button>
        <a href="{{ route('company.permission-profiles.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
@endsection
