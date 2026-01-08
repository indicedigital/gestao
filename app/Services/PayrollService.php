<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Payable;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Atualiza a folha salarial do mês para uma empresa
     */
    public function updateMonthlyPayroll(Company $company, ?Carbon $month = null): void
    {
        if (!$month) {
            $month = now();
        }

        // Busca todos os funcionários CLT e PJ ativos com salário
        $employees = Employee::where('company_id', $company->id)
            ->whereIn('type', ['clt', 'pj'])
            ->where('status', 'active')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->get();

        // Calcula o total da folha
        $totalPayroll = $employees->sum('salary');

        // Se não há funcionários com salário, não cria folha
        if ($totalPayroll <= 0) {
            return;
        }

        // Data de vencimento: dia 5 do mês seguinte (padrão de folha)
        $dueDate = $month->copy()->addMonth()->day(5);

        // Descrição da folha
        $monthName = $month->locale('pt_BR')->translatedFormat('F \d\e Y');
        $description = "Folha de Pagamento - {$monthName}";
        
        // Lista de funcionários incluídos na folha (para referência)
        $employeesList = $employees->pluck('name')->take(5)->implode(', ');
        if ($employees->count() > 5) {
            $employeesList .= ' e mais ' . ($employees->count() - 5);
        }

        // Busca se já existe uma folha para este mês
        // Procura por folha com vencimento no mesmo mês/ano
        $payroll = Payable::where('company_id', $company->id)
            ->where('type', 'salary')
            ->where('description', 'like', "%Folha de Pagamento%")
            ->whereYear('due_date', $dueDate->year)
            ->whereMonth('due_date', $dueDate->month)
            ->first();

        if ($payroll) {
            // Atualiza a folha existente
            $payroll->update([
                'value' => $totalPayroll,
                'description' => $description,
                'notes' => "Folha salarial gerada automaticamente. Valor calculado pela soma dos salários dos funcionários CLT e PJ ativos. Funcionários: {$employeesList}",
            ]);
        } else {
            // Cria nova folha
            Payable::create([
                'company_id' => $company->id,
                'type' => 'salary',
                'category' => 'clt',
                'description' => $description,
                'value' => $totalPayroll,
                'due_date' => $dueDate->toDateString(),
                'status' => 'pending',
                'notes' => "Folha salarial gerada automaticamente. Valor calculado pela soma dos salários dos funcionários CLT e PJ ativos. Funcionários: {$employeesList}",
            ]);
        }
    }

    /**
     * Atualiza a folha quando um funcionário é criado/atualizado
     */
    public function handleEmployeeChange(Employee $employee): void
    {
        // Se for freelancer, não atualiza folha (freelancers são contas variáveis)
        if ($employee->type === 'freelancer') {
            return;
        }

        // Sempre atualiza a folha do mês atual (mesmo se não tiver salário, para recalcular)
        $this->updateMonthlyPayroll($employee->company);
    }

    /**
     * Remove funcionário da folha quando é deletado
     */
    public function handleEmployeeDeletion(Company $company): void
    {
        $this->updateMonthlyPayroll($company);
    }
}
