<form method="GET" action="{{ route('company.dailies.productivity') }}" class="prod-filters" id="prod-filter-form" data-prod-filter-form data-no-loading>
    <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'overview' }}" data-prod-tab-input>

    <div class="prod-filters-top">
        <div class="prod-period-pills" role="group" aria-label="Período">
            @foreach(['today' => 'Hoje', 'week' => 'Semana', 'month' => 'Mês', 'custom' => 'Personalizado'] as $key => $label)
                <button type="button"
                        class="prod-period-pill {{ ($filters['period'] ?? 'month') === $key ? 'active' : '' }}"
                        data-prod-period="{{ $key }}">{{ $label }}</button>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-link text-decoration-none prod-filters-toggle" data-prod-toggle-filters>
            <i class="fas fa-sliders-h me-1"></i> Filtros avançados
        </button>
    </div>

    <input type="hidden" name="period" value="{{ $filters['period'] ?? 'month' }}" data-prod-period-input>

    <div class="prod-filters-advanced" data-prod-advanced-filters hidden>
        <div class="prod-filters-grid">
            <div class="prod-custom-dates" data-prod-custom-dates @if(($filters['period'] ?? 'month') !== 'custom') hidden @endif>
                <div>
                    <label class="form-label">De</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? $period['from'] }}">
                </div>
                <div>
                    <label class="form-label">Até</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? $period['to'] }}">
                </div>
            </div>

            <div>
                <label class="form-label">Colaborador</label>
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($filterOptions['employees'] as $emp)
                        <option value="{{ $emp->id }}" @selected(($filters['employee_id'] ?? '') == $emp->id)>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Equipe / Setor</label>
                <select name="team" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($filterOptions['teams'] as $team)
                        <option value="{{ $team }}" @selected(($filters['team'] ?? '') === $team)>{{ $team }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Projeto</label>
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($filterOptions['projects'] as $proj)
                        <option value="{{ $proj->id }}" @selected(($filters['project_id'] ?? '') == $proj->id)>{{ $proj->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Categoria</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($filterOptions['categories'] as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($filterOptions['statuses'] as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Prioridade</label>
                <select name="priority" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($filterOptions['priorities'] as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['priority'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Meta</label>
                <select name="goal_met" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="1" @selected(($filters['goal_met'] ?? '') === '1' || ($filters['goal_met'] ?? null) === true)>Atingida</option>
                    <option value="0" @selected(($filters['goal_met'] ?? '') === '0' || ($filters['goal_met'] ?? null) === false)>Não atingida</option>
                </select>
            </div>

            <div class="prod-filter-checks">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="overdue" value="1" id="f-overdue" @checked($filters['overdue'] ?? false)>
                    <label class="form-check-label small" for="f-overdue">Com atrasos</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="inactive" value="1" id="f-inactive" @checked($filters['inactive'] ?? false)>
                    <label class="form-check-label small" for="f-inactive">Inativos</label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-filter me-1"></i> Aplicar</button>
                <a href="{{ route('company.dailies.productivity') }}" class="btn btn-outline-secondary btn-sm">Limpar</a>
            </div>
        </div>
    </div>
</form>
