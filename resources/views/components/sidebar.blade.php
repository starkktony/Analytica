<div class="sidebar">
    <div class="text-center py-3">
        <img src="{{ asset('images/analytica-logo.png') }}"
             alt="CLSU Analytica Logo"
             class="img-fluid"
             style="max-width: 150px;">
    </div>

    <!-- Scrollable Content Container -->
    <div class="sidebar-scrollable">
        <!-- Search -->
        <div class="input-group mx-3 mb-3" style="width: 85%;">
            <span class="input-group-text rounded-start-pill">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control rounded-end-pill"
                   placeholder="Search here">
        </div>

        <!-- Dashboard -->
        <a href="{{ url('/dashboard') }}" class="sidebar-menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3 me-2"></i> Dashboard
        </a>

        <!-- STUDENT -->
        <div class="sidebar-item">
            <div class="d-flex align-items-center justify-content-between student-menu-container {{ request()->is('student*') || request()->is('enrollment') || request()->is('graduation') || request()->is('scholarship') ? 'active' : '' }}" style="padding: 0;">
                <a href="{{ url('/student') }}" 
                   class="sidebar-link flex-grow-1"
                   style="padding: 12px 20px; margin: 0;">
                    <i class="bi bi-people me-2"></i>
                    Student
                </a>
                <i class="bi bi-chevron-down chevron-toggle" 
                   data-bs-toggle="collapse"
                   data-bs-target="#studentMenu"
                   style="cursor: pointer; padding-right: 20px;"></i>
            </div>

            <!-- SUBMENU -->
            <div class="collapse {{ request()->is('student*') || request()->is('enrollment') || request()->is('graduation') || request()->is('scholarship') ? 'show' : '' }}" 
                 id="studentMenu">
                <a href="{{ url('/enrollment') }}" class="submenu-link {{ request()->is('enrollment') ? 'active' : '' }}">Enrollment</a>
                <a href="{{ url('/graduation') }}" class="submenu-link {{ request()->is('graduation') ? 'active' : '' }}">Graduation</a>
                <a href="{{ url('/scholarship') }}" class="submenu-link {{ request()->is('scholarship') ? 'active' : '' }}">Scholarship</a>
            </div>
        </div>

        <!-- FACULTY -->
        <div class="sidebar-item">
            <a href="#facultyMenu" 
               class="sidebar-link d-flex align-items-center justify-content-between {{ request()->is('faculty*') ? 'active' : '' }}" 
               data-bs-toggle="collapse">
                <span>
                    <i class="bi bi-person-badge me-2"></i>
                    Faculty
                </span>
                <i class="bi bi-chevron-down"></i>
            </a>

            <div class="collapse" id="facultyMenu">
                <a href="{{ route('stzfaculty.overview') }}" class="submenu-link">Faculty Overview</a>
                <a href="{{ route('stzfaculty.teaching-load') }}" class="submenu-link">Teaching Load</a>
                <a href="{{ route('stzfaculty.research-performance') }}" class="submenu-link">Research</a>
            </div>
        </div>

        <!-- ALUMNI -->
        <div class="sidebar-item">
            <a href="#alumniMenu" 
               class="sidebar-link d-flex align-items-center justify-content-between {{ request()->is('alumni*') ? 'active' : '' }}" 
               data-bs-toggle="collapse">
                <span>
                    <i class="bi bi-mortarboard me-2"></i>
                    Alumni
                </span>
                <i class="bi bi-chevron-down"></i>
            </a>

            <div class="collapse" id="alumniMenu">
                <a href="#" class="submenu-link">Alumni List</a>
                <a href="#" class="submenu-link">Add Alumni</a>
            </div>
        </div>

        <hr class="sidebar-divider mx-3" style="border-color: #555;">

        <a href="#" class="sidebar-menu-item">
            <i class="bi bi-info-circle me-2"></i> About Analytica
        </a>
    </div>

    <!-- Profile Section - Fixed at bottom -->
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
</div>

<style>
    /* ===== SIDEBAR BASE STYLES ===== */
    .sidebar {
        width: 210px;
        background: #1f1f1f;
        min-height: 100vh;
        height: 100vh;
        position: fixed;
        color: white;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* ===== SCROLLABLE CONTENT AREA ===== */
    .sidebar-scrollable {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-bottom: 20px;
    }

    /* Custom scrollbar styling */
    .sidebar-scrollable::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-scrollable::-webkit-scrollbar-track {
        background: #1f1f1f;
    }

    .sidebar-scrollable::-webkit-scrollbar-thumb {
        background: #555;
        border-radius: 3px;
    }

    .sidebar-scrollable::-webkit-scrollbar-thumb:hover {
        background: #666;
    }

    /* ===== PROFILE SECTION (FIXED AT BOTTOM) ===== */
    .sidebar-profile {
        background-color: #009539;
        color: white;
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .profile-icon {
        font-size: 32px;
        line-height: 1;
        display: flex;
        align-items: center;
    }

    .profile-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .profile-name {
        font-weight: 600;
        font-size: 14px;
        line-height: 1.2;
    }

    .profile-role {
        font-size: 12px;
        opacity: 0.9;
        line-height: 1.2;
    }

    .logout-form {
        margin: 0;
    }

    .logout-btn {
        background: transparent;
        border: none;
        color: white;
        font-size: 24px;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: opacity 0.2s ease;
    }

    .logout-btn:hover {
        opacity: 0.8;
    }

    /* ===== MENU ITEMS ===== */
    .sidebar-menu-item,
    .sidebar-link {
        color: #cfcfcf;
        text-decoration: none;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .sidebar-menu-item:hover,
    .sidebar-link:hover {
        background: #0f8f3a;
        color: white;
    }

    /* ===== ACTIVE STATES ===== */
    .sidebar-menu-item.active,
    .sidebar-link.active,
    .student-menu-container.active {
        background: #0f8f3a !important;
        color: white !important;
    }

    .student-menu-container.active .sidebar-link,
    .student-menu-container.active .chevron-toggle {
        color: white !important;
    }

    /* ===== STUDENT MENU SPECIFIC ===== */
    .sidebar-item {
        margin-bottom: 0;
    }

    .student-menu-container {
        transition: background-color 0.2s ease;
    }

    .student-menu-container:hover {
        background: #0f8f3a;
    }

    .student-menu-container:hover .sidebar-link,
    .student-menu-container:hover .chevron-toggle {
        color: white;
    }

    .student-menu-container .sidebar-link {
        background: transparent !important;
    }

    .student-menu-container .chevron-toggle {
        color: #cfcfcf;
        transition: color 0.2s ease;
    }

    /* ===== CHEVRON ANIMATION ===== */
    .sidebar-link i.bi-chevron-down {
        transition: transform 0.3s ease;
    }

    .sidebar-link[aria-expanded="true"] {
        background: #0f8f3a;
        color: white;
    }

    .sidebar-link[aria-expanded="true"] i.bi-chevron-down {
        transform: rotate(180deg);
    }

    /* ===== COLLAPSE/DROPDOWN ===== */
    .collapse {
        background: #1f1f1f;
    }

    /* ===== SUBMENU LINKS ===== */
    .submenu-link {
        display: block;
        color: #cfcfcf;
        text-decoration: none;
        padding: 10px 20px 10px 50px;
        position: relative;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .submenu-link:hover {
        background: #0f8f3a;
        color: white;
    }

    .submenu-link.active {
        background: #0f8f3a;
        color: white;
    }

    /* ===== DIVIDER ===== */
    .sidebar-divider {
        border-color: #555;
        margin: 10px 0;
    }

    /* ===== DROPDOWN MENU (Bootstrap overrides) ===== */
    .dropdown-menu {
        background: #2a2a2a;
        border: none;
        border-radius: 0;
        margin: 0;
        width: 100%;
    }

    .dropdown-item {
        color: #cfcfcf;
        padding: 10px 20px 10px 40px;
    }

    .dropdown-item:hover {
        background: #0f8f3a;
        color: white;
    }

    .dropdown-toggle::after {
        float: right;
        margin-top: 8px;
    }
</style>

<!-- 
    ========================================
    STYLES TO REMOVE FROM OTHER PAGES
    ========================================
    
    The following styles are NOW IN THE SIDEBAR and should be DELETED from other page files:

    .sidebar {
        width: 210px;
        background: #1f1f1f;
        min-height: 100vh;
        position: fixed;
        color: white;
    }
    
    .sidebar a {
        color: #cfcfcf;
        text-decoration: none;
        display: block;
        padding: 12px 20px;
    }
    
    .sidebar a.active,
    .sidebar a:hover {
        background: #0f8f3a;
        color: white;
    }
    
    .dropdown-menu {
        background: #2a2a2a;
        border: none;
        border-radius: 0;
        margin: 0;
        width: 100%;
    }
    
    .dropdown-item {
        color: #cfcfcf;
        padding: 10px 20px 10px 40px;
    }
    
    .dropdown-item:hover {
        background: #0f8f3a;
        color: white;
    }
    
    .dropdown-toggle::after {
        float: right;
        margin-top: 8px;
    }
    
    .sidebar-link {
        color: #cfcfcf;
        text-decoration: none;
        display: block;
        padding: 12px 20px;
    }

    .sidebar-link:hover {
        background: #0f8f3a;
        color: white;
    }

    .collapse {
        background: #1f1f1f;
        padding: 20px;
    }

    .submenu-link {
        display: block;
        color: #cfcfcf;
        text-decoration: none;
        position: relative;
        padding: 20px;
    }

    .sidebar-link[aria-expanded="true"] {
        background: #0f8f3a;
        color: white;
    }

    ========================================
    KEEP THESE STYLES IN OTHER PAGES
    ========================================
    
    These are page-specific styles that should STAY in each page:

    body {
        background: #f5f5f5;
    }
    
    .header {
        background: #009539;
        color: white;
        padding: 10px;
        font-size: 62px;
        font-weight: bold;
    }
    
    .content {
        margin-left: 210px;
        font-family: 'buttershine', serif;
    }
    
    All the tab, filter, stats, and chart styles...
-->