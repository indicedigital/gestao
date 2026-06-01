(() => {
    'use strict';

    const root = document.getElementById('prod-dashboard');
    if (!root) return;

    const tabUrl = root.dataset.tabUrl;
    const panel = root.querySelector('[data-prod-panel]');
    const panelInner = root.querySelector('[data-prod-panel-inner]');
    const filterForm = document.querySelector('[data-prod-filter-form]');
    const skeletonTpl = document.getElementById('prod-skeleton-template');
    const periodLabel = root.querySelector('[data-prod-period-label]');
    const alertBadge = root.querySelector('[data-prod-alert-badge]');

    let activeTab = root.dataset.activeTab || 'overview';
    let chartInstances = [];
    let fetchController = null;
    let chartJsReady = typeof Chart !== 'undefined';

    const waitChartJs = () => new Promise(resolve => {
        if (typeof Chart !== 'undefined') { chartJsReady = true; resolve(); return; }
        const iv = setInterval(() => {
            if (typeof Chart !== 'undefined') { clearInterval(iv); chartJsReady = true; resolve(); }
        }, 50);
        setTimeout(() => { clearInterval(iv); resolve(); }, 5000);
    });

    function destroyCharts() {
        chartInstances.forEach(c => { try { c.destroy(); } catch (_) {} });
        chartInstances = [];
    }

    function themeColors() {
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        return {
            grid: dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
            text: dark ? '#cbd5e1' : '#64748b',
            colors: ['#5e72e4', '#2dce89', '#fb6340', '#11cdef', '#8965e0', '#ffd600'],
        };
    }

    function registerChart(instance) {
        chartInstances.push(instance);
        return instance;
    }

    function renderOverviewCharts(charts) {
        if (!charts || !chartJsReady) return;
        const { grid, text, colors } = themeColors();
        Chart.defaults.color = text;
        Chart.defaults.borderColor = grid;

        const mk = (id, cfg) => {
            const el = document.getElementById(id);
            if (el) registerChart(new Chart(el, cfg));
        };

        mk('chartEvolution', {
            type: 'line',
            data: {
                labels: charts.evolution.labels,
                datasets: [{ label: 'Horas', data: charts.evolution.hours, borderColor: '#5e72e4', backgroundColor: 'rgba(94,114,228,0.12)', fill: true, tension: 0.35, pointRadius: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: true, animation: { duration: 400 }, plugins: { legend: { display: false } }, scales: { y: { grid: { color: grid } }, x: { grid: { display: false } } } }
        });

        mk('chartByEmployee', {
            type: 'bar',
            data: {
                labels: charts.by_employee.labels,
                datasets: [
                    { label: 'Horas', data: charts.by_employee.hours, backgroundColor: '#5e72e4', borderRadius: 6 },
                    { label: 'Eficiência', data: charts.by_employee.efficiency, backgroundColor: '#2dce89', borderRadius: 6 }
                ]
            },
            options: { responsive: true, animation: { duration: 400 }, scales: { x: { ticks: { maxRotation: 45, font: { size: 10 } } } } }
        });

        mk('chartByTeam', {
            type: 'bar',
            data: { labels: charts.by_team.labels, datasets: [{ data: charts.by_team.productivity, backgroundColor: colors, borderRadius: 6 }] },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        mk('chartTaskStatus', {
            type: 'doughnut',
            data: {
                labels: ['Concluídas', 'Pendentes', 'Atrasadas'],
                datasets: [{ data: [charts.task_status.completed, charts.task_status.pending, charts.task_status.overdue], backgroundColor: ['#2dce89', '#5e72e4', '#f5365c'], borderWidth: 0 }]
            },
            options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12 } } } }
        });

        mk('chartGoals', {
            type: 'line',
            data: { labels: charts.goal_evolution.labels, datasets: [{ data: charts.goal_evolution.values, borderColor: '#2dce89', backgroundColor: 'rgba(45,206,137,0.1)', fill: true, tension: 0.35 }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { max: 100 } } }
        });

        const statusLabels = Object.keys(charts.status_distribution || {});
        mk('chartStatusDist', {
            type: 'pie',
            data: { labels: statusLabels, datasets: [{ data: statusLabels.map(k => charts.status_distribution[k]), backgroundColor: colors, borderWidth: 0 }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
        });

        mk('chartRanking', {
            type: 'bar',
            data: { labels: charts.by_employee.labels, datasets: [{ data: charts.by_employee.scores, backgroundColor: '#5e72e4', borderRadius: 6 }] },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
        });
    }

    function renderEmployeeCharts(charts, metrics) {
        if (!charts || !chartJsReady) return;
        const { grid, text, colors } = themeColors();

        const mk = (id, cfg) => {
            const el = document.getElementById(id);
            if (el) registerChart(new Chart(el, cfg));
        };

        if (charts.daily) {
            mk('chartEmpDaily', {
                type: 'line',
                data: { labels: charts.daily.labels, datasets: [{ data: charts.daily.hours, borderColor: '#5e72e4', backgroundColor: 'rgba(94,114,228,0.1)', fill: true, tension: 0.35 }] },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { grid: { color: grid } }, x: { ticks: { color: text } } } }
            });
        }

        const cats = charts.categories || {};
        mk('chartEmpCategory', {
            type: 'doughnut',
            data: { labels: Object.keys(cats), datasets: [{ data: Object.values(cats), backgroundColor: colors, borderWidth: 0 }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: text, boxWidth: 10 } } } }
        });

        if (metrics) {
            mk('chartEmpVsTeam', {
                type: 'bar',
                data: {
                    labels: ['Colaborador', 'Média equipe'],
                    datasets: [{ data: [metrics.productivity_pct, metrics.team_avg_productivity], backgroundColor: ['#5e72e4', '#2dce89'], borderRadius: 8 }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { grid: { color: grid }, ticks: { color: text } }, x: { ticks: { color: text } } } }
            });
        }
    }

    function renderCompareCharts(comparatives) {
        if (!comparatives || !chartJsReady) return;
        const { grid, text } = themeColors();
        const mk = (id, data, colors) => {
            const el = document.getElementById(id);
            if (el) registerChart(new Chart(el, {
                type: 'bar',
                data: { labels: ['Período anterior', 'Período atual'], datasets: [{ data, backgroundColor: colors, borderRadius: 8 }] },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { grid: { color: grid }, ticks: { color: text } }, x: { ticks: { color: text } } } }
            }));
        };
        mk('chartCompareProd', [comparatives.productivity_previous, comparatives.productivity_current], ['#8898aa', '#5e72e4']);
        mk('chartCompareTasks', [comparatives.completed_previous, comparatives.completed_current], ['#8898aa', '#2dce89']);
    }

    function renderHistoryChart(history) {
        if (!history?.monthly || !chartJsReady) return;
        const el = document.getElementById('chartHistory');
        if (!el) return;
        const { grid, text } = themeColors();
        registerChart(new Chart(el, {
            type: 'line',
            data: {
                labels: history.monthly.map(h => h.label),
                datasets: [
                    { label: 'Produtividade %', data: history.monthly.map(h => h.productivity), borderColor: '#5e72e4', yAxisID: 'y', tension: 0.35 },
                    { label: 'Concluídas', data: history.monthly.map(h => h.completed), borderColor: '#2dce89', yAxisID: 'y1', tension: 0.35 }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { position: 'left', grid: { color: grid }, ticks: { color: text } },
                    y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: text } },
                    x: { ticks: { color: text } }
                }
            }
        }));
    }

    function renderGoalsChart(charts) {
        if (!charts?.goal_evolution || !chartJsReady) return;
        const el = document.getElementById('chartGoalsTab');
        if (!el) return;
        registerChart(new Chart(el, {
            type: 'line',
            data: {
                labels: charts.goal_evolution.labels,
                datasets: [{ data: charts.goal_evolution.values, borderColor: '#2dce89', backgroundColor: 'rgba(45,206,137,0.1)', fill: true, tension: 0.35 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { max: 100 } } }
        }));
    }

    function initChartsForTab(tab, payload) {
        destroyCharts();
        waitChartJs().then(() => {
            if (tab === 'overview') renderOverviewCharts(payload.charts);
            if (tab === 'collaborators') renderEmployeeCharts(payload.employeeCharts, payload.employeeMetrics);
            if (tab === 'insights') renderCompareCharts(payload.comparatives);
            if (tab === 'history') renderHistoryChart(payload.history);
            if (tab === 'goals') renderGoalsChart(payload.charts);
        });
    }

    function showSkeleton() {
        panel.setAttribute('aria-busy', 'true');
        panel.classList.add('is-loading');
        if (skeletonTpl) {
            panelInner.innerHTML = skeletonTpl.innerHTML;
        }
    }

    function buildQueryParams(tab) {
        const params = new URLSearchParams(new FormData(filterForm));
        params.set('tab', tab);
        return params;
    }

    function updateUrl(tab, params) {
        const url = new URL(window.location.href);
        url.search = params.toString();
        history.replaceState({ tab }, '', url);
    }

    async function loadTab(tab, pushState = true) {
        if (fetchController) fetchController.abort();
        fetchController = new AbortController();

        activeTab = tab;
        root.dataset.activeTab = tab;

        root.querySelectorAll('[data-prod-tab]').forEach(btn => {
            const on = btn.dataset.prodTab === tab;
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });

        const tabInput = filterForm?.querySelector('[data-prod-tab-input]');
        if (tabInput) tabInput.value = tab;

        showSkeleton();

        const params = buildQueryParams(tab);
        if (pushState) updateUrl(tab, params);

        try {
            const res = await fetch(`${tabUrl}?${params}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                signal: fetchController.signal,
            });

            if (!res.ok) throw new Error('Falha ao carregar');

            const data = await res.json();
            panelInner.innerHTML = data.html;
            panel.classList.remove('is-loading');
            panel.setAttribute('aria-busy', 'false');

            if (data.period && periodLabel) {
                const company = root.dataset.companyName || '';
                periodLabel.textContent = `${data.period.label} · ${data.period.business_days} dias úteis · ${company}`;
            }

            if (data.alert_count !== undefined && alertBadge) {
                alertBadge.textContent = data.alert_count;
                alertBadge.classList.toggle('d-none', data.alert_count <= 0);
            }

            initChartsForTab(tab, data);
            bindPanelEvents();
        } catch (err) {
            if (err.name === 'AbortError') return;
            panelInner.innerHTML = `<div class="prod-empty-state"><i class="fas fa-exclamation-circle"></i><p>Não foi possível carregar os dados. <button type="button" class="btn btn-sm btn-primary mt-2" data-prod-retry>Tentar novamente</button></p></div>`;
            panel.classList.remove('is-loading');
            panel.querySelector('[data-prod-retry]')?.addEventListener('click', () => loadTab(tab, false));
        }
    }

    function bindPanelEvents() {
        panelInner.querySelector('#prod-emp-search')?.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            panelInner.querySelectorAll('#prod-emp-list .prod-employee-link').forEach(el => {
                el.style.display = el.dataset.name.includes(q) ? '' : 'none';
            });
        });

        panelInner.querySelectorAll('[data-prod-emp-link]').forEach(link => {
            link.addEventListener('click', e => {
                e.preventDefault();
                const empId = link.dataset.prodEmpLink;
                const input = filterForm.querySelector('[name=selected_employee_id]') || document.createElement('input');
                if (!input.name) {
                    input.type = 'hidden';
                    input.name = 'selected_employee_id';
                    filterForm.appendChild(input);
                }
                input.value = empId;
                loadTab('collaborators');
            });
        });
    }

    root.querySelectorAll('[data-prod-tab]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.prodTab === activeTab) return;
            loadTab(btn.dataset.prodTab);
        });
    });

    if (filterForm) {
        filterForm.querySelectorAll('[data-prod-period]').forEach(btn => {
            btn.addEventListener('click', () => {
                filterForm.querySelector('[data-prod-period-input]').value = btn.dataset.prodPeriod;
                filterForm.querySelectorAll('[data-prod-period]').forEach(b => b.classList.toggle('active', b === btn));
                const customDates = filterForm.querySelector('[data-prod-custom-dates]');
                if (customDates) customDates.hidden = btn.dataset.prodPeriod !== 'custom';
                if (btn.dataset.prodPeriod !== 'custom') loadTab(activeTab);
            });
        });

        filterForm.querySelector('[data-prod-toggle-filters]')?.addEventListener('click', () => {
            const adv = filterForm.querySelector('[data-prod-advanced-filters]');
            if (adv) adv.hidden = !adv.hidden;
        });

        filterForm.addEventListener('submit', e => {
            e.preventDefault();
            AppLoading.show('Atualizando...');
            loadTab(activeTab).finally(() => AppLoading.hide());
        });
    }

    waitChartJs().then(() => {
        try {
            const payload = JSON.parse(root.dataset.initialPayload || '{}');
            if (Object.keys(payload).length) {
                initChartsForTab(activeTab, payload);
            }
        } catch (_) {}
        bindPanelEvents();
    });

    window.addEventListener('popstate', () => {
        const tab = new URLSearchParams(window.location.search).get('tab') || 'overview';
        loadTab(tab, false);
    });
})();
