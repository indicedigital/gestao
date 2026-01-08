@if($payables->count() > 0)
<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead>
            <tr>
                <th>Descrição</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Vencimento</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payables as $payable)
            <tr>
                <td>{{ $payable->description }}</td>
                <td>
                    <span class="badge bg-info">{{ ucfirst($payable->type) }}</span>
                </td>
                <td><strong>R$ {{ number_format($payable->value, 2, ',', '.') }}</strong></td>
                <td>{{ $payable->due_date->format('d/m/Y') }}</td>
                <td>
                    <a href="{{ route('company.payables.show', $payable) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<p class="text-muted text-center py-3">Nenhuma conta a pagar neste período</p>
@endif
