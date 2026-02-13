<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Faculty Qualifications</title>
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
            padding: 25px 30px;
            font-size: 42px;
            font-weight: bold;
        }
        .content {
            margin-left: 210px;
        }

        .filter-bar {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 20px;
            background: #d4d9d4;
            padding: 16px 30px;
            border-bottom: 1px solid #e0e0e0;
        }

        .filter-bar-label {
            font-size: 16px;
            font-weight: 700;
            color: #1f1f1f;
            white-space: nowrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #1f1f1f;
            white-space: nowrap;
        }

        .filter-group select {
            font-size: 14px;
            padding: 6px 32px 6px 12px;
            border-radius: 8px;
            border: 1px solid #999;
            background-color: #ffffff;
            color: #1f1f1f;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%231f1f1f' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }

        .filter-group select:focus {
            outline: none;
            border-color: #009539;
        }

        /* Statistics Cards Styles */
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

        .charts-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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

        .clear-filters-btn {
            background: #009539;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-left: auto;
        }

        .clear-filters-btn:hover {
            background: #0f8f3a;
        }

        .full-width-chart {
            grid-column: 1 / -1;
        }
        
        .badge-degree {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-doctorate {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        .badge-masters {
            background-color: #e8f5e8;
            color: #2e7d32;
        }
        
        .badge-bachelors {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .badge-yes {
            background-color: #e8f5e8;
            color: #2e7d32;
        }
        
        .badge-no {
            background-color: #ffebee;
            color: #c62828;
        }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">
        <div class="header">Faculty Qualifications</div>
        
        <!-- Filters Section -->
        <div class="filter-bar">
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
            
            <div class="filter-group">
                <label>Rank:</label>
                <select id="rankFilter">
                    <option value="all">All</option>
                    @foreach($facultyRanks as $rank)
                        <option value="{{ $rank->generic_faculty_rank }}" {{ $filters['rank'] == $rank->generic_faculty_rank ? 'selected' : '' }}>
                            {{ $rank->generic_faculty_rank }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <button class="clear-filters-btn" onclick="clearFilters()">
                Clear Filters
            </button>
        </div>

<!-- Qualifications Statistics -->
<div class="stats-container">
    <div class="stat-card green">
        <div class="icon-box">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $highestDegrees->phd_count ?? 0 }}</div>
            <div class="stat-label">Doctorate Holders</div>
        </div>
    </div>

    <div class="stat-card blue">
        <div class="icon-box">
            <i class="bi bi-award-fill"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $highestDegrees->masters_count ?? 0 }}</div>
            <div class="stat-label">Master's Holders</div>
        </div>
    </div>

    <div class="stat-card orange">
        <div class="icon-box">
            <i class="bi bi-file-text-fill"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $thesisExperience->with_thesis ?? 0 }}</div>
            <div class="stat-label">Thesis Experience</div>
        </div>
    </div>

    <div class="stat-card purple">
        <div class="icon-box">
            <i class="bi bi-globe-americas"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number">{{ $internationalEducation->international_count ?? 0 }}</div>
            <div class="stat-label">International Education</div>
        </div>
    </div>
</div>

        <!-- Charts Section -->
        <div class="charts-container">
            <!-- Chart 1: Highest Degree Distribution -->
            <div class="chart-card">
                <div class="chart-title">
                    Highest Degree Distribution
                </div>
                <div class="chart-wrapper">
                    <canvas id="highestDegreeChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: PhD Percentage by Department -->
            <div class="chart-card">
                <div class="chart-title">
                    PhD Percentage by Department
                </div>
                <div class="chart-wrapper">
                    <canvas id="phdByDepartmentChart"></canvas>
                </div>
            </div>

            <!-- Chart 3: Qualification by Faculty Rank -->
            <div class="chart-card full-width-chart">
                <div class="chart-title">
                    Qualification by Faculty Rank
                </div>
                <div class="chart-wrapper">
                    <canvas id="qualificationByRankChart"></canvas>
                </div>
            </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
// ============================================
// 1. HIGHEST DEGREE DISTRIBUTION (Donut Chart)
// ============================================
const highestDegreeCtx = document.getElementById('highestDegreeChart');
if (highestDegreeCtx) {
    const degreeData = @json($highestDegreeDistribution);
    
    // Ensure we have data
    const dataValues = [
        degreeData?.doctorate || 0,
        degreeData?.masters || 0,
        degreeData?.bachelors || 0,
        degreeData?.no_degree || 0
    ];
    
    // Only create chart if we have data
    if (dataValues.some(value => value > 0)) {
        new Chart(highestDegreeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Doctorate', 'Master\'s', 'Bachelor\'s', 'No Degree'],
                datasets: [{
                    data: dataValues,
                    backgroundColor: ['#1565c0', '#2e7d32', '#ef6c00', '#757575'],
                    borderColor: 'white',
                    borderWidth: 3,
                    hoverOffset: 15
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
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    } else {
        // Display "No data" message
        highestDegreeCtx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%;"><p>No data available</p></div>';
    }
}
            // ============================================
            // 2. PHD PERCENTAGE BY DEPARTMENT (Bar Chart)
            // ============================================
            const phdByDeptCtx = document.getElementById('phdByDepartmentChart');
            if (phdByDeptCtx) {
                const deptLabels = {!! json_encode($degreeByDepartment->pluck('department_acro')) !!};
                const phdPercentages = {!! json_encode($degreeByDepartment->pluck('phd_percentage')) !!};
                const facultyCounts = {!! json_encode($degreeByDepartment->pluck('total_faculty')) !!};
                
                // Color code based on PhD percentage
                const backgroundColors = phdPercentages.map(percentage => {
                    if (percentage > 50) return '#1565c0'; // High - Blue
                    if (percentage > 30) return '#2e7d32'; // Medium-high - Green
                    if (percentage > 15) return '#f6c343'; // Medium - Orange
                    return '#e74c3c'; // Low - Red
                });
                
                new Chart(phdByDeptCtx, {
                    type: 'bar',
                    data: {
                        labels: deptLabels,
                        datasets: [{
                            label: 'PhD Percentage (%)',
                            data: phdPercentages,
                            backgroundColor: backgroundColors,
                            borderColor: backgroundColors,
                            borderWidth: 1,
                            borderRadius: 8,
                            yAxisID: 'y'
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
                                    afterLabel: function(context) {
                                        const index = context.dataIndex;
                                        return `Total Faculty: ${facultyCounts[index]}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                grid: { drawBorder: false, color: '#e0e0e0' },
                                ticks: {
                                    font: { family: "'Bricolage Grotesque', sans-serif" },
                                    color: '#666',
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'PhD Percentage',
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
            // 3. QUALIFICATION BY FACULTY RANK (Grouped Bar Chart)
            // ============================================
            const qualificationByRankCtx = document.getElementById('qualificationByRankChart');
            if (qualificationByRankCtx) {
                const ranks = {!! json_encode($qualificationByRank->pluck('faculty_rank')) !!};
                const totalFaculty = {!! json_encode($qualificationByRank->pluck('total_faculty')) !!};
                const phdCounts = {!! json_encode($qualificationByRank->pluck('phd_count')) !!};
                const mastersCounts = {!! json_encode($qualificationByRank->pluck('masters_count')) !!};
                
                new Chart(qualificationByRankCtx, {
                    type: 'bar',
                    data: {
                        labels: ranks,
                        datasets: [
                            {
                                label: 'Total Faculty',
                                data: totalFaculty,
                                backgroundColor: '#757575',
                                borderColor: '#757575',
                                borderWidth: 1,
                                borderRadius: 5
                            },
                            {
                                label: 'PhD Holders',
                                data: phdCounts,
                                backgroundColor: '#1565c0',
                                borderColor: '#1565c0',
                                borderWidth: 1,
                                borderRadius: 5
                            },
                            {
                                label: 'Master\'s Holders',
                                data: mastersCounts,
                                backgroundColor: '#2e7d32',
                                borderColor: '#2e7d32',
                                borderWidth: 1,
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
                                bodyFont: { family: "'Bricolage Grotesque', sans-serif" },
                                callbacks: {
                                    afterBody: function(context) {
                                        const index = context[0].dataIndex;
                                        const phdPercentage = ((phdCounts[index] / totalFaculty[index]) * 100).toFixed(1);
                                        return `PhD Percentage: ${phdPercentage}%`;
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
                                    text: 'Number of Faculty',
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
            // FILTER HANDLERS
            // ============================================
            document.querySelectorAll('#semesterFilter, #collegeFilter, #departmentFilter, #rankFilter')
                .forEach(select => {
                    select.addEventListener('change', function() {
                        applyFilters();
                    });
                });
        });

        // Function to apply filters
        function applyFilters() {
            const params = new URLSearchParams();
            
            const semester = document.getElementById('semesterFilter').value;
            const college = document.getElementById('collegeFilter').value;
            const department = document.getElementById('departmentFilter').value;
            const rank = document.getElementById('rankFilter').value;
            
            if (semester !== 'all') params.append('semester', semester);
            if (college !== 'all') params.append('college', college);
            if (department !== 'all') params.append('department', department);
            if (rank !== 'all') params.append('rank', rank);
            
            const url = new URL(window.location.href);
            url.search = params.toString();
            window.location.href = url.toString();
        }

        // Function to clear filters
        function clearFilters() {
            window.location.href = '{{ route('stzfaculty.qualifications') }}';
        }
    </script>
</body>
</html>