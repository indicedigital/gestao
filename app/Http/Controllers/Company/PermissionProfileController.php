<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Concerns\InteractsWithCompany;
use App\Http\Controllers\Controller;
use App\Models\CompanyPermissionProfile;
use App\Models\User;
use App\Services\CompanyPermissionProfileService;
use App\Support\PermissionModules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PermissionProfileController extends Controller
{
    use InteractsWithCompany;

    public function __construct(
        protected CompanyPermissionProfileService $profileService
    ) {}

    public function index()
    {
        abort_unless($this->authz()->canManageProfiles(), 403);

        $company = $this->getCurrentCompany();
        $this->profileService->ensureDefaults($company);

        $profiles = CompanyPermissionProfile::where('company_id', $company->id)
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $members = DB::table('user_company')
            ->join('users', 'users.id', '=', 'user_company.user_id')
            ->leftJoin('company_permission_profiles', 'company_permission_profiles.id', '=', 'user_company.permission_profile_id')
            ->where('user_company.company_id', $company->id)
            ->where('user_company.is_active', true)
            ->whereIn('user_company.role', ['manager', 'user', 'freelancer'])
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'user_company.role',
                'user_company.permission_profile_id',
                'company_permission_profiles.name as profile_name'
            )
            ->orderBy('users.name')
            ->get();

        $moduleGroups = PermissionModules::grouped();

        return view('company.permission-profiles.index', compact('company', 'profiles', 'members', 'moduleGroups'));
    }

    public function create()
    {
        abort_unless($this->authz()->canManageProfiles(), 403);

        $company = $this->getCurrentCompany();
        $profile = new CompanyPermissionProfile([
            'permissions' => PermissionModules::collaboratorDefaults(),
        ]);
        $moduleGroups = PermissionModules::grouped();

        return view('company.permission-profiles.form', [
            'company' => $company,
            'profile' => $profile,
            'moduleGroups' => $moduleGroups,
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->authz()->canManageProfiles(), 403);

        $company = $this->getCurrentCompany();
        $validated = $this->validateProfile($request);

        $permissions = $this->buildPermissionsFromRequest($request);

        CompanyPermissionProfile::create([
            'company_id' => $company->id,
            'name' => $validated['name'],
            'slug' => CompanyPermissionProfile::slugFromName($validated['name']),
            'permissions' => $permissions,
            'is_system' => false,
        ]);

        return redirect()
            ->route('company.permission-profiles.index')
            ->with('success', 'Perfil criado com sucesso!');
    }

    public function edit(CompanyPermissionProfile $permissionProfile)
    {
        abort_unless($this->authz()->canManageProfiles(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($permissionProfile->company_id === $company->id, 404);

        $moduleGroups = PermissionModules::grouped();

        return view('company.permission-profiles.form', [
            'company' => $company,
            'profile' => $permissionProfile,
            'moduleGroups' => $moduleGroups,
        ]);
    }

    public function update(Request $request, CompanyPermissionProfile $permissionProfile)
    {
        abort_unless($this->authz()->canManageProfiles(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($permissionProfile->company_id === $company->id, 404);

        $validated = $this->validateProfile($request, $permissionProfile);

        $permissions = $this->buildPermissionsFromRequest($request);

        $permissionProfile->update([
            'name' => $validated['name'],
            'slug' => CompanyPermissionProfile::slugFromName($validated['name']),
            'permissions' => $permissions,
        ]);

        return redirect()
            ->route('company.permission-profiles.index')
            ->with('success', 'Perfil atualizado!');
    }

    public function destroy(CompanyPermissionProfile $permissionProfile)
    {
        abort_unless($this->authz()->canManageProfiles(), 403);

        $company = $this->getCurrentCompany();
        abort_unless($permissionProfile->company_id === $company->id, 404);

        if ($permissionProfile->is_system) {
            return back()->withErrors(['profile' => 'Perfis padrão do sistema não podem ser excluídos.']);
        }

        DB::table('user_company')
            ->where('company_id', $company->id)
            ->where('permission_profile_id', $permissionProfile->id)
            ->update(['permission_profile_id' => null]);

        $permissionProfile->delete();

        return redirect()
            ->route('company.permission-profiles.index')
            ->with('success', 'Perfil excluído.');
    }

    public function assignMember(Request $request)
    {
        abort_unless($this->authz()->canManageProfiles(), 403);

        $company = $this->getCurrentCompany();

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
            'permission_profile_id' => [
                'nullable',
                'integer',
                Rule::exists('company_permission_profiles', 'id')->where('company_id', $company->id),
            ],
        ]);

        $pivot = $company->users()->where('users.id', $validated['user_id'])->first();
        abort_unless($pivot, 404);
        abort_if(in_array($pivot->pivot->role, ['owner', 'admin', 'client'], true), 422);

        $company->users()->updateExistingPivot($validated['user_id'], [
            'permission_profile_id' => $validated['permission_profile_id'] ?: null,
        ]);

        return back()->with('success', 'Perfil atribuído ao colaborador.');
    }

    protected function validateProfile(Request $request, ?CompanyPermissionProfile $profile = null): array
    {
        $company = $this->getCurrentCompany();

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('company_permission_profiles', 'name')
                    ->where('company_id', $company->id)
                    ->ignore($profile?->id),
            ],
            'modules' => 'nullable|array',
            'modules.*' => 'boolean',
            'scopes' => 'nullable|array',
            'scopes.*' => 'in:all,assigned,own',
        ]);
    }

    protected function buildPermissionsFromRequest(Request $request): array
    {
        $modules = [];
        foreach (PermissionModules::keys() as $key) {
            $modules[$key] = $request->boolean("modules.{$key}");
        }

        return PermissionModules::normalizePermissions([
            'modules' => $modules,
            'scopes' => $request->input('scopes', []),
        ]);
    }
}
