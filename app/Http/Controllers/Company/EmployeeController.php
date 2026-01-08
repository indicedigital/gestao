<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    protected function getCurrentCompany(): Company
    {
        $user = Auth::user();
        
        // Super admin não pode acessar rotas de empresa
        if ($user->is_super_admin ?? false) {
            abort(403, 'Super administradores devem usar o painel administrativo.');
        }
        
        $companyId = session('current_company_id');
        if (!$companyId) {
            $company = $user->currentCompany();
            if ($company) {
                session(['current_company_id' => $company->id]);
                return $company;
            }
            abort(403, 'Você não possui uma empresa vinculada.');
        }
        return Company::findOrFail($companyId);
    }

    public function index(Request $request)
    {
        $company = $this->getCurrentCompany();
        $employees = Employee::where('company_id', $company->id)
            ->latest()
            ->paginate(15);
        
        // Detecta se é mobile
        $isMobile = $request->has('mobile') || 
                   $request->cookie('is_mobile') === '1' ||
                   (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(android|iphone|ipad|mobile)/i', $_SERVER['HTTP_USER_AGENT']));
        
        if ($isMobile) {
            return view('company.employees.index-mobile', compact('employees', 'company'));
        }
        
        return view('company.employees.index', compact('employees', 'company'));
    }

    public function create()
    {
        $company = $this->getCurrentCompany();
        return view('company.employees.create', compact('company'));
    }

    public function store(Request $request)
    {
        $company = $this->getCurrentCompany();
        
        $validated = $request->validate([
            'type' => 'required|in:clt,pj,freelancer',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'role' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,dismissed',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }

        $employee = Employee::create($validated);

        // Atualiza folha salarial se for CLT ou PJ (mesmo sem salário, para recalcular)
        if (in_array($employee->type, ['clt', 'pj'])) {
            $payrollService = new PayrollService();
            $payrollService->handleEmployeeChange($employee);
        }

        return redirect()->route('company.employees.index')
            ->with('success', 'Funcionário criado com sucesso!');
    }

    public function show(Employee $employee)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($employee, $company);
        
        $employee->load('contracts', 'projects', 'payables');
        return view('company.employees.show', compact('employee', 'company'));
    }

    public function edit(Employee $employee)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($employee, $company);
        
        return view('company.employees.edit', compact('employee', 'company'));
    }

    public function update(Request $request, Employee $employee)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($employee, $company);
        
        $validated = $request->validate([
            'type' => 'required|in:clt,pj,freelancer',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:100',
            'role' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,dismissed',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $oldSalary = $employee->salary;
        $oldType = $employee->type;
        $oldStatus = $employee->status;

        $employee->update($validated);

        // Atualiza folha salarial se houve mudança relevante ou se é CLT/PJ
        $salaryChanged = $oldSalary != $employee->salary;
        $typeChanged = $oldType != $employee->type;
        $statusChanged = $oldStatus != $employee->status;

        if (in_array($employee->type, ['clt', 'pj']) && ($salaryChanged || $typeChanged || $statusChanged)) {
            $payrollService = new PayrollService();
            $payrollService->handleEmployeeChange($employee);
        }

        return redirect()->route('company.employees.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy(Employee $employee)
    {
        $company = $this->getCurrentCompany();
        $this->authorizeAccess($employee, $company);
        
        // Atualiza folha antes de deletar
        $payrollService = new PayrollService();
        $payrollService->handleEmployeeDeletion($company);
        
        $employee->delete();

        return redirect()->route('company.employees.index')
            ->with('success', 'Funcionário removido com sucesso!');
    }

    public function generatePayroll()
    {
        $company = $this->getCurrentCompany();
        
        $payrollService = new PayrollService();
        $payrollService->updateMonthlyPayroll($company);
        
        return redirect()->route('company.employees.index')
            ->with('success', 'Folha salarial do mês gerada/atualizada com sucesso!');
    }

    protected function authorizeAccess(Employee $employee, Company $company): void
    {
        if ($employee->company_id !== $company->id) {
            abort(403, 'Acesso negado.');
        }
    }
}
