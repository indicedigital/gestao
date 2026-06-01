<div class="row g-4">
    @foreach([
        'general' => ['title' => 'Ranking geral', 'icon' => 'fa-trophy'],
        'growth' => ['title' => 'Maior crescimento', 'icon' => 'fa-arrow-trend-up'],
        'decline' => ['title' => 'Maior queda', 'icon' => 'fa-arrow-trend-down'],
        'efficiency' => ['title' => 'Mais eficientes', 'icon' => 'fa-bolt'],
        'overdue' => ['title' => 'Mais atrasos', 'icon' => 'fa-clock'],
        'consistency' => ['title' => 'Maior consistência', 'icon' => 'fa-calendar-check'],
    ] as $key => $meta)
    <div class="col-lg-6">
        <div class="prod-section h-100">
            <div class="prod-section-head">
                <span><i class="fas {{ $meta['icon'] }} me-2 text-primary"></i>{{ $meta['title'] }}</span>
            </div>
            <div class="prod-section-body p-3">
                @forelse($ranking[$key] ?? [] as $i => $m)
                <div class="prod-rank-item">
                    @php
                        $posClass = match($i) { 0 => 'gold', 1 => 'silver', 2 => 'bronze', default => '' };
                    @endphp
                    <div class="prod-rank-pos {{ $posClass }}">{{ $m['rank'] ?? ($i + 1) }}</div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold">{{ $m['employee']->name }}</div>
                        <div class="small text-muted">{{ $m['team'] }}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold">{{ $m['score'] }}</div>
                        <div class="small prod-trend {{ $m['trend'] }}">
                            @if($m['trend'] === 'up')<i class="fas fa-arrow-up"></i>@elseif($m['trend'] === 'down')<i class="fas fa-arrow-down"></i>@else<i class="fas fa-minus"></i>@endif
                            {{ $m['growth'] }}%
                        </div>
                    </div>
                    <span class="prod-badge {{ $m['level'] }}">{{ round($m['completion_rate']) }}%</span>
                </div>
                @empty
                <p class="text-muted small mb-0">Sem dados.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endforeach
</div>
