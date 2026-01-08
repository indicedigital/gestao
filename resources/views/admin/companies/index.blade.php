@extends('layouts.app')

@section('title', 'Gerenciar Empresas')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Empresas</h1>
        <p class="page-subtitle">Gerencie todas as empresas do sistema</p>
    </div>
    <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nova Empresa
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td>{{ $company->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; margin-right: 10px;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <strong>{{ $company->name }}</strong>
                                </div>
                            </td>
                            <td><code>{{ $company->slug ?? 'N/A' }}</code></td>
                            <td>{{ $company->email ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'suspended' => 'warning',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$company->status ?? 'active'] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ ucfirst($company->status ?? 'active') }}
                                </span>
                            </td>
                            <td>{{ $company->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-sm btn-info text-white" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-sm btn-warning text-white" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja excluir esta empresa?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhuma empresa encontrada</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($companies->hasPages())
            <div class="mt-3">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
