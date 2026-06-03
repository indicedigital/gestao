@extends('layouts.app')

@section('title', 'Daily — '.$employee->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dailies.css') }}">
@endpush

@section('content')
<div class="daily-page daily-admin-page">

    <div class="daily-header">
        <div>
            <a href="{{ route('company.dailies.index', ['date' => $date, 'month' => $monthParam]) }}" class="btn btn-sm btn-link text-decoration-none ps-0 mb-2">
                <i class="fas fa-arrow-left me-1"></i>Voltar à equipe
            </a>
            <h1 class="page-title mb-1">{{ $employee->name }}</h1>
            <p class="page-subtitle mb-0">
                {{ $employee->position ?? $employee->role ?? 'Colaborador' }}
                @if($employee->email)
                    · {{ $employee->email }}
                @endif
            </p>
        </div>
    </div>

    <div class="daily-layout">
        <div class="daily-main">
            <div class="daily-kpi-strip">
                <div class="daily-kpi primary">
                    <div class="daily-kpi-label">Horas no dia</div>
                    <div class="daily-kpi-value">{{ number_format($dayTotal, 1, ',', '.') }}h</div>
                </div>
                <div class="daily-kpi success">
                    <div class="daily-kpi-label">Meta diária</div>
                    <div class="daily-kpi-value">{{ $dayProgress }}%</div>
                </div>
                <div class="daily-kpi info">
                    <div class="daily-kpi-label">Registros no dia</div>
                    <div class="daily-kpi-value">{{ $dailies->count() }}</div>
                </div>
            </div>

            <div class="daily-panel">
                <div class="daily-panel-head">
                    <h6><i class="fas fa-list"></i> Registros do dia</h6>
                    <div class="daily-date-nav">
                        <a href="{{ route('company.dailies.collaborator', ['employee' => $employee, 'date' => $prevDate, 'month' => $monthParam]) }}" class="daily-date-btn" title="Dia anterior">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <form method="GET" class="m-0">
                            <input type="hidden" name="month" value="{{ $monthParam }}">
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
                        </form>
                        <a href="{{ route('company.dailies.collaborator', ['employee' => $employee, 'date' => $nextDate, 'month' => $monthParam]) }}" class="daily-date-btn" title="Próximo dia">
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
                                · {{ $daily->created_at->format('d/m/Y H:i') }}
                            </div>
                            <p class="daily-entry-desc">{{ $daily->description }}</p>
                            @if($daily->blockers)
                                <div class="daily-entry-blocker">
                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $daily->blockers }}
                                </div>
                            @endif
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

        <aside class="daily-sidebar">
            <div class="daily-panel">
                <div class="daily-panel-head">
                    <h6><i class="fas fa-calendar-alt"></i> {{ $monthLabel }}</h6>
                    <div class="d-flex gap-1">
                        <a href="{{ route('company.dailies.collaborator', ['employee' => $employee, 'date' => $date, 'month' => $prevMonth]) }}" class="daily-date-btn" style="width:28px;height:28px;font-size:11px">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('company.dailies.collaborator', ['employee' => $employee, 'date' => $date, 'month' => $nextMonth]) }}" class="daily-date-btn" style="width:28px;height:28px;font-size:11px">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="daily-month-bars px-3 pb-3">
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
                                <a href="{{ route('company.dailies.collaborator', ['employee' => $employee, 'date' => $cell['date'], 'month' => $monthParam]) }}" class="daily-bar-link" aria-label="Ver dia {{ $cell['day'] }}"></a>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="daily-panel">
                <div class="daily-panel-head">
                    <h6><i class="fas fa-history"></i> Histórico de dias</h6>
                </div>
                <div class="daily-panel-body flush">
                    @forelse($historyDays as $row)
                        @php
                            $dayStr = $row->day instanceof \Carbon\Carbon ? $row->day->format('Y-m-d') : (string) $row->day;
                            $dayCarbon = \Carbon\Carbon::parse($dayStr, 'America/Sao_Paulo');
                            $isActive = $dayStr === $date;
                        @endphp
                        <a href="{{ route('company.dailies.collaborator', ['employee' => $employee, 'date' => $dayStr, 'month' => $dayCarbon->format('Y-m')]) }}"
                           class="daily-history-item {{ $isActive ? 'active' : '' }}">
                            <div>
                                <div class="daily-history-date">{{ $dayCarbon->format('d/m/Y') }}</div>
                                <div class="daily-history-sub">
                                    {{ $row->entries }} {{ $row->entries == 1 ? 'registro' : 'registros' }}
                                </div>
                            </div>
                            <span class="daily-history-hours">{{ number_format($row->total, 1, ',', '.') }}h</span>
                        </a>
                    @empty
                        <div class="daily-empty">
                            <i class="fas fa-clock-rotate-left"></i>
                            Nenhum registro encontrado.
                        </div>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
