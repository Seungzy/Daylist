document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));

const sidebar = document.getElementById('workspace-sidebar');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const mobileSidebar = window.matchMedia('(max-width: 720px)');
function closeSidebar() {
    document.body.classList.remove('sidebar-open');
    sidebarToggle?.setAttribute('aria-expanded', 'false');
    sidebarToggle?.setAttribute('aria-label', 'Open navigation');
    if (sidebar && mobileSidebar.matches) sidebar.inert = true;
}
if (sidebar && sidebarToggle) {
    sidebar.inert = mobileSidebar.matches;
    sidebarToggle.addEventListener('click', () => {
        const open = !document.body.classList.contains('sidebar-open');
        document.body.classList.toggle('sidebar-open', open);
        sidebar.inert = false;
        sidebarToggle.setAttribute('aria-expanded', String(open));
        sidebarToggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
        if (!open) closeSidebar();
        if (open && mobileSidebar.matches) sidebar.querySelector('a')?.focus();
    });
    document.querySelectorAll('[data-sidebar-close]').forEach(button => button.addEventListener('click', () => { closeSidebar(); sidebarToggle.focus(); }));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) { closeSidebar(); sidebarToggle.focus(); }
        if (event.key === 'Tab' && mobileSidebar.matches && document.body.classList.contains('sidebar-open')) {
            const targets = [...sidebar.querySelectorAll('a,button')].filter(el => el.getClientRects().length);
            if (event.shiftKey && document.activeElement === targets[0]) { event.preventDefault(); targets.at(-1).focus(); }
            else if (!event.shiftKey && document.activeElement === targets.at(-1)) { event.preventDefault(); targets[0].focus(); }
        }
    });
    mobileSidebar.addEventListener('change', () => { closeSidebar(); sidebar.inert = mobileSidebar.matches; });
}
