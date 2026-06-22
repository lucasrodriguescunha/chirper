const dialog = document.getElementById('command-palette');
const triggers = document.querySelectorAll('.command-palette-trigger');
const input = document.getElementById('command-palette-input');
const results = document.getElementById('command-palette-results');

if (dialog && input && results) {
    const open = () => {
        if (typeof dialog.showModal === 'function' && !dialog.open) {
            dialog.showModal();
            setTimeout(() => input.focus(), 0);
        }
    };
    const close = () => {
        if (dialog.open) dialog.close();
    };

    triggers.forEach((trigger) => trigger.addEventListener('click', open));

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            dialog.open ? close() : open();
        }
    });

    dialog.addEventListener('close', () => {
        input.value = '';
        renderEmpty();
    });

    const renderEmpty = () => {
        results.innerHTML = '<p class="p-4 text-sm text-base-content/50 text-center">Type to search...</p>';
    };

    const renderNoResults = (q) => {
        results.innerHTML = `<p class="p-4 text-sm text-base-content/50 text-center">No results for "${escapeHtml(q)}"</p>`;
    };

    const renderLoading = () => {
        results.innerHTML = '<p class="p-4 text-sm text-base-content/50 text-center"><span class="loading loading-spinner loading-sm"></span></p>';
    };

    const escapeHtml = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const render = (data, q) => {
        const users = data.users || [];
        const chirps = data.chirps || [];

        if (users.length === 0 && chirps.length === 0) {
            renderNoResults(q);
            return;
        }

        let html = '';

        if (users.length) {
            html += '<div class="px-2 py-1 text-xs font-semibold text-base-content/50 uppercase">Users</div>';
            html += '<ul>';
            users.forEach((u) => {
                html += `
                    <li>
                        <a href="${u.url}" class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-base-200">
                            <div class="avatar">
                                <div class="w-8 rounded-full">
                                    <img src="${escapeHtml(u.avatar)}" alt="${escapeHtml(u.name)}" />
                                </div>
                            </div>
                            <span class="text-sm">${escapeHtml(u.name)}</span>
                        </a>
                    </li>`;
            });
            html += '</ul>';
        }

        if (chirps.length) {
            html += '<div class="px-2 py-1 mt-2 text-xs font-semibold text-base-content/50 uppercase">Chirps</div>';
            html += '<ul>';
            chirps.forEach((c) => {
                html += `
                    <li>
                        <a href="${c.url}" class="block rounded-lg px-3 py-2 hover:bg-base-200">
                            <div class="text-sm">${escapeHtml(c.message)}</div>
                            <div class="text-xs text-base-content/50 mt-0.5">by ${escapeHtml(c.author)}</div>
                        </a>
                    </li>`;
            });
            html += '</ul>';
        }

        html += `<div class="border-t border-base-300 mt-2 pt-2 px-2">
            <a href="/search?q=${encodeURIComponent(q)}" class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-base-200 text-sm">
                <span>See all results for "${escapeHtml(q)}"</span>
                <kbd class="kbd kbd-xs">↵</kbd>
            </a>
        </div>`;

        results.innerHTML = html;
    };

    let timer = null;
    let activeReq = 0;

    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(timer);

        if (q === '') {
            renderEmpty();
            return;
        }

        renderLoading();
        const reqId = ++activeReq;

        timer = setTimeout(async () => {
            try {
                const res = await fetch(`/search/suggest?q=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json' },
                });
                if (reqId !== activeReq) return;
                if (!res.ok) {
                    renderNoResults(q);
                    return;
                }
                const data = await res.json();
                render(data, q);
            } catch {
                if (reqId === activeReq) renderNoResults(q);
            }
        }, 180);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const q = input.value.trim();
            if (q !== '') {
                e.preventDefault();
                window.location.href = `/search?q=${encodeURIComponent(q)}`;
            }
        }
    });
}
