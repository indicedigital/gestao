@extends('layouts.app')

@section('title', 'Detalhes do Funcionário')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">{{ $employee->name }}</h1>
            <p class="page-subtitle">Detalhes do funcionário</p>
        </div>
        <div>
            <a href="{{ route('company.employees.edit', $employee) }}" class="btn btn-warning text-white">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <a href="{{ route('company.employees.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Tipo:</dt>
                <dd class="col-sm-9">
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
                </dd>

                <dt class="col-sm-3">E-mail:</dt>
                <dd class="col-sm-9">{{ $employee->email ?? '-' }}</dd>

                <dt class="col-sm-3">Telefone:</dt>
                <dd class="col-sm-9">{{ $employee->phone ?? '-' }}</dd>

                <dt class="col-sm-3">Cargo:</dt>
                <dd class="col-sm-9">{{ $employee->position ?? '-' }}</dd>

                <dt class="col-sm-3">Função:</dt>
                <dd class="col-sm-9">{{ $employee->role ?? '-' }}</dd>

                @if($employee->salary)
                <dt class="col-sm-3">Salário:</dt>
                <dd class="col-sm-9">R$ {{ number_format($employee->salary, 2, ',', '.') }}</dd>
                @endif

                <dt class="col-sm-3">Status:</dt>
                <dd class="col-sm-9">
                    @php
                        $statusColors = [
                            'active' => 'success',
                            'inactive' => 'secondary',
                            'dismissed' => 'danger'
                        ];
                        $statusColor = $statusColors[$employee->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusColor }}">{{ ucfirst($employee->status) }}</span>
                </dd>

                @if($employee->hire_date)
                <dt class="col-sm-3">Data de Contratação:</dt>
                <dd class="col-sm-9">{{ \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') }}</dd>
                @endif

                @if($employee->address)
                <dt class="col-sm-3">Endereço:</dt>
                <dd class="col-sm-9">{{ $employee->address }}</dd>
                @endif

                @if($employee->notes)
                <dt class="col-sm-3">Observações:</dt>
                <dd class="col-sm-9">{{ $employee->notes }}</dd>
                @endif
            </dl>
        </div>
    </div>
</div>
@endsection
