<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Teaching Load</title>
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

        /* Teaching Load Styles */
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
        .stat-card.red {
            background: white;
            color: #1f1f1f;
        }

        .stat-card.blue .icon-box,
        .stat-card.orange .icon-box,
        .stat-card.red .icon-box {
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
        .stat-card.red .icon-box i {
            font-size: 22px;
            color: white;
        }

        .stat-card.blue .stat-content,
        .stat-card.orange .stat-content,
        .stat-card.red .stat-content {
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

        .full-width-chart {
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">
        <div class="header">Teaching Load</div>
        
        <!-- NEW FILTER BAR DESIGN -->
        <div class="filter-bar">
            <div class="page-title">FACULTY</div>

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

        <!-- Teaching Load Statistics -->
        <div class="stats-container">
            <div class="stat-card green">
                <div class="icon-box">
                    <i class="bi bi-bar-chart-line-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($avgAtl, 1) }}</div>
                    <div class="stat-label">Avg ATL</div>
                </div>
            </div>

            <div class="stat-card blue">
                <div class="icon-box">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalFaculty }}</div>
                    <div class="stat-label">Total Faculty</div>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="icon-box">
                    <i class="bi bi-book-fill"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalSubjects }}</div>
                    <div class="stat-label">Total Subjects</div>
                </div>
            </div>

            <div class="stat-card red">
                <div class="icon-box">
                    <i class="bi bi-person-video3"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($totalStudents) }}</div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <!-- Chart 1: ATL Distribution by Department -->
            <div class="chart-card">
                <div class="chart-title">
                    ATL Distribution by Department
                </div>
                <div class="chart-wrapper">
                    <canvas id="atlByDepartmentChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Faculty Workload Distribution -->
            <div class="chart-card">
                <div class="chart-title">
                    Faculty Workload Distribution
                </div>
                <div class="chart-wrapper">
                    <canvas id="workloadDistributionChart"></canvas>
                </div>
            </div>

            <!-- Chart 3: Student Enrollment by Department -->
            <div class="chart-card full-width-chart">
                <div class="chart-title">
                    Teaching Load Metrics by Department
                </div>
                <div class="chart-wrapper">
                    <canvas id="teachingMetricsChart"></canvas>
                </div>
            </div>
        </div>

 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // ============================================
            // 1. ATL DISTRIBUTION BY DEPARTMENT
            // ============================================
            const atlByDeptCtx = document.getElementById('atlByDepartmentChart');
            if (atlByDeptCtx) {
                const deptLabels = {!! json_encode($departmentStats->pluck('department_acro')) !!};
                const atlData = {!! json_encode($departmentStats->pluck('avg_atl')) !!};
                
                // Color code based on ATL ranges
                const backgroundColors = atlData.map(atl => {
                    if (atl > 20) return '#e74c3c'; // High load - Red
                    if (atl > 15) return '#f6c343'; // Medium-high - Orange
                    if (atl > 10) return '#009539'; // Medium - Green
                    return '#2c7be5'; // Low - Blue
                });
                
                new Chart(atlByDeptCtx, {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [{
                            label: 'Average ATL',
                            data: atlData,
                            backgroundColor: backgroundColors,
                            borderColor: backgroundColors,
                            borderWidth: 1,
                            borderRadius: 8
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
                                        return 'ATL: ' + context.parsed.y.toFixed(1) + ' hours';
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
                                    color: '#666'
                                },
                                title: {
                                    display: true,
                                    text: 'ATL Hours',
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
            // 2. FACULTY WORKLOAD DISTRIBUTION (Pie Chart)
            // ============================================
            const workloadDistCtx = document.getElementById('workloadDistributionChart');
            if (workloadDistCtx) {
                const workloadRanges = {!! json_encode($workloadDistribution) !!};
                
                new Chart(workloadDistCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Low Load (<10)', 'Moderate (10-15)', 'High (15-20)', 'Very High (>20)'],
                        datasets: [{
                            data: [
                                workloadRanges.low || 0,
                                workloadRanges.moderate || 0,
                                workloadRanges.high || 0,
                                workloadRanges.very_high || 0
                            ],
                            backgroundColor: ['#2c7be5', '#009539', '#f6c343', '#e74c3c'],
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
                                bodyFont: { family: "'Bricolage Grotesque', sans-serif" },
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' faculty';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ============================================
            // 3. TEACHING METRICS BY DEPARTMENT (Multi-axis)
            // ============================================
            const teachingMetricsCtx = document.getElementById('teachingMetricsChart');
            if (teachingMetricsCtx) {
                const deptLabels = {!! json_encode($departmentStats->pluck('department_acro')) !!};
                const subjectData = {!! json_encode($departmentStats->pluck('total_subjects')) !!};
                const studentData = {!! json_encode($departmentStats->pluck('total_students')) !!};
                const facultyData = {!! json_encode($departmentStats->pluck('faculty_count')) !!};
                
                new Chart(teachingMetricsCtx, {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [
                            {
                                label: 'Total Subjects',
                                data: subjectData,
                                backgroundColor: '#009539',
                                borderColor: '#009539',
                                borderWidth: 1,
                                borderRadius: 5,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Faculty Count',
                                data: facultyData,
                                backgroundColor: '#2c7be5',
                                borderColor: '#2c7be5',
                                borderWidth: 1,
                                borderRadius: 5,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Total Students',
                                data: studentData,
                                type: 'line',
                                backgroundColor: 'rgba(155, 89, 182, 0.1)',
                                borderColor: '#9b59b6',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                yAxisID: 'y1'
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
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Subjects / Faculty Count',
                                    color: '#009539',
                                    font: { family: "'Bricolage Grotesque', sans-serif", weight: '600', size: 12 }
                                },
                                grid: { drawBorder: false, color: '#e0e0e0' },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif" },
                                    color: '#666'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Students',
                                    color: '#9b59b6',
                                    font: { family: "'Bricolage Grotesque', sans-serif", weight: '600', size: 12 }
                                },
                                grid: { drawOnChartArea: false },
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
            // FILTER HANDLERS
            // ============================================
            document.querySelectorAll('#semesterFilter, #departmentFilter')
                .forEach(select => {
                    select.addEventListener('change', function() {
                        applyFilters();
                    });
                });
        });

        // Function to apply filters - FIXED: removed undefined 'faculty' variable
        function applyFilters() {
            const params = new URLSearchParams();
            
            const semester = document.getElementById('semesterFilter').value;
            const department = document.getElementById('departmentFilter').value;
            
            if (semester !== 'all') params.append('semester', semester);
            if (department !== 'all') params.append('department', department);
            
            const url = new URL(window.location.href);
            url.search = params.toString();
            window.location.href = url.toString();
        }

        // Function to clear filters
        function clearFilters() {
            window.location.href = '{{ route('stzfaculty.teaching-load') }}';
        }
    </script>
</body>
</html>