<nav class="nav project-module-nav flex-wrap">
    @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProjectOverview())
    <a href="{{ route('company.projects.show', $project) }}" class="nav-link project-tab-link" data-tab="show" @class(['active' => ($currentTab ?? '') === 'show' || request()->routeIs('company.projects.show')])>
        <i class="fas fa-info-circle me-1"></i> Visão Geral
    </a>
    @endif
    <a href="{{ route('company.projects.kanban', $project) }}" class="nav-link project-tab-link" data-tab="kanban" @class(['active' => ($currentTab ?? '') === 'kanban' || request()->routeIs('company.projects.kanban')])>
        <i class="fas fa-columns me-1"></i> Quadro
    </a>
    @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProjectDashboard())
    <a href="{{ route('company.projects.dashboard', $project) }}" class="nav-link project-tab-link" data-tab="dashboard" @class(['active' => ($currentTab ?? '') === 'dashboard' || request()->routeIs('company.projects.dashboard')])>
        <i class="fas fa-chart-bar me-1"></i> Dashboard
    </a>
    @endif
    <a href="{{ route('company.projects.team', $project) }}" class="nav-link project-tab-link" data-tab="team" @class(['active' => ($currentTab ?? '') === 'team' || request()->routeIs('company.projects.team*')])>
        <i class="fas fa-users me-1"></i> Equipe
    </a>
    @if(app(\App\Services\CompanyAuthorizationService::class)->canCreateTaskOnProject($project))
    <a href="{{ route('company.tasks.create', ['project_id' => $project->id, 'redirect_to' => 'kanban']) }}" class="nav-link nav-cta">
        <i class="fas fa-plus me-1"></i> Nova Tarefa
    </a>
    @endif
</nav>
