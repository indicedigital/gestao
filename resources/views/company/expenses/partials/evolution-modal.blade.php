{{-- Modal + script: evolução mensal no ano (Chart.js). Incluir no final da @section('content'). --}}
<div class="modal fade" id="expenseEvolutionModal" tabindex="-1" aria-labelledby="expenseEvolutionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="expenseEvolutionModalLabel">Evolução no ano</h5>
                    <p class="small text-muted mb-0" id="expenseEvolutionSubtitle"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <label for="expenseEvolutionYear" class="small text-muted mb-0">Ano</label>
                    <select id="expenseEvolutionYear" class="form-select form-select-sm" style="width: 110px;"></select>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="expenseEvolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const evolutionUrl = @json(route('company.expenses.monthly-evolution'));
    const pageType = @json($type);
    const defaultYear = {{ (int) substr($monthInput, 0, 4) }};
    const defaultCategoryKey = @json($selectedCategoryKey);

    const modalEl = document.getElementById('expenseEvolutionModal');
    const yearSel = document.getElementById('expenseEvolutionYear');
    const titleEl = document.getElementById('expenseEvolutionModalLabel');
    const subtitleEl = document.getElementById('expenseEvolutionSubtitle');
    const canvas = document.getElementById('expenseEvolutionChart');

    let chartInstance = null;
    let modalInstance = null;
    let requestState = { year: defaultYear, type: pageType, category_id: defaultCategoryKey };
    let activeMode = 'sum';

    function brl(n) {
        return Number(n).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function buildUrl() {
        const u = new URL(evolutionUrl, window.location.origin);
        u.searchParams.set('year', String(requestState.year));
        u.searchParams.set('type', requestState.type);
        if (requestState.category_id && requestState.category_id !== '') {
            u.searchParams.set('category_id', requestState.category_id);
        }
        return u.toString();
    }

    function fillYearSelect() {
        if (!yearSel) return;
        const y0 = defaultYear;
        yearSel.innerHTML = '';
        for (let y = y0 - 4; y <= y0 + 2; y++) {
            const opt = document.createElement('option');
            opt.value = String(y);
            opt.textContent = String(y);
            yearSel.appendChild(opt);
        }
        yearSel.value = String(requestState.year);
    }

    function destroyChart() {
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
    }

    function buildChartConfig(mode, data) {
        const labels = data.labels;
        let datasets = [];
        let yTitle = '';

        if (mode === 'sum' || mode === 'category') {
            datasets = [{
                label: 'Valor (R$)',
                data: data.sums,
                borderColor: '#2dce89',
                backgroundColor: 'rgba(45, 206, 137, 0.12)',
                fill: true,
                tension: 0.25,
            }];
            yTitle = 'R$';
        } else if (mode === 'count') {
            datasets = [{
                label: 'Lançamentos',
                data: data.counts,
                borderColor: '#5e72e4',
                backgroundColor: 'rgba(94, 114, 228, 0.12)',
                fill: true,
                tension: 0.25,
            }];
            yTitle = 'Quantidade';
        } else if (mode === 'average') {
            datasets = [{
                label: 'Média (R$)',
                data: data.averages,
                borderColor: '#11cdef',
                backgroundColor: 'rgba(17, 205, 239, 0.12)',
                fill: true,
                tension: 0.25,
            }];
            yTitle = 'R$';
        } else if (mode === 'share') {
            datasets = [{
                label: 'Participação (%)',
                data: data.shares,
                borderColor: '#fb6340',
                backgroundColor: 'rgba(251, 99, 64, 0.12)',
                fill: true,
                tension: 0.25,
            }];
            yTitle = '%';
        } else if (mode === 'type_total') {
            datasets = [{
                label: requestState.type === 'fixed' ? 'Total fixas (R$)' : 'Total variáveis no mês (R$)',
                data: data.type_sums,
                borderColor: '#5e72e4',
                backgroundColor: 'rgba(94, 114, 228, 0.12)',
                fill: true,
                tension: 0.25,
            }];
            yTitle = 'R$';
        }

        return {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: datasets.length > 1 },
                    tooltip: {
                        callbacks: {
                            label(ctx) {
                                const v = ctx.parsed.y;
                                if (mode === 'share') return ctx.dataset.label + ': ' + v.toLocaleString('pt-BR', { maximumFractionDigits: 2 }) + '%';
                                if (mode === 'count') return ctx.dataset.label + ': ' + v;
                                return ctx.dataset.label + ': ' + brl(v);
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: yTitle },
                    },
                },
            },
        };
    }

    async function loadAndRender() {
        if (!canvas) return;
        destroyChart();
        const res = await fetch(buildUrl(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            subtitleEl.textContent = 'Não foi possível carregar os dados.';
            return;
        }
        const data = await res.json();
        const mode = activeMode;
        if (mode === 'average') {
            subtitleEl.textContent = 'Média = soma ÷ quantidade de lançamentos em cada mês.';
        } else if (mode === 'share') {
            subtitleEl.textContent = 'Percentual do filtro atual sobre o total do tipo no mesmo mês.';
        } else if (mode === 'type_total') {
            subtitleEl.textContent = requestState.type === 'variable'
                ? 'Soma de todas as variáveis com vencimento em cada mês.'
                : 'Valor mensal total das despesas fixas (cadastro).';
        } else if (mode === 'count') {
            subtitleEl.textContent = 'Quantidade de lançamentos por mês (mesmo critério do filtro).';
        } else {
            subtitleEl.textContent = requestState.type === 'variable'
                ? 'Soma dos valores com data de vencimento em cada mês.'
                : 'Valor total das fixas que entram no filtro de categoria (repetido por mês).';
        }
        chartInstance = new Chart(canvas.getContext('2d'), buildChartConfig(mode, data));
    }

    function openFromTrigger(el) {
        const mode = el.getAttribute('data-expense-evolution') || 'sum';
        activeMode = mode;
        const y = parseInt(el.getAttribute('data-evolution-year') || String(defaultYear), 10);
        const t = el.getAttribute('data-evolution-type') || pageType;
        const catRaw = el.getAttribute('data-category-id');
        const title = el.getAttribute('data-evolution-title') || 'Evolução no ano';

        requestState.year = y;
        requestState.type = t;
        requestState.category_id = catRaw === null ? defaultCategoryKey : catRaw;

        titleEl.textContent = title;
        fillYearSelect();
        if (yearSel) yearSel.value = String(requestState.year);

        if (!modalInstance && modalEl && window.bootstrap) {
            modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        modalInstance?.show();
        loadAndRender().catch(() => {
            subtitleEl.textContent = 'Erro ao carregar evolução.';
        });
    }

    document.querySelectorAll('[data-expense-evolution]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (e.target.closest('a[href]')) return;
            openFromTrigger(el);
        });
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openFromTrigger(el);
            }
        });
    });

    yearSel?.addEventListener('change', function () {
        requestState.year = parseInt(yearSel.value, 10) || defaultYear;
        loadAndRender().catch(function () {});
    });

    modalEl?.addEventListener('hidden.bs.modal', function () {
        destroyChart();
    });
})();
</script>
@endpush
