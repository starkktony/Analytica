<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Research & Non-Teaching Load</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
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
            padding: 5px 30px;
            font-size: 42px;
            font-weight: bold;
            height: 75px;
        }
        .content {
            margin-left: 210px;
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
        
        /* NEW FILTER BAR DESIGN - Matching reference image */
        .filter-bar {
            font-family: 'Bricolage Grotesque', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 14px 30px;
            border-bottom: 1px solid #b0b5b0;
            height: 40px;
        }

        .filter-bar-label {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            margin-right: 5px;
            margin-left: auto;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #2d2d2d;
            white-space: nowrap;
        }

        .filter-group select {
            font-size: 12px;
            padding: 5px 28px 5px 12px;
            border-radius: 20px;
            border: 1px solid #8a8f8a;
            background-color: #f5f5f5;
            color: #2d2d2d;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%232d2d2d' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 8px;
            min-width: 90px;
            cursor: pointer;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #009539;
            background-color: white;
        }

        .page-title {
            font-size: 16px;
            font-weight: 700;
            color: #2d2d2d;
            margin-right: 10px;
        }

        .clear-filters-btn {
            background: #009539;
            color: white;
            border: none;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 8px;
        }

        .clear-filters-btn:hover {
            background: #00802e;
        }

        /* Research Page Styles */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 30px;
        }

        .stat-card {
            border-radius: 15px;
            padding: 20px 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100px;
        }

        .stat-card.green {
            background: #009539;
            color: white;
        }

        .stat-card.green .icon-box {
            background: white;
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 15px;
            left: 15px;
        }

        .stat-card.green .icon-box i {
            font-size: 22px;
            color: #009539;
        }

        .stat-card.green .stat-content {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            flex: 1;
        }

        .stat-card.green .stat-number {
            font-size: 48px;
            font-weight: 700;
            color: white;
            line-height: 1;
        }

        .stat-card.green .stat-label {
            font-size: 13px;
            color: white;
            font-weight: 600;
            margin-top: 4px;
        }

        .stat-card.blue,
        .stat-card.orange,
        .stat-card.purple {
            background: white;
            color: #1f1f1f;
        }

        .stat-card.blue .icon-box,
        .stat-card.orange .icon-box,
        .stat-card.purple .icon-box {
            background: #009539;
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 15px;
            left: 15px;
        }

        .stat-card.blue .icon-box i,
        .stat-card.orange .icon-box i,
        .stat-card.purple .icon-box i {
            font-size: 22px;
            color: white;
        }

        .stat-card.blue .stat-content,
        .stat-card.orange .stat-content,
        .stat-card.purple .stat-content {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            flex: 1;
        }

        .stat-number {
            font-size: 48px;
            font-weight: 700;
            color: #1f1f1f;
            line-height: 1;
        }

        .stat-card.green .stat-number {
            color: white;
        }

        .stat-label {
            font-size: 13px;
            color: #666;
            font-weight: 600;
            margin-top: 4px;
        }

        .stat-card.green .stat-label {
            color: white;
        }

        /* DIFFERENT LAYOUT: Single column with wider charts */
        .chart-main-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            padding: 0 30px 30px 30px;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 100%;
        }

        .chart-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f1f1f;
            margin-bottom: 25px;
        }

        .chart-wrapper {
            height: 350px;
            position: relative;
        }

        .table-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 0 30px 30px 30px;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: #009539;
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
        }

        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #e0e0e0;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Publication tags */
        .publication-tag {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            margin: 2px;
        }

        .tag-journal { background: #e3f2fd; color: #1565c0; }
        .tag-conference { background: #f3e5f5; color: #7b1fa2; }
        .tag-book { background: #e8f5e9; color: #2e7d32; }
        .tag-report { background: #fff3e0; color: #ef6c00; }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">
        <div class="header">Research & Non-Teaching Load</div>
        
        <!-- NEW FILTER BAR DESIGN -->
        <div class="filter-bar">
            <div class="page-title">RESEARCH & PUBLICATIONS</div>

            <div class="filter-bar-label">Filters:</div>

            <div class="filter-group">
                <label>Year:</label>
                <select id="semesterFilter">
                    <option value="all">All</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->sem_id }}" {{ $filters['semester'] == $semester->sem_id ? 'selected' : '' }}>
                            {{ $semester->semester }} {{ $semester->sy }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Type:</label>
                <select id="departmentFilter">
                    <option value="all">All</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ $filters['department'] == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_acro }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Status:</label>
                <select id="roleTypeFilter">
                    <option value="all">All</option>
                    @foreach($roleTypes as $type)
                        <option value="{{ $type }}" {{ $filters['role_type'] == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <button class="clear-filters-btn" onclick="clearFilters()">
                Clear Filters
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card green">
                <div class="icon-box">
                    <i class="bi bi-flask"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $researchLoad->sum('research_count') }}</div>
                    <div class="stat-label">Research Projects</div>
                </div>
            </div>

            <div class="stat-card blue">
                <div class="icon-box">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $publications->sum('publication_count') }}</div>
                    <div class="stat-label">Publications</div>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="icon-box">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $adminRoles->count() }}</div>
                    <div class="stat-label">Admin Roles</div>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="icon-box">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $researchLoad->sum('faculty_with_research') }}</div>
                    <div class="stat-label">Faculty with ETL</div>
                </div>
            </div>
        </div>

        <!-- DIFFERENT LAYOUT: Single column with sidebar -->
        <div class="chart-main-container">
            <!-- Left Column: Main Research Chart -->
            <div class="chart-card">
                <div class="chart-title">
                    Research Load by Department (ETL Hours)
                </div>
                <div class="chart-wrapper">
                    <canvas id="researchLoadChart"></canvas>
                </div>
                <div style="margin-top: 20px; font-size: 13px; color: #666; text-align: center;">
                    <i class="bi bi-info-circle me-1"></i>
                    Total ETL: {{ number_format($researchLoad->sum('total_etl'), 1) }} hours across {{ $researchLoad->count() }} departments
                </div>
            </div>

            <!-- Right Column: Two smaller charts stacked -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Publications Chart -->
                <div class="chart-card">
                    <div class="chart-title">
                        Publication Types
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="publicationChart"></canvas>
                    </div>
                </div>

                <!-- Admin Roles Chart -->
                <div class="chart-card">
                    <div class="chart-title">
                        Top Admin Roles
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="adminRolesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // ============================================
            // 1. RESEARCH LOAD BY DEPARTMENT CHART
            // ============================================
            const researchLoadCtx = document.getElementById('researchLoadChart');
            if (researchLoadCtx) {
                const deptLabels = {!! json_encode($researchLoad->pluck('department_acro')) !!};
                const researchData = {!! json_encode($researchLoad->pluck('total_etl')) !!};
                const facultyData = {!! json_encode($researchLoad->pluck('faculty_with_research')) !!};
                
                new Chart(researchLoadCtx, {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [
                            {
                                label: 'ETL Hours',
                                data: researchData,
                                backgroundColor: '#009539',
                                borderColor: '#009539',
                                borderWidth: 1,
                                borderRadius: 8,
                                barPercentage: 0.6
                            },
                            {
                                label: 'Faculty Count',
                                data: facultyData,
                                backgroundColor: '#2c7be5',
                                borderColor: '#2c7be5',
                                borderWidth: 1,
                                borderRadius: 8,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    font: {
                                        family: "'Bricolage Grotesque', sans-serif",
                                        size: 13
                                    },
                                    color: '#1f1f1f'
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1f1f1f',
                                titleFont: {
                                    family: "'Bricolage Grotesque', sans-serif",
                                    weight: '600'
                                },
                                bodyFont: {
                                    family: "'Bricolage Grotesque', sans-serif"
                                },
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.dataset.label === 'ETL Hours') {
                                            label += context.parsed.y.toFixed(1) + ' hours';
                                        } else {
                                            label += context.parsed.y + ' faculty';
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: '#e0e0e0'
                                },
                                ticks: {
                                    font: {
                                        family: "'Bricolage Grotesque', sans-serif"
                                    },
                                    color: '#666'
                                },
                                title: {
                                    display: true,
                                    text: 'ETL Hours / Faculty Count',
                                    font: {
                                        family: "'Bricolage Grotesque', sans-serif",
                                        weight: '600',
                                        size: 12
                                    },
                                    color: '#666'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: "'Bricolage Grotesque', sans-serif",
                                        weight: '600'
                                    },
                                    color: '#1f1f1f',
                                    maxRotation: 45
                                }
                            }
                        }
                    }
                });
            }
            
            // ============================================
            // 2. PUBLICATION TYPES CHART (Doughnut)
            // ============================================
            const publicationCtx = document.getElementById('publicationChart');
            if (publicationCtx) {
                // Create sample data for publication types
                const pubTypes = ['Journal Articles', 'Conference Papers', 'Book Chapters', 'Technical Reports'];
                const pubCounts = [
                    Math.floor({{ $publications->sum('publication_count') }} * 0.5),
                    Math.floor({{ $publications->sum('publication_count') }} * 0.3),
                    Math.floor({{ $publications->sum('publication_count') }} * 0.15),
                    Math.floor({{ $publications->sum('publication_count') }} * 0.05)
                ];
                
                new Chart(publicationCtx, {
                    type: 'doughnut',
                    data: {
                        labels: pubTypes,
                        datasets: [{
                            data: pubCounts,
                            backgroundColor: [
                                '#009539', // Green
                                '#2c7be5', // Blue
                                '#f6c343', // Orange
                                '#9b59b6'  // Purple
                            ],
                            borderColor: 'white',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    font: {
                                        family: "'Bricolage Grotesque', sans-serif",
                                        size: 11
                                    },
                                    color: '#1f1f1f',
                                    padding: 15
                                }
                            }
                        }
                    }
                });
            }
            
            // ============================================
            // 3. ADMIN ROLES CHART (Horizontal Bar)
            // ============================================
            const adminRolesCtx = document.getElementById('adminRolesChart');
            if (adminRolesCtx && {!! $adminRoles->count() !!} > 0) {
                // Get top 8 admin roles by faculty count
                const topRoles = {!! json_encode($adminRoles->sortByDesc('faculty_count')->take(8)) !!};
                const roleLabels = topRoles.map(role => role.designation.length > 20 ? 
                    role.designation.substring(0, 20) + '...' : role.designation);
                const roleData = topRoles.map(role => role.faculty_count);
                
                new Chart(adminRolesCtx, {
                    type: 'bar',
                    data: {
                        labels: roleLabels,
                        datasets: [{
                            label: 'Faculty Count',
                            data: roleData,
                            backgroundColor: function(context) {
                                const index = context.dataIndex;
                                const colors = [
                                    '#009539', '#2c7be5', '#f6c343', '#9b59b6',
                                    '#e74c3c', '#3498db', '#1abc9c', '#8e44ad'
                                ];
                                return colors[index % colors.length];
                            },
                            borderColor: 'white',
                            borderWidth: 1,
                            borderRadius: 5
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#1f1f1f',
                                titleFont: {
                                    family: "'Bricolage Grotesque', sans-serif",
                                    weight: '600'
                                },
                                bodyFont: {
                                    family: "'Bricolage Grotesque', sans-serif"
                                },
                                callbacks: {
                                    title: function(tooltipItems) {
                                        const index = tooltipItems[0].dataIndex;
                                        return topRoles[index]?.designation || '';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    drawBorder: false,
                                    color: '#e0e0e0'
                                },
                                ticks: {
                                    font: {
                                        family: "'Bricolage Grotesque', sans-serif"
                                    },
                                    color: '#666',
                                    stepSize: 1
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: "'Bricolage Grotesque', sans-serif",
                                        weight: '600',
                                        size: 11
                                    },
                                    color: '#1f1f1f'
                                }
                            }
                        }
                    }
                });
            } else {
                // Display message if no data
                adminRolesCtx.parentElement.innerHTML = `
                    <div style="height: 100%; display: flex; align-items: center; justify-content: center; color: #999;">
                        <div style="text-align: center;">
                            <i class="bi bi-people" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p style="margin-top: 10px; font-size: 0.9rem;">No admin roles data</p>
                        </div>
                    </div>
                `;
            }
            
            // ============================================
            // FILTER HANDLERS
            // ============================================
            document.querySelectorAll('#semesterFilter, #departmentFilter, #roleTypeFilter')
                .forEach(select => {
                    select.addEventListener('change', function() {
                        applyFilters();
                    });
                });
        });
        
        function applyFilters() {
            const params = new URLSearchParams();
            
            const semester = document.getElementById('semesterFilter').value;
            const department = document.getElementById('departmentFilter').value;
            const roleType = document.getElementById('roleTypeFilter').value;
            
            if (semester !== 'all') params.append('semester', semester);
            if (department !== 'all') params.append('department', department);
            if (roleType !== 'all') params.append('role_type', roleType);
            
            const url = new URL(window.location.href);
            url.search = params.toString();
            window.location.href = url.toString();
        }
        
        function clearFilters() {
            window.location.href = '{{ route("stzfaculty.research-performance") }}';
        }
    </script>
</body>
</html>