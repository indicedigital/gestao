<div class="work-panel">
    <div class="work-panel-header">Colaboradores alocados</div>
    <div class="work-panel-body">
        @php $canViewFinancial = $canViewFinancial ?? false; @endphp
        @if($canEdit)
        <form action="{{ route('company.projects.team.update', $project) }}" method="POST">
            @csrf
            @method('PUT')
        @endif
            <div id="team-rows">
                @forelse($project->employees as $index => $member)
                    <div class="row g-2 mb-3 team-row align-items-center">
                        <div class="{{ $canViewFinancial ? 'col-md-4' : 'col-md-5' }}">
                            @if($canEdit)
                            <select name="employees[{{ $index }}][employee_id]" class="form-select" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" @selected($emp->id === $member->id)>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <div class="form-control-plaintext fw-semibold">{{ $member->name }}</div>
                            @endif
                        </div>
                        <div class="{{ $canViewFinancial ? 'col-md-2' : 'col-md-3' }}">
                            @if($canEdit)
                            <input type="text" name="employees[{{ $index }}][role]" class="form-control" placeholder="Função" value="{{ $member->pivot->role }}">
                            @else
                            <div class="text-muted">{{ $member->pivot->role ?: '—' }}</div>
                            @endif
                        </div>
                        @if($canViewFinancial)
                        <div class="col-md-2">
                            @if($canEdit)
                            <input type="number" step="0.01" name="employees[{{ $index }}][hourly_rate]" class="form-control" placeholder="R$/h" value="{{ $member->pivot->hourly_rate }}">
                            @else
                            <div>{{ $member->pivot->hourly_rate ? 'R$ '.number_format($member->pivot->hourly_rate, 2, ',', '.') : '—' }}</div>
                            @endif
                        </div>
                        @endif
                        <div class="{{ $canViewFinancial ? 'col-md-2' : 'col-md-3' }}">
                            @if($canEdit)
                            <input type="number" name="employees[{{ $index }}][allocated_hours]" class="form-control" placeholder="Horas" value="{{ $member->pivot->allocated_hours }}">
                            @else
                            <div>{{ $member->pivot->allocated_hours ? $member->pivot->allocated_hours.'h' : '—' }}</div>
                            @endif
                        </div>
                        @if($canEdit)
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100 remove-row"><i class="fas fa-trash"></i></button>
                        </div>
                        @endif
                    </div>
                @empty
                    @if($canEdit)
                    <div class="row g-2 mb-3 team-row">
                        <div class="{{ $canViewFinancial ? 'col-md-4' : 'col-md-5' }}">
                            <select name="employees[0][employee_id]" class="form-select">
                                <option value="">Selecione colaborador</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="{{ $canViewFinancial ? 'col-md-2' : 'col-md-3' }}"><input type="text" name="employees[0][role]" class="form-control" placeholder="Função"></div>
                        @if($canViewFinancial)
                        <div class="col-md-2"><input type="number" step="0.01" name="employees[0][hourly_rate]" class="form-control" placeholder="R$/h"></div>
                        @endif
                        <div class="{{ $canViewFinancial ? 'col-md-2' : 'col-md-3' }}"><input type="number" name="employees[0][allocated_hours]" class="form-control" placeholder="Horas"></div>
                        <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-row"><i class="fas fa-trash"></i></button></div>
                    </div>
                    @else
                    <p class="text-muted mb-0">Nenhum colaborador alocado a este projeto.</p>
                    @endif
                @endforelse
            </div>
            @if($canEdit)
            <div class="d-flex gap-2">
                <button type="button" id="add-member" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i>Adicionar</button>
                <button type="submit" class="btn btn-primary">Salvar Time</button>
            </div>
        </form>
        @endif
    </div>
</div>

@if($canEdit)
<template id="team-row-template">
    <div class="row g-2 mb-3 team-row">
        <div class="{{ ($canViewFinancial ?? false) ? 'col-md-4' : 'col-md-5' }}">
            <select name="employees[__INDEX__][employee_id]" class="form-select">
                <option value="">Selecione colaborador</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="{{ ($canViewFinancial ?? false) ? 'col-md-2' : 'col-md-3' }}"><input type="text" name="employees[__INDEX__][role]" class="form-control" placeholder="Função"></div>
        @if($canViewFinancial ?? false)
        <div class="col-md-2"><input type="number" step="0.01" name="employees[__INDEX__][hourly_rate]" class="form-control" placeholder="R$/h"></div>
        @endif
        <div class="{{ ($canViewFinancial ?? false) ? 'col-md-2' : 'col-md-3' }}"><input type="number" name="employees[__INDEX__][allocated_hours]" class="form-control" placeholder="Horas"></div>
        <div class="col-md-2"><button type="button" class="btn btn-outline-danger w-100 remove-row"><i class="fas fa-trash"></i></button></div>
    </div>
</template>
@endif
