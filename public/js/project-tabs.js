(function () {
    'use strict';

    const PARTIAL_HEADER = 'project-tab';
    const ROOT_SEL = '#project-tab-root';
    const PANEL_SEL = '#project-tab-panel';
    const NAV_SEL = '.project-module-nav';
    const TAB_LINK_SEL = '.project-tab-link';
    const TAB_FORM_SEL = '.project-tab-form';

    const AppLoading = window.AppLoading || { show: () => {}, hide: () => {}, forceHide: () => {} };

    function runScripts(container) {
        container.querySelectorAll('script').forEach(function (oldScript) {
            const script = document.createElement('script');
            Array.from(oldScript.attributes).forEach(function (attr) {
                script.setAttribute(attr.name, attr.value);
            });
            script.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(script, oldScript);
        });
    }

    function setActiveTab(tab) {
        const root = document.querySelector(ROOT_SEL);
        if (root) root.dataset.currentTab = tab || '';

        document.querySelectorAll(NAV_SEL + ' ' + TAB_LINK_SEL).forEach(function (link) {
            const isActive = tab && link.dataset.tab === tab;
            link.classList.toggle('active', isActive);
        });
    }

    function isProjectTabUrl(url) {
        return /\/company\/projects\/\d+\/(kanban|dashboard|team)?\/?(\?.*)?$/.test(url.pathname)
            || /\/company\/projects\/\d+$/.test(url.pathname);
    }

    function tabFromUrl(pathname) {
        if (pathname.includes('/kanban')) return 'kanban';
        if (pathname.includes('/dashboard')) return 'dashboard';
        if (pathname.includes('/team')) return 'team';
        if (/\/company\/projects\/\d+$/.test(pathname)) return 'show';
        return null;
    }

    async function loadTab(url, options) {
        options = options || {};
        const pushState = options.pushState !== false;
        const tab = options.tab || tabFromUrl(new URL(url, window.location.origin).pathname);

        const panel = document.querySelector(PANEL_SEL);
        if (!panel) {
            window.location.href = url;
            return;
        }

        AppLoading.show(options.message || 'Carregando...');
        panel.classList.add('is-loading');

        try {
            const res = await fetch(url, {
                headers: {
                    'X-Partial-Load': PARTIAL_HEADER,
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const html = await res.text();
            panel.innerHTML = html;
            runScripts(panel);
            initTabPanel();

            const responseTab = res.headers.get('X-Project-Tab') || tab;
            setActiveTab(responseTab);

            if (pushState) {
                history.pushState({ projectTab: responseTab }, '', url);
            }

            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } catch (err) {
            console.error('Project tab load failed:', err);
            window.location.href = url;
        } finally {
            panel.classList.remove('is-loading');
            AppLoading.hide();
        }
    }

    function initKanbanDragDrop() {
        const board = document.getElementById('kanban-board');
        if (!board || board.dataset.canMove !== '1') return;

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (!csrfMeta) return;

        const csrf = csrfMeta.content;
        let dragged = null;

        board.querySelectorAll('.kanban-task[draggable="true"]').forEach(function (card) {
            card.addEventListener('dragstart', function () {
                dragged = card;
                card.classList.add('dragging');
            });
            card.addEventListener('dragend', function () {
                card.classList.remove('dragging');
                dragged = null;
            });
        });

        board.querySelectorAll('.kanban-column').forEach(function (col) {
            col.addEventListener('dragover', function (e) {
                e.preventDefault();
                col.classList.add('drag-over');
            });
            col.addEventListener('dragleave', function () {
                col.classList.remove('drag-over');
            });
            col.addEventListener('drop', async function (e) {
                e.preventDefault();
                col.classList.remove('drag-over');
                if (!dragged) return;

                const status = col.dataset.status;
                const taskId = dragged.dataset.taskId;
                const tasksContainer = col.querySelector('.kanban-tasks');
                tasksContainer.appendChild(dragged);

                AppLoading.show('Salvando...');
                try {
                    const res = await fetch('/company/tasks/' + taskId + '/status', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ status: status }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Erro', data.message || 'Não foi possível mover a task.', 'error');
                        }
                        loadTab(window.location.href, { pushState: false, tab: 'kanban' });
                    }
                } catch {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
                    }
                    loadTab(window.location.href, { pushState: false, tab: 'kanban' });
                } finally {
                    AppLoading.hide();
                }
            });
        });

    }

    function initTeamForm() {
        const addBtn = document.getElementById('add-member');
        const rows = document.getElementById('team-rows');
        const tpl = document.getElementById('team-row-template');
        if (!addBtn || !rows || !tpl || addBtn.dataset.init === '1') return;

        addBtn.addEventListener('click', function () {
            const index = rows.querySelectorAll('.team-row').length;
            rows.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, index));
        });
        rows.addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) {
                e.target.closest('.team-row').remove();
            }
        });
        addBtn.dataset.init = '1';
    }

    function initDashboardChart() {
        if (typeof window.initProjectDashboardChart === 'function') {
            window.initProjectDashboardChart();
        }
    }

    function initTabPanel() {
        initKanbanDragDrop();
        initTeamForm();
        initDashboardChart();
    }

    function handleTabClick(e) {
        const link = e.target.closest(TAB_LINK_SEL);
        if (!link || !document.querySelector(ROOT_SEL)) return;

        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || link.target === '_blank') return;

        e.preventDefault();
        const url = link.href;
        const tab = link.dataset.tab || tabFromUrl(new URL(url).pathname);
        loadTab(url, { tab: tab });
    }

    function handleTabFormSubmit(e) {
        const form = e.target.closest(TAB_FORM_SEL);
        if (!form || !document.querySelector(ROOT_SEL)) return;

        e.preventDefault();
        const url = new URL(form.action, window.location.origin);
        const formData = new FormData(form);
        url.search = '';
        formData.forEach(function (value, key) {
            url.searchParams.append(key, value);
        });
        loadTab(url.toString(), {
            pushState: true,
            tab: tabFromUrl(new URL(form.action).pathname) || 'kanban',
            message: 'Filtrando...',
        });
    }

    document.addEventListener('click', handleTabClick);
    document.addEventListener('submit', handleTabFormSubmit);

    window.addEventListener('popstate', function (e) {
        if (!document.querySelector(ROOT_SEL)) return;
        const tab = (e.state && e.state.projectTab) || tabFromUrl(window.location.pathname);
        loadTab(window.location.href, { pushState: false, tab: tab });
    });

    document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector(ROOT_SEL)) {
            const tab = document.querySelector(ROOT_SEL).dataset.currentTab
                || tabFromUrl(window.location.pathname);
            setActiveTab(tab);
            history.replaceState({ projectTab: tab }, '', window.location.href);
            initTabPanel();
        }
    });

    window.ProjectTabs = { load: loadTab, init: initTabPanel };
})();
