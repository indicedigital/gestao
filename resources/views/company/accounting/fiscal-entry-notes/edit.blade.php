@extends('layouts.app')

@section('title', 'Editar lançamento — NF entrada')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Editar lançamento</h1>
            <p class="page-subtitle text-muted">#{{ $note->id }} — {{ $note->client_name }}</p>
        </div>
        <a href="{{ route('company.accounting.fiscal-entry-notes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.accounting.fiscal-entry-notes.update', $note) }}" method="POST">
                @csrf
                @method('PUT')
                @include('company.accounting.fiscal-entry-notes._form', ['note' => $note])
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Atualizar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
