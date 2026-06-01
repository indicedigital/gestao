<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Rules\BelongsToCompany;
use App\Services\CompanyUserProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use InvalidArgumentException;

class MemberAccessController extends Controller
{
    use InteractsWithCompany;

    public function __construct(
        protected CompanyUserProvisioningService $provisioning
    ) {}

    public function storeClient(Request $request, Client $client)
    {
        abort_unless($this->authz()->canManageAccess(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($client->company_id === $company->id, 403);

        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|string|max:255',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $this->provisioning->provisionClientAccess($company, $client, $validated);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['access' => $e->getMessage()]);
        }

        return back()->with('success', 'Acesso ao portal criado com sucesso!');
    }

    public function updateClient(Request $request, Client $client)
    {
        abort_unless($this->authz()->canManageAccess(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($client->company_id === $company->id, 403);

        $access = $this->provisioning->clientAccess($company, $client);
        if (! $access) {
            return back()->withErrors(['access' => 'Este cliente ainda não possui acesso.']);
        }

        $validated = $request->validate([
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => 'nullable|boolean',
        ]);

        try {
            if (! empty($validated['password'])) {
                $this->provisioning->resetPassword($company, $access['user'], $validated['password']);
            }
            $this->provisioning->setActive($company, $access['user'], $request->boolean('is_active'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['access' => $e->getMessage()]);
        }

        return back()->with('success', 'Acesso do cliente atualizado!');
    }

    public function destroyClient(Client $client)
    {
        abort_unless($this->authz()->canManageAccess(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($client->company_id === $company->id, 403);

        try {
            $this->provisioning->revokeClientAccess($company, $client);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['access' => $e->getMessage()]);
        }

        return back()->with('success', 'Acesso do cliente revogado.');
    }

    public function storeEmployee(Request $request, Employee $employee)
    {
        abort_unless($this->authz()->canManageAccess(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($employee->company_id === $company->id, 403);

        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|string|max:255',
            'role' => 'nullable|in:user,freelancer',
            'permission_profile_id' => ['nullable', 'integer', new BelongsToCompany('company_permission_profiles', $company->id)],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        try {
            $this->provisioning->provisionEmployeeAccess($company, $employee, $validated);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['access' => $e->getMessage()]);
        }

        return back()->with('success', 'Acesso do colaborador criado com sucesso!');
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        abort_unless($this->authz()->canManageAccess(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($employee->company_id === $company->id, 403);

        $access = $this->provisioning->employeeAccess($company, $employee);
        if (! $access) {
            return back()->withErrors(['access' => 'Este colaborador ainda não possui acesso.']);
        }

        $validated = $request->validate([
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'is_active' => 'nullable|boolean',
            'role' => 'nullable|in:user,freelancer',
            'permission_profile_id' => ['nullable', 'integer', new BelongsToCompany('company_permission_profiles', $company->id)],
        ]);

        try {
            if (! empty($validated['password'])) {
                $this->provisioning->resetPassword($company, $access['user'], $validated['password']);
            }
            $this->provisioning->setActive($company, $access['user'], $request->boolean('is_active'));
            if (! empty($validated['role']) && $validated['role'] !== $access['role']) {
                $company->users()->updateExistingPivot($access['user']->id, ['role' => $validated['role']]);
            }
            if ($request->has('permission_profile_id')) {
                $profileId = $validated['permission_profile_id'] ?: null;
                $company->users()->updateExistingPivot($access['user']->id, [
                    'permission_profile_id' => $profileId,
                ]);
            }
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['access' => $e->getMessage()]);
        }

        return back()->with('success', 'Acesso do colaborador atualizado!');
    }

    public function destroyEmployee(Employee $employee)
    {
        abort_unless($this->authz()->canManageAccess(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($employee->company_id === $company->id, 403);

        try {
            $this->provisioning->revokeEmployeeAccess($company, $employee);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['access' => $e->getMessage()]);
        }

        return back()->with('success', 'Acesso do colaborador revogado.');
    }
}
