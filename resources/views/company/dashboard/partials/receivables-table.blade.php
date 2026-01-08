@if($receivables->count() > 0)
<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receivables as $receivable)
            <tr>
                <td>{{ $receivable->client->name ?? '-' }}</td>
                <td>{{ $receivable->description }}</td>
                <td><strong>R$ {{ number_format($receivable->value, 2, ',', '.') }}</strong></td>
                <td>{{ $receivable->due_date->format('d/m/Y') }}</td>
                <td>
                    <a href="{{ route('company.receivables.show', $receivable) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted text-center py-3">Nenhuma conta a receber neste período</p>
@endif
