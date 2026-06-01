@extends('layouts.app')

@section('title', 'Tutorial')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/tutorial.css') }}">
@endpush

@section('content')
@php
    $totalSteps = collect($guide['sections'] ?? [])->sum(fn ($s) => count($s['steps'] ?? []));
    $groupedSections = collect($guide['sections'] ?? [])->groupBy('group');
@endphp

<div class="tutorial-page">
    <header class="tutorial-hero">
        <div class="tutorial-hero-inner">
            <div class="tutorial-hero-top">
                <div class="tutorial-hero-badge">
                    <i class="fas {{ $guide['icon'] ?? 'fa-graduation-cap' }}"></i>
                    <span>Guia · {{ $personaLabel }}</span>
                </div>
                @if(count($guide['sections'] ?? []) > 0)
                    <div class="tutorial-hero-stats">
                        <span><i class="fas fa-layer-group"></i> {{ count($guide['sections']) }} módulos</span>
                        <span><i class="fas fa-list-ol"></i> {{ $totalSteps }} passos</span>
                    </div>
                @endif
            </div>
            <h1>{{ $guide['title'] }}</h1>
            <p class="tutorial-hero-sub">{{ $guide['subtitle'] }}</p>
        </div>
    </header>

    @if(count($guide['sections']) === 0)
        <div class="tutorial-empty">
            <i class="fas fa-info-circle"></i>
            <p>Nenhum conteúdo disponível para seu perfil no momento. Entre em contato com o administrador da empresa.</p>
        </div>
    @else
        <div class="tutorial-layout">
            <aside class="tutorial-sidebar" aria-label="Navegação do tutorial">
                <div class="tutorial-sidebar-inner">
                    <div class="tutorial-sidebar-head">
                        <div class="tutorial-toc-title">Índice do guia</div>
                        <div class="tutorial-progress" aria-hidden="true">
                            <div class="tutorial-progress-bar" data-tutorial-progress></div>
                        </div>
                        <div class="tutorial-progress-label" data-tutorial-progress-label>0% concluído</div>
                    </div>

                    <nav class="tutorial-toc">
                        @php $globalIndex = 0; @endphp
                        @foreach($groupedSections as $groupName => $groupSections)
                            <div class="tutorial-toc-group">
                                <div class="tutorial-toc-group-title">{{ $groupName }}</div>
                                <ul>
                                    @foreach($groupSections as $section)
                                        @php $globalIndex++; @endphp
                                        <li>
                                            <a href="#tutorial-section-{{ $section['id'] }}"
                                               data-tutorial-link
                                               data-section-id="{{ $section['id'] }}">
                                                <span class="tutorial-toc-num">{{ $globalIndex }}</span>
                                                <span class="tutorial-toc-text">
                                                    <strong>{{ $section['title'] }}</strong>
                                                    @if(!empty($section['module_label']))
                                                        <small>{{ $section['module_label'] }}</small>
                                                    @endif
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </nav>
                </div>
            </aside>

            <main class="tutorial-main">
                <div class="tutorial-sections">
                    @foreach($guide['sections'] as $index => $section)
                        <article class="tutorial-section" id="tutorial-section-{{ $section['id'] }}" data-tutorial-section>
                            <div class="tutorial-section-header">
                                <div class="tutorial-section-icon">
                                    <i class="fas {{ $section['icon'] ?? 'fa-book' }}"></i>
                                </div>
                                <div class="tutorial-section-meta">
                                    <div class="tutorial-section-tags">
                                        <span class="tutorial-section-tag">Módulo {{ $index + 1 }} de {{ count($guide['sections']) }}</span>
                                        @if(!empty($section['group']) && $section['group'] !== 'Geral')
                                            <span class="tutorial-section-tag muted">{{ $section['group'] }}</span>
                                        @endif
                                        @if(!empty($section['module_label']))
                                            <span class="tutorial-section-tag accent">{{ $section['module_label'] }}</span>
                                        @endif
                                    </div>
                                    <h2>{{ $section['title'] }}</h2>
                                    @if(!empty($section['summary']))
                                        <p class="tutorial-section-summary">{{ $section['summary'] }}</p>
                                    @endif
                                </div>
                            </div>

                            @if(!empty($section['flow']))
                                <div class="tutorial-flow">
                                    <div class="tutorial-flow-title"><i class="fas fa-route"></i> Fluxo do processo</div>
                                    <ol class="tutorial-flow-steps">
                                        @foreach($section['flow'] as $flowStep)
                                            <li>{{ $flowStep }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif

                            @if(count($section['steps'] ?? []) > 0)
                                <ol class="tutorial-steps">
                                    @foreach($section['steps'] as $stepIndex => $step)
                                        <li class="tutorial-step">
                                            <div class="tutorial-step-marker">{{ $stepIndex + 1 }}</div>
                                            <div class="tutorial-step-body">
                                                <h3>{{ $step['title'] }}</h3>
                                                <p>{!! $step['body'] !!}</p>

                                                @if(!empty($step['bullets']))
                                                    <ul class="tutorial-step-bullets">
                                                        @foreach($step['bullets'] as $bullet)
                                                            <li>{!! $bullet !!}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif

                                                @if(!empty($step['tip']))
                                                    <div class="tutorial-tip">
                                                        <i class="fas fa-lightbulb"></i>
                                                        <span>{{ $step['tip'] }}</span>
                                                    </div>
                                                @endif

                                                @if(!empty($step['route']) && Route::has($step['route']))
                                                    <a href="{{ route($step['route']) }}" class="tutorial-cta">
                                                        <i class="fas fa-arrow-right"></i>
                                                        {{ $step['route_label'] ?? 'Acessar módulo' }}
                                                    </a>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            @endif
                        </article>
                    @endforeach
                </div>
            </main>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tutorial.js') }}" defer></script>
@endpush
