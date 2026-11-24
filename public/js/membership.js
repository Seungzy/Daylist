document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));

const sidebar = document.getElementById('workspace-sidebar');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const mobileSidebar = window.matchMedia('(max-width: 720px)');
const memberWorkspace = document.body.classList.contains('member-workspace');
let memberSidebarCollapsed = false;
if (memberWorkspace) {
    try { memberSidebarCollapsed = localStorage.getItem('daylist-member-sidebar-collapsed') === 'true'; } catch {}
}
function syncSidebar() {
    if (!sidebar || !sidebarToggle) return;
    const mobile = mobileSidebar.matches;
    const collapsed = !mobile && memberWorkspace && memberSidebarCollapsed;
    if (!mobile) document.body.classList.remove('sidebar-open');
    document.body.classList.toggle('sidebar-collapsed', collapsed);
    const open = mobile ? document.body.classList.contains('sidebar-open') : !collapsed;
    sidebar.inert = !open;
    sidebarToggle.setAttribute('aria-expanded', String(open));
    sidebarToggle.setAttribute('aria-label', mobile ? (open ? 'Close navigation' : 'Open navigation') : (collapsed ? 'Show sidebar' : 'Hide sidebar'));
    sidebarToggle.title = mobile ? (open ? 'Close Navigation' : 'Open Navigation') : (collapsed ? 'Show Sidebar' : 'Hide Sidebar');
}
function closeSidebar() {
    document.body.classList.remove('sidebar-open');
    syncSidebar();
}
if (sidebar && sidebarToggle) {
    syncSidebar();
    sidebarToggle.addEventListener('click', () => {
        if (!mobileSidebar.matches) {
            if (!memberWorkspace) return;
            memberSidebarCollapsed = !memberSidebarCollapsed;
            try { localStorage.setItem('daylist-member-sidebar-collapsed', String(memberSidebarCollapsed)); } catch {}
            syncSidebar();
            return;
        }
        const open = !document.body.classList.contains('sidebar-open');
        document.body.classList.toggle('sidebar-open', open);
        syncSidebar();
        if (open) sidebar.querySelector('a')?.focus();
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
    mobileSidebar.addEventListener('change', closeSidebar);
}
