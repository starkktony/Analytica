<div class="sidebar" id="mainSidebar">

    {{-- Scrollable content area --}}
    <div class="sidebar-scrollable">

        {{-- Logo - full --}}
        <div class="sidebar-logo-full" style="text-align:center;padding:12px 0;">
            <div class="sidebar-brand">
                <span class="brand-text">Siel<span class="brand-metrics">Metrics</span><span class="brand-plus">+</span></span>
            </div>
        </div>
        {{-- Logo - mini (collapsed) --}}
        <div class="sidebar-logo-mini" style="text-align:center;padding:12px 0;display:none;">
            <span class="brand-plus" style="font-size:1.8rem;font-weight:900;color:#4ade80;">+</span>
        </div>

        {{-- Search - full --}}
        <div class="sidebar-search-full" style="margin:0 12px 12px;display:flex;align-items:center;gap:0;">
            <span style="background:#2a2a2a;border:1px solid #444;border-right:none;padding:8px 12px;border-radius:20px 0 0 20px;">
                <i class="bi bi-search" style="color:#cfcfcf;font-size:0.9rem;"></i>
            </span>
            <input type="text" placeholder="Search here"
                style="flex:1;background:#2a2a2a;border:1px solid #444;border-left:none;padding:8px 12px;border-radius:0 20px 20px 0;color:#cfcfcf;font-size:0.85rem;outline:none;">
        </div>
        {{-- Search - mini (collapsed) --}}
        <div class="sidebar-search-mini" style="text-align:center;margin-bottom:12px;display:none;">
            <i class="bi bi-search" style="color:#cfcfcf;font-size:1.1rem;"></i>
        </div>

        {{-- Dashboard --}}
        <a href="{{ url('/dashboard') }}"
           class="sidebar-menu-item {{ request()->is('dashboard') ? 'active' : '' }}"
           data-label="Dashboard">
            <span class="menu-icon"><i class="bi bi-grid-3x3"></i></span>
            <span class="menu-label">Dashboard</span>
        </a>

        {{-- STUDENT --}}
        <div class="sidebar-item">
            <div class="sidebar-parent {{ request()->is('student*') || request()->is('enrollment') || request()->is('graduation') || request()->is('scholarship') || request()->routeIs('graduates.index') ? 'active' : '' }}"
                 data-label="Student">
                <a href="{{ url('/student') }}" class="sidebar-link" style="flex:1;">
                    <span class="menu-icon"><i class="bi bi-people"></i></span>
                    <span class="menu-label">Student</span>
                </a>
                <span class="sidebar-chevron" data-target="studentMenu"
                    style="cursor:pointer;padding:0 16px 0 8px;">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            <div class="sidebar-submenu {{ request()->is('student*') || request()->is('enrollment') || request()->is('graduation') || request()->is('scholarship') || request()->routeIs('graduates.index') ? 'open' : '' }}"
                 id="studentMenu">
                <a href="{{ url('/enrollment') }}" class="submenu-link {{ request()->is('enrollment') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i><span class="menu-label">Enrollment</span>
                </a>
                <a href="{{ route('graduates.index') }}"
                   class="submenu-link {{ request()->routeIs('graduates.index') ? 'active' : '' }}">
                    <i class="bi bi-award-fill"></i><span class="menu-label">Graduates</span>
                </a>
                <a href="{{ url('/scholarship') }}" class="submenu-link {{ request()->is('scholarship') ? 'active' : '' }}">
                    <i class="bi bi-award"></i><span class="menu-label">Scholarship</span>
                </a>
            </div>
        </div>

        {{-- FACULTY --}}
        <div class="sidebar-item">
            <div class="sidebar-parent {{ request()->routeIs('stzfaculty.*') || request()->routeIs('suc-faculty.*') ? 'active' : '' }}"
                 data-label="Faculty">
                <a href="{{ route('stzfaculty.overview') }}" class="sidebar-link" style="flex:1;">
                    <span class="menu-icon"><i class="bi bi-person-badge"></i></span>
                    <span class="menu-label">Faculty</span>
                </a>
                <span class="sidebar-chevron" data-target="facultyMenu"
                    style="cursor:pointer;padding:0 16px 0 8px;">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            <div class="sidebar-submenu {{ request()->routeIs('stzfaculty.*') || request()->routeIs('suc-faculty.*') ? 'open' : '' }}"
                 id="facultyMenu">
                <a href="{{ route('stzfaculty.overview') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.overview') ? 'active' : '' }}">
                    <i class="bi bi-person-vcard"></i><span class="menu-label">Faculty Profile</span>
                </a>
                <a href="{{ route('stzfaculty.teaching-load') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.teaching-load') ? 'active' : '' }}">
                    <i class="bi bi-book"></i><span class="menu-label">Teaching Load</span>
                </a>
                <a href="{{ route('stzfaculty.research-performance') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.research-performance') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data"></i><span class="menu-label">Research</span>
                </a>
                <a href="{{ route('stzfaculty.approval') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.approval') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle"></i><span class="menu-label">Faculty Approval</span>
                </a>
                <a href="{{ route('suc-faculty.index') }}"
                   class="submenu-link {{ request()->routeIs('suc-faculty.index') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i><span class="menu-label">SUC Faculty</span>
                </a>
            </div>
        </div>

        {{-- FINANCIAL REPORTS --}}
        <div class="sidebar-item">
            <div class="sidebar-parent {{ request()->routeIs('normative-funding.*') ? 'active' : '' }}"
                 data-label="Financial Reports">
                <a href="{{ route('normative-funding.index') }}" class="sidebar-link" style="flex:1;">
                    <span class="menu-icon"><i class="bi bi-cash-stack"></i></span>
                    <span class="menu-label">Financial Reports</span>
                </a>
                <span class="sidebar-chevron" data-target="financialMenu"
                    style="cursor:pointer;padding:0 16px 0 8px;">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            <div class="sidebar-submenu {{ request()->routeIs('normative-funding.*') ? 'open' : '' }}"
                 id="financialMenu">
                <a href="{{ route('normative-funding.index') }}"
                   class="submenu-link {{ request()->routeIs('normative-funding.index') ? 'active' : '' }}">
                    <i class="bi bi-pie-chart"></i><span class="menu-label">Normative Funding</span>
                </a>
            </div>
        </div>

        {{-- RADIIS --}}
        <div class="sidebar-item">
            <div class="sidebar-parent {{ request()->routeIs('radiis.*') ? 'active' : '' }}"
                 data-label="RADIIS">
                <a href="{{ route('radiis.programs') }}" class="sidebar-link" style="flex:1;">
                    <span class="menu-icon"><i class="bi bi-diagram-3"></i></span>
                    <span class="menu-label">RADIIS</span>
                </a>
                <span class="sidebar-chevron" data-target="radiisMenu"
                    style="cursor:pointer;padding:0 16px 0 8px;">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            <div class="sidebar-submenu {{ request()->routeIs('radiis.*') ? 'open' : '' }}"
                 id="radiisMenu">
                <a href="{{ route('radiis.programs') }}"
                   class="submenu-link {{ request()->routeIs('radiis.programs') ? 'active' : '' }}">
                    <i class="bi bi-collection"></i><span class="menu-label">Programs</span>
                </a>
                <a href="{{ route('radiis.projects') }}"
                   class="submenu-link {{ request()->routeIs('radiis.projects') ? 'active' : '' }}">
                    <i class="bi bi-kanban"></i><span class="menu-label">Projects</span>
                </a>
                <a href="{{ route('radiis.studies') }}"
                   class="submenu-link {{ request()->routeIs('radiis.studies') ? 'active' : '' }}">
                    <i class="bi bi-journal-bookmark"></i><span class="menu-label">Studies</span>
                </a>
                <a href="{{ route('radiis.publications') }}"
                   class="submenu-link {{ request()->routeIs('radiis.publications') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i><span class="menu-label">Publications</span>
                </a>
                <a href="{{ route('radiis.presentations') }}"
                   class="submenu-link {{ request()->routeIs('radiis.presentations') ? 'active' : '' }}">
                    <i class="bi bi-easel"></i><span class="menu-label">Presentations</span>
                </a>
                <a href="{{ route('radiis.iprights') }}"
                   class="submenu-link {{ request()->routeIs('radiis.iprights') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i><span class="menu-label">IP Rights</span>
                </a>
                <a href="{{ route('radiis.awards') }}"
                   class="submenu-link {{ request()->routeIs('radiis.awards') ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i><span class="menu-label">Awards</span>
                </a>
                <a href="{{ route('radiis.linkages') }}"
                   class="submenu-link {{ request()->routeIs('radiis.linkages') ? 'active' : '' }}">
                    <i class="bi bi-link-45deg"></i><span class="menu-label">Linkages</span>
                </a>
                <a href="{{ route('radiis.researchers') }}"
                   class="submenu-link {{ request()->routeIs('radiis.researchers') ? 'active' : '' }}">
                    <i class="bi bi-person-workspace"></i><span class="menu-label">Researchers</span>
                </a>
                <a href="{{ route('radiis.agencies') }}"
                   class="submenu-link {{ request()->routeIs('radiis.agencies') ? 'active' : '' }}">
                    <i class="bi bi-building"></i><span class="menu-label">Funding Agency</span>
                </a>
            </div>
        </div>

        {{-- EIS --}}
        <div class="sidebar-item">
            <div class="sidebar-parent {{ request()->routeIs('eis.*') ? 'active' : '' }}"
                 data-label="EIS">
                <a href="{{ route('eis.igp') }}" class="sidebar-link" style="flex:1;">
                    <span class="menu-icon"><i class="bi bi-bar-chart-steps"></i></span>
                    <span class="menu-label">EIS</span>
                </a>
                <span class="sidebar-chevron" data-target="eisMenu"
                    style="cursor:pointer;padding:0 16px 0 8px;">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            <div class="sidebar-submenu {{ request()->routeIs('eis.*') ? 'open' : '' }}"
                 id="eisMenu">
                <a href="{{ route('eis.igp') }}"
                   class="submenu-link {{ request()->routeIs('eis.igp') ? 'active' : '' }}">
                    <i class="bi bi-briefcase"></i><span class="menu-label">IGPs</span>
                </a>
                <a href="{{ route('eis.fund') }}"
                   class="submenu-link {{ request()->routeIs('eis.fund') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i><span class="menu-label">Funds</span>
                </a>
                <a href="{{ route('eis.facility') }}"
                   class="submenu-link {{ request()->routeIs('eis.facility') ? 'active' : '' }}">
                    <i class="bi bi-buildings"></i><span class="menu-label">Facilities</span>
                </a>
                <a href="{{ route('eis.bid') }}"
                   class="submenu-link {{ request()->routeIs('eis.bid') ? 'active' : '' }}">
                    <i class="bi bi-hammer"></i><span class="menu-label">Bids & Awards</span>
                </a>
            </div>
        </div>


        {{-- RESEARCH --}}
        <div class="sidebar-item">
            <div class="sidebar-parent" data-label="Research">
                <a href="#" class="sidebar-link" style="flex:1;">
                    <span class="menu-icon"><i class="bi bi-search-heart"></i></span>
                    <span class="menu-label">Research</span>
                </a>
                <span class="sidebar-chevron" data-target="researchMenu"
                    style="cursor:pointer;padding:0 16px 0 8px;">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            <div class="sidebar-submenu" id="researchMenu">
                <a href="#" class="submenu-link"><i class="bi bi-journal"></i><span class="menu-label">Publications</span></a>
                <a href="#" class="submenu-link"><i class="bi bi-trophy"></i><span class="menu-label">Awards</span></a>
                <a href="#" class="submenu-link"><i class="bi bi-graph-up"></i><span class="menu-label">Analytics</span></a>
            </div>
        </div>

        {{-- ALUMNI --}}
        <div class="sidebar-item">
            <div class="sidebar-parent {{ request()->is('alumni*') ? 'active' : '' }}"
                 data-label="Alumni">
                <a href="#" class="sidebar-link" style="flex:1;">
                    <span class="menu-icon"><i class="bi bi-mortarboard"></i></span>
                    <span class="menu-label">Alumni</span>
                </a>
                <span class="sidebar-chevron" data-target="alumniMenu"
                    style="cursor:pointer;padding:0 16px 0 8px;">
                    <i class="bi bi-chevron-down"></i>
                </span>
            </div>
            <div class="sidebar-submenu" id="alumniMenu">
                <a href="#" class="submenu-link"><i class="bi bi-list-ul"></i><span class="menu-label">Alumni List</span></a>
                <a href="#" class="submenu-link"><i class="bi bi-person-plus"></i><span class="menu-label">Add Alumni</span></a>
            </div>
        </div>

        <hr style="border-color:#555;margin:8px 12px;">

        <a href="#" class="sidebar-menu-item" data-label="About Analytica">
            <span class="menu-icon"><i class="bi bi-info-circle"></i></span>
            <span class="menu-label">About Analytica</span>
        </a>

    </div>{{-- end sidebar-scrollable --}}

    {{-- Profile Section --}}
    <div class="sidebar-profile">
        <div class="profile-info">
            <div class="profile-icon"><i class="bi bi-person-circle"></i></div>
            <div class="profile-text">
                <div class="profile-name">{{ Auth::user()->name }}</div>
                <div class="profile-role">{{ Auth::user()->role }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="logout-btn" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>

    {{-- Toggle Button --}}
    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>

</div>{{-- end .sidebar --}}

<style>
    /* ============================================================
       SIDEBAR BASE
    ============================================================ */
    .sidebar {
        position: fixed;
        top: 0; left: 0;
        width: 250px;
        height: 100vh;
        background-color: #1f1f1f;
        display: flex;
        flex-direction: column;
        overflow: visible;
        z-index: 1000;
        transition: width 0.3s ease;
        font-family: 'Bricolage Grotesque', 'Segoe UI', sans-serif;
    }
    .sidebar.collapsed { width: 68px; }

    /* ============================================================
       BRAND / LOGO
    ============================================================ */
    .sidebar-brand { padding: 4px 0; }
    .brand-text {
        font-family: 'Segoe UI', sans-serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.5px;
    }
    .brand-metrics { color: #4ade80; }
    .brand-plus { color: #4ade80; font-size: 1.8rem; font-weight: 900; }

    /* ============================================================
       SCROLLABLE AREA
    ============================================================ */
    .sidebar-scrollable {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        background-color: #1f1f1f;
        padding-bottom: 20px;
    }
    .sidebar-scrollable::-webkit-scrollbar { width: 4px; }
    .sidebar-scrollable::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scrollable::-webkit-scrollbar-thumb { background: #444; border-radius: 2px; }
    .sidebar-scrollable::-webkit-scrollbar-thumb:hover { background: #0f8f3a; }

    /* ============================================================
       COLLAPSED — HIDE / SHOW
    ============================================================ */
    .sidebar.collapsed .menu-label,
    .sidebar.collapsed .sidebar-search-full,
    .sidebar.collapsed .sidebar-logo-full,
    .sidebar.collapsed .sidebar-chevron,
    .sidebar.collapsed .profile-text {
        display: none !important;
    }
    .sidebar.collapsed .sidebar-logo-mini,
    .sidebar.collapsed .sidebar-search-mini {
        display: block !important;
    }
    .sidebar.collapsed .sidebar-submenu {
        display: none !important;
    }

    /* ============================================================
       COLLAPSED — CENTER ICONS
    ============================================================ */
    .sidebar.collapsed .sidebar-menu-item,
    .sidebar.collapsed .sidebar-link,
    .sidebar.collapsed .sidebar-parent {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }
    .sidebar.collapsed .sidebar-parent .sidebar-link {
        padding: 12px 0;
        justify-content: center;
        width: 68px;
    }
    .sidebar.collapsed .menu-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 68px;
        font-size: 1.1rem;
    }

    /* ============================================================
       COLLAPSED — TOOLTIPS
    ============================================================ */
    .sidebar.collapsed .sidebar-menu-item,
    .sidebar.collapsed .sidebar-parent { position: relative; }

    .sidebar.collapsed .sidebar-menu-item[data-label]:hover::after,
    .sidebar.collapsed .sidebar-parent[data-label]:hover::after {
        content: attr(data-label);
        position: absolute;
        left: 72px;
        top: 50%;
        transform: translateY(-50%);
        background: #0f8f3a;
        color: white;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        white-space: nowrap;
        z-index: 9999;
        pointer-events: none;
    }

    /* ============================================================
       TOGGLE BUTTON
    ============================================================ */
    .sidebar-toggle-btn {
        position: fixed;
        top: 50%;
        left: 230px;
        transform: translateY(-50%);
        width: 36px; height: 36px;
        background: #0f8f3a;
        border: none;
        border-radius: 50%;
        color: white;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 1100;
        box-shadow: 0 2px 8px rgba(0,0,0,0.5);
        transition: background 0.2s ease, left 0.3s ease;
    }
    .sidebar-toggle-btn:hover { background: #09722d; }
    #toggleIcon { transition: transform 0.3s ease; }
    .sidebar.collapsed #toggleIcon { transform: rotate(180deg); }
    body.sidebar-collapsed .sidebar-toggle-btn { left: 48px; }

    /* ============================================================
       PROFILE — PINNED BOTTOM
    ============================================================ */
    .sidebar-profile {
        flex-shrink: 0;
        width: 100%;
        padding: 12px 16px;
        background-color: #009539;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: padding 0.3s ease;
    }
    .sidebar.collapsed .sidebar-profile {
        padding: 12px 0;
        justify-content: center;
    }
    .sidebar.collapsed .sidebar-profile .profile-info { display: none; }
    .sidebar.collapsed .sidebar-profile form { margin: 0 auto; }

    .profile-info { display: flex; align-items: center; gap: 10px; }
    .profile-icon { font-size: 30px; line-height: 1; }
    .profile-text { display: flex; flex-direction: column; gap: 1px; }
    .profile-name { font-weight: 700; font-size: 14px; line-height: 1.2; }
    .profile-role { font-size: 11px; opacity: 0.85; }

    .logout-btn {
        background: transparent;
        border: none;
        color: white;
        font-size: 22px;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: opacity 0.2s;
    }
    .logout-btn:hover { opacity: 0.75; }

    /* ============================================================
       TOP-LEVEL MENU ITEMS
    ============================================================ */
    .sidebar-menu-item {
        color: #cfcfcf;
        text-decoration: none;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.92rem;
        font-weight: 500;
        transition: background-color 0.2s ease, color 0.2s ease;
    }
    .sidebar-menu-item:hover { background: #0f8f3a; color: white; }
    .sidebar-menu-item.active { background: #009539; color: white; }

    /* ============================================================
       PARENT ROW
    ============================================================ */
    .sidebar-parent {
        display: flex;
        align-items: center;
        transition: background-color 0.2s ease;
        cursor: default;
    }
    .sidebar-parent:hover { background: #0f8f3a; }
    .sidebar-parent:hover .sidebar-link,
    .sidebar-parent:hover .sidebar-chevron i { color: white; }
    .sidebar-parent.active { background: #009539; }
    .sidebar-parent.active .sidebar-link,
    .sidebar-parent.active .sidebar-chevron i { color: white; }

    /* ============================================================
       SIDEBAR LINK
    ============================================================ */
    .sidebar-link {
        color: #cfcfcf;
        text-decoration: none;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.92rem;
        font-weight: 500;
        background: transparent;
        transition: color 0.2s ease;
    }
    .sidebar-link:hover { color: white; }

    .sidebar-chevron i {
        color: #cfcfcf;
        font-size: 0.75rem;
        transition: color 0.2s ease, transform 0.3s ease;
        display: block;
    }
    .sidebar-chevron.rotated i { transform: rotate(180deg); }

    /* ============================================================
       SUBMENU
    ============================================================ */
    .sidebar-submenu {
        background: #2a2a2a;
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.3s ease;
    }
    .sidebar-submenu.open { max-height: 600px; }

    .submenu-link {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #cfcfcf;
        text-decoration: none;
        padding: 10px 20px 10px 44px;
        font-size: 0.87rem;
        font-weight: 400;
        border-left: 3px solid transparent;
        background: transparent;
        transition: color 0.2s ease, border-left-color 0.2s ease, background-color 0.2s ease;
    }
    .submenu-link i { font-size: 0.85rem; opacity: 0.75; }
    .submenu-link:hover { color: white; border-left-color: #009539; background: #333; }
    .submenu-link:hover i { opacity: 1; }
    .submenu-link.active { color: white; border-left-color: #009539; background: #333; }
    .submenu-link.active i { opacity: 1; }

    /* ============================================================
       MENU ICON
    ============================================================ */
    .menu-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        flex-shrink: 0;
    }
    .menu-icon i { font-size: 1rem; }

    /* ============================================================
       MAIN CONTENT SHIFT
    ============================================================ */
    .content {
        margin-left: 250px;
        transition: margin-left 0.3s ease;
    }
    body.sidebar-collapsed .content {
        margin-left: 68px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar     = document.getElementById('mainSidebar');
        const toggleBtn   = document.getElementById('sidebarToggle');
        const STORAGE_KEY = 'clsu_analytica_sidebar_collapsed';

        // ── Collapse/Expand sidebar ───────────────────────────────
        function applyState(isCollapsed) {
            sidebar.classList.toggle('collapsed', isCollapsed);
            document.body.classList.toggle('sidebar-collapsed', isCollapsed);
            toggleBtn.style.left = isCollapsed ? '48px' : '230px';
            localStorage.setItem(STORAGE_KEY, isCollapsed);
        }

        const savedState = localStorage.getItem(STORAGE_KEY) === 'true';
        applyState(savedState);

        toggleBtn.addEventListener('click', function () {
            applyState(!sidebar.classList.contains('collapsed'));
        });

        // ── Submenu toggles ───────────────────────────────────────
        document.querySelectorAll('.sidebar-chevron').forEach(function (chevron) {
            chevron.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                if (!targetId) return;
                const submenu = document.getElementById(targetId);
                if (!submenu) return;

                const isOpen = submenu.classList.contains('open');

                // Close all other submenus
                document.querySelectorAll('.sidebar-submenu.open').forEach(function (el) {
                    if (el.id !== targetId) {
                        el.classList.remove('open');
                        const chevronEl = document.querySelector(`.sidebar-chevron[data-target="${el.id}"]`);
                        if (chevronEl) chevronEl.classList.remove('rotated');
                    }
                });

                submenu.classList.toggle('open', !isOpen);
                this.classList.toggle('rotated', !isOpen);
            });
        });

        // ── Set initial chevron rotation for open submenus ────────
        document.querySelectorAll('.sidebar-submenu.open').forEach(function (submenu) {
            const chevron = document.querySelector(`.sidebar-chevron[data-target="${submenu.id}"]`);
            if (chevron) chevron.classList.add('rotated');
        });
    });
</script>