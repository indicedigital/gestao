<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailiesExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected $dailies) {}

    public function collection()
    {
        return $this->dailies;
    }

    public function headings(): array
    {
        return ['Data', 'Colaborador', 'Projeto', 'Task', 'Horas', 'Descrição', 'Impedimentos'];
    }

    public function map($daily): array
    {
        return [
            $daily->work_date->format('d/m/Y'),
            $daily->employee->name ?? $daily->user->name ?? '-',
            $daily->project->name ?? '-',
            $daily->task->title ?? '-',
            $daily->hours,
            $daily->description,
            $daily->blockers ?? '-',
        ];
    }
}
