@extends('layouts.app')

@section('title', 'Contratos')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Contratos</h1>
            <p class="page-subtitle">Gerencie seus contratos</p>
        </div>
        <a href="{{ route('company.contracts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Novo Contrato
        </a>
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
                            <th>Cliente/Funcionário</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        <tr>
                            <td>{{ $contract->id }}</td>
                            <td>{{ $contract->name }}</td>
                            <td>
                                @php
                                    $typeLabels = [
                                        'client_recurring' => 'Cliente Recorrente',
                                        'client_fixed' => 'Cliente Fechado',
                                        'employee_clt' => 'Funcionário CLT',
                                        'employee_pj' => 'Funcionário PJ'
                                    ];
                                    $typeLabel = $typeLabels[$contract->type] ?? $contract->type;
                                @endphp
                                <span class="badge bg-info">{{ $typeLabel }}</span>
                            </td>
                            <td>
                                @if($contract->client)
                                    {{ $contract->client->name }}
                                @elseif($contract->employee)
                                    {{ $contract->employee->name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>R$ {{ number_format($contract->value, 2, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active' => 'success',
                                        'suspended' => 'warning',
                                        'cancelled' => 'danger',
                                        'expired' => 'secondary'
                                    ];
                                    $statusColor = $statusColors[$contract->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($contract->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('company.contracts.show', $contract) }}" class="btn btn-sm btn-info text-white" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('company.contracts.edit', $contract) }}" class="btn btn-sm btn-warning text-white" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('company.contracts.destroy', $contract) }}" method="POST" class="d-inline delete-form" data-message="Tem certeza que deseja remover este contrato?">
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
                                <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum contrato cadastrado</p>
                                <a href="{{ route('company.contracts.create') }}" class="btn btn-primary">Cadastrar Primeiro Contrato</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($contracts->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $contracts->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
