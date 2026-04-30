@extends('layouts.app')

@section('title', 'Editar Lead')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Editar Lead</h1>
            <p class="page-subtitle">Atualize as informações da oportunidade.</p>
        </div>
        <a href="{{ route('company.leads.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.leads.update', $lead) }}" method="POST">
                @csrf
                @method('PUT')
                @include('company.leads._form')

                <div class="d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('company.leads.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

