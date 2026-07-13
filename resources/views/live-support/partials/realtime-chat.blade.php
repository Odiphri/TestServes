<script>
    (() => {
        const list = document.querySelector('[data-live-support-messages]');
        const form = document.querySelector('[data-live-support-form]');
        const emptyState = document.querySelector('[data-live-support-empty]');
        const channelName = @json($channelName);
        const channelType = @json($channelType ?? 'public');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!list || !channelName) {
            return;
        }

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const formatDate = (value) => {
            if (!value) {
                return 'Just now';
            }

            const date = new Date(value);
            return Number.isNaN(date.getTime())
                ? 'Just now'
                : date.toLocaleString(undefined, {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                });
        };

        const scrollMessagesToBottom = () => {
            const scroller = list.closest('[data-chat-scroll]') || list.parentElement;

            if (scroller) {
                scroller.scrollTop = scroller.scrollHeight;
                return;
            }

            list.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'end' });
        };

        const appendMessage = (payload) => {
            if (!payload?.id || list.querySelector(`[data-message-id="${payload.id}"]`)) {
                return;
            }

            const isAdmin = payload.sender_type === 'admin';
            const wrapper = document.createElement('div');
            wrapper.className = `p-3 rounded live-support-message ${isAdmin ? 'bg-primary text-white ms-md-5' : 'bg-light me-md-5'}`;
            wrapper.dataset.messageId = payload.id;
            wrapper.innerHTML = `
                <div class="fw-bold">${escapeHtml(payload.sender_name || payload.sender_type || 'Support')}</div>
                <div style="white-space: pre-wrap;">${escapeHtml(payload.message)}</div>
                <div class="small ${isAdmin ? 'text-white-50' : 'text-muted'} mt-2">${escapeHtml(formatDate(payload.created_at))}</div>
            `;

            emptyState?.remove();
            list.appendChild(wrapper);
            scrollMessagesToBottom();
        };

        scrollMessagesToBottom();

        const connect = () => {
            if (!window.Echo) {
                window.setTimeout(connect, 250);
                return;
            }

            const channel = channelType === 'private'
                ? window.Echo.private(channelName)
                : window.Echo.channel(channelName);

            channel.listen('.message.sent', appendMessage);
        };

        connect();

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const button = form.querySelector('button[type="submit"], button:not([type])');
            const textarea = form.querySelector('textarea[name="message"]');
            const body = new FormData(form);

            button?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                    },
                    body,
                });

                if (!response.ok) {
                    throw new Error('Message could not be sent.');
                }

                const data = await response.json();
                appendMessage(data.message);
                form.reset();
                textarea?.focus();
            } catch (error) {
                alert(error.message || 'Message could not be sent.');
            } finally {
                button?.removeAttribute('disabled');
            }
        });
    })();
</script>
