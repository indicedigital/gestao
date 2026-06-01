<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyPermissionProfile;
use App\Services\CompanyPermissionProfileService;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class CompanyUserProvisioningService
{
    public function clientAccess(Company $company, Client $client): ?array
    {
        $row = DB::table('user_company')
            ->join('users', 'users.id', '=', 'user_company.user_id')
            ->where('user_company.company_id', $company->id)
            ->where('user_company.client_id', $client->id)
            ->where('user_company.role', 'client')
            ->select('users.*', 'user_company.role', 'user_company.is_active', 'user_company.client_id', 'user_company.employee_id', 'user_company.permission_profile_id')
            ->first();

        if (! $row) {
            return null;
        }

        return $this->formatAccessRow($row);
    }

    public function employeeAccess(Company $company, Employee $employee): ?array
    {
        $row = DB::table('user_company')
            ->join('users', 'users.id', '=', 'user_company.user_id')
            ->where('user_company.company_id', $company->id)
            ->where('user_company.employee_id', $employee->id)
            ->whereIn('user_company.role', ['user', 'freelancer'])
            ->select('users.*', 'user_company.role', 'user_company.is_active', 'user_company.client_id', 'user_company.employee_id', 'user_company.permission_profile_id')
            ->first();

        if (! $row) {
            $row = DB::table('user_company')
                ->join('users', 'users.id', '=', 'user_company.user_id')
                ->where('user_company.company_id', $company->id)
                ->whereIn('user_company.role', ['user', 'freelancer'])
                ->where('users.email', $employee->email)
                ->select('users.*', 'user_company.role', 'user_company.is_active', 'user_company.client_id', 'user_company.employee_id', 'user_company.permission_profile_id')
                ->first();
        }

        if (! $row) {
            return null;
        }

        return $this->formatAccessRow($row);
    }

    public function provisionClientAccess(Company $company, Client $client, array $data): array
    {
        if ($client->company_id !== $company->id) {
            throw new InvalidArgumentException('Cliente inválido para esta empresa.');
        }

        $email = strtolower(trim($data['email'] ?? $client->email ?? ''));
        if ($email === '') {
            throw new InvalidArgumentException('Informe um e-mail para o acesso do cliente.');
        }

        $name = trim($data['name'] ?? $client->name);
        $password = $data['password'] ?? null;
        if (! $password) {
            throw new InvalidArgumentException('Informe uma senha para o acesso.');
        }

        return DB::transaction(function () use ($company, $client, $email, $name, $password) {
            $existingForClient = $this->clientAccess($company, $client);
            if ($existingForClient) {
                throw new InvalidArgumentException('Este cliente já possui acesso ao portal. Use a opção de alterar senha.');
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                $this->assertUserNotLinkedElsewhere($company, $user, 'client', $client->id);
                $this->attachOrUpdatePivot($company, $user, [
                    'role' => 'client',
                    'client_id' => $client->id,
                    'employee_id' => null,
                    'is_active' => true,
                ]);
                $user->update(['name' => $name]);
            } else {
                $user = $this->createUser($name, $email, $password);
                $this->attachOrUpdatePivot($company, $user, [
                    'role' => 'client',
                    'client_id' => $client->id,
                    'employee_id' => null,
                    'is_active' => true,
                ]);
            }

            if (! $client->email) {
                $client->update(['email' => $email]);
            }

            return $this->clientAccess($company, $client->fresh());
        });
    }

    public function provisionEmployeeAccess(Company $company, Employee $employee, array $data): array
    {
        if ($employee->company_id !== $company->id) {
            throw new InvalidArgumentException('Colaborador inválido para esta empresa.');
        }

        $email = strtolower(trim($data['email'] ?? $employee->email ?? ''));
        if ($email === '') {
            throw new InvalidArgumentException('Informe um e-mail para o acesso do colaborador.');
        }

        $name = trim($data['name'] ?? $employee->name);
        $password = $data['password'] ?? null;
        if (! $password) {
            throw new InvalidArgumentException('Informe uma senha para o acesso.');
        }

        $role = $data['role'] ?? ($employee->type === 'freelancer' ? 'freelancer' : 'user');
        if (! in_array($role, ['user', 'freelancer'], true)) {
            throw new InvalidArgumentException('Perfil de acesso inválido.');
        }

        $profileId = $this->resolveProfileId($company, $data['permission_profile_id'] ?? null, $role);

        return DB::transaction(function () use ($company, $employee, $email, $name, $password, $role, $profileId) {
            $existing = $this->employeeAccess($company, $employee);
            if ($existing) {
                throw new InvalidArgumentException('Este colaborador já possui acesso. Use a opção de alterar senha.');
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                $this->assertUserNotLinkedElsewhere($company, $user, $role, null, $employee->id);
                $this->attachOrUpdatePivot($company, $user, [
                    'role' => $role,
                    'client_id' => null,
                    'employee_id' => $employee->id,
                    'permission_profile_id' => $profileId,
                    'is_active' => true,
                ]);
                $user->update(['name' => $name]);
            } else {
                $user = $this->createUser($name, $email, $password);
                $this->attachOrUpdatePivot($company, $user, [
                    'role' => $role,
                    'client_id' => null,
                    'employee_id' => $employee->id,
                    'permission_profile_id' => $profileId,
                    'is_active' => true,
                ]);
            }

            if (! $employee->email) {
                $employee->update(['email' => $email]);
            }

            return $this->employeeAccess($company, $employee->fresh());
        });
    }

    public function resetPassword(Company $company, User $user, string $password): void
    {
        if (! $company->users()->where('users.id', $user->id)->exists()) {
            throw new InvalidArgumentException('Usuário não pertence a esta empresa.');
        }

        $user->update(['password' => Hash::make($password)]);
    }

    public function setActive(Company $company, User $user, bool $active): void
    {
        $pivot = $company->users()->where('users.id', $user->id)->first();
        if (! $pivot) {
            throw new InvalidArgumentException('Usuário não pertence a esta empresa.');
        }

        if (in_array($pivot->pivot->role, ['owner', 'admin'], true)) {
            throw new InvalidArgumentException('Não é possível desativar administradores por aqui.');
        }

        $company->users()->updateExistingPivot($user->id, ['is_active' => $active]);
    }

    public function revokeClientAccess(Company $company, Client $client): void
    {
        $access = $this->clientAccess($company, $client);
        if (! $access) {
            return;
        }

        $userId = $access['user']->id;
        $pivot = $company->users()->where('users.id', $userId)->first();
        if ($pivot && in_array($pivot->pivot->role, ['owner', 'admin'], true)) {
            throw new InvalidArgumentException('Não é permitido revogar acesso de administrador.');
        }

        $company->users()->detach($userId);
    }

    public function revokeEmployeeAccess(Company $company, Employee $employee): void
    {
        $access = $this->employeeAccess($company, $employee);
        if (! $access) {
            return;
        }

        $userId = $access['user']->id;
        $company->users()->detach($userId);
    }

    protected function createUser(string $name, string $email, string $password): User
    {
        $data = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ];

        if (Schema::hasColumn('users', 'status')) {
            $data['status'] = 'active';
        }

        return User::create($data);
    }

    protected function attachOrUpdatePivot(Company $company, User $user, array $pivotData): void
    {
        if ($company->users()->where('users.id', $user->id)->exists()) {
            $company->users()->updateExistingPivot($user->id, array_merge($pivotData, [
                'joined_at' => now(),
            ]));
        } else {
            $company->users()->attach($user->id, array_merge($pivotData, [
                'joined_at' => now(),
            ]));
        }
    }

    protected function assertUserNotLinkedElsewhere(Company $company, User $user, string $role, ?int $clientId = null, ?int $employeeId = null): void
    {
        $pivot = $company->users()->where('users.id', $user->id)->first();
        if (! $pivot) {
            return;
        }

        $currentRole = $pivot->pivot->role;
        if (in_array($currentRole, ['owner', 'admin', 'manager'], true)) {
            throw new InvalidArgumentException('Este e-mail pertence a um administrador/gestor da empresa.');
        }

        if ($role === 'client' && (int) $pivot->pivot->client_id !== (int) $clientId) {
            throw new InvalidArgumentException('Este e-mail já está vinculado a outro cliente ou colaborador.');
        }

        if (in_array($role, ['user', 'freelancer'], true) && $pivot->pivot->employee_id && (int) $pivot->pivot->employee_id !== (int) $employeeId) {
            throw new InvalidArgumentException('Este e-mail já está vinculado a outro colaborador.');
        }
    }

    protected function resolveProfileId(Company $company, mixed $profileId, string $role): ?int
    {
        if ($profileId) {
            $exists = CompanyPermissionProfile::where('company_id', $company->id)
                ->whereKey($profileId)
                ->exists();

            return $exists ? (int) $profileId : null;
        }

        if (! in_array($role, ['user', 'freelancer'], true)) {
            return null;
        }

        app(CompanyPermissionProfileService::class)->ensureDefaults($company);

        return CompanyPermissionProfile::where('company_id', $company->id)
            ->where('slug', 'programador')
            ->value('id');
    }

    protected function formatAccessRow(object $row): array
    {
        $user = User::find($row->id);
        $profile = ! empty($row->permission_profile_id)
            ? CompanyPermissionProfile::find($row->permission_profile_id)
            : null;

        return [
            'user' => $user,
            'role' => $row->role,
            'is_active' => (bool) $row->is_active,
            'client_id' => $row->client_id,
            'employee_id' => $row->employee_id,
            'permission_profile_id' => $row->permission_profile_id ?? null,
            'permission_profile' => $profile,
        ];
    }
}
