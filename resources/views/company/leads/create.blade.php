@extends('layouts.app')

@section('title', 'Novo Lead')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Novo Lead</h1>
            <p class="page-subtitle">Registre as informações da reunião e do projeto.</p>
        </div>
        <a href="{{ route('company.leads.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.leads.store') }}" method="POST">
                @csrf
                @include('company.leads._form')

                <div class="d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('company.leads.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

