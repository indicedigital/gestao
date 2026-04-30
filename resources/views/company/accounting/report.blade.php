@extends('layouts.app')

@section('title', 'Relatório fiscal — não emitidas')

@push('styles')
<style>
    .fiscal-report-root { width: 100%; max-width: 100%; }
    .fiscal-report-toolbar { margin-bottom: 1.25rem; }
    .fiscal-report-document {
        width: 100%;
        max-width: 100%;
        margin: 0;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem;
        box-shadow: 0 1px 8px rgba(15, 23, 42, 0.05);
    }
    .fiscal-report-header {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid #1e293b;
    }
    .fiscal-report-logo {
        max-height: 56px;
        max-width: 200px;
        width: auto;
        object-fit: contain;
        display: block;
    }
    .fiscal-report-logo-fallback {
        width: 52px;
        height: 52px;
        border-radius: 8px;
        background: #1e293b;
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .fiscal-report-header-text h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.2rem 0;
        letter-spacing: -0.02em;
    }
    .fiscal-report-header-text .fiscal-report-tagline {
        color: #475569;
        font-size: 0.8125rem;
        margin: 0;
        line-height: 1.4;
    }
    .fiscal-report-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem 1.5rem;
        padding: 0.65rem 1rem;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        margin-bottom: 1rem;
        font-size: 0.8125rem;
        color: #334155;
    }
    .fiscal-report-summary strong { color: #0f172a; }
    .fiscal-report-table-wrap {
        width: 100%;
        border: 1px solid #94a3b8;
        border-radius: 4px;
        overflow-x: auto;
    }
    .fiscal-report-table-wrap table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        font-size: 0.875rem;
    }
    .fiscal-report-table-wrap thead th {
        background: #334155;
        color: #f8fafc;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 0.65rem 0.75rem;
        border: 1px solid #1e293b;
        white-space: nowrap;
    }
    .fiscal-report-table-wrap tbody td {
        padding: 0.55rem 0.75rem;
        border: 1px solid #e2e8f0;
        vertical-align: top;
        color: #1e293b;
    }
    .fiscal-report-table-wrap tbody tr:nth-child(even) td {
        background: #f8fafc;
    }
    .fiscal-report-table-wrap tbody tr:hover td {
        background: #eff6ff;
    }
    .fiscal-report-table-doc {
        font-family: ui-monospace, Consolas, monospace;
        font-size: 0.8125rem;
        white-space: nowrap;
    }
    .fiscal-report-empty {
        text-align: center;
        padding: 2rem 1rem;
        color: #64748b;
    }
    .fiscal-report-footer {
        margin-top: 1rem;
        padding-top: 0.65rem;
        border-top: 1px solid #cbd5e1;
        font-size: 0.6875rem;
        color: #64748b;
        text-align: right;
    }
    @media print {
        .fiscal-report-document {
            border: none;
            box-shadow: none;
            border-radius: 0;
            padding: 0;
        }
        .fiscal-report-header {
            border-bottom-color: #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .fiscal-report-logo { max-height: 36px; max-width: 140px; }
        .fiscal-report-logo-fallback {
            width: 36px;
            height: 36px;
            font-size: 11px;
            border-radius: 4px;
        }
        .fiscal-report-header-text h2 { font-size: 13pt; }
        .fiscal-report-header-text .fiscal-report-tagline { font-size: 8pt; }
        .fiscal-report-summary {
            padding: 4px 8px;
            margin-bottom: 6px;
            font-size: 8pt;
            background: #f1f5f9;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .fiscal-report-table-wrap {
            border-color: #000;
        }
        .fiscal-report-table-wrap thead th {
            font-size: 6.5pt;
            padding: 3px 5px;
            background: #1e293b !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .fiscal-report-table-wrap tbody td {
            font-size: 7pt;
            padding: 2px 4px;
            line-height: 1.25;
        }
        .fiscal-report-table-doc { font-size: 6.5pt; }
        .fiscal-report-table-wrap tbody tr:nth-child(even) td {
            background: #f8fafc !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .fiscal-report-footer { font-size: 6pt; margin-top: 6px; padding-top: 4px; }
        .fiscal-report-table-wrap table {
            page-break-inside: auto;
        }
        .fiscal-report-table-wrap thead {
            display: table-header-group;
        }
        .fiscal-report-table-wrap tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }
    }
</style>
<style media="print">
    .sidebar, .header, .menu-toggle, .btn, .no-print, .fiscal-report-toolbar { display: none !important; }
    .main-content { margin: 0 !important; padding: 0.5rem !important; max-width: 100% !important; }
    body { background: white !important; }
    .container-fluid { max-width: 100% !important; padding-left: 0 !important; padding-right: 0 !important; }
</style>
@endpush

@section('content')
@php
    $refMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthFilter)->startOfMonth();
    $notesList = $direction === 'entrada' ? $entryNotes : $exitNotes;
    $typeLabel = $direction === 'entrada' ? 'Notas fiscais de entrada' : 'Notas fiscais de saída';
@endphp
<div class="container-fluid py-4 fiscal-report-root">
    <div class="fiscal-report-toolbar no-print d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h1 class="h3 mb-1">Relatório fiscal (pendentes de emissão)</h1>
            <p class="text-muted mb-0">Tabela em <strong>tela cheia</strong>. Na impressão ou PDF, layout compacto em <strong>A4 paisagem</strong> para caber no mínimo de páginas.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('company.accounting.fiscal-entry-notes.index') }}" class="btn btn-outline-secondary btn-sm">NF entrada</a>
            <a href="{{ route('company.accounting.fiscal-exit-notes.index') }}" class="btn btn-outline-secondary btn-sm">NF saída</a>
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> Imprimir / PDF</button>
        </div>
    </div>

    <div class="card shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="get" action="{{ route('company.accounting.report') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tipo de nota</label>
                    <select name="direction" class="form-select" id="report_direction">
                        <option value="entrada" @selected($direction === 'entrada')>Notas fiscais de entrada</option>
                        <option value="saida" @selected($direction === 'saida')>Notas fiscais de saída (contas a receber)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mês de referência</label>
                    <input type="month" name="month" class="form-control" value="{{ $monthFilter }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Gerar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="fiscal-report-document">
        <header class="fiscal-report-header">
            <div class="fiscal-report-logo-wrap">
                @if($company->logoPublicUrl())
                    <img src="{{ $company->logoPublicUrl() }}" alt="{{ $company->name }}" class="fiscal-report-logo">
                @else
                    <div class="fiscal-report-logo-fallback" aria-hidden="true">{{ $company->logoInitials() }}</div>
                @endif
            </div>
            <div class="fiscal-report-header-text">
                <h2>{{ $company->name }}</h2>
                <p class="fiscal-report-tagline">
                    <strong>{{ $typeLabel }}</strong> — pendentes de emissão &nbsp;|&nbsp; Competência <strong>{{ $refMonth->format('m/Y') }}</strong>
                    @if($company->email || $company->phone)
                        &nbsp;|&nbsp;
                        @if($company->email){{ $company->email }}@endif
                        @if($company->email && $company->phone) · @endif
                        @if($company->phone){{ $company->phone }}@endif
                    @endif
                </p>
            </div>
        </header>

        <div class="fiscal-report-summary">
            @if($notesList->isEmpty())
                <span>Nenhum lançamento pendente neste período.</span>
            @else
                <span><strong>{{ $notesList->count() }}</strong> registro(s)</span>
                <span>Total: <strong>R$ {{ number_format($totalAmount, 2, ',', '.') }}</strong></span>
                <span>Documento gerado em <strong>{{ now()->format('d/m/Y H:i') }}</strong></span>
            @endif
        </div>

        @if($notesList->isEmpty())
            <div class="fiscal-report-empty">
                Não há notas pendentes de emissão para o mês selecionado.
            </div>
        @else
            <div class="fiscal-report-table-wrap">
                @if($direction === 'entrada')
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tomador / Razão social</th>
                                <th>Tipo</th>
                                <th>CPF / CNPJ</th>
                                <th>Contato</th>
                                <th>Endereço completo</th>
                                <th class="text-end">Valor (R$)</th>
                                <th>Data receb.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notesList as $i => $n)
                                <tr>
                                    <td class="text-muted text-center">{{ $i + 1 }}</td>
                                    <td><strong>{{ $n->client_name }}</strong></td>
                                    <td>{{ $n->person_type === 'pj' ? 'PJ' : 'PF' }}</td>
                                    <td class="fiscal-report-table-doc">{{ \App\Support\BrazilianDocument::format($n->document, $n->document_type) }}</td>
                                    <td>
                                        @if($n->client_phone){{ $n->client_phone }}@endif
                                        @if($n->client_phone && $n->client_email)<br>@endif
                                        @if($n->client_email){{ $n->client_email }}@endif
                                        @if(!$n->client_phone && !$n->client_email)—@endif
                                    </td>
                                    <td>
                                        @php
                                            $parts = array_filter([$n->address, $n->city, $n->state, $n->zip_code, $n->country]);
                                        @endphp
                                        {{ $parts ? implode(', ', $parts) : '—' }}
                                    </td>
                                    <td class="text-end text-nowrap">{{ number_format($n->amount_received, 2, ',', '.') }}</td>
                                    <td class="text-nowrap">{{ $n->received_date->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Conta a receber</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>CPF / CNPJ</th>
                                <th>Contato</th>
                                <th>Endereço</th>
                                <th class="text-end">Valor (R$)</th>
                                <th>Data receb.</th>
                                <th>Pagamento</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notesList as $i => $n)
                                <tr>
                                    <td class="text-muted text-center">{{ $i + 1 }}</td>
                                    <td>
                                        <strong>#{{ $n->receivable_id }}</strong>
                                        @if($n->receivable_description)
                                            <br><span style="font-size:0.85em;color:#64748b">{{ \Illuminate\Support\Str::limit($n->receivable_description, 48) }}</span>
                                        @endif
                                    </td>
                                    <td><strong>{{ $n->client_name }}</strong></td>
                                    <td>{{ $n->person_type === 'pj' ? 'PJ' : 'PF' }}</td>
                                    <td class="fiscal-report-table-doc">{{ \App\Support\BrazilianDocument::format($n->document, $n->document_type) }}</td>
                                    <td>
                                        @if($n->client_phone){{ $n->client_phone }}@endif
                                        @if($n->client_phone && $n->client_email)<br>@endif
                                        @if($n->client_email){{ $n->client_email }}@endif
                                        @if(!$n->client_phone && !$n->client_email)—@endif
                                    </td>
                                    <td>
                                        @php
                                            $parts = array_filter([$n->address, $n->city, $n->state, $n->zip_code, $n->country]);
                                        @endphp
                                        {{ $parts ? implode(', ', $parts) : '—' }}
                                    </td>
                                    <td class="text-end text-nowrap">{{ number_format($n->amount_received, 2, ',', '.') }}</td>
                                    <td class="text-nowrap">{{ $n->received_date->format('d/m/Y') }}</td>
                                    <td><small>{{ $n->payment_method ?: '—' }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        <footer class="fiscal-report-footer">
            Relatório interno para apoio à emissão fiscal — ÍNDICE DIGITAL
        </footer>
    </div>
</div>
@endsection
