@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 class="page-title">Bem-vindo, {{ Auth::user()->name }}!</h1>
    <p class="page-subtitle">Todos os sistemas estão funcionando perfeitamente!</p>
</div>

<div class="row">
    <!-- Cards de Estatísticas -->
    <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase;">Usuários</h6>
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
    
    <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
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
    
    <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2" style="font-size: 12px; text-transform: uppercase;">Atividades</h6>
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
    
    <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
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
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Visão Geral</h5>
            </div>
            <div class="card-body">
                <p>Bem-vindo ao seu painel de controle. Aqui você pode gerenciar todas as funcionalidades do sistema.</p>
                
                @if(Auth::user()->is_super_admin ?? false)
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-cog me-2"></i>Acessar Painel Administrativo
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
