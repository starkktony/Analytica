<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f5f5f5;
        }
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
        /* Sidebar parent item */
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

        /* Submenu container */
        .collapse {
            background: #1f1f1f;
            padding: 20px;
        }

        /* Submenu items */
        .submenu-link {
            display: block;
            color: #cfcfcf;
            text-decoration: none;
            position: relative;
            padding: 20px;
        }

        /* Opened parent highlight */
        .sidebar-link[aria-expanded="true"] {
            background: #0f8f3a;
            color: white;
        }
        
        .filter-bar {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 18px;
            background: #f3faf6;
            padding: 12px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .filter-left i {
            font-size: 18px;
            color: #1f1f1f;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #009539;
            white-space: nowrap;
        }
        
        .filter-left {
            position: relative;
            padding-right: 16px;
            margin-right: 6px;
        }

        .filter-left::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 22px;
            background: #bdbdbd;
        }

        .filter-group select {
            font-size: 14px;
            padding: 4px 28px 4px 10px;
            border-radius: 8px;
            border: 1px solid #bdbdbd;
            background-color: #ffffff;
            color: #009539;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23009539' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #009539;
        }

        /* Graduation Hero Section */
        .graduation-hero {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            border-radius: 20px;
            margin: 20px;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .hero-left {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .hero-icon {
            font-size: 120px;
            opacity: 0.9;
        }

        .hero-text h2 {
            font-size: 42px;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .hero-number {
            font-size: 72px;
            font-weight: bold;
            margin-top: 10px;
        }

        .hero-right {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .hero-stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px 30px;
            min-width: 400px;
        }

        .hero-stat-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1f1f1f;
            margin-bottom: 8px;
        }

        .hero-stat-card .stat-number {
            font-size: 38px;
            font-weight: bold;
            color: #1f1f1f;
        }

        /* Charts Section */
        .graduation-charts {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 0 20px 20px 20px;
        }

        .grad-chart-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .grad-chart-card h3 {
            font-size: 16px;
            color: #1f1f1f;
            margin-bottom: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .chart-placeholder {
            width: 100%;
            height: 300px;
            background: #f9f9f9;
            border: 1px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">
        <div class="header">Graduation Overview</div>
        <div class="filter-bar">
            <div class="filter-left">
                <i class="bi bi-funnel-fill"></i>
            </div>

            <div class="filter-group">
                <label>Student Level:</label>
                <select>
                    <option>University</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Semester:</label>
                <select>
                    <option>1st Sem, 2024 - 2025</option>
                </select>
            </div>

            <div class="filter-group">
                <label>College:</label>
                <select>
                    <option>All</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Program:</label>
                <select>
                    <option>All</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Year Level:</label>
                <select>
                    <option>All</option>
                </select>
            </div>
        </div>

        <!-- Graduation Hero Section -->
        <div class="graduation-hero">
            <div class="hero-left">
                <div class="hero-icon">
                    🎓
                </div>
                <div class="hero-text">
                    <h2>First Gen Students<br>of the University</h2>
                    <div class="hero-number">3,125</div>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-stat-card">
                    <h3>Undergraduate First Gen Students</h3>
                    <div class="stat-number">XX,XXX</div>
                </div>
                <div class="hero-stat-card">
                    <h3>Graduate First Gen Students</h3>
                    <div class="stat-number">XX,XXX</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="graduation-charts">
            <div class="grad-chart-card">
                <h3>First Gen Students of the University</h3>
                <div class="chart-placeholder">Line Chart</div>
            </div>
            
            <div class="grad-chart-card">
                <h3>First Gen Students of the University</h3>
                <div class="chart-placeholder">Stacked Bar Chart</div>
            </div>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>