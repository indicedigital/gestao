@extends('layouts.app')

@section('title', 'Editar NF de saída')

@section('content')
<div class="container-fluid py-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Editar NF de saída</h1>
            <p class="page-subtitle text-muted">Recebimento #{{ $note->receivable_payment_id }} — {{ $note->client_name }}</p>
        </div>
        <a href="{{ route('company.accounting.fiscal-exit-notes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('company.accounting.fiscal-exit-notes.update', $note) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('company.accounting.fiscal-exit-notes._form', ['note' => $note])
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Atualizar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
