@extends('layouts.app')

@section('title', 'Notas fiscais de saída')

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">NF de saída (contas a receber)</h1>
            <p class="text-muted mb-0">Geradas automaticamente ao registrar um recebimento. Ajuste dados ou marque como emitida aqui.</p>
            @if(($pendingSyncCount ?? 0) > 0)
                <p class="small text-warning mb-0 mt-2">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Há <strong>{{ $pendingSyncCount }}</strong> recebimento(s) sem NF de saída — use o botão ao lado para criar ou para atualizar dados das notas ainda não emitidas.
                </p>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <form action="{{ route('company.accounting.fiscal-exit-notes.sync-from-receivables') }}" method="post" class="d-inline" onsubmit="return confirm('Sincronizar: criar NF que faltam e atualizar dados das NF ainda não emitidas (cliente, valores, datas). Notas já emitidas não serão alteradas. Continuar?');">
                @csrf
                <button type="submit" class="btn btn-warning text-dark" title="Cria lançamentos faltantes e atualiza tomador/dados do recebimento nas NF pendentes; NF emitidas não mudam">
                    <i class="fas fa-sync-alt me-1"></i> Sincronizar com recebimentos
                    @if(($pendingSyncCount ?? 0) > 0)
                        <span class="badge bg-dark ms-1">{{ $pendingSyncCount }}</span>
                    @endif
                </button>
            </form>
            <a href="{{ route('company.accounting.report', ['month' => $monthFilter, 'direction' => 'saida']) }}" class="btn btn-outline-primary">
                <i class="fas fa-file-alt me-1"></i> Relatório não emitidas
            </a>
            <a href="{{ route('company.receivables.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-circle-down me-1"></i> Contas a receber
            </a>
        </div>
    </div>

    <p class="small text-muted mb-3">
        Indicadores referentes ao período <strong>{{ $statsMonthLabel }}</strong>
        <span class="text-muted">(data de recebimento; alinhado ao filtro de mês abaixo)</span>
    </p>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Total de lançamentos</div>
                    <div class="fs-3 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-3">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Pendente de emissão</div>
                    <div class="fs-3 fw-bold text-warning">{{ $stats['pending_issue'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-3">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Já emitidas</div>
                    <div class="fs-3 fw-bold text-success">{{ $stats['issued'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="get" action="{{ route('company.accounting.fiscal-exit-notes.index') }}" class="row g-2 align-items-end mb-4">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Mês (data recebimento)</label>
                    <input type="month" name="month" class="form-control" value="{{ $monthFilter }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Emissão</label>
                    <select name="issued" class="form-select">
                        <option value="">Todos</option>
                        <option value="0" @selected(request('issued') === '0' || request('issued') === 'false')>Não emitida</option>
                        <option value="1" @selected(request('issued') === '1' || request('issued') === 'true')>Emitida</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Busca</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cliente, documento ou descrição">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Recebimento</th>
                            <th>Conta a receber</th>
                            <th>Cliente (tomador)</th>
                            <th>Tipo</th>
                            <th class="text-end">Valor</th>
                            <th>NF</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notes as $n)
                            <tr>
                                <td>{{ $n->received_date->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('company.receivables.show', $n->receivable_id) }}" class="text-decoration-none">#{{ $n->receivable_id }}</a>
                                    @if($n->receivable_description)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($n->receivable_description, 36) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $n->client_name }}</strong>
                                    @if($n->client_email)
                                        <br><small class="text-muted">{{ $n->client_email }}</small>
                                    @endif
                                </td>
                                <td>{{ $n->person_type === 'pj' ? 'PJ' : 'PF' }}</td>
                                <td class="text-end">R$ {{ number_format($n->amount_received, 2, ',', '.') }}</td>
                                <td>
                                    @if($n->is_issued)
                                        <span class="badge bg-success">Emitida</span>
                                        @if($n->issued_at)
                                            <br><small class="text-muted">{{ $n->issued_at->format('d/m/Y') }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <form action="{{ route('company.accounting.fiscal-exit-notes.toggle-issued', $n) }}" method="post" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Alternar emitida / pendente">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('company.accounting.fiscal-exit-notes.edit', $n) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('company.accounting.fiscal-exit-notes.destroy', $n) }}" method="post" class="d-inline" onsubmit="return confirm('Remover este lançamento de NF? O recebimento em contas a receber não será alterado.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Nenhum lançamento. Registre pagamentos em Contas a receber para gerar NF de saída.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
