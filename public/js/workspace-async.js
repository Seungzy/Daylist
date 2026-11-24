// Enhance existing Laravel responses; ordinary links and forms still work without JavaScript.
(() => {
    if (!window.fetch || !window.AbortController) return;
    const status = document.querySelector('[data-live-status]');
    let pending = null;
    let writing = false;
    let noticeTimer;
    const announce = message => {
        clearTimeout(noticeTimer);
        if (status) status.textContent = message;
        if (message === 'Updated.') noticeTimer = setTimeout(() => { if (status) status.textContent = ''; }, 3000);
    };
    const regionFor = element => element.closest('[data-live-region]');
    const samePage = url => url.origin === location.origin && url.pathname === location.pathname;

    async function update(url, region, {method = 'GET', body, historyMode = 'push'} = {}) {
        if (writing) return;
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        writing = method !== 'GET';
        const focus = document.activeElement;
        const focusKey = focus?.id || focus?.name;
        const focusText = focus?.tagName === 'A' ? focus.textContent.trim() : null;
        const selection = focus?.selectionStart;
        const scroll = {x: window.scrollX, y: window.scrollY};
        const openDetails = [...region.querySelectorAll('details')].map(item => item.open);
        const buttons = [...region.querySelectorAll('button, input[type="submit"]')].filter(button => !button.disabled);
        if (writing) buttons.forEach(button => { button.disabled = true; });
        region.setAttribute('aria-busy', 'true');
        region.classList.add('is-updating');
        announce('Updating…');
        try {
            const response = await fetch(url, {
                method, body, signal: controller.signal, credentials: 'same-origin', cache: 'no-store',
                headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'},
            });
            if (!response.ok) throw new Error('Request failed');
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (controller.signal.aborted) return;
            const destination = new URL(response.url);
            const replacement = html.getElementById(region.id);
            if (destination.origin !== location.origin || !replacement?.hasAttribute('data-live-region')) {
                location.assign(response.url);
                return;
            }
            // A script-dependent page should load normally rather than lose its setup.
            if (replacement.querySelector('script')) {
                location.assign(response.url);
                return;
            }
            region.replaceWith(replacement);
            replacement.querySelectorAll('details').forEach((item, index) => { item.open = openDetails[index] || false; });
            const candidates = [...replacement.querySelectorAll('input,select,textarea,button,a')];
            const nextFocus = candidates.find(item => focusKey && (item.id === focusKey || item.name === focusKey))
                || candidates.find(item => focusText && item.tagName === 'A' && item.textContent.trim() === focusText);
            if (nextFocus) {
                nextFocus.focus({preventScroll: true});
                if (typeof selection === 'number' && ['text', 'search'].includes(nextFocus.type)) nextFocus.setSelectionRange(selection, selection);
            } else if (focus && region.contains(focus)) {
                replacement.setAttribute('tabindex', '-1');
                replacement.focus({preventScroll: true});
            }
            document.title = html.title;
            const targetUrl = destination.pathname + destination.search + destination.hash;
            if (historyMode === 'push' && targetUrl !== location.pathname + location.search + location.hash) history.pushState({}, '', targetUrl);
            else if (historyMode === 'replace') history.replaceState({}, '', targetUrl);
            window.scrollTo(scroll.x, scroll.y);
            announce(replacement.querySelector('.alert.error') ? 'Please check the highlighted errors.' : 'Updated.');
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (method === 'GET') location.assign(url);
            else announce('Could not confirm the update. Refresh to check the result before trying again.');
        } finally {
            buttons.forEach(button => { button.disabled = false; });
            region.removeAttribute('aria-busy');
            region.classList.remove('is-updating');
            if (pending === controller) { pending = null; writing = false; }
        }
    }

    document.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;
        const region = regionFor(link);
        const url = new URL(link.href);
        if (!region || !samePage(url) || url.hash) return;
        event.preventDefault();
        update(url.href, region);
    });
    document.addEventListener('submit', event => {
        const form = event.target;
        const region = regionFor(form);
        if (!region || event.defaultPrevented || form.target) return;
        const url = new URL(form.action || location.href);
        if (url.origin !== location.origin) return;
        event.preventDefault();
        const data = new FormData(form);
        if (event.submitter?.name) data.append(event.submitter.name, event.submitter.value);
        const method = form.method.toUpperCase();
        if (method === 'GET') {
            url.search = new URLSearchParams(data).toString();
            url.hash = '';
            update(url.href, region);
        } else update(url.href, region, {method, body: data, historyMode: 'replace'});
    });
    document.addEventListener('change', event => {
        const select = event.target.closest('.filters select');
        if (select && regionFor(select)) select.form.requestSubmit();
    });
    window.addEventListener('popstate', () => {
        const region = document.querySelector('[data-live-region]');
        if (region) update(location.href, region, {historyMode: 'none'});
        else location.reload();
    });
})();
