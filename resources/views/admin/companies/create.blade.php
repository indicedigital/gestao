@extends('layouts.app')

@section('title', 'Nova Empresa')

@section('content')
<div class="page-header">
    <h1 class="page-title">Nova Empresa</h1>
    <p class="page-subtitle">Preencha os dados abaixo para criar uma nova empresa</p>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.companies.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">
                        <i class="fas fa-building me-2"></i>Nome da Empresa <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control @error('name') is-invalid @enderror" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        required 
                        autofocus
                        placeholder="Nome da empresa"
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>E-mail
                    </label>
                    <input 
                        type="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        placeholder="empresa@exemplo.com"
                    >
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone me-2"></i>Telefone
                    </label>
                    <input 
                        type="text" 
                        class="form-control @error('phone') is-invalid @enderror" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone') }}"
                        placeholder="(00) 00000-0000"
                    >
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">
                        <i class="fas fa-info-circle me-2"></i>Status
                    </label>
                    <select 
                        class="form-select @error('status') is-invalid @enderror" 
                        id="status" 
                        name="status"
                    >
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Ativa</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspensa</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="owner_id" class="form-label">
                        <i class="fas fa-user-shield me-2"></i>Administrador da Empresa
                    </label>
                    <select 
                        class="form-select @error('owner_id') is-invalid @enderror" 
                        id="owner_id" 
                        name="owner_id"
                    >
                        <option value="">Selecione um administrador (opcional)</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('owner_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('owner_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">O administrador poderá fazer login e gerenciar a empresa</small>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Salvar Empresa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
