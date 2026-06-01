@extends('layouts.app')

@section('title', 'Daily')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dailies.css') }}">
@endpush

@section('content')
<div class="daily-page">

    <div class="daily-header">
        <div>
            <h1 class="page-title mb-1">Daily</h1>
            <p class="page-subtitle mb-0">Registro diário de atividades</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(app(\App\Services\CompanyAuthorizationService::class)->canViewProductivity())
            <a href="{{ route('company.dailies.productivity') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-chart-line me-1"></i>Produtividade
            </a>
            <a href="{{ route('company.dailies.export.excel') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>Exportar mês
            </a>
            @endif
        </div>
    </div>

    <div class="daily-layout">

        {{-- Área principal --}}
        <div class="daily-main">

            <div class="daily-kpi-strip">
                <div class="daily-kpi primary">
                    <div class="daily-kpi-label">Horas hoje</div>
                    <div class="daily-kpi-value">{{ number_format($dayTotal, 1, ',', '.') }}h</div>
                </div>
                <div class="daily-kpi success">
                    <div class="daily-kpi-label">Meta diária</div>
                    <div class="daily-kpi-value">{{ $dayProgress }}%</div>
                </div>
                <div class="daily-kpi info">
                    <div class="daily-kpi-label">Registros hoje</div>
                    <div class="daily-kpi-value">{{ $dailies->count() }}</div>
                </div>
            </div>

            <div class="daily-content-grid">

                {{-- Formulário --}}
                <div class="daily-panel">
                    <div class="daily-panel-head">
                        <h6><i class="fas fa-pen"></i> Registrar atividade</h6>
                    </div>
                    <div class="daily-panel-body">
                        @if(session('success'))
                            <div class="alert alert-success py-2 small mb-3">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger py-2 small mb-3">{{ $errors->first() }}</div>
                        @endif

                        <form action="{{ route('company.dailies.store') }}" method="POST" class="daily-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Data</label>
                                <input type="date" name="work_date" class="form-control" value="{{ old('work_date', $date) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Task</label>
                                <select name="task_id" id="daily-task" class="form-select" required>
                                    <option value="">Selecione a task</option>
                                    @foreach($tasks as $t)
                                        <option value="{{ $t->id }}" data-subtasks='@json($t->subtasks)' @selected(old('task_id') == $t->id)>
                                            [{{ $t->project->name ?? '' }}] {{ $t->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subtask <span class="text-muted fw-normal">(opcional)</span></label>
                                <select name="subtask_id" id="daily-subtask" class="form-select">
                                    <option value="">Nenhuma</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">O que foi feito</label>
                                <textarea name="description" class="form-control" rows="3" required placeholder="Descreva o que você realizou...">{{ old('description') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tempo gasto (horas)</label>
                                <input type="number" step="0.25" min="0.25" max="24" name="hours" class="form-control" value="{{ old('hours') }}" placeholder="Ex: 2.5" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Impedimentos <span class="text-muted fw-normal">(opcional)</span></label>
                                <textarea name="blockers" class="form-control" rows="2" placeholder="Bloqueios ou dependências...">{{ old('blockers') }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus me-1"></i> Registrar Daily
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Registros do dia --}}
                <div class="daily-panel">
                    <div class="daily-panel-head">
                        <h6><i class="fas fa-list"></i> Registros do dia</h6>
                        <div class="daily-date-nav">
                            <a href="{{ route('company.dailies.index', ['date' => $prevDate, 'month' => $monthParam]) }}" class="daily-date-btn" title="Dia anterior">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <form method="GET" class="m-0">
                                <input type="hidden" name="month" value="{{ $monthParam }}">
                                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                            </form>
                            <a href="{{ route('company.dailies.index', ['date' => $nextDate, 'month' => $monthParam]) }}" class="daily-date-btn" title="Próximo dia">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="daily-panel-body flush">
                        @forelse($dailies as $daily)
                            <div class="daily-entry">
                                <div class="daily-entry-top">
                                    <div class="daily-entry-title">{{ $daily->task->title ?? 'Task removida' }}</div>
                                    <span class="daily-entry-hours">{{ number_format($daily->hours, 2, ',', '.') }}h</span>
                                </div>
                                <div class="daily-entry-meta">
                                    @if($daily->project)
                                        <i class="fas fa-folder-open me-1"></i>{{ $daily->project->name }}
                                    @endif
                                    @if($daily->subtask)
                                        · Subtask: {{ $daily->subtask->title }}
                                    @endif
                                    · {{ $daily->created_at->format('H:i') }}
                                </div>
                                <p class="daily-entry-desc">{{ $daily->description }}</p>
                                @if($daily->blockers)
                                    <div class="daily-entry-blocker">
                                        <i class="fas fa-exclamation-triangle me-1"></i>{{ $daily->blockers }}
                                    </div>
                                @endif
                                <div class="daily-entry-actions">
                                    <form action="{{ route('company.dailies.destroy', $daily) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger delete-form" data-message="Remover este registro?">
                                            <i class="fas fa-trash-alt me-1"></i>Remover
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="daily-empty">
                                <i class="fas fa-clipboard"></i>
                                Nenhum registro para {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="daily-sidebar">

            {{-- Progresso do mês --}}
            <div class="daily-panel">
                <div class="daily-panel-head">
                    <h6><i class="fas fa-chart-pie"></i> Progresso do mês</h6>
                    <div class="d-flex gap-1">
                        <a href="{{ route('company.dailies.index', ['date' => $date, 'month' => $prevMonth]) }}" class="daily-date-btn" style="width:28px;height:28px;font-size:11px">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('company.dailies.index', ['date' => $date, 'month' => $nextMonth]) }}" class="daily-date-btn" style="width:28px;height:28px;font-size:11px">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="daily-month-progress">
                    <div class="daily-month-title">{{ $monthLabel }}</div>
                    <div class="daily-month-ring">
                        <svg viewBox="0 0 36 36" aria-hidden="true">
                            <defs>
                                <linearGradient id="dailyRingGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#5e72e4"/>
                                    <stop offset="100%" stop-color="#2dce89"/>
                                </linearGradient>
                            </defs>
                            <path class="ring-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="ring-fill" stroke-dasharray="{{ $monthProgress }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div class="daily-month-ring-center">
                            <strong>{{ number_format($monthTotalHours, 0, ',', '.') }}</strong>
                            <span>/ {{ number_format($monthTargetHours, 0, ',', '.') }}h</span>
                        </div>
                    </div>
                    <div class="daily-month-pct">{{ $monthProgress }}% da meta mensal</div>
                </div>

                <div class="daily-month-stats">
                    <div class="daily-month-stat">
                        <div class="daily-month-stat-val">{{ $monthDaysWorked }}</div>
                        <div class="daily-month-stat-lbl">Dias registrados</div>
                    </div>
                    <div class="daily-month-stat">
                        <div class="daily-month-stat-val">{{ $monthEntries }}</div>
                        <div class="daily-month-stat-lbl">Lançamentos</div>
                    </div>
                    <div class="daily-month-stat">
                        <div class="daily-month-stat-val">{{ $businessDays }}</div>
                        <div class="daily-month-stat-lbl">Dias úteis</div>
                    </div>
                    <div class="daily-month-stat">
                        <div class="daily-month-stat-val">{{ number_format($monthTotalHours / max(1, $monthDaysWorked), 1, ',', '.') }}h</div>
                        <div class="daily-month-stat-lbl">Média/dia</div>
                    </div>
                </div>

                <div class="daily-month-bars">
                    <div class="daily-month-bars-label">Mapa de horas no mês</div>
                    <div class="daily-bars-weekdays">
                        @foreach(['D','S','T','Q','Q','S','S'] as $wd)
                            <span>{{ $wd }}</span>
                        @endforeach
                    </div>
                    <div class="daily-bars-grid">
                        @foreach($calendarDays as $cell)
                            @if(!empty($cell['empty']))
                                <div class="daily-bar-cell daily-bar-empty" aria-hidden="true"></div>
                            @else
                            <div class="daily-bar-cell {{ $cell['hours'] > 0 ? 'has-hours' : '' }} {{ $cell['is_today'] ? 'is-today' : '' }} {{ $cell['is_selected'] ? 'is-selected' : '' }}"
                                 style="--intensity: {{ $cell['intensity'] }}"
                                 title="Dia {{ $cell['day'] }} — {{ number_format($cell['hours'], 1, ',', '.') }}h">
                                <a href="{{ route('company.dailies.index', ['date' => $cell['date'], 'month' => $monthParam]) }}" class="daily-bar-link" aria-label="Ver dia {{ $cell['day'] }}"></a>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="daily-bar-legend">
                        <span>Menos</span>
                        <span>Mais horas</span>
                    </div>
                </div>
            </div>

            {{-- Histórico --}}
            <div class="daily-panel">
                <div class="daily-panel-head">
                    <h6><i class="fas fa-history"></i> Histórico recente</h6>
                </div>
                <div class="daily-panel-body flush">
                    @forelse($historyDays as $row)
                        @php
                            $dayStr = $row->day instanceof \Carbon\Carbon ? $row->day->format('Y-m-d') : (string) $row->day;
                            $dayCarbon = \Carbon\Carbon::parse($dayStr, 'America/Sao_Paulo');
                            $isActive = $dayStr === $date;
                            $weekdays = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
                            $weekdayName = $weekdays[$dayCarbon->dayOfWeek] ?? '';
                        @endphp
                        <a href="{{ route('company.dailies.index', ['date' => $dayStr, 'month' => $dayCarbon->format('Y-m')]) }}"
                           class="daily-history-item {{ $isActive ? 'active' : '' }}">
                            <div>
                                <div class="daily-history-date">{{ $dayCarbon->format('d/m/Y') }}</div>
                                <div class="daily-history-sub">
                                    {{ $weekdayName }}
                                    · {{ $row->entries }} {{ $row->entries == 1 ? 'registro' : 'registros' }}
                                </div>
                            </div>
                            <span class="daily-history-hours">{{ number_format($row->total, 1, ',', '.') }}h</span>
                        </a>
                    @empty
                        <div class="daily-empty">
                            <i class="fas fa-clock-rotate-left"></i>
                            Nenhum registro recente.
                        </div>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('daily-task')?.addEventListener('change', function () {
    const subtaskSelect = document.getElementById('daily-subtask');
    subtaskSelect.innerHTML = '<option value="">Nenhuma</option>';
    const opt = this.selectedOptions[0];
    if (!opt?.dataset.subtasks) return;
    try {
        JSON.parse(opt.dataset.subtasks).forEach(st => {
            subtaskSelect.insertAdjacentHTML('beforeend', `<option value="${st.id}">${st.title}</option>`);
        });
    } catch (e) {}
});
@if(old('task_id'))
document.getElementById('daily-task')?.dispatchEvent(new Event('change'));
@endif
</script>
@endpush
@endsection
