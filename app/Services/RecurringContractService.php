<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Receivable;
use Carbon\Carbon;

class RecurringContractService
{
    /**
     * Calcula a data de vencimento baseado na configuração do contrato
     */
    public function calculateDueDate(Contract $contract, Carbon $month): Carbon
    {
        $dueDateType = $contract->recurring_due_date_type ?? 'fixed_day';
        $dueDateDay = $contract->recurring_due_date_day ?? 5;
        
        switch ($dueDateType) {
            case 'last_business_day':
                // Último dia útil do mês
                return $this->getLastBusinessDayOfMonth($month);
                
            case 'first_business_day':
                // Primeiro dia útil do mês
                return $this->getFirstBusinessDayOfMonth($month);
                
            case 'fifth_business_day':
                // Quinto dia útil do mês
                return $this->getNthBusinessDayOfMonth($month, 5);
                
            case 'fixed_day':
            default:
                // Dia fixo do mês
                $day = min($dueDateDay, $month->daysInMonth);
                return $month->copy()->day($day);
        }
    }
    
    /**
     * Retorna o último dia útil do mês
     */
    protected function getLastBusinessDayOfMonth(Carbon $month): Carbon
    {
        $lastDay = $month->copy()->endOfMonth();
        
        // Se for sábado (6) ou domingo (0), volta para sexta
        while ($lastDay->dayOfWeek === Carbon::SATURDAY || $lastDay->dayOfWeek === Carbon::SUNDAY) {
            $lastDay->subDay();
        }
        
        return $lastDay;
    }
    
    /**
     * Retorna o primeiro dia útil do mês
     */
    protected function getFirstBusinessDayOfMonth(Carbon $month): Carbon
    {
        $firstDay = $month->copy()->startOfMonth();
        
        // Se for sábado (6) ou domingo (0), avança para segunda
        while ($firstDay->dayOfWeek === Carbon::SATURDAY || $firstDay->dayOfWeek === Carbon::SUNDAY) {
            $firstDay->addDay();
        }
        
        return $firstDay;
    }
    
    /**
     * Retorna o N-ésimo dia útil do mês
     */
    protected function getNthBusinessDayOfMonth(Carbon $month, int $n): Carbon
    {
        $current = $month->copy()->startOfMonth();
        $count = 0;
        
        while ($count < $n) {
            // Se não for sábado nem domingo, conta como dia útil
            if ($current->dayOfWeek !== Carbon::SATURDAY && $current->dayOfWeek !== Carbon::SUNDAY) {
                $count++;
                if ($count === $n) {
                    return $current;
                }
            }
            $current->addDay();
        }
        
        return $current;
    }
    
    /**
     * Gera contas a receber para contratos recorrentes
     */
    public function generateReceivablesForMonth(Contract $contract, Carbon $month): ?Receivable
    {
        // Verifica se já existe uma conta a receber para este mês
        $existing = Receivable::where('company_id', $contract->company_id)
            ->where('contract_id', $contract->id)
            ->where('type', 'recurring')
            ->whereYear('due_date', $month->year)
            ->whereMonth('due_date', $month->month)
            ->first();
        
        if ($existing) {
            return null; // Já existe, não cria duplicado
        }
        
        // Verifica se o contrato está ativo e se o mês está dentro do período do contrato
        if ($contract->status !== 'active') {
            return null;
        }
        
        // Verifica se o mês está dentro do período do contrato
        if ($contract->start_date) {
            $startMonth = Carbon::parse($contract->start_date)->startOfMonth();
            if ($month->lt($startMonth)) {
                return null;
            }
        }
        
        if ($contract->end_date) {
            $endMonth = Carbon::parse($contract->end_date)->endOfMonth();
            if ($month->gt($endMonth)) {
                return null;
            }
        }
        
        // Calcula a data de vencimento
        $dueDate = $this->calculateDueDate($contract, $month);
        
        // Cria a conta a receber
        return Receivable::create([
            'company_id' => $contract->company_id,
            'client_id' => $contract->client_id,
            'contract_id' => $contract->id,
            'type' => 'recurring',
            'description' => "Mensalidade - {$contract->name} - " . $month->locale('pt_BR')->translatedFormat('F \d\e Y'),
            'value' => $contract->value,
            'due_date' => $dueDate->toDateString(),
            'status' => 'pending',
        ]);
    }
    
    /**
     * Gera contas a receber para todos os contratos recorrentes ativos
     * Para o mês atual e próximo mês
     */
    public function generateAllRecurringReceivables(): array
    {
        $generated = [
            'current_month' => 0,
            'next_month' => 0,
            'errors' => []
        ];
        
        $currentMonth = now()->startOfMonth();
        $nextMonth = now()->addMonth()->startOfMonth();
        
        // Busca todos os contratos recorrentes ativos
        $contracts = Contract::where('type', 'client_recurring')
            ->where('status', 'active')
            ->where('billing_period', 'monthly')
            ->get();
        
        foreach ($contracts as $contract) {
            try {
                // Gera para o mês atual
                $receivable = $this->generateReceivablesForMonth($contract, $currentMonth);
                if ($receivable) {
                    $generated['current_month']++;
                }
                
                // Gera para o próximo mês
                $receivable = $this->generateReceivablesForMonth($contract, $nextMonth);
                if ($receivable) {
                    $generated['next_month']++;
                }
            } catch (\Exception $e) {
                $generated['errors'][] = [
                    'contract_id' => $contract->id,
                    'contract_name' => $contract->name,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $generated;
    }
}
