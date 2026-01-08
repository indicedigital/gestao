@extends('layouts.app')

@section('title', 'Funcionários')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Funcionários</h1>
            <p class="page-subtitle">Gerencie sua equipe</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('company.employees.generate-payroll') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success" title="Gerar/Atualizar Folha Salarial do Mês">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Gerar/Atualizar Folha Salarial
                </button>
            </form>
            <a href="{{ route('company.employees.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Novo Funcionário
            </a>
        </div>
    </div>


    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Cargo</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                        <tr>
                            <td>{{ $employee->id }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>
                                @php
                                    $typeLabels = [
                                        'clt' => 'CLT',
                                        'pj' => 'PJ',
                                        'freelancer' => 'Freelancer'
                                    ];
                                    $typeLabel = $typeLabels[$employee->type] ?? $employee->type;
                                @endphp
                                <span class="badge bg-{{ $employee->type === 'clt' ? 'primary' : ($employee->type === 'pj' ? 'info' : 'secondary') }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td>{{ $employee->role ?? '-' }}</td>
                            <td>{{ $employee->email ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'dismissed' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$employee->status] ?? 'secondary';
                                    $statusLabels = [
                                        'active' => 'Ativo',
                                        'inactive' => 'Inativo',
                                        'dismissed' => 'Demitido'
                                    ];
                                    $statusLabel = $statusLabels[$employee->status] ?? ucfirst($employee->status);
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <a href="{{ route('company.employees.show', $employee) }}" class="btn btn-sm btn-info text-white" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('company.employees.edit', $employee) }}" class="btn btn-sm btn-warning text-white" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('company.employees.destroy', $employee) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este funcionário?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Remover">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum funcionário cadastrado</p>
                                <a href="{{ route('company.employees.create') }}" class="btn btn-primary">Cadastrar Primeiro Funcionário</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($employees->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $employees->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
