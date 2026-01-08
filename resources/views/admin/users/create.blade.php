@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content')
<div class="page-header">
    <h1 class="page-title">Novo Usuário</h1>
    <p class="page-subtitle">Preencha os dados abaixo para criar um novo usuário</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">
                        <i class="fas fa-user me-2"></i>Nome Completo <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control @error('name') is-invalid @enderror" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        required 
                        autofocus
                        placeholder="Nome completo"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>E-mail <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required
                        placeholder="usuario@exemplo.com"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Senha <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Mínimo 8 caracteres"
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Mínimo 8 caracteres</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">
                        <i class="fas fa-lock me-2"></i>Confirmar Senha <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                        placeholder="Digite a senha novamente"
                    >
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">
                        <i class="fas fa-info-circle me-2"></i>Status
                    </label>
                    <select 
                        class="form-select @error('status') is-invalid @enderror" 
                        id="status" 
                        name="status"
                    >
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                        <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="is_super_admin" class="form-label">
                        <i class="fas fa-shield-alt me-2"></i>Super Administrador
                    </label>
                    <div class="form-check form-switch mt-2">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            id="is_super_admin" 
                            name="is_super_admin" 
                            value="1"
                            {{ old('is_super_admin') ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="is_super_admin">
                            Tornar este usuário super administrador
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Salvar Usuário
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
