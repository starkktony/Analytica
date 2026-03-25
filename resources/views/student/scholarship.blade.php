<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: #e8f5e9;
            margin: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
        }
        .header {
            background: #009539;
            color: white;
            padding: 10px;
            font-size: 62px;
            font-weight: bold;
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

        /* Scholarship Content - for "All" view */
        .scholarship-container {
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            gap: 15px;
            padding: 15px;
            height: calc(100vh - 140px);
        }

        .scholarship-title {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            color: white;
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .scholarship-title h2 {
            font-size: 36px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .scholarship-title p {
            font-size: 24px;
            margin: 8px 0 0 0;
            opacity: 0.95;
        }

        .donut-chart-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
        }

        .scholarship-category {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 12px;
        }

        .category-header {
            font-size: 14px;
            color: #999;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .scholarship-item {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            color: white;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .scholarship-item:last-child {
            margin-bottom: 0;
        }

        .item-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-icon {
            font-size: 28px;
            opacity: 0.9;
        }

        .item-details h4 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 3px 0;
        }

        .item-details p {
            font-size: 13px;
            margin: 0;
            opacity: 0.95;
        }

        .item-right {
            text-align: right;
        }

        .item-right .status {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .item-right .date {
            font-size: 11px;
            opacity: 0.9;
        }

        .view-more-btn {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            float: right;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .view-more-btn:hover {
            opacity: 0.9;
        }

        /* Main Container */
        .scholarship-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 20px;
            height: calc(100vh - 160px);
        }

        /* Left Section */
        .scholarship-left {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .scholarship-header {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            border-radius: 30px;
            padding: 40px;
            text-align: center;
            color: white;
        }

        .scholarship-header h1 {
            font-size: 48px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .year-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .year-selector label {
            font-size: 18px;
            font-weight: 600;
        }

        .year-selector select {
            padding: 8px 35px 8px 15px;
            border-radius: 10px;
            border: 2px solid white;
            background-color: rgba(255,255,255,0.2);
            color: white;
            font-size: 16px;
            font-weight: 600;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            cursor: pointer;
        }

        .year-selector select:focus {
            outline: none;
            background-color: rgba(255,255,255,0.3);
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chart-placeholder {
            width: 100%;
            height: 100%;
            background: #f9f9f9;
            border: 2px dashed #ddd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 14px;
        }

        /* Right Section */
        .scholarship-right {
            background: white;
            border-radius: 15px;
            padding: 25px;
            overflow-y: auto;
        }

        .scholarship-right h2 {
            font-size: 24px;
            font-weight: 700;
            color: #666;
            margin: 0 0 20px 0;
        }

        .scholarship-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .scholarship-card {
            background: linear-gradient(135deg, #009539 0%, #0f8f3a 100%);
            border-radius: 20px;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
        }

        .card-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .card-icon {
            font-size: 40px;
        }

        .card-info h3 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }

        .card-info p {
            font-size: 15px;
            margin: 0;
            font-weight: 500;
        }

        .card-right {
            text-align: right;
        }

        .card-status {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .card-date {
            font-size: 12px;
            opacity: 0.9;
        }

        .scholarship-type-section {
            display: none;
        }

        .scholarship-type-section.active {
            display: block;
        }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">
        <div class="header">Scholarship Overview</div>
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
            
            <div class="filter-group">
                <label>Type of Scholarship:</label>
                <select id="scholarshipType" onchange="filterScholarship()">
                    <option value="all">All</option>
                    <option value="private">Private</option>
                    <option value="government">Government</option>
                    <option value="institutional">Institutional</option>
                </select>
            </div>
        </div>

        <!-- ALL SCHOLARSHIPS (Overview) -->
        <div id="all-section" class="scholarship-type-section active">
            <div class="scholarship-container">
                <div class="scholarship-left">
                    <div class="scholarship-title">
                        <h2>Scholarship Grant</h2>
                        <p>(2024 - 2025)</p>
                    </div>
                    
                    <div class="donut-chart-card">
                        <div class="chart-placeholder">Donut Chart</div>
                    </div>
                </div>

                <div class="scholarship-right">
                    <div class="scholarship-category">
                        <div class="category-header">Institutional Scholarship</div>
                        
                        <div class="scholarship-item">
                            <div class="item-left">
                                <div class="item-icon">🎓</div>
                                <div class="item-details">
                                    <h4>20</h4>
                                    <p>University Scholar</p>
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="status">Completed</div>
                                <div class="date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-item">
                            <div class="item-left">
                                <div class="item-icon">🎓</div>
                                <div class="item-details">
                                    <h4>600</h4>
                                    <p>College Scholar</p>
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="status">Completed</div>
                                <div class="date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>
                    </div>

                    <div class="scholarship-category">
                        <div class="category-header">Government Scholarship</div>
                        
                        <div class="scholarship-item">
                            <div class="item-left">
                                <div class="item-icon">🎓</div>
                                <div class="item-details">
                                    <h4>20</h4>
                                    <p>PHILMIECA</p>
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="status">Completed</div>
                                <div class="date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-item">
                            <div class="item-left">
                                <div class="item-icon">🎓</div>
                                <div class="item-details">
                                    <h4>XXX</h4>
                                    <p>Lorem Ipsum</p>
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="status">Completed</div>
                                <div class="date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                    </div>

                    <div class="scholarship-category">
                        <div class="category-header">Private Scholarship</div>
                        
                        <div class="scholarship-item">
                            <div class="item-left">
                                <div class="item-icon">🎓</div>
                                <div class="item-details">
                                    <h4>XXX</h4>
                                    <p>Lorem Ipsum</p>
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="status">Completed</div>
                                <div class="date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-item">
                            <div class="item-left">
                                <div class="item-icon">🎓</div>
                                <div class="item-details">
                                    <h4>XXX</h4>
                                    <p>Lorem Ipsum</p>
                                </div>
                            </div>
                            <div class="item-right">
                                <div class="status">Completed</div>
                                <div class="date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- PRIVATE SCHOLARSHIP -->
        <div id="private-section" class="scholarship-type-section">
            <div class="scholarship-main">
                <div class="scholarship-left">
                    <div class="scholarship-header">
                        <h1>Private Scholarship</h1>
                        <div class="year-selector">
                            <label>Select Academic Year:</label>
                            <select>
                                <option>Academic Year</option>
                                <option>2024 - 2025</option>
                                <option>2023 - 2024</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">Horizontal Bar Chart</div>
                    </div>
                </div>

                <div class="scholarship-right">
                    <h2>Private Scholarship</h2>
                    <div class="scholarship-list">
                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>CLSU Alumni Associate Inc.</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>GAPASCA</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>PHILDEV</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>Vicente B. Bello Scholarship Program</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GOVERNMENT SCHOLARSHIP -->
        <div id="government-section" class="scholarship-type-section">
            <div class="scholarship-main">
                <div class="scholarship-left">
                    <div class="scholarship-header">
                        <h1>Government Scholarship</h1>
                        <div class="year-selector">
                            <label>Select Academic Year:</label>
                            <select>
                                <option>Academic Year</option>
                                <option>2024 - 2025</option>
                                <option>2023 - 2024</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">Horizontal Bar Chart</div>
                    </div>
                </div>

                <div class="scholarship-right">
                    <h2>Government Scholarship</h2>
                    <div class="scholarship-list">
                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>CHED Scholarship Program</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>DOST SEI</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>CHED-TDP</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>CHED-TES</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>DOST-ERDT</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>XXX</h3>
                                    <p>DOST-ASTHRDP</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- INSTITUTIONAL SCHOLARSHIP -->
        <div id="institutional-section" class="scholarship-type-section">
            <div class="scholarship-main">
                <div class="scholarship-left">
                    <div class="scholarship-header">
                        <h1>Institutional Scholarship</h1>
                        <div class="year-selector">
                            <label>Select Academic Year:</label>
                            <select>
                                <option>Academic Year</option>
                                <option>2024 - 2025</option>
                                <option>2023 - 2024</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">Horizontal Bar Chart</div>
                    </div>
                </div>

                <div class="scholarship-right">
                    <h2>Institutional Scholarship</h2>
                    <div class="scholarship-list">
                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>20</h3>
                                    <p>University Scholar</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>600</h3>
                                    <p>College Scholar</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>

                        <div class="scholarship-card">
                            <div class="card-left">
                                <div class="card-icon">🎓</div>
                                <div class="card-info">
                                    <h3>20</h3>
                                    <p>ROTC</p>
                                </div>
                            </div>
                            <div class="card-right">
                                <div class="card-status">Completed</div>
                                <div class="card-date">1st Sem, 2021 - 2025</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterScholarship() {
            const type = document.getElementById('scholarshipType').value;
            
            // Hide all sections
            document.querySelectorAll('.scholarship-type-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(type + '-section').classList.add('active');
        }
    </script>
</body>
</html>