<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payable;
use Carbon\Carbon;

class FixedExpenseService
{
    /**
     * Gera contas a pagar para despesas fixas (mês atual + 3 meses à frente)
     */
    public function generatePayablesForFixedExpenses(): void
    {
        $expenses = Expense::where('type', 'fixed')
            ->where('is_active', true)
            ->get();
        
        $currentMonth = now()->startOfMonth();
        
        // Agrupa por empresa
        $expensesByCompany = $expenses->groupBy('company_id');
        
        // Gera para o mês atual + 3 meses à frente (4 meses total)
        foreach ($expensesByCompany as $companyId => $companyExpenses) {
            for ($i = 0; $i < 4; $i++) {
                $month = $currentMonth->copy()->addMonths($i);
                $this->generatePayablesForMonth($companyExpenses, $month);
            }
        }
    }
    
    /**
     * Gera contas a pagar para um mês específico
     */
    protected function generatePayablesForMonth($expenses, Carbon $month): void
    {
        // Carrega relacionamentos para evitar N+1
        $expenses->load('supplier');
        
        foreach ($expenses as $expense) {
            $dueDate = $this->calculateDueDate($expense, $month);
            
            // Verifica se já existe uma conta a pagar para este mês
            $existingPayable = Payable::where('company_id', $expense->company_id)
                ->where('type', 'service')
                ->where('category', 'recurring')
                ->where('description', $expense->description)
                ->whereYear('due_date', $dueDate->year)
                ->whereMonth('due_date', $dueDate->month)
                ->where('status', 'pending')
                ->first();
            
            if (!$existingPayable) {
                $supplierName = $expense->supplier ? $expense->supplier->name : null;
                Payable::create([
                    'company_id' => $expense->company_id,
                    'type' => 'service',
                    'category' => 'recurring',
                    'description' => $expense->description,
                    'value' => $expense->value,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'supplier_name' => $supplierName,
                    'notes' => $expense->notes,
                ]);
            } else {
                // Atualiza se o valor ou outros dados mudaram
                $supplierName = $expense->supplier ? $expense->supplier->name : null;
                $existingPayable->update([
                    'value' => $expense->value,
                    'supplier_name' => $supplierName,
                    'notes' => $expense->notes,
                    'due_date' => $dueDate,
                ]);
            }
        }
    }
    
    /**
     * Calcula a data de vencimento baseado no dia configurado
     */
    protected function calculateDueDate(Expense $expense, Carbon $month): Carbon
    {
        $dueDateDay = $expense->due_date_day ?? 5;
        $day = min($dueDateDay, $month->daysInMonth);
        return $month->copy()->day($day);
    }
    
    /**
     * Atualiza contas a pagar pendentes quando uma despesa fixa é atualizada
     */
    public function updatePendingPayablesForExpense(Expense $expense): void
    {
        if ($expense->type !== 'fixed') {
            return;
        }
        
        $supplierName = $expense->supplier ? $expense->supplier->name : null;
        Payable::where('company_id', $expense->company_id)
            ->where('type', 'service')
            ->where('category', 'recurring')
            ->where('description', 'like', '%' . $expense->description . '%')
            ->where('status', 'pending')
            ->update([
                'value' => $expense->value,
                'supplier_name' => $supplierName,
                'notes' => $expense->notes,
            ]);
    }
}
