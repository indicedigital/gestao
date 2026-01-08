<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ClientsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $clients;

    public function __construct($clients)
    {
        $this->clients = $clients;
    }

    public function collection()
    {
        return $this->clients;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Tipo',
            'Documento',
            'E-mail',
            'Telefone',
            'Endereço',
            'Cidade',
            'Estado',
            'CEP',
            'Status',
            'Data de Cadastro',
        ];
    }

    public function map($client): array
    {
        return [
            $client->id,
            $client->name,
            $client->type === 'pj' ? 'PJ' : 'PF',
            $client->document ?? '-',
            $client->email ?? '-',
            $client->phone ?? '-',
            $client->address ?? '-',
            $client->city ?? '-',
            $client->state ?? '-',
            $client->zip_code ?? '-',
            ucfirst($client->status),
            $client->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '5e72e4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 30,
            'C' => 10,
            'D' => 18,
            'E' => 30,
            'F' => 15,
            'G' => 40,
            'H' => 20,
            'I' => 10,
            'J' => 12,
            'K' => 12,
            'L' => 18,
        ];
    }
}
