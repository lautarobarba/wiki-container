{{--
    Floating AI chat widget ("Temis"), included from layouts/base.blade.php only when
    the current view belongs to a system (shelf), passed as $aiChatShelf.
    Backed by the route registered in themes/custom/functions.php.
--}}
<div id="ai-chat" class="print-hidden">
    <button type="button" id="ai-chat-fab" aria-label="{{ trans('entities.ai_chat_open') }}" aria-expanded="false" title="{{ trans('entities.ai_chat_title') }}">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM8 11a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 8 11zm4 0a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 12 11zm4 0a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 16 11z"/>
        </svg>
    </button>

    <div id="ai-chat-panel" role="dialog" aria-label="{{ trans('entities.ai_chat_title') }}" hidden>
        <div class="ai-chat-header">
            <div class="ai-chat-avatar" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                    <path d="M12,3C10.73,3 9.6,3.8 9.18,5H3V7H4.95L2,14C1.53,16 3,17 5.5,17C8,17 9.56,16 9,14L6.05,7H9.17C9.5,7.85 10.15,8.5 11,8.83V20H2V22H22V20H13V8.82C13.85,8.5 14.5,7.85 14.82,7H17.95L15,14C14.53,16 16,17 18.5,17C21,17 22.56,16 22,14L19.05,7H21V5H14.83C14.4,3.8 13.27,3 12,3M12,5A1,1 0 0,1 13,6A1,1 0 0,1 12,7A1,1 0 0,1 11,6A1,1 0 0,1 12,5M5.5,10.25L7,14H4L5.5,10.25M18.5,10.25L20,14H17L18.5,10.25Z"/>
                </svg>
            </div>
            <div class="ai-chat-header-text">
                <strong>{{ trans('entities.ai_chat_title') }}</strong>
                <span>{{ $aiChatShelf->name }}</span>
            </div>
            <div class="ai-chat-header-actions">
                <button type="button" id="ai-chat-clear" aria-label="{{ trans('entities.ai_chat_clear') }}" title="{{ trans('entities.ai_chat_clear') }}">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                    </svg>
                </button>
                <button type="button" id="ai-chat-close" aria-label="{{ trans('entities.ai_chat_close') }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                        <path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="ai-chat-messages" aria-live="polite"></div>

        <form class="ai-chat-form">
            <input type="text" class="ai-chat-input" placeholder="{{ trans('entities.ai_chat_placeholder') }}"
                   autocomplete="off" maxlength="1000">
            <button type="submit" class="ai-chat-send" aria-label="{{ trans('entities.ai_chat_send') }}">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                    <path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </form>
    </div>
</div>

<style>
    #ai-chat {
        --ai-chat-surface: #ffffff;
        --ai-chat-surface-alt: #f2f5f9;
        --ai-chat-text: #3b4351;
        --ai-chat-border: #e2e8f1;
        --ai-chat-primary-dark: color-mix(in srgb, var(--color-primary) 78%, #001a2e);
        --ai-chat-user-bubble: linear-gradient(135deg, var(--color-primary), var(--ai-chat-primary-dark));
        /* El back-to-top se movió a la izquierda (ver theme-styles), el chat es dueño del rincón derecho */
        position: fixed;
        inset-block-end: 1rem;
        inset-inline-end: 1rem;
        z-index: 500;
    }
    html.dark-mode #ai-chat {
        --ai-chat-surface: #1c2126;
        --ai-chat-surface-alt: #272d34;
        --ai-chat-text: #e3e6ea;
        --ai-chat-border: rgba(255, 255, 255, 0.1);
    }

    #ai-chat-fab {
        width: 58px;
        height: 58px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-primary), var(--ai-chat-primary-dark));
        color: #ffffff;
        cursor: pointer;
        display: grid;
        place-items: center;
        box-shadow: 0 6px 18px color-mix(in srgb, var(--color-primary) 45%, rgba(0, 0, 0, 0.25));
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    #ai-chat-fab:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 10px 24px color-mix(in srgb, var(--color-primary) 55%, rgba(0, 0, 0, 0.3));
    }

    #ai-chat-panel {
        display: flex;
        flex-direction: column;
        width: min(390px, calc(100vw - 2rem));
        height: min(580px, calc(100vh - 5rem));
        background: var(--ai-chat-surface);
        color: var(--ai-chat-text);
        border: 1px solid var(--ai-chat-border);
        border-radius: 16px;
        box-shadow: 0 18px 44px rgba(9, 30, 51, 0.28), 0 4px 12px rgba(9, 30, 51, 0.12);
        overflow: hidden;
        transform-origin: bottom right;
        animation: ai-chat-pop 0.22s ease;
    }
    #ai-chat-panel[hidden] { display: none; }
    @keyframes ai-chat-pop {
        from { opacity: 0; transform: translateY(10px) scale(0.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @media (prefers-reduced-motion: reduce) {
        #ai-chat-panel { animation: none; }
        #ai-chat-fab, #ai-chat-fab:hover { transition: none; transform: none; }
    }

    .ai-chat-header {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.75rem 0.9rem;
        background: linear-gradient(120deg, var(--color-primary), var(--ai-chat-primary-dark));
        color: #ffffff;
    }
    .ai-chat-avatar {
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: grid;
        place-items: center;
    }
    .ai-chat-header-text {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
        min-width: 0;
        flex: 1;
    }
    .ai-chat-header-text strong {
        font-size: 1.05rem;
        letter-spacing: 0.01em;
    }
    .ai-chat-header-text span {
        font-size: 0.78rem;
        opacity: 0.85;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ai-chat-header-actions {
        flex-shrink: 0;
        display: flex;
        gap: 0.15rem;
    }
    #ai-chat-close, #ai-chat-clear {
        border: none;
        background: transparent;
        color: #ffffff;
        cursor: pointer;
        padding: 0.3rem;
        border-radius: 8px;
        display: grid;
        place-items: center;
        transition: background-color 0.12s ease;
    }
    #ai-chat-close:hover, #ai-chat-clear:hover { background: rgba(255, 255, 255, 0.18); }

    .ai-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 0.9rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        background: var(--ai-chat-surface-alt);
        scrollbar-width: thin;
    }
    .ai-chat-bubble {
        max-width: 88%;
        padding: 0.55rem 0.8rem;
        border-radius: 14px;
        font-size: 0.92rem;
        line-height: 1.45;
        white-space: pre-wrap;
        overflow-wrap: break-word;
        animation: ai-chat-bubble-in 0.18s ease;
    }
    @keyframes ai-chat-bubble-in {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .ai-chat-bubble.user {
        align-self: flex-end;
        background: var(--ai-chat-user-bubble);
        color: #ffffff;
        border-end-end-radius: 4px;
    }
    .ai-chat-bubble.assistant {
        align-self: flex-start;
        background: var(--ai-chat-surface);
        border: 1px solid var(--ai-chat-border);
        border-end-start-radius: 4px;
        box-shadow: 0 1px 2px rgba(16, 30, 54, 0.05);
    }

    .ai-chat-sources {
        align-self: flex-start;
        max-width: 88%;
        font-size: 0.78rem;
        opacity: 0.85;
        padding-inline-start: 0.25rem;
    }
    .ai-chat-sources ul {
        margin: 0.15rem 0 0;
        padding-inline-start: 1.1rem;
    }
    .ai-chat-sources a { color: var(--color-link); }

    .ai-chat-typing {
        align-self: flex-start;
        display: flex;
        gap: 5px;
        padding: 0.7rem 0.9rem;
        background: var(--ai-chat-surface);
        border: 1px solid var(--ai-chat-border);
        border-radius: 14px;
        border-end-start-radius: 4px;
    }
    .ai-chat-typing i {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.4;
        animation: ai-chat-blink 1.2s infinite;
    }
    .ai-chat-typing i:nth-child(2) { animation-delay: 0.2s; }
    .ai-chat-typing i:nth-child(3) { animation-delay: 0.4s; }
    @keyframes ai-chat-blink {
        0%, 60%, 100% { opacity: 0.35; transform: translateY(0); }
        30% { opacity: 1; transform: translateY(-3px); }
    }

    .ai-chat-form {
        display: flex;
        gap: 0.5rem;
        padding: 0.65rem;
        border-top: 1px solid var(--ai-chat-border);
        background: var(--ai-chat-surface);
    }
    .ai-chat-input {
        flex: 1;
        min-width: 0;
        border: 1px solid var(--ai-chat-border);
        border-radius: 999px;
        padding: 0.5rem 0.9rem;
        font-size: 0.92rem;
        background: var(--ai-chat-surface-alt);
        color: var(--ai-chat-text);
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
    }
    .ai-chat-input:focus {
        outline: none;
        background: var(--ai-chat-surface);
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--color-primary) 22%, transparent);
    }
    .ai-chat-send {
        flex-shrink: 0;
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--color-primary), var(--ai-chat-primary-dark));
        color: #ffffff;
        cursor: pointer;
        display: grid;
        place-items: center;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .ai-chat-send:hover:not(:disabled) {
        transform: scale(1.06);
        box-shadow: 0 3px 10px color-mix(in srgb, var(--color-primary) 40%, transparent);
    }
    .ai-chat-send:disabled, .ai-chat-input:disabled { opacity: 0.6; }

    @media print {
        #ai-chat { display: none; }
    }
</style>

<script nonce="{{ $cspNonce ?? '' }}">
    (function () {
        var root = document.getElementById('ai-chat');
        if (!root) {
            return;
        }

        var config = {
            askUrl: @json(url('/ai-chat/ask')),
            historyUrl: @json(url('/ai-chat/history')),
            clearUrl: @json(url('/ai-chat/clear')),
            shelfId: @json($aiChatShelf->id),
            errorMessage: @json(trans('entities.ai_chat_error')),
            sourcesLabel: @json(trans('entities.ai_chat_sources_label')),
            welcomeText: @json(trans('entities.ai_chat_welcome')),
        };

        var fab = document.getElementById('ai-chat-fab');
        var panel = document.getElementById('ai-chat-panel');
        var closeButton = document.getElementById('ai-chat-close');
        var clearButton = document.getElementById('ai-chat-clear');
        var form = root.querySelector('.ai-chat-form');
        var input = root.querySelector('.ai-chat-input');
        var sendButton = root.querySelector('.ai-chat-send');
        var messages = root.querySelector('.ai-chat-messages');
        var tokenMeta = document.querySelector('meta[name="token"]');
        var csrfToken = tokenMeta ? tokenMeta.content : '';

        function setOpen(open) {
            panel.hidden = !open;
            fab.style.display = open ? 'none' : '';
            fab.setAttribute('aria-expanded', String(open));
            if (open) {
                input.focus();
                messages.scrollTop = messages.scrollHeight;
            }
        }

        fab.addEventListener('click', function () { setOpen(true); });
        closeButton.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !panel.hidden) {
                setOpen(false);
            }
        });

        function scrollToEnd() {
            messages.scrollTop = messages.scrollHeight;
        }

        function addBubble(role, text) {
            var bubble = document.createElement('div');
            bubble.className = 'ai-chat-bubble ' + role;
            bubble.textContent = text;
            messages.appendChild(bubble);
            scrollToEnd();
            return bubble;
        }

        // Mensaje de bienvenida de Temis: solo cuando la conversación está vacía,
        // es puramente visual (no forma parte del historial que ve el modelo).
        function showWelcome() {
            if (!messages.childElementCount) {
                addBubble('assistant', config.welcomeText);
            }
        }

        function addSources(sources) {
            if (!sources || !sources.length) {
                return;
            }
            var wrap = document.createElement('div');
            wrap.className = 'ai-chat-sources';

            var label = document.createElement('div');
            label.textContent = config.sourcesLabel;
            wrap.appendChild(label);

            var list = document.createElement('ul');
            sources.forEach(function (source) {
                var item = document.createElement('li');
                var link = document.createElement('a');
                link.href = source.url;
                link.textContent = source.book + ' — ' + source.page;
                item.appendChild(link);
                list.appendChild(item);
            });
            wrap.appendChild(list);
            messages.appendChild(wrap);
            scrollToEnd();
        }

        function showTyping() {
            var typing = document.createElement('div');
            typing.className = 'ai-chat-typing';
            typing.innerHTML = '<i></i><i></i><i></i>';
            messages.appendChild(typing);
            scrollToEnd();
            return typing;
        }

        // La conversación vive en la sesión del usuario: se recupera al cargar la página.
        fetch(config.historyUrl + '?shelf_id=' + encodeURIComponent(config.shelfId), {
            headers: { 'Accept': 'application/json' },
        })
            .then(function (response) { return response.ok ? response.json() : { messages: [] }; })
            .then(function (data) {
                (data.messages || []).forEach(function (entry) {
                    addBubble(entry.role === 'assistant' ? 'assistant' : 'user', entry.text);
                    if (entry.role === 'assistant') {
                        addSources(entry.sources);
                    }
                });
            })
            .catch(function () { /* sin historial, no es fatal */ })
            .finally(showWelcome);

        clearButton.addEventListener('click', function () {
            fetch(config.clearUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ shelf_id: config.shelfId }),
            }).finally(function () {
                messages.textContent = '';
                showWelcome();
                input.focus();
            });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var message = input.value.trim();
            if (!message || input.disabled) {
                return;
            }

            addBubble('user', message);
            input.value = '';
            input.disabled = true;
            sendButton.disabled = true;
            var typing = showTyping();

            fetch(config.askUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message: message,
                    shelf_id: config.shelfId,
                }),
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('ai_chat_request_failed');
                    }
                    return response.json();
                })
                .then(function (data) {
                    addBubble('assistant', data.answer);
                    addSources(data.sources);
                })
                .catch(function () {
                    addBubble('assistant', config.errorMessage);
                })
                .finally(function () {
                    typing.remove();
                    input.disabled = false;
                    sendButton.disabled = false;
                    input.focus();
                });
        });
    })();
</script>
