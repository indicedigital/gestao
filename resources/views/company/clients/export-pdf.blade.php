<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Clientes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #5e72e4;
        }
        .header h1 {
            color: #5e72e4;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #5e72e4;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #4c63d2;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #2dce89;
            color: white;
        }
        .badge-secondary {
            background-color: #8392ab;
            color: white;
        }
        .badge-danger {
            background-color: #f5365c;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Clientes</h1>
        <p>{{ $company->name }} - Gerado em {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Documento</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Cidade</th>
                <th>Estado</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr>
                <td>{{ $client->id }}</td>
                <td>{{ $client->name }}</td>
                <td>{{ $client->type === 'pj' ? 'PJ' : 'PF' }}</td>
                <td>{{ $client->document ?? '-' }}</td>
                <td>{{ $client->email ?? '-' }}</td>
                <td>{{ $client->phone ?? '-' }}</td>
                <td>{{ $client->city ?? '-' }}</td>
                <td>{{ $client->state ?? '-' }}</td>
                <td>
                    @php
                        $statusClass = [
                            'active' => 'badge-success',
                            'inactive' => 'badge-secondary',
                            'blocked' => 'badge-danger'
                        ];
                        $class = $statusClass[$client->status] ?? 'badge-secondary';
                    @endphp
                    <span class="badge {{ $class }}">{{ ucfirst($client->status) }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">
                    Nenhum cliente encontrado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Total de registros: {{ $clients->count() }}</p>
    </div>
</body>
</html>
