<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Faculty Overview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">
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

        /* Statistics Cards */
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

        .stat-label {
            font-size: 13px;
            color: #666;
            font-weight: 600;
            margin-top: 4px;
        }

        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 0 30px 30px 30px;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .chart-card.full-width {
            grid-column: 1 / -1;
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
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">
        <div class="header">Faculty Overview</div>

        <!-- NEW FILTER BAR DESIGN -->
        <div class="filter-bar">
            <div class="page-title">QUALIFICATIONS</div>

            <div class="filter-bar-label">Filters:</div>

            <div class="filter-group">
                <label>Semester:</label>
                <select id="semesterFilter">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->sem_id }}" {{ $filters['semester'] == $semester->sem_id ? 'selected' : '' }}>
                            {{ $semester->semester }} {{ $semester->sy }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>College:</label>
                <select id="collegeFilter">
                    <option value="all">All</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->c_u_id }}" {{ $filters['college'] == $college->c_u_id ? 'selected' : '' }}>
                            {{ $college->college_acro }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Department:</label>
                <select id="departmentFilter">
                    <option value="all">All</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}" {{ $filters['department'] == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_acro }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="clear-filters-btn" onclick="clearFilters()">
                Clear Filters
            </button>
        </div>

        <!-- Statistics Cards - 4 Most Important Metrics -->
        <div class="stats-container">
            <!-- Card 1: Total Faculty (Green) -->
            <div class="stat-card green">
                <div class="icon-box">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalFaculty }}</div>
                    <div class="stat-label">Total Faculty</div>
                </div>
            </div>

            <!-- Card 2: Active Faculty -->
            <div class="stat-card blue">
                <div class="icon-box">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $activeCount }}</div>
                    <div class="stat-label">Active Faculty</div>
                </div>
            </div>

            <!-- Card 3: PhD Holders -->
            <div class="stat-card orange">
                <div class="icon-box">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $phdHolders }}</div>
                    <div class="stat-label">PhD Holders</div>
                </div>
            </div>

            <!-- Card 4: Masters Holders -->
            <div class="stat-card purple">
                <div class="icon-box">
                    <i class="bi bi-book-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $mastersHolders }}</div>
                    <div class="stat-label">Masters Holders</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <!-- Chart 1: Faculty by Department -->
            <div class="chart-card">
                <div class="chart-title">
                    Faculty by Department
                </div>
                <div class="chart-wrapper">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Faculty Categories -->
            <div class="chart-card">
                <div class="chart-title">
                    Employment Status
                </div>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Chart 3: PhD Count by Department -->
            <div class="chart-card">
                <div class="chart-title">
                    PhD Holders by Department
                </div>
                <div class="chart-wrapper">
                    <canvas id="phdByDepartmentChart"></canvas>
                </div>
            </div>

            <!-- Chart 4: Active vs On Leave -->
            <div class="chart-card">
                <div class="chart-title">
                    Faculty Status Overview
                </div>
                <div class="chart-wrapper">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Chart 5: Highest Degree Distribution (Full Width) -->
            <div class="chart-card full-width">
                <div class="chart-title">
                    Highest Academic Qualification Distribution
                </div>
                <div class="chart-wrapper">
                    <canvas id="qualificationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // ============================================
            // 1. FACULTY BY DEPARTMENT (Bar Chart)
            // ============================================
            const deptCtx = document.getElementById('departmentChart');
            if (deptCtx) {
                const departmentLabels = {!! json_encode($departmentStats->pluck('code')) !!};
                const departmentData = {!! json_encode($departmentStats->pluck('count')) !!};

                new Chart(deptCtx, {
                    type: 'bar',
                    data: {
                        labels: departmentLabels,
                        datasets: [{
                            label: 'Faculty Count',
                            data: departmentData,
                            backgroundColor: '#009539',
                            borderRadius: 8,
                            barPercentage: 0.6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1f1f1f',
                                titleFont: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                                bodyFont: { family: "'Bricolage Grotesque', sans-serif" }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { drawBorder: false, color: '#e0e0e0' },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif" },
                                    color: '#666'
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                                    color: '#1f1f1f',
                                    maxRotation: 45
                                }
                            }
                        }
                    }
                });
            }

            // ============================================
            // 2. EMPLOYMENT STATUS (Donut Chart)
            // ============================================
            const catCtx = document.getElementById('categoryChart');
            if (catCtx) {
                const categoryLabels = {!! json_encode($categories->pluck('category')) !!};
                const categoryData = {!! json_encode($categories->pluck('count')) !!};

                new Chart(catCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categoryData,
                            backgroundColor: ['#009539', '#2c7be5', '#f6c343', '#e74c3c'],
                            borderColor: 'white',
                            borderWidth: 3
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
                                    font: { family: "'Bricolage Grotesque', sans-serif", size: 13 },
                                    color: '#1f1f1f',
                                    padding: 15,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1f1f1f',
                                titleFont: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                                bodyFont: { family: "'Bricolage Grotesque', sans-serif" }
                            }
                        }
                    }
                });
            }

            // ============================================
// 3. PHD BY DEPARTMENT (Bar Chart) - SINGLE GREEN
// ============================================
const phdDeptCtx = document.getElementById('phdByDepartmentChart');
if (phdDeptCtx) {
    const phdLabels = {!! json_encode($phdByDepartment->pluck('department_acro')) !!};
    const phdCounts = {!! json_encode($phdByDepartment->pluck('phd_count')) !!};

    new Chart(phdDeptCtx, {
        type: 'bar',
        data: {
            labels: phdLabels,
            datasets: [{
                label: 'PhD Holders',
                data: phdCounts,
                backgroundColor: '#009539', // Single solid green
                borderColor: '#006400',
                borderWidth: 1,
                borderRadius: 8,
                hoverBackgroundColor: '#00802e' // Darker on hover
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f1f1f',
                    titleFont: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                    bodyFont: { family: "'Bricolage Grotesque', sans-serif" },
                    callbacks: {
                        label: function(context) {
                            return 'PhD Holders: ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { drawBorder: false, color: '#e0e0e0' },
                    ticks: {
                        font: { family: "'Bricolage Grotesque', sans-serif" },
                        color: '#666',
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Number of PhD Holders',
                        font: { family: "'Bricolage Grotesque', sans-serif", weight: '600', size: 12 },
                        color: '#666'
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                        color: '#1f1f1f',
                        maxRotation: 45
                    }
                }
            }
        }
    });
}

            // ============================================
            // 4. FACULTY STATUS (Horizontal Bar)
            // ============================================
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Active', 'On Leave'],
                        datasets: [{
                            data: [{{ $activeCount }}, {{ $onLeaveCount }}],
                            backgroundColor: ['#009539', '#e74c3c'],
                            borderRadius: 8,
                            barPercentage: 0.5
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1f1f1f',
                                titleFont: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                                bodyFont: { family: "'Bricolage Grotesque', sans-serif" }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { drawBorder: false, color: '#e0e0e0' },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif" },
                                    color: '#666'
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif", weight: '600', size: 14 },
                                    color: '#1f1f1f'
                                }
                            }
                        }
                    }
                });
            }

            // ============================================
            // 5. QUALIFICATION DISTRIBUTION (Grouped Bar)
            // ============================================
            const qualCtx = document.getElementById('qualificationChart');
            if (qualCtx) {
                const deptLabels = {!! json_encode($phdByDepartment->pluck('department_acro')) !!};
                const phdCounts = {!! json_encode($phdByDepartment->pluck('phd_count')) !!};
                const mastersCounts = {!! json_encode($phdByDepartment->pluck('masters_count')) !!};
                const totalFaculty = {!! json_encode($phdByDepartment->pluck('total_faculty')) !!};

                const bachelorsCounts = totalFaculty.map((total, index) => 
                    total - (phdCounts[index] || 0) - (mastersCounts[index] || 0)
                );

                new Chart(qualCtx, {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [
                            {
                                label: 'PhD',
                                data: phdCounts,
                                backgroundColor: '#1565c0',
                                borderRadius: 5
                            },
                            {
                                label: "Master's",
                                data: mastersCounts,
                                backgroundColor: '#009539',
                                borderRadius: 5
                            },
                            {
                                label: "Bachelor's Only",
                                data: bachelorsCounts,
                                backgroundColor: '#f6c343',
                                borderRadius: 5
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
                                    font: { family: "'Bricolage Grotesque', sans-serif", size: 13 },
                                    color: '#1f1f1f',
                                    padding: 15,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: '#1f1f1f',
                                titleFont: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                                bodyFont: { family: "'Bricolage Grotesque', sans-serif" }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif", weight: '600' },
                                    color: '#1f1f1f',
                                    maxRotation: 45
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { drawBorder: false, color: '#e0e0e0' },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif" },
                                    color: '#666'
                                }
                            }
                        }
                    }
                });
            }

            // ============================================
            // FILTER HANDLERS
            // ============================================
            document.querySelectorAll('#semesterFilter, #collegeFilter, #departmentFilter')
                .forEach(select => {
                    select.addEventListener('change', function() {
                        applyFilters();
                    });
                });
        });

        function applyFilters() {
            const params = new URLSearchParams();
            
            const semester = document.getElementById('semesterFilter').value;
            const college = document.getElementById('collegeFilter').value;
            const department = document.getElementById('departmentFilter').value;
            
            if (semester !== 'all') params.append('semester', semester);
            if (college !== 'all') params.append('college', college);
            if (department !== 'all') params.append('department', department);
            
            const url = new URL(window.location.href);
            url.search = params.toString();
            window.location.href = url.toString();
        }

        function clearFilters() {
            window.location.href = '{{ route('stzfaculty.overview') }}';
        }
    </script>
</body>
</html>