@extends('layouts.app')

@section('title', 'Dashboard Administrativo')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard Administrativo</h1>
    <p class="page-subtitle">Bem-vindo, {{ $user->name }}! Todos os sistemas estão funcionando perfeitamente.</p>
</div>

<div class="row">
    <!-- Cards de Estatísticas -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase;">Total Usuários</h6>
                        <h3 class="mb-0" style="font-size: 28px; font-weight: 600;">0</h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> 0%
                        </small>
                    </div>
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase;">Empresas</h6>
                        <h3 class="mb-0" style="font-size: 28px; font-weight: 600;">0</h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> 0%
                        </small>
                    </div>
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase;">Planos Ativos</h6>
                        <h3 class="mb-0" style="font-size: 28px; font-weight: 600;">0</h3>
                        <small class="text-success">
                            <i class="fas fa-arrow-up"></i> 0%
                        </small>
                    </div>
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase;">Sistema</h6>
                        <h3 class="mb-0" style="font-size: 28px; font-weight: 600;">OK</h3>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> Online
                        </small>
                    </div>
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações do Usuário</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Nome:</strong> {{ $user->name }}
                </p>
                <p class="mb-2">
                    <strong>E-mail:</strong> {{ $user->email }}
                </p>
                <p class="mb-2">
                    <strong>Status:</strong> 
                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }}">
                        {{ $user->status ?? 'N/A' }}
                    </span>
                </p>
                <p class="mb-0">
                    <strong>Super Admin:</strong> 
                    <span class="badge bg-{{ $user->is_super_admin ? 'primary' : 'secondary' }}">
                        {{ $user->is_super_admin ? 'Sim' : 'Não' }}
                    </span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                        <i class="fas fa-users me-2"></i>Gerenciar Usuários
                    </a>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-primary">
                        <i class="fas fa-building me-2"></i>Gerenciar Empresas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
