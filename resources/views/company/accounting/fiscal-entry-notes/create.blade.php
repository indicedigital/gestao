@extends('layouts.app')

@section('title', 'Novo lançamento — NF entrada')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Novo lançamento</h1>
            <p class="page-subtitle text-muted">Nota fiscal de entrada — dados para emissão e recebimento</p>
        </div>
        <a href="{{ route('company.accounting.fiscal-entry-notes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.accounting.fiscal-entry-notes.store') }}" method="POST">
                @csrf
                @include('company.accounting.fiscal-entry-notes._form', ['note' => null])
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Salvar lançamento
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
