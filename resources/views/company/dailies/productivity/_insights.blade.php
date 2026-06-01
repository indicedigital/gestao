<div class="prod-section">
    <div class="prod-section-head">
        <span><i class="fas fa-brain me-2 text-primary"></i>Insights inteligentes</span>
    </div>
    <div class="prod-section-body">
        <div class="row g-3">
            @foreach($insights as $i => $insight)
            <div class="col-md-6">
                <div class="prod-insight h-100">
                    <i class="fas fa-{{ ['chart-line','users','exclamation-triangle','clock','star','bullseye','stopwatch'][$i % 7] }}"></i>
                    <span>{{ $insight }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="prod-charts-grid mt-4">
    <div class="prod-chart-box">
        <div class="prod-chart-title">Comparativo de produtividade</div>
        <canvas id="chartCompareProd" height="200"></canvas>
    </div>
    <div class="prod-chart-box">
        <div class="prod-chart-title">Comparativo de entregas</div>
        <canvas id="chartCompareTasks" height="200"></canvas>
    </div>
</div>
