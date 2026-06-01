<?php

namespace App\Services;

use App\Models\CompanyPermissionProfile;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\CurrentCompanyContext;
use App\Support\PermissionModules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyAuthorizationService
{
    public const MANAGER_ROLES = ['owner', 'admin', 'manager'];

    public const INTERNAL_ROLES = ['owner', 'admin', 'manager', 'user', 'freelancer'];

    protected ?string $role = null;

    protected ?array $employeeIds = null;

    protected ?int $clientId = null;

    protected ?int $permissionProfileId = null;

    protected ?array $profilePermissions = null;

    protected bool $profileLoaded = false;

    protected bool $resolved = false;

    public function resolve(): void
    {
        if ($this->resolved) {
            return;
        }

        $user = Auth::user();
        $companyId = session('current_company_id');

        if (! $user || ! $companyId) {
            $this->role = 'guest';
            $this->resolved = true;

            return;
        }

        $pivot = CurrentCompanyContext::membership()
            ?? $user->companies()
                ->where('companies.id', $companyId)
                ->wherePivot('is_active', true)
                ->first();

        $this->role = $pivot?->pivot?->role ?? 'guest';
        $this->clientId = $pivot?->pivot?->client_id ? (int) $pivot->pivot->client_id : null;
        $this->permissionProfileId = $pivot?->pivot?->permission_profile_id
            ? (int) $pivot->pivot->permission_profile_id
            : null;

        if (in_array($this->role, ['freelancer', 'user', 'manager'], true)) {
            $employeeId = $pivot?->pivot?->employee_id ? (int) $pivot->pivot->employee_id : null;
            if ($employeeId) {
                $this->employeeIds = [$employeeId];
            } elseif (in_array($this->role, ['freelancer', 'user'], true)) {
                $this->employeeIds = Employee::where('company_id', $companyId)
                    ->where('email', $user->email)
                    ->pluck('id')
                    ->all();
            }
        }

        $this->resolved = true;
    }

    public function role(): string
    {
        $this->resolve();

        return $this->role ?? 'guest';
    }

    public function clientId(): ?int
    {
        $this->resolve();

        return $this->clientId;
    }

    public function employeeIds(): array
    {
        $this->resolve();

        return $this->employeeIds ?? [];
    }

    public function permissionProfileId(): ?int
    {
        $this->resolve();

        return $this->permissionProfileId;
    }

    public function isClient(): bool
    {
        return $this->role() === 'client';
    }

    public function isFreelancer(): bool
    {
        return $this->role() === 'freelancer';
    }

    public function canManageAccess(): bool
    {
        return in_array($this->role(), ['owner', 'admin'], true);
    }

    public function canManageProfiles(): bool
    {
        return $this->canManageAccess();
    }

    public function canManage(): bool
    {
        return in_array($this->role(), self::MANAGER_ROLES, true);
    }

    public function canManageProjects(): bool
    {
        return $this->canManage();
    }

    public function canManageTeam(): bool
    {
        return $this->canManage();
    }

    public function canViewProductivity(): bool
    {
        return $this->canAccessModule('productivity');
    }

    public function canViewProjectOverview(): bool
    {
        return $this->canAccessModule('project_overview');
    }

    public function canViewProjectFinancial(): bool
    {
        return $this->canAccessModule('project_financial');
    }

    public function canViewProjectDashboard(): bool
    {
        return $this->canAccessModule('project_dashboard');
    }

    public function canViewCompanyDashboard(): bool
    {
        if (in_array($this->role(), ['owner', 'admin'], true)) {
            return true;
        }

        return (bool) ($this->effectivePermissions()['modules']['dashboard'] ?? false);
    }

    public function canViewDeveloperDashboard(): bool
    {
        if (in_array($this->role(), ['owner', 'admin'], true)) {
            return false;
        }

        if ($this->canViewCompanyDashboard()) {
            return false;
        }

        return (bool) ($this->effectivePermissions()['modules']['developer_dashboard'] ?? false);
    }

    public function canAccessModule(string $module): bool
    {
        if ($this->isClient()) {
            return false;
        }

        if ($module === 'developer_dashboard') {
            return $this->canViewDeveloperDashboard();
        }

        if (in_array($this->role(), ['owner', 'admin'], true)) {
            return true;
        }

        if ($module === 'permission_profiles') {
            return $this->canManageProfiles();
        }

        $permissions = $this->effectivePermissions();

        return (bool) ($permissions['modules'][$module] ?? false);
    }

    /** @return array<string, bool> */
    public function moduleAccessMap(): array
    {
        $map = [];
        foreach (PermissionModules::keys() as $key) {
            $map[$key] = $this->canAccessModule($key);
        }

        return $map;
    }

    public function moduleScope(string $module): string
    {
        if (in_array($this->role(), ['owner', 'admin'], true)) {
            return PermissionModules::SCOPE_ALL;
        }

        if (! in_array($module, PermissionModules::scopedModules(), true)) {
            return PermissionModules::SCOPE_ALL;
        }

        $permissions = $this->effectivePermissions();

        return $permissions['scopes'][$module] ?? PermissionModules::SCOPE_ASSIGNED;
    }

    public function hasFullDataScope(string $module): bool
    {
        return $this->moduleScope($module) === PermissionModules::SCOPE_ALL;
    }

    public function firstAccessibleRouteName(): ?string
    {
        if ($this->canViewCompanyDashboard()) {
            return 'company.dashboard';
        }

        if ($this->canViewDeveloperDashboard()) {
            return 'company.developer-dashboard';
        }

        foreach (PermissionModules::definitions() as $key => $def) {
            if (in_array($key, ['dashboard', 'developer_dashboard'], true)) {
                continue;
            }
            if ($this->canAccessModule($key) && ! empty($def['route'])) {
                return $def['route'];
            }
        }

        return null;
    }

    public function canCreateTask(): bool
    {
        if (! $this->canAccessModule('tasks')) {
            return false;
        }

        return in_array($this->role(), [...self::MANAGER_ROLES, 'user'], true);
    }

    public function canDeleteTask(Task $task): bool
    {
        return $this->canManage() && $this->sameCompany($task->company_id);
    }

    public function canViewTask(Task $task): bool
    {
        if ($task->company_id !== (int) session('current_company_id')) {
            return false;
        }

        if ($this->isClient()) {
            return $this->clientId() && (int) ($task->project?->client_id ?? 0) === $this->clientId();
        }

        if (! $this->canAccessModule('tasks')) {
            return false;
        }

        if ($this->hasFullDataScope('tasks')) {
            return true;
        }

        return in_array($task->assignee_id, $this->employeeIds(), true)
            || $this->isOnProjectTeam($task->project_id);
    }

    public function canUpdateTask(Task $task): bool
    {
        if (! $this->canViewTask($task)) {
            return false;
        }

        return $this->canManage() || in_array($task->assignee_id, $this->employeeIds(), true);
    }

    public function canUpdateTaskField(Task $task, string $field): bool
    {
        if ($this->canManage()) {
            return true;
        }

        $assigneeFields = ['title', 'description', 'status', 'estimated_hours'];

        return in_array($task->assignee_id, $this->employeeIds(), true)
            && in_array($field, $assigneeFields, true);
    }

    public function canMoveTaskStatus(Task $task): bool
    {
        if ($this->isClient()) {
            return false;
        }

        if ($this->canManage()) {
            return true;
        }

        return in_array($this->role(), ['user', 'freelancer'], true)
            && in_array($task->assignee_id, $this->employeeIds(), true);
    }

    public function canManageSubtasks(Task $task): bool
    {
        return $this->canUpdateTask($task);
    }

    public function canRegisterDaily(Task $task): bool
    {
        if (! $this->canAccessModule('dailies')) {
            return false;
        }

        if ($this->isClient()) {
            return false;
        }

        if ($this->hasFullDataScope('dailies')) {
            return true;
        }

        return in_array($task->assignee_id, $this->employeeIds(), true);
    }

    public function canViewProject(Project $project): bool
    {
        if ($project->company_id !== (int) session('current_company_id')) {
            return false;
        }

        if ($this->isClient()) {
            return $this->clientId() && (int) $project->client_id === $this->clientId();
        }

        if (! $this->canAccessModule('projects')) {
            return false;
        }

        if ($this->hasFullDataScope('projects')) {
            return true;
        }

        return $this->isOnProjectTeam($project->id)
            || Task::where('project_id', $project->id)
                ->whereIn('assignee_id', $this->employeeIds())
                ->exists();
    }

    public function canApproveHomologation(Task $task): bool
    {
        return $this->isClient()
            && $this->canViewTask($task)
            && $task->status === 'homologation';
    }

    public function applyProjectScope($query): void
    {
        if ($this->hasFullDataScope('projects')) {
            return;
        }

        $employeeIds = $this->employeeIds();
        $query->where(function ($q) use ($employeeIds) {
            $q->whereHas('tasks', fn ($t) => $t->whereIn('assignee_id', $employeeIds))
                ->orWhereHas('employees', fn ($e) => $e->whereIn('employees.id', $employeeIds));
        });
    }

    public function applyTaskScope($query): void
    {
        if ($this->hasFullDataScope('tasks')) {
            return;
        }

        $employeeIds = $this->employeeIds();

        $query->where(function ($q) use ($employeeIds) {
            $q->whereIn('assignee_id', $employeeIds)
                ->orWhereHas('project.employees', function ($e) use ($employeeIds) {
                    $e->whereIn('employees.id', $employeeIds)
                        ->where('project_employees.is_active', true);
                });
        });
    }

    protected function effectivePermissions(): array
    {
        if ($this->profileLoaded) {
            return $this->profilePermissions ?? PermissionModules::collaboratorDefaults();
        }

        $this->profileLoaded = true;

        if ($this->permissionProfileId) {
            $companyId = (int) session('current_company_id');
            $profile = CompanyPermissionProfile::query()
                ->where('company_id', $companyId)
                ->whereKey($this->permissionProfileId)
                ->first();

            if ($profile) {
                $this->profilePermissions = $profile->normalizedPermissions();

                return $this->profilePermissions;
            }
        }

        if ($this->canManage()) {
            $this->profilePermissions = PermissionModules::managerDefaults();

            return $this->profilePermissions;
        }

        $this->profilePermissions = PermissionModules::collaboratorDefaults();

        return $this->profilePermissions;
    }

    protected function isOnProjectTeam(int $projectId): bool
    {
        if (empty($this->employeeIds())) {
            return false;
        }

        return DB::table('project_employees')
            ->where('project_id', $projectId)
            ->whereIn('employee_id', $this->employeeIds())
            ->where('is_active', true)
            ->exists();
    }

    protected function sameCompany(int $companyId): bool
    {
        return $companyId === (int) session('current_company_id');
    }
}
