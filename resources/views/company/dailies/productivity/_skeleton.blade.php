<div class="prod-panel-loading" aria-hidden="true">
    <div class="prod-kpi-grid prod-kpi-grid--hero">
        @for($i = 0; $i < 4; $i++)
        <div class="prod-kpi prod-skeleton">
            <div class="prod-skeleton-line sm"></div>
            <div class="prod-skeleton-line lg"></div>
        </div>
        @endfor
    </div>
    <div class="prod-charts-grid">
        <div class="prod-chart-box wide prod-skeleton">
            <div class="prod-skeleton-line sm mb-3"></div>
            <div class="prod-skeleton-chart"></div>
        </div>
        @for($i = 0; $i < 2; $i++)
        <div class="prod-chart-box prod-skeleton">
            <div class="prod-skeleton-line sm mb-3"></div>
            <div class="prod-skeleton-chart"></div>
        </div>
        @endfor
    </div>
</div>
