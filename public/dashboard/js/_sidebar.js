(function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    let tooltipInstances = [];
    let flyoutEl = null; // active flyout panel element

    const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;
    const isCollapsed = () => sidebar.classList.contains('collapsed');

    // --- Tooltips (collapsed only) ---
    function enableTooltips() {
        disableTooltips();
        document.querySelectorAll('.side_nav-link[title]').forEach(el => {
            tooltipInstances.push(new bootstrap.Tooltip(el, { container: 'body', trigger: 'hover',placement: 'right' }));
        });
    }
    function disableTooltips() {
        tooltipInstances.forEach(t => t.dispose());
        tooltipInstances = [];
    }

    // --- Flyout submenu for collapsed state ---
    function showFlyoutFor(linkEl) {
        hideFlyout();
        const item = linkEl.closest('.has-sub');
        if (!item) return;
        const subId = 'submenu-' + (linkEl.dataset.sub || '');
        const submenu = item.querySelector('.side_submenu');
        if (!submenu) return;

        // Clone submenu for flyout
        flyoutEl = document.createElement('div');
        flyoutEl.className = 'side_flyout';
        flyoutEl.innerHTML = submenu.outerHTML; // retain .side_submenu styles
        document.body.appendChild(flyoutEl);

        // Position near the hovered item
        const rect = linkEl.getBoundingClientRect();
        const top = Math.max(8, rect.top);
        flyoutEl.style.top = top + 'px';

        // Close when mouse leaves both the link and the flyout
        const closeIfOutside = (ev) => {
            if (!flyoutEl) return;
            if (!flyoutEl.contains(ev.relatedTarget) && ev.currentTarget === flyoutEl) {
                hideFlyout();
            }
        };
        flyoutEl.addEventListener('mouseleave', closeIfOutside);
    }
    function hideFlyout() {
        if (flyoutEl) {
            flyoutEl.remove();
            flyoutEl = null;
        }
    }

    // --- Toggle logic ---
    function setIcon(collapsedOrMobileOpen) {
        // Show X when sidebar is collapsed on desktop OR when mobile panel is open
        const i = toggleBtn.querySelector('i');
        i.className = collapsedOrMobileOpen ? 'bi bi-x' : 'bi bi-list';
    }

    function collapseDesktop(on) {
        sidebar.classList.toggle('collapsed', on);
        mainContent.classList.toggle('expanded', on);
        if (on) {
            enableTooltips();
        } else {
            disableTooltips();
            hideFlyout();
        }
        setIcon(on);
    }

    function openMobile(on) {
        sidebar.classList.toggle('show', on);
        overlay.classList.toggle('show', on);
        setIcon(on);
    }

    function syncMode() {
        // When switching breakpoints, normalize state
        if (isMobile()) {
            // Mobile never uses collapsed width; content spans full width
            collapseDesktop(false);
            mainContent.classList.remove('expanded');
            setIcon(sidebar.classList.contains('show'));
        } else {
            openMobile(false);
            setIcon(isCollapsed());
        }
    }

    // Sidebar toggle click
    toggleBtn.addEventListener('click', () => {
        toggleBtn.classList.add('switching');
        setTimeout(() => toggleBtn.classList.remove('switching'), 220);

        if (isMobile()) {
            openMobile(!sidebar.classList.contains('show'));
        } else {
            collapseDesktop(!isCollapsed());
        }
    });

    // Overlay click closes on mobile
    overlay.addEventListener('click', () => openMobile(false));

    // Submenu CLICK behavior in expanded/desktop mode
    document.querySelectorAll('.has-sub > .side_nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            // Only toggle inline submenu when NOT collapsed and NOT mobile
            if (!isMobile() && !isCollapsed()) {
                e.preventDefault();
                const item = link.closest('.has-sub');
                const submenu = item.querySelector('.side_submenu');
                const willShow = !submenu.classList.contains('show');
                document.querySelectorAll('.side_submenu.show').forEach(el => el.classList.remove('show'));
                submenu.classList.toggle('show', willShow);
                link.setAttribute('aria-expanded', String(willShow));
            }
        });

        // Hover behavior for collapsed desktop: show flyout
        link.addEventListener('mouseenter', (e) => {
            if (!isMobile() && isCollapsed()) {
                showFlyoutFor(link);
            }
        });
        link.addEventListener('mouseleave', (e) => {
            if (!isMobile() && isCollapsed()) {
                // If moving into flyout, keep it; otherwise hide
                const to = e.relatedTarget;
                if (!flyoutEl || (to && !flyoutEl.contains(to))) hideFlyout();
            }
        });
    });

    // Hide tooltips when expanded/open
    document.addEventListener('click', () => { if (!isCollapsed()) disableTooltips(); });

    window.addEventListener('resize', syncMode);
    syncMode();
})();
