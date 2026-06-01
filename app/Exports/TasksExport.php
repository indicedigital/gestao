<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TasksExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected $tasks) {}

    public function collection()
    {
        return $this->tasks;
    }

    public function headings(): array
    {
        return ['ID', 'Título', 'Projeto', 'Categoria', 'Prioridade', 'Status', 'Responsável', 'SLA', 'Horas Est.', 'Horas Reais', 'Criada em'];
    }

    public function map($task): array
    {
        /** @var Task $task */
        return [
            $task->id,
            $task->title,
            $task->project->name ?? '-',
            Task::CATEGORIES[$task->category] ?? $task->category,
            $task->priority,
            Task::STATUSES[$task->status] ?? $task->status,
            $task->assignee->name ?? '-',
            $task->sla_deadline?->format('d/m/Y H:i') ?? '-',
            $task->estimated_hours ?? '-',
            $task->actual_hours,
            $task->created_at->format('d/m/Y H:i'),
        ];
    }
}
