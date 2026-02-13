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
        .sidebar-link {
            color: #cfcfcf;
            text-decoration: none;
            display: block;
            padding: 12px 20px;
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

        /* Main Stats Section */
        .main-stats {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            margin: 20px;
            padding: 40px;
            border-radius: 20px;
            display: grid;
            grid-template-columns: 1fr auto 1fr 1fr;
            gap: 30px;
            align-items: center;
        }

        .main-stats-left {
            display: flex;
            align-items: center;
            gap: 30px;
            color: white;
        }

        .student-icon {
            font-size: 120px;
            opacity: 0.9;
        }

        .main-stats-info h2 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }

        .main-stats-info h3 {
            font-size: 20px;
            font-weight: 600;
            margin: 0 0 15px 0;
            opacity: 0.95;
        }

        .main-stats-info .number {
            font-size: 72px;
            font-weight: 700;
            margin: 0;
        }

        .vertical-divider {
            width: 2px;
            height: 120px;
            background: rgba(255,255,255,0.3);
        }

        .stat-box {
            background: rgba(255,255,255,0.15);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            color: white;
        }

        .stat-box h4 {
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 15px 0;
            opacity: 0.95;
        }

        .stat-box .number {
            font-size: 52px;
            font-weight: 700;
            margin: 0;
        }

        /* Chart Sections */
        .chart-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 0 20px 20px 20px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0 0 20px 0;
        }

        .chart-placeholder {
            width: 100%;
            height: 300px;
            background: #f9f9f9;
            border: 2px dashed #ddd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
        }

        /* Black Bar Section */
        .section-divider {
            background: #2a2a2a;
            color: white;
            text-align: center;
            padding: 20px;
            margin: 20px 20px;
            border-radius: 15px;
            font-size: 24px;
            font-weight: 700;
        }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">
        <div class="header">Student Data Overview</div>
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

        <!-- Main Stats Section -->
        <div class="main-stats">
            <div class="main-stats-left">
                <div class="student-icon">👤📖</div>
                <div class="main-stats-info">
                    <h2>Total University</h2>
                    <h3>Enrollees</h3>
                    <h3>(1st Sem, 2025-2026)</h3>
                    <div class="number">16,196</div>
                </div>
            </div>

            <div class="vertical-divider"></div>

            <div class="stat-box">
                <h4>Total Graduate<br>Enrollees</h4>
                <div class="number">916</div>
            </div>

            <div class="stat-box">
                <h4>Total Undergraduate<br>Enrollees</h4>
                <div class="number">15,280</div>
            </div>
        </div>

        <!-- First Row Charts -->
        <div class="chart-row">
            <div class="chart-card">
                <h3>Total University Enrollment per Semester (2020 - 2026)</h3>
                <div class="chart-placeholder">Line Chart</div>
            </div>

            <div class="chart-card">
                <h3>Enrollment Trend per Semester by Student Level (2020 - 2026)</h3>
                <div class="chart-placeholder">Stacked Bar Chart</div>
            </div>
        </div>

        <!-- Black Bar Section -->
        <div class="section-divider">All Students</div>

        <!-- Second Row Charts -->
        <div class="chart-row">
            <div class="chart-card">
                <h3>Enrollment Ranking by College (1st Sem, 2025-2026)</h3>
                <div class="chart-placeholder">Horizontal Bar Chart</div>
            </div>

            <div class="chart-card">
                <h3>Enrollment Trend per Semester by College (2020 - 2026)</h3>
                <div class="chart-placeholder">Stacked Bar Chart</div>
            </div>
        </div>

        <!-- Third Row Charts -->
        <div class="chart-row">
            <div class="chart-card">
                <h3>Enrollment by Sex (1st Sem, 2025-2026)</h3>
                <div class="chart-placeholder">Pie Chart</div>
            </div>

            <div class="chart-card">
                <h3>Enrollment Trend per Semester by Sex (2020 - 2025)</h3>
                <div class="chart-placeholder">Stacked Bar Chart</div>
            </div>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>