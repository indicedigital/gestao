<?php

namespace App\Services;

use App\Models\Contract;
use InvalidArgumentException;

class ProjectContractSyncService
{
    public function apply(Contract $contract): array
    {
        if (! in_array($contract->type, ['client_fixed', 'client_recurring'], true)) {
            throw new InvalidArgumentException('Selecione um contrato de cliente.');
        }

        if (! $contract->client_id) {
            throw new InvalidArgumentException('O contrato selecionado não possui cliente vinculado.');
        }

        return [
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'type' => $contract->type === 'client_recurring' ? 'recurring' : 'fixed',
            'total_value' => $contract->value,
            'installments' => $contract->type === 'client_recurring'
                ? 1
                : max(1, (int) ($contract->installments_count ?: 1)),
            'category' => $contract->type === 'client_recurring' ? 'suporte' : 'desenvolvimento',
            'start_date' => $contract->start_date,
            'end_date' => $contract->end_date,
        ];
    }

    public function previewData(Contract $contract): array
    {
        $sync = $this->apply($contract);

        return [
            'client_name' => $contract->client?->name ?? '—',
            'contract_name' => $contract->name,
            'contract_number' => $contract->number,
            'financial_type' => $sync['type'] === 'recurring' ? 'Recorrente (Mensal)' : 'Fechado (Parcelado)',
            'category_label' => \App\Models\Project::CATEGORIES[$sync['category']] ?? $sync['category'],
            'total_value' => number_format((float) $sync['total_value'], 2, ',', '.'),
            'installments' => $sync['installments'],
            'start_date' => $contract->start_date?->format('d/m/Y'),
            'end_date' => $contract->end_date?->format('d/m/Y'),
        ];
    }
}
