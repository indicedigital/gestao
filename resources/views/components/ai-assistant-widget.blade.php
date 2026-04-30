@if(auth()->check() && request()->routeIs('company.*'))
@php
    $aiCompany = auth()->user()->company ?? null;
    $aiLogoUrl = $aiCompany && method_exists($aiCompany, 'logoPublicUrl') ? $aiCompany->logoPublicUrl() : null;
@endphp
<style>
    .ai-assistant-fab { position: fixed; right: 22px; bottom: 24px; z-index: 1080; width: 62px; height: 62px; border-radius: 999px; border: none; background: radial-gradient(circle at 20% 20%, #7c8cff 0%, #4f46e5 45%, #0ea5e9 100%); color: #fff; box-shadow: 0 14px 30px rgba(79,70,229,.45); transition: all .2s ease; }
    .ai-assistant-fab:hover { transform: translateY(-3px) scale(1.02); }
    .ai-modal .modal-dialog { max-width: 980px; }
    .ai-modal .modal-content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 20px 50px rgba(2,6,23,.25); }
    .ai-header { background: linear-gradient(135deg, #0f766e 0%, #0ea5e9 45%, #4f46e5 100%); color: #fff; padding: 14px 18px; }
    .ai-brand { display: flex; align-items: center; gap: 10px; }
    .ai-logo { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; font-weight: 800; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35); }
    .ai-logo img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; }
    .ai-body { background: #f4f7fb; padding: 14px; }
    .ai-controls { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 10px; margin-bottom: 10px; }
    .ai-chat-log { height: 420px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px; }
    .ai-chat-row { display: flex; }
    .ai-chat-row.user { justify-content: flex-end; }
    .ai-chat-msg-user, .ai-chat-msg-assistant { max-width: 86%; border-radius: 14px; padding: 10px 12px; line-height: 1.45; font-size: 14px; }
    .ai-chat-msg-user { background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%); color: #fff; box-shadow: 0 6px 16px rgba(59,130,246,.25); }
    .ai-chat-msg-assistant { background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a; }
    .ai-input-wrap { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin-top: 10px; padding: 10px; }
    .ai-footer-note { margin-top: 6px; font-size: 11px; color: #94a3b8; text-align: center; }
</style>

<button type="button" class="ai-assistant-fab" data-bs-toggle="modal" data-bs-target="#aiAssistantModal" title="Assistente IA Índice">
    <i class="fas fa-robot fa-lg"></i>
</button>

<div class="modal fade ai-modal" id="aiAssistantModal" tabindex="-1" aria-labelledby="aiAssistantModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="ai-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ai-brand">
                        <div class="ai-logo">
                            @if($aiLogoUrl)
                                <img src="{{ $aiLogoUrl }}" alt="Logo Índice">
                            @else
                                Í
                            @endif
                        </div>
                        <div>
                            <div class="fw-bold" id="aiAssistantModalLabel">Assistente IA Índice</div>
                            <div class="small opacity-75">Gemini conectado aos dados da sua empresa</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
            </div>
            <div class="ai-body">
                <div class="ai-controls">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="ai_theme" class="form-label small text-muted mb-1">Tema</label>
                            <select id="ai_theme" class="form-select form-select-sm">
                                <option value="clientes">Clientes</option>
                                <option value="contratos">Contratos</option>
                                <option value="financeiro">Financeiro</option>
                                <option value="contabil">Contábil</option>
                                <option value="fluxo_caixa">Fluxo de Caixa</option>
                                <option value="despesas">Despesas</option>
                            </select>
                        </div>
                        <div class="col-md-8 d-flex align-items-end justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="ai_clear_btn">Limpar chat</button>
                        </div>
                    </div>
                </div>

                <div class="ai-chat-log" id="ai_chat_log">
                    <div class="ai-chat-row assistant">
                        <div class="ai-chat-msg-assistant">Olá! Sou o assistente da Índice. Escolha um tema e me pergunte qualquer coisa sobre os seus dados.</div>
                    </div>
                </div>

                <div class="ai-input-wrap">
                    <label for="ai_question" class="form-label small text-muted mb-1">Sua pergunta</label>
                    <div class="d-flex gap-2">
                        <textarea id="ai_question" class="form-control form-control-sm" rows="2" placeholder="Ex.: Quais clientes têm maior risco de inadimplência?"></textarea>
                        <button type="button" class="btn btn-primary btn-sm px-3" id="ai_send_btn">
                            <i class="fas fa-paper-plane me-1"></i>Enviar
                        </button>
                    </div>
                    <div class="ai-footer-note">Ctrl+Enter para enviar. IA pode cometer erros; valide dados críticos.</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const endpoint = @json(route('company.ai-assistant.chat'));
        const themeEl = document.getElementById('ai_theme');
        const qEl = document.getElementById('ai_question');
        const sendBtn = document.getElementById('ai_send_btn');
        const clearBtn = document.getElementById('ai_clear_btn');
        const logEl = document.getElementById('ai_chat_log');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const historyByTheme = {};

        function escapeHtml(value) {
            return (value || '').toString()
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function applyInlineMarkdown(line) {
            return line
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.+?)\*/g, '<em>$1</em>');
        }

        function markdownToHtml(text) {
            const safeText = escapeHtml(text);
            const lines = safeText.split(/\r?\n/);
            const html = [];
            let inUl = false;
            let inOl = false;

            function closeLists() {
                if (inUl) {
                    html.push('</ul>');
                    inUl = false;
                }
                if (inOl) {
                    html.push('</ol>');
                    inOl = false;
                }
            }

            lines.forEach(function (rawLine) {
                const line = rawLine.trim();

                if (line === '') {
                    closeLists();
                    html.push('<br>');
                    return;
                }

                const ulMatch = line.match(/^[-*]\s+(.+)$/);
                if (ulMatch) {
                    if (inOl) {
                        html.push('</ol>');
                        inOl = false;
                    }
                    if (!inUl) {
                        html.push('<ul class="mb-2 ps-4">');
                        inUl = true;
                    }
                    html.push('<li>' + applyInlineMarkdown(ulMatch[1]) + '</li>');
                    return;
                }

                const olMatch = line.match(/^(\d+)\.\s+(.+)$/);
                if (olMatch) {
                    if (inUl) {
                        html.push('</ul>');
                        inUl = false;
                    }
                    if (!inOl) {
                        html.push('<ol class="mb-2 ps-4">');
                        inOl = true;
                    }
                    html.push('<li>' + applyInlineMarkdown(olMatch[2]) + '</li>');
                    return;
                }

                closeLists();
                html.push('<div>' + applyInlineMarkdown(line) + '</div>');
            });

            closeLists();
            return html.join('');
        }

        function getCurrentTheme() {
            return themeEl?.value || 'financeiro';
        }

        function getHistory(theme) {
            if (!historyByTheme[theme]) {
                historyByTheme[theme] = [];
            }
            return historyByTheme[theme];
        }

        function addMsg(role, text, options = {}) {
            if (!logEl) return;
            const persist = options.persist !== false;
            const theme = options.theme || getCurrentTheme();
            const row = document.createElement('div');
            row.className = 'ai-chat-row ' + (role === 'user' ? 'user' : 'assistant');
            const div = document.createElement('div');
            div.className = role === 'user' ? 'ai-chat-msg-user' : 'ai-chat-msg-assistant';
            div.innerHTML = role === 'assistant'
                ? markdownToHtml(text || '')
                : escapeHtml(text || '').replace(/\n/g, '<br>');
            row.appendChild(div);
            logEl.appendChild(row);
            logEl.scrollTop = logEl.scrollHeight;

            if (persist) {
                getHistory(theme).push({ role, text: (text || '').toString() });
                if (getHistory(theme).length > 20) {
                    historyByTheme[theme] = getHistory(theme).slice(-20);
                }
            }
        }

        function renderHistory(theme) {
            if (!logEl) return;
            const history = getHistory(theme);
            if (!history.length) {
                logEl.innerHTML = '<div class="ai-chat-row assistant"><div class="ai-chat-msg-assistant">Olá! Sou o assistente da Índice. Escolha um tema e me pergunte qualquer coisa sobre os seus dados.</div></div>';
                return;
            }

            logEl.innerHTML = '';
            history.forEach(function (msg) {
                addMsg(msg.role, msg.text, { persist: false, theme });
            });
        }

        async function send() {
            if (!themeEl || !qEl || !sendBtn) return;
            const theme = themeEl.value;
            const message = (qEl.value || '').trim();
            if (!message) return;

            addMsg('user', message, { theme });
            qEl.value = '';
            sendBtn.disabled = true;
            addMsg('assistant', 'Analisando seus dados...', { persist: false, theme });

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        theme,
                        message,
                        history: getHistory(theme).slice(-12)
                    })
                });
                const data = await res.json();
                logEl.lastElementChild?.remove();
                if (!res.ok || !data.ok) {
                    addMsg('assistant', data.message || 'Falha ao consultar o assistente.', { theme });
                    return;
                }
                addMsg('assistant', data.answer || 'Sem resposta.', { theme });
            } catch (e) {
                logEl.lastElementChild?.remove();
                addMsg('assistant', 'Erro de conexão com o assistente.', { theme });
            } finally {
                sendBtn.disabled = false;
            }
        }

        sendBtn?.addEventListener('click', send);
        qEl?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                send();
            }
        });
        clearBtn?.addEventListener('click', function () {
            if (!logEl) return;
            const theme = getCurrentTheme();
            historyByTheme[theme] = [];
            logEl.innerHTML = '<div class="ai-chat-row assistant"><div class="ai-chat-msg-assistant">Chat limpo. Escolha um tema e faça uma nova pergunta.</div></div>';
        });
        themeEl?.addEventListener('change', function () {
            renderHistory(getCurrentTheme());
        });
    })();
</script>
@endpush
@endif
