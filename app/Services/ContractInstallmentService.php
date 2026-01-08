<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractInstallment;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractInstallmentService
{
    /**
     * Gera parcelas automaticamente para um contrato
     */
    public function generateInstallments(Contract $contract): void
    {
        // Remove parcelas existentes se houver
        $contract->installments()->delete();

        // Se não for contrato fixo com cliente, não gera parcelas
        if ($contract->type !== 'client_fixed') {
            return;
        }

        $totalValue = $contract->value;
        $downPaymentValue = $contract->down_payment_value ?? 0;
        $hasDownPayment = $contract->has_down_payment && $downPaymentValue > 0;
        
        // Valor a ser parcelado (total - entrada)
        $installmentValue = $hasDownPayment ? ($totalValue - $downPaymentValue) : $totalValue;
        
        $installmentsCount = $contract->installments_count ?? 1;
        $equalInstallments = $contract->equal_installments ?? true;
        $paymentFrequency = $contract->payment_frequency ?? 'monthly';
        $firstInstallmentDate = $contract->first_installment_date ?? $contract->start_date;

        // Se tem entrada, cria a entrada como parcela 0
        if ($hasDownPayment && $downPaymentValue > 0) {
            ContractInstallment::create([
                'contract_id' => $contract->id,
                'installment_number' => 0,
                'description' => 'Entrada',
                'value' => $downPaymentValue,
                'due_date' => $contract->down_payment_date ?? $contract->start_date,
                'status' => 'pending',
            ]);

            // Cria conta a receber para entrada
            $this->createReceivable($contract, 0, $downPaymentValue, $contract->down_payment_date ?? $contract->start_date, 'Entrada');
        }

        // Calcula valores das parcelas
        $installmentValues = $this->calculateInstallmentValues($installmentValue, $installmentsCount, $equalInstallments);

        // Gera as parcelas
        $currentDate = Carbon::parse($firstInstallmentDate);
        
        for ($i = 0; $i < $installmentsCount; $i++) {
            $installmentNumber = $i + 1;
            $value = $installmentValues[$i];
            
            // Calcula data de vencimento baseado na frequência
            // Primeira parcela usa a data definida, as demais adicionam a frequência
            if ($i === 0) {
                // Primeira parcela usa a data definida
            } else {
                $currentDate = $this->addFrequency($currentDate, $paymentFrequency);
            }

            $installment = ContractInstallment::create([
                'contract_id' => $contract->id,
                'installment_number' => $installmentNumber,
                'description' => "Parcela {$installmentNumber} de {$installmentsCount}",
                'value' => $value,
                'due_date' => $currentDate->toDateString(),
                'status' => 'pending',
            ]);

            // Cria conta a receber para a parcela
            $this->createReceivable($contract, $installmentNumber, $value, $currentDate->toDateString(), "Parcela {$installmentNumber} de {$installmentsCount}");
        }
    }

    /**
     * Calcula valores das parcelas
     */
    protected function calculateInstallmentValues(float $totalValue, int $count, bool $equal): array
    {
        if ($equal) {
            // Parcelas iguais
            $value = round($totalValue / $count, 2);
            $values = array_fill(0, $count, $value);
            
            // Ajusta diferenças de arredondamento na última parcela
            $sum = array_sum($values);
            $diff = $totalValue - $sum;
            if (abs($diff) > 0.01) {
                $values[$count - 1] += $diff;
            }
            
            return $values;
        } else {
            // Parcelas diferentes - retorna array vazio para ser preenchido manualmente
            return array_fill(0, $count, 0);
        }
    }

    /**
     * Adiciona frequência à data
     */
    protected function addFrequency(Carbon $date, string $frequency): Carbon
    {
        return match($frequency) {
            'daily' => $date->copy()->addDay(),
            'weekly' => $date->copy()->addWeek(),
            'biweekly' => $date->copy()->addWeeks(2),
            'monthly' => $date->copy()->addMonth(),
            'quarterly' => $date->copy()->addMonths(3),
            'yearly' => $date->copy()->addYear(),
            default => $date->copy()->addMonth(),
        };
    }

    /**
     * Cria conta a receber para uma parcela
     */
    protected function createReceivable(Contract $contract, int $installmentNumber, float $value, string $dueDate, string $description): Receivable
    {
        return Receivable::create([
            'company_id' => $contract->company_id,
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'type' => 'contract',
            'description' => $description,
            'value' => $value,
            'due_date' => $dueDate,
            'status' => 'pending',
            'installment_number' => $installmentNumber,
            'total_installments' => ($contract->installments_count ?? 0) + ($contract->has_down_payment ? 1 : 0),
        ]);
    }

    /**
     * Atualiza valores de parcelas manualmente
     */
    public function updateInstallmentValues(Contract $contract, array $values, ?array $dates = null): void
    {
        $installments = $contract->installments()
            ->where('installment_number', '>', 0)
            ->orderBy('installment_number')
            ->get();
        
        foreach ($installments as $installment) {
            $index = $installment->installment_number - 1; // Ajusta índice (parcela 1 = índice 0)
            
            if (isset($values[$index])) {
                $newValue = (float) $values[$index];
                $installment->update(['value' => $newValue]);
                
                // Atualiza conta a receber se existir
                $receivable = Receivable::where('contract_id', $contract->id)
                    ->where('installment_number', $installment->installment_number)
                    ->first();
                if ($receivable) {
                    $receivable->update(['value' => $newValue]);
                }
            }
            
            // Atualiza data se fornecida
            if ($dates && isset($dates[$index])) {
                $installment->update(['due_date' => $dates[$index]]);
                
                $receivable = Receivable::where('contract_id', $contract->id)
                    ->where('installment_number', $installment->installment_number)
                    ->first();
                if ($receivable) {
                    $receivable->update(['due_date' => $dates[$index]]);
                }
            }
        }
    }

    /**
     * Gera parcelas a partir do preview editado
     */
    public function generateInstallmentsFromPreview(Contract $contract, Request $request): void
    {
        // Limpa parcelas existentes
        $contract->installments()->delete();
        Receivable::where('contract_id', $contract->id)->delete();

        $values = $request->input('preview_installment_values', []);
        $dates = $request->input('preview_installment_dates', []);
        $statuses = $request->input('preview_installment_status', []);

        // Valida se a soma é igual ao valor total
        $totalValue = $contract->value;
        $calculatedTotal = array_sum($values);
        
        if (abs($totalValue - $calculatedTotal) > 0.01) {
            throw new \Exception("A soma das parcelas (R$ " . number_format($calculatedTotal, 2, ',', '.') . ") não corresponde ao valor total do contrato (R$ " . number_format($totalValue, 2, ',', '.') . ").");
        }

        // Cria parcelas
        foreach ($values as $installmentNumber => $value) {
            $installmentNumber = (int) $installmentNumber;
            $value = (float) $value;
            $dueDate = $dates[$installmentNumber] ?? $contract->start_date;
            $status = $statuses[$installmentNumber] ?? 'pending';

            $description = $installmentNumber === 0 
                ? 'Entrada' 
                : "Parcela {$installmentNumber}/" . ($contract->installments_count ?? 1);

            $installment = ContractInstallment::create([
                'contract_id' => $contract->id,
                'installment_number' => $installmentNumber,
                'description' => $description,
                'value' => $value,
                'due_date' => $dueDate,
                'status' => $status,
            ]);

            // Cria conta a receber
            $this->createReceivableFromInstallment($contract, $installment, $description);
        }
    }

    /**
     * Cria conta a receber a partir de uma parcela
     */
    protected function createReceivableFromInstallment(Contract $contract, ContractInstallment $installment, string $description): Receivable
    {
        return Receivable::create([
            'company_id' => $contract->company_id,
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'contract_installment_id' => $installment->id,
            'type' => 'contract_installment',
            'description' => $description . " - Contrato: " . $contract->name,
            'value' => $installment->value,
            'due_date' => $installment->due_date,
            'status' => $installment->status === 'paid' ? 'paid' : ($installment->status === 'overdue' ? 'overdue' : 'pending'),
            'installment_number' => $installment->installment_number,
            'total_installments' => ($contract->installments_count ?? 0) + ($contract->has_down_payment ? 1 : 0),
        ]);
    }
}
