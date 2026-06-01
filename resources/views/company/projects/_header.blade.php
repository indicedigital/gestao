<div class="work-shell">
    <div class="work-breadcrumb">
        <a href="{{ route('company.projects.index') }}">Projetos</a>
        <span class="sep">/</span>
        <span>{{ $project->name }}</span>
    </div>
    <div class="work-header">
        <div>
            <h1 class="work-header-title">{{ $project->name }}</h1>
            <div class="work-header-meta">
                {{ $project->client->name ?? 'Sem cliente' }}
                @if($project->contract)
                    · {{ $project->contract->name }}
                @endif
            </div>
        </div>
        <div class="work-header-actions">
            @if(isset($headerActions))
                {!! $headerActions !!}
            @else
                @if(app(\App\Services\CompanyAuthorizationService::class)->canManageProjects())
                <a href="{{ route('company.projects.edit', $project) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
                @endif
                @if(app(\App\Services\CompanyAuthorizationService::class)->canCreateTask())
                <a href="{{ route('company.tasks.create', ['project_id' => $project->id, 'redirect_to' => 'kanban']) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Tarefa
                </a>
                @endif
            @endif
        </div>
    </div>
    @include('company.projects._nav')
</div>
