<div class="sidebar" id="mainSidebar">

    {{-- Scrollable content area --}}
    <div class="sidebar-scrollable">

        {{-- Logo - full --}}
        <div class="text-center py-3 sidebar-logo-full">
            <img src="{{ asset('images/analytica-logo.png') }}"
                 alt="CLSU Analytica Logo"
                 class="img-fluid"
                 style="max-width: 150px;">
        </div>
        {{-- Logo - mini (collapsed) --}}
        <div class="sidebar-logo-mini d-none text-center py-3">
            <i class="bi bi-bar-chart-fill" style="font-size:1.6rem;color:#4ade80;"></i>
        </div>

        {{-- Search - full --}}
        <div class="input-group mx-3 mb-3 sidebar-search-full" style="width: 85%;">
            <span class="input-group-text rounded-start-pill">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control rounded-end-pill" placeholder="Search here">
        </div>
        {{-- Search - mini (collapsed) --}}
        <div class="sidebar-search-mini d-none text-center mb-3">
            <i class="bi bi-search" style="color:#cfcfcf;font-size:1.1rem;"></i>
        </div>

        {{-- Dashboard --}}
        <a href="{{ url('/dashboard') }}"
           class="sidebar-menu-item {{ request()->is('dashboard') ? 'active' : '' }}"
           data-label="Dashboard">
            <span class="menu-icon"><i class="bi bi-grid-3x3"></i></span>
            <span class="menu-label ms-2">Dashboard</span>
        </a>

        {{-- STUDENT --}}
        <div class="sidebar-item">
            <div class="sidebar-parent d-flex align-items-center justify-content-between
                {{ request()->is('student*') || request()->is('enrollment') || request()->is('graduation') || request()->is('scholarship') ? 'active' : '' }}"
                 data-label="Student">
                <a href="{{ url('/student') }}" class="sidebar-link flex-grow-1">
                    <span class="menu-icon"><i class="bi bi-people"></i></span>
                    <span class="menu-label ms-2">Student</span>
                </a>
                <i class="bi bi-chevron-down sidebar-chevron"
                   data-bs-toggle="collapse"
                   data-bs-target="#studentMenu"
                   style="cursor:pointer; padding: 0 16px 0 8px;"></i>
            </div>
            <div class="collapse {{ request()->is('student*') || request()->is('enrollment') || request()->is('graduation') || request()->is('scholarship') ? 'show' : '' }}"
                 id="studentMenu">
                <a href="{{ url('/enrollment') }}"  class="submenu-link {{ request()->is('enrollment')  ? 'active' : '' }}">
                    <i class="bi bi-journal-text me-2"></i><span class="menu-label">Enrollment</span>
                </a>
                <a href="{{ url('/graduation') }}"  class="submenu-link {{ request()->is('graduation')  ? 'active' : '' }}">
                    <i class="bi bi-mortarboard me-2"></i><span class="menu-label">Graduation</span>
                </a>
                <a href="{{ url('/scholarship') }}" class="submenu-link {{ request()->is('scholarship') ? 'active' : '' }}">
                    <i class="bi bi-award me-2"></i><span class="menu-label">Scholarship</span>
                </a>
            </div>
        </div>

        {{-- FACULTY --}}
        <div class="sidebar-item">
            <div class="sidebar-parent d-flex align-items-center justify-content-between
                {{ request()->routeIs('stzfaculty.*') ? 'active' : '' }}"
                 data-label="Faculty">
                <a href="{{ route('stzfaculty.overview') }}" class="sidebar-link flex-grow-1">
                    <span class="menu-icon"><i class="bi bi-person-badge"></i></span>
                    <span class="menu-label ms-2">Faculty</span>
                </a>
                <i class="bi bi-chevron-down sidebar-chevron"
                   data-bs-toggle="collapse"
                   data-bs-target="#facultyMenu"
                   style="cursor:pointer; padding: 0 16px 0 8px;"></i>
            </div>
            <div class="collapse {{ request()->routeIs('stzfaculty.*') ? 'show' : '' }}"
                 id="facultyMenu">
                <a href="{{ route('stzfaculty.overview') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.overview') ? 'active' : '' }}">
                    <i class="bi bi-person-vcard me-2"></i><span class="menu-label">Faculty Profile</span>
                </a>
                <a href="{{ route('stzfaculty.teaching-load') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.teaching-load') ? 'active' : '' }}">
                    <i class="bi bi-book me-2"></i><span class="menu-label">Teaching Load</span>
                </a>
                <a href="{{ route('stzfaculty.research-performance') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.research-performance') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-data me-2"></i><span class="menu-label">Research</span>
                </a>
                    <a href="{{ route('stzfaculty.approval') }}"
                   class="submenu-link {{ request()->routeIs('stzfaculty.approval') ? 'active' : '' }}">
                    <i class="bi bi-check2-circle me-2"></i><span class="menu-label">Faculty Approval</span>
                </a>
            </div>
        </div>


        {{-- FINANCIAL REPORTS --}}
        <div class="sidebar-item">
            <div class="sidebar-parent d-flex align-items-center justify-content-between"
                 data-label="Financial Reports">
                <a href="#" class="sidebar-link flex-grow-1">
                    <span class="menu-icon"><i class="bi bi-cash-stack"></i></span>
                    <span class="menu-label ms-2">Financial Reports</span>
                </a>
                <i class="bi bi-chevron-down sidebar-chevron"
                   data-bs-toggle="collapse"
                   data-bs-target="#financialMenu"
                   style="cursor:pointer; padding: 0 16px 0 8px;"></i>
            </div>
            <div class="collapse" id="financialMenu">
                <a href="#" class="submenu-link">
                    <i class="bi bi-pie-chart me-2"></i><span class="menu-label">Normative Funding Allocation</span>
                </a>
            </div>
        </div>

        {{-- RESEARCH --}}
        <div class="sidebar-item">
            <div class="sidebar-parent d-flex align-items-center justify-content-between"
                 data-label="Research">
                <a href="#" class="sidebar-link flex-grow-1">
                    <span class="menu-icon"><i class="bi bi-search-heart"></i></span>
                    <span class="menu-label ms-2">Research</span>
                </a>
                <i class="bi bi-chevron-down sidebar-chevron"
                   data-bs-toggle="collapse"
                   data-bs-target="#researchMenu"
                   style="cursor:pointer; padding: 0 16px 0 8px;"></i>
            </div>
            <div class="collapse" id="researchMenu">
                <a href="#" class="submenu-link">
                    <i class="bi bi-journal me-2"></i><span class="menu-label">Publications</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="bi bi-trophy me-2"></i><span class="menu-label">Awards</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="bi bi-graph-up me-2"></i><span class="menu-label">Analytics</span>
                </a>
            </div>
        </div>

        {{-- ALUMNI --}}
        <div class="sidebar-item">
            <div class="sidebar-parent d-flex align-items-center justify-content-between
                {{ request()->is('alumni*') ? 'active' : '' }}"
                 data-label="Alumni">
                <a href="#alumniMenu" class="sidebar-link flex-grow-1" data-bs-toggle="collapse">
                    <span class="menu-icon"><i class="bi bi-mortarboard"></i></span>
                    <span class="menu-label ms-2">Alumni</span>
                </a>
                <i class="bi bi-chevron-down sidebar-chevron"
                   data-bs-toggle="collapse"
                   data-bs-target="#alumniMenu"
                   style="cursor:pointer; padding: 0 16px 0 8px;"></i>
            </div>
            <div class="collapse" id="alumniMenu">
                <a href="#" class="submenu-link">
                    <i class="bi bi-list-ul me-2"></i><span class="menu-label">Alumni List</span>
                </a>
                <a href="#" class="submenu-link">
                    <i class="bi bi-person-plus me-2"></i><span class="menu-label">Add Alumni</span>
                </a>
            </div>
        </div>

        <hr class="mx-3" style="border-color:#555;">

        <a href="#" class="sidebar-menu-item" data-label="About Analytica">
            <span class="menu-icon"><i class="bi bi-info-circle"></i></span>
            <span class="menu-label ms-2">About Analytica</span>
        </a>

    </div>{{-- end sidebar-scrollable --}}

    {{-- Profile Section — pinned at bottom --}}
    <div class="sidebar-profile">
        <div class="profile-info">
            <div class="profile-icon">
                <i class="bi bi-person-circle"></i>
            </div>
            <div class="profile-text">
                <div class="profile-name">{{ Auth::user()->name }}</div>
                <div class="profile-role">{{ Auth::user()->role }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </form>
    </div>

    {{-- Collapse/Expand Toggle Button --}}
    <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-chevron-left" id="toggleIcon"></i>
    </button>

</div>{{-- end .sidebar --}}

<style>
    /* ============================================================
       SIDEBAR — BASE
    ============================================================ */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background-color: #1f1f1f;
        display: flex;
        flex-direction: column;
        overflow: visible;
        z-index: 1000;
        transition: width 0.3s ease;
        font-family: 'Bricolage Grotesque', sans-serif;
    }

    .sidebar.collapsed {
        width: 68px;
    }

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
       HIDE / SHOW LABELS WHEN COLLAPSED
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

    /* Hide collapse menus when sidebar is collapsed */
    .sidebar.collapsed .collapse {
        display: none !important;
    }

    /* ============================================================
       CENTER ICONS WHEN COLLAPSED
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

    /* Tooltip on hover when collapsed */
    .sidebar.collapsed .sidebar-menu-item,
    .sidebar.collapsed .sidebar-parent {
        position: relative;
    }

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
        width: 36px;
        height: 36px;
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

    /* Move toggle button when collapsed */
    .sidebar.collapsed ~ * .sidebar-toggle-btn,
    body.sidebar-collapsed .sidebar-toggle-btn {
        left: 48px;
    }

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
    .sidebar.collapsed .sidebar-profile .logout-form  { margin: 0 auto; }

    .profile-info { display: flex; align-items: center; gap: 10px; }
    .profile-icon { font-size: 30px; line-height: 1; }
    .profile-text { display: flex; flex-direction: column; gap: 1px; }
    .profile-name { font-weight: 700; font-size: 14px; line-height: 1.2; }
    .profile-role { font-size: 11px; opacity: 0.85; }

    .logout-form { margin: 0; }
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
        font-size: 0.92rem;
        font-weight: 500;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .sidebar-menu-item:hover { background: #0f8f3a; color: white; }
    .sidebar-menu-item.active { background: #009539; color: white; }

    /* ============================================================
       PARENT ROW (expandable sections)
    ============================================================ */
    .sidebar-parent {
        transition: background-color 0.2s ease;
        cursor: default;
    }

    .sidebar-parent:hover { background: #0f8f3a; }
    .sidebar-parent:hover .sidebar-link,
    .sidebar-parent:hover .sidebar-chevron { color: white; }

    .sidebar-parent.active { background: #009539; }
    .sidebar-parent.active .sidebar-link,
    .sidebar-parent.active .sidebar-chevron { color: white; }

    /* ============================================================
       SIDEBAR LINK (parent label area)
    ============================================================ */
    .sidebar-link {
        color: #cfcfcf;
        text-decoration: none;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        font-size: 0.92rem;
        font-weight: 500;
        background: transparent !important;
        transition: color 0.2s ease;
    }

    .sidebar-link:hover { color: white; }

    .sidebar-chevron {
        color: #cfcfcf;
        font-size: 0.75rem;
        transition: color 0.2s ease, transform 0.3s ease;
    }

    /* Rotate chevron when submenu is open */
    .sidebar-parent .collapse.show ~ .sidebar-chevron,
    .bi-chevron-down[aria-expanded="true"] {
        transform: rotate(180deg);
    }

    /* ============================================================
       SUBMENU
    ============================================================ */
    .collapse { background: #2a2a2a; }

    .submenu-link {
        display: block;
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
       MAIN CONTENT SHIFT — applied to .content in each page
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
        const sidebar   = document.getElementById('mainSidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const STORAGE_KEY = 'clsu_analytica_sidebar_collapsed';

        function applyState(isCollapsed) {
            sidebar.classList.toggle('collapsed', isCollapsed);
            document.body.classList.toggle('sidebar-collapsed', isCollapsed);

            // Move the toggle button position
            toggleBtn.style.left = isCollapsed ? '48px' : '230px';

            localStorage.setItem(STORAGE_KEY, isCollapsed);
        }

        // Restore saved state on load
        const savedState = localStorage.getItem(STORAGE_KEY) === 'true';
        applyState(savedState);

        toggleBtn.addEventListener('click', function () {
            applyState(!sidebar.classList.contains('collapsed'));
        });

        // Rotate chevrons when their submenu opens/closes
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (trigger) {
            const targetId = trigger.getAttribute('data-bs-target');
            if (!targetId) return;
            const target = document.querySelector(targetId);
            if (!target) return;

            target.addEventListener('show.bs.collapse', function () {
                trigger.style.transform = 'rotate(180deg)';
            });
            target.addEventListener('hide.bs.collapse', function () {
                trigger.style.transform = 'rotate(0deg)';
            });

            // Set initial state
            if (target.classList.contains('show')) {
                trigger.style.transform = 'rotate(180deg)';
            }
        });
    });
</script>