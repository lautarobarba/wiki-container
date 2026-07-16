{{--
    Floating AI chat widget, included from layouts/base.blade.php only when
    the current view belongs to a system (shelf), passed as $aiChatShelf.
    Backed by the route registered in themes/custom/functions.php.
--}}
<div id="ai-chat" class="print-hidden">
    <button type="button" id="ai-chat-fab" aria-label="{{ trans('entities.ai_chat_open') }}" aria-expanded="false">
        <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" aria-hidden="true">
            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM8 11a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 8 11zm4 0a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 12 11zm4 0a1.25 1.25 0 1 1 0-2.5A1.25 1.25 0 0 1 16 11z"/>
        </svg>
    </button>

    <div id="ai-chat-panel" role="dialog" aria-label="{{ trans('entities.ai_chat_title') }}" hidden>
        <div class="ai-chat-header">
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

        <div class="ai-chat-messages" aria-live="polite">
            <div class="ai-chat-hint">{{ trans('entities.ai_chat_subtitle') }}</div>
        </div>

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
        --ai-chat-surface-alt: #f1f3f5;
        --ai-chat-text: #444444;
        --ai-chat-border: rgba(0, 0, 0, 0.12);
        --ai-chat-user-bubble: color-mix(in srgb, var(--color-primary) 12%, #ffffff);
        /* El back-to-top se movió a la izquierda (ver layouts/base), el chat es dueño del rincón derecho */
        position: fixed;
        inset-block-end: 1rem;
        inset-inline-end: 1rem;
        z-index: 500;
    }
    html.dark-mode #ai-chat {
        --ai-chat-surface: #1e2226;
        --ai-chat-surface-alt: #2b3035;
        --ai-chat-text: #e3e3e3;
        --ai-chat-border: rgba(255, 255, 255, 0.15);
        --ai-chat-user-bubble: color-mix(in srgb, var(--color-primary) 30%, #1e2226);
    }

    #ai-chat-fab {
        width: 54px;
        height: 54px;
        border: none;
        border-radius: 50%;
        background: var(--color-primary);
        color: #ffffff;
        cursor: pointer;
        display: grid;
        place-items: center;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    #ai-chat-fab:hover {
        transform: scale(1.06);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.34);
    }

    #ai-chat-panel {
        display: flex;
        flex-direction: column;
        width: min(380px, calc(100vw - 2rem));
        height: min(560px, calc(100vh - 5rem));
        background: var(--ai-chat-surface);
        color: var(--ai-chat-text);
        border: 1px solid var(--ai-chat-border);
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.30);
        overflow: hidden;
    }
    #ai-chat-panel[hidden] { display: none; }

    .ai-chat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.65rem 0.9rem;
        background: var(--color-primary);
        color: #ffffff;
    }
    .ai-chat-header-text {
        display: flex;
        flex-direction: column;
        line-height: 1.25;
        min-width: 0;
    }
    .ai-chat-header-text span {
        font-size: 0.8rem;
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
        padding: 0.25rem;
        border-radius: 6px;
        display: grid;
        place-items: center;
    }
    #ai-chat-close:hover, #ai-chat-clear:hover { background: rgba(255, 255, 255, 0.18); }

    .ai-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 0.9rem;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }
    .ai-chat-hint {
        font-size: 0.85rem;
        opacity: 0.65;
        text-align: center;
        margin: auto 0.5rem;
    }
    .ai-chat-bubble {
        max-width: 88%;
        padding: 0.5rem 0.75rem;
        border-radius: 12px;
        font-size: 0.92rem;
        line-height: 1.45;
        white-space: pre-wrap;
        overflow-wrap: break-word;
    }
    .ai-chat-bubble.user {
        align-self: flex-end;
        background: var(--ai-chat-user-bubble);
        border-end-end-radius: 4px;
    }
    .ai-chat-bubble.assistant {
        align-self: flex-start;
        background: var(--ai-chat-surface-alt);
        border-end-start-radius: 4px;
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
        background: var(--ai-chat-surface-alt);
        border-radius: 12px;
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
        padding: 0.6rem;
        border-top: 1px solid var(--ai-chat-border);
    }
    .ai-chat-input {
        flex: 1;
        min-width: 0;
        border: 1px solid var(--ai-chat-border);
        border-radius: 8px;
        padding: 0.45rem 0.65rem;
        font-size: 0.92rem;
        background: var(--ai-chat-surface);
        color: var(--ai-chat-text);
    }
    .ai-chat-input:focus {
        outline: 2px solid var(--color-primary);
        outline-offset: -1px;
    }
    .ai-chat-send {
        flex-shrink: 0;
        width: 40px;
        border: none;
        border-radius: 8px;
        background: var(--color-primary);
        color: #ffffff;
        cursor: pointer;
        display: grid;
        place-items: center;
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
            hintText: @json(trans('entities.ai_chat_subtitle')),
        };

        var fab = document.getElementById('ai-chat-fab');
        var panel = document.getElementById('ai-chat-panel');
        var closeButton = document.getElementById('ai-chat-close');
        var clearButton = document.getElementById('ai-chat-clear');
        var form = root.querySelector('.ai-chat-form');
        var input = root.querySelector('.ai-chat-input');
        var sendButton = root.querySelector('.ai-chat-send');
        var messages = root.querySelector('.ai-chat-messages');
        var hint = root.querySelector('.ai-chat-hint');
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
            if (hint) {
                hint.remove();
                hint = null;
            }
            var bubble = document.createElement('div');
            bubble.className = 'ai-chat-bubble ' + role;
            bubble.textContent = text;
            messages.appendChild(bubble);
            scrollToEnd();
            return bubble;
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

        function showHint() {
            var el = document.createElement('div');
            el.className = 'ai-chat-hint';
            el.textContent = config.hintText;
            messages.appendChild(el);
            hint = el;
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
            .catch(function () { /* sin historial, no es fatal */ });

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
                showHint();
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
