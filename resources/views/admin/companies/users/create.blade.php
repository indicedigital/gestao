@extends('layouts.app')

@section('title', 'Novo Usuário da Empresa')

@section('content')
<div class="page-header">
    <h1 class="page-title">Novo Usuário</h1>
    <p class="page-subtitle">Criar novo usuário e vincular à empresa: {{ $company->name }}</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.companies.users.store', $company) }}">
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
                    <label for="role" class="form-label">
                        <i class="fas fa-user-tag me-2"></i>Papel na Empresa <span class="text-danger">*</span>
                    </label>
                    <select 
                        class="form-select @error('role') is-invalid @enderror" 
                        id="role" 
                        name="role"
                        required
                    >
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>Gerente</option>
                        <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>Usuário</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <strong>Administrador:</strong> Acesso total à empresa<br>
                        <strong>Gerente:</strong> Acesso parcial<br>
                        <strong>Usuário:</strong> Acesso básico
                    </small>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Empresa:</strong> {{ $company->name }}<br>
                Este usuário será criado e automaticamente vinculado à empresa com o papel selecionado.
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.companies.users.index', $company) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Criar e Vincular Usuário
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
