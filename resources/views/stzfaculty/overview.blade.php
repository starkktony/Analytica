<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Faculty Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.27.1.min.js"></script>
    
    <style>
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* =====================================================
           CONTENT AREA — shifts with sidebar collapse/expand
        ===================================================== */
        .content {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
            max-width: calc(100vw - 250px);
            overflow-x: hidden;
        }
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        .header {
            background: #009539;
            color: white;
            padding: 5px 30px;
            font-size: 42px;
            font-weight: bold;
            height: 75px;
            font-family: 'Inter', sans-serif;
        }

        /* Filter Bar */
        .filter-bar {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 14px 30px;
            border-bottom: 1px solid #b0b5b0;
            height: 40px;
            width: 100%;
            box-sizing: border-box;
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
        .clear-filters-btn:hover { background: #00802e; }

        /* Statistics Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 30px;
            width: 100%;
            box-sizing: border-box;
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
        .stat-card.green { background: #009539; color: white; }
        .stat-card.green .icon-box {
            background: white; width: 50px; height: 50px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 15px; left: 15px;
        }
        .stat-card.green .icon-box i { font-size: 22px; color: #009539; }
        .stat-card.green .stat-content { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; flex: 1; }
        .stat-card.green .stat-number { font-size: 48px; font-weight: 700; color: white; line-height: 1; }
        .stat-card.green .stat-label  { font-size: 13px; color: white; font-weight: 600; margin-top: 4px; }
        .stat-card.blue, .stat-card.orange, .stat-card.purple { background: white; color: #1f1f1f; }
        .stat-card.blue .icon-box, .stat-card.orange .icon-box, .stat-card.purple .icon-box {
            background: #009539; width: 50px; height: 50px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 15px; left: 15px;
        }
        .stat-card.blue .icon-box i, .stat-card.orange .icon-box i, .stat-card.purple .icon-box i { font-size: 22px; color: white; }
        .stat-card.blue .stat-content, .stat-card.orange .stat-content, .stat-card.purple .stat-content {
            display: flex; flex-direction: column; align-items: flex-end; justify-content: center; flex: 1;
        }
        .stat-number { font-size: 48px; font-weight: 700; color: #1f1f1f; line-height: 1; }
        .stat-label  { font-size: 13px; color: #666; font-weight: 600; margin-top: 4px; }

        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 0 30px 30px 30px;
            width: 100%;
            box-sizing: border-box;
        }
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            width: 100%;
            box-sizing: border-box;
            overflow: hidden;
        }
        .chart-card.full-width { grid-column: 1 / -1; }
        .chart-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f1f1f;
            margin-bottom: 15px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 40px;
        }
        .chart-wrapper {
            height: 350px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .chart-wrapper > div { width: 100%; height: 100%; }

        @media (max-width: 1400px) {
            .stats-container { grid-template-columns: repeat(2, 1fr); }
            .charts-container { grid-template-columns: 1fr; }
            .chart-card.full-width { grid-column: 1; }
            .chart-title { font-size: 16px; white-space: normal; padding-right: 0; }
        }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">
        <div class="header" id="pageHeader">
            @php
                $selectedSemesterObj = $semesters->firstWhere('sem_id', $filters['semester']);
                $semesterDisplay = $selectedSemesterObj ? $selectedSemesterObj->semester . ' ' . $selectedSemesterObj->sy : '';
                $unitDisplay = 'All';
                $deptDisplay = '';
                if ($filters['college'] != 'all') {
                    $selectedUnit = $colleges->firstWhere('c_u_id', $filters['college']);
                    $unitDisplay = $selectedUnit ? $selectedUnit->college_acro : 'Unknown';
                }
                if ($filters['department'] != 'all') {
                    $selectedDept = $departments->firstWhere('department_id', $filters['department']);
                    $deptDisplay = $selectedDept ? $selectedDept->department_acro : '';
                }
                if ($filters['sector'] == 'Academic') {
                    if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                        echo $deptDisplay . ' Faculty Profile (' . $semesterDisplay . ')';
                    } elseif ($filters['college'] != 'all') {
                        echo $unitDisplay . ' Faculty Profile (' . $semesterDisplay . ')';
                    } else {
                        echo 'Academic Faculty Profile (' . $semesterDisplay . ')';
                    }
                } else {
                    if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                        echo $deptDisplay . ' Faculty Profile (' . $semesterDisplay . ')';
                    } elseif ($filters['college'] != 'all') {
                        echo $unitDisplay . ' Faculty Profile (' . $semesterDisplay . ')';
                    } else {
                        echo $filters['sector'] . ' Faculty Profile (' . $semesterDisplay . ')';
                    }
                }
            @endphp
        </div>

        <!-- FILTER BAR -->
        <div class="filter-bar">
            <div class="page-title">FACULTY PROFILE</div>
            <div class="filter-bar-label">Filters:</div>

            <div class="filter-group">
                <label>Sector:</label>
                <select id="sectorFilter" onchange="toggleDepartmentFilter()">
                    <option value="Academic" {{ $filters['sector'] == 'Academic' ? 'selected' : '' }}>Academic</option>
                    <option value="Research" {{ $filters['sector'] == 'Research' ? 'selected' : '' }}>Research</option>
                    <option value="Admin"    {{ $filters['sector'] == 'Admin'    ? 'selected' : '' }}>Admin</option>
                    <option value="Others"   {{ $filters['sector'] == 'Others'   ? 'selected' : '' }}>Others</option>
                </select>
            </div>

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
                <label>Unit/Office:</label>
                <select id="collegeFilter" onchange="updateDepartments()">
                    <option value="all">All</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->c_u_id }}" {{ $filters['college'] == $college->c_u_id ? 'selected' : '' }}>
                            {{ $college->college_acro }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group" id="departmentFilterGroup" style="{{ $filters['sector'] == 'Academic' && $filters['college'] != 'all' ? '' : 'display: none;' }}">
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

            <button class="clear-filters-btn" onclick="clearFilters()">Clear Filters</button>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container">
            <div class="stat-card green">
                <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalFaculty }}</div>
                    <div class="stat-label">Total Faculty</div>
                </div>
            </div>
            <div class="stat-card blue">
                <div class="icon-box"><i class="bi bi-person-check-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $activeCount }}</div>
                    <div class="stat-label">Active Faculty</div>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="icon-box"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $phdHolders }}</div>
                    <div class="stat-label">PhD Holders</div>
                </div>
            </div>
            <div class="stat-card purple">
                <div class="icon-box"><i class="bi bi-book-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $mastersHolders }}</div>
                    <div class="stat-label">Masters Holders</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">
            <div class="chart-card">
                <div class="chart-title" id="rankingTitle">
                    @php
                        if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                            $deptName = $departments->where('department_id', $filters['department'])->first();
                            echo $deptName->department_acro . ' Faculty Count';
                        } elseif ($filters['college'] != 'all') {
                            $collegeName = $colleges->where('c_u_id', $filters['college'])->first();
                            echo $collegeName->college_acro . ' Faculty Count';
                        } else {
                            echo 'Ranking of Faculty Count by College/Department';
                        }
                    @endphp
                </div>
                <div class="chart-wrapper"><div id="facultyRankingChart"></div></div>
            </div>

            <div class="chart-card">
                <div class="chart-title" id="employmentTitle">
                    @php
                        if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                            $deptName = $departments->where('department_id', $filters['department'])->first();
                            echo 'Faculty Employment Status - ' . $deptName->department;
                        } elseif ($filters['college'] != 'all') {
                            $collegeName = $colleges->where('c_u_id', $filters['college'])->first();
                            echo 'Faculty Employment Status - ' . $collegeName->college_unit;
                        } else {
                            echo 'Faculty Employment Status';
                        }
                    @endphp
                </div>
                <div class="chart-wrapper"><div id="employmentChart"></div></div>
            </div>

            <div class="chart-card">
                <div class="chart-title" id="statusTitle">
                    @php
                        if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                            $deptName = $departments->where('department_id', $filters['department'])->first();
                            echo 'Faculty Availability - ' . $deptName->department;
                        } elseif ($filters['college'] != 'all') {
                            $collegeName = $colleges->where('c_u_id', $filters['college'])->first();
                            echo 'Faculty Availability - ' . $collegeName->college_unit;
                        } else {
                            echo 'Faculty Availability Status';
                        }
                    @endphp
                </div>
                <div class="chart-wrapper"><div id="statusChart"></div></div>
            </div>

            <div class="chart-card">
                <div class="chart-title" id="sectorTitle">
                    @php
                        if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                            $deptName = $departments->where('department_id', $filters['department'])->first();
                            echo 'Faculty by Sector - ' . $deptName->department;
                        } elseif ($filters['college'] != 'all') {
                            $collegeName = $colleges->where('c_u_id', $filters['college'])->first();
                            echo 'Faculty by Sector - ' . $collegeName->college_unit;
                        } else {
                            echo 'Faculty by Sector Distribution';
                        }
                    @endphp
                </div>
                <div class="chart-wrapper"><div id="sectorChart"></div></div>
            </div>

            <div class="chart-card full-width">
                <div class="chart-title" id="qualificationTitle">
                    @php
                        if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                            $deptName = $departments->where('department_id', $filters['department'])->first();
                            echo 'Faculty Qualifications - ' . $deptName->department;
                        } elseif ($filters['college'] != 'all') {
                            $collegeName = $colleges->where('c_u_id', $filters['college'])->first();
                            echo 'Faculty Qualifications - ' . $collegeName->college_unit;
                        } else {
                            echo 'Faculty Qualification Distribution';
                        }
                    @endphp
                </div>
                <div class="chart-wrapper"><div id="qualificationChart"></div></div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ============================================
    // Reflow all Plotly charts (called on sidebar toggle)
    // ============================================
    function reflowCharts() {
        const charts = ['facultyRankingChart', 'employmentChart', 'statusChart', 'sectorChart', 'qualificationChart'];
        charts.forEach(function(id) {
            const div = document.getElementById(id);
            if (div && div.data) {
                Plotly.relayout(div, { autosize: true });
            }
        });
    }

    // Listen for sidebar toggle and reflow after the CSS transition ends (300ms)
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
            setTimeout(reflowCharts, 320);
        });
    }

    // ============================================
    // Header title updater
    // ============================================
    function updateHeaderTitle() {
        const sector = document.getElementById('sectorFilter').value;
        const semester = document.getElementById('semesterFilter');
        const semesterText = semester.options[semester.selectedIndex].text;
        const college = document.getElementById('collegeFilter');
        const collegeText = college.options[college.selectedIndex].text;
        const department = document.getElementById('departmentFilter');
        const departmentText = department ? department.options[department.selectedIndex].text : 'All';
        const header = document.getElementById('pageHeader');
        let title = '';
        if (sector === 'Academic') {
            if (college.value !== 'all' && department && department.value !== 'all') {
                title = departmentText + ' Faculty Profile (' + semesterText + ')';
            } else if (college.value !== 'all') {
                title = collegeText + ' Faculty Profile (' + semesterText + ')';
            } else {
                title = 'Academic Faculty Profile (' + semesterText + ')';
            }
        } else {
            if (college.value !== 'all' && department && department.value !== 'all') {
                title = departmentText + ' Faculty Profile (' + semesterText + ')';
            } else if (college.value !== 'all') {
                title = collegeText + ' Faculty Profile (' + semesterText + ')';
            } else {
                title = sector + ' Faculty Profile (' + semesterText + ')';
            }
        }
        header.textContent = title;
    }

    function applyFilters() {
        updateHeaderTitle();
        const params = new URLSearchParams();
        const sector     = document.getElementById('sectorFilter').value;
        const semester   = document.getElementById('semesterFilter').value;
        const college    = document.getElementById('collegeFilter').value;
        const department = document.getElementById('departmentFilter')?.value || 'all';
        if (sector)              params.append('sector',     sector);
        if (semester !== 'all')  params.append('semester',   semester);
        if (college  !== 'all')  params.append('college',    college);
        if (department !== 'all') params.append('department', department);
        const url = new URL(window.location.href);
        url.search = params.toString();
        window.location.href = url.toString();
    }

    function toggleDepartmentFilter() {
        const sector   = document.getElementById('sectorFilter').value;
        const college  = document.getElementById('collegeFilter').value;
        const deptGroup = document.getElementById('departmentFilterGroup');
        deptGroup.style.display = (sector === 'Academic' && college !== 'all') ? 'flex' : 'none';
        updateHeaderTitle();
    }

    document.getElementById('sectorFilter').addEventListener('change', function() { toggleDepartmentFilter(); updateHeaderTitle(); });
    document.getElementById('semesterFilter').addEventListener('change', function() { updateHeaderTitle(); });
    document.getElementById('collegeFilter').addEventListener('change', function() { updateDepartments(); updateHeaderTitle(); });
    if (document.getElementById('departmentFilter')) {
        document.getElementById('departmentFilter').addEventListener('change', function() { updateHeaderTitle(); });
    }

    // ============================================
    // 1. FACULTY COUNT RANKING CHART (Horizontal Bar)
    // ============================================
    const rankingDiv = document.getElementById('facultyRankingChart');
    if (rankingDiv) {
        rankingDiv.innerHTML = '';
        @php
            $rankingLabels  = [];
            $rankingData    = [];
            $highlightIndex = -1;
            if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                foreach($phdByDepartment as $index => $dept) {
                    $rankingLabels[] = $dept->department_acro;
                    $rankingData[]   = $dept->total_faculty;
                    if ($dept->department_acro == ($filters['department_acro'] ?? '')) { $highlightIndex = $index; }
                }
            } elseif ($filters['college'] != 'all') {
                foreach($phdByDepartment as $dept) {
                    $rankingLabels[] = $dept->department_acro;
                    $rankingData[]   = $dept->total_faculty;
                }
            } else {
                $rankingLabels = $collegeStats->pluck('college')->toArray();
                $rankingData   = $collegeStats->pluck('total_faculty')->toArray();
            }
        @endphp

        let barColors = new Array({{ count($rankingLabels) }}).fill('#009539');
        @if($highlightIndex >= 0)
            barColors[{{ $highlightIndex }}] = '#FFA500';
        @endif

        const rankingData = [{
            x: {!! json_encode($rankingData) !!},
            y: {!! json_encode($rankingLabels) !!},
            type: 'bar',
            orientation: 'h',
            marker: { color: barColors, line: { color: 'rgba(0,0,0,0.1)', width: 1 } },
            text: {!! json_encode($rankingData) !!},
            textposition: 'outside',
            textfont: { family: 'Inter', size: 11, color: '#1f1f1f' },
            hoverinfo: 'x+name',
            hovertemplate: '<b>%{y}</b><br>Faculty Count: %{x}<extra></extra>'
        }];

        const rankingLayout = {
            font: { family: 'Inter' },
            autosize: true,
            margin: { l: 80, r: 30, t: 30, b: 40 },
            xaxis: {
                title: { text: 'Number of Faculty', font: { family: 'Inter', size: 11, weight: 600 } },
                gridcolor: '#e0e0e0', zeroline: false,
                tickfont: { family: 'Inter', size: 10, color: '#666' }
            },
            yaxis: {
                gridcolor: 'transparent',
                tickfont: { family: 'Inter', size: 11, weight: 600, color: '#1f1f1f' },
                autorange: 'reversed'
            },
            paper_bgcolor: 'white', plot_bgcolor: 'white', showlegend: false, bargap: 0.3
        };

        Plotly.newPlot(rankingDiv, rankingData, rankingLayout, {
            responsive: true, displaylogo: false,
            modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d'],
            toImageButtonOptions: { format: 'png', filename: 'faculty_ranking', height: 500, width: 700, scale: 1 }
        });
    }

    // ============================================
    // 2. EMPLOYMENT STATUS (Donut with center total)
    // ============================================
    const empDiv = document.getElementById('employmentChart');
    if (empDiv) {
        empDiv.innerHTML = '';
        const categoryLabels = {!! json_encode($categories->pluck('category')) !!};
        const categoryData   = {!! json_encode($categories->pluck('count')) !!};
        const totalFaculty   = {{ $totalFaculty }};
        const nonZeroData = [], nonZeroLabels = [], nonZeroColors = [];
        const colorMap = ['#009539', '#2c7be5', '#f6c343', '#e74c3c'];
        for (let i = 0; i < categoryData.length; i++) {
            if (categoryData[i] > 0) {
                nonZeroData.push(categoryData[i]);
                nonZeroLabels.push(categoryLabels[i]);
                nonZeroColors.push(colorMap[i % colorMap.length]);
            }
        }
        const empData = [{
            values: nonZeroData, labels: nonZeroLabels, type: 'pie', hole: 0.7,
            marker: { colors: nonZeroColors },
            textinfo: 'percent', textposition: 'outside',
            textfont: { family: 'Inter', size: 11, color: '#1f1f1f' },
            hovertemplate: '<b>%{label}</b><br>Count: %{value}<extra></extra>',
            showlegend: true, direction: 'clockwise', rotation: 0, sort: false
        }];
        const empLayout = {
            font: { family: 'Inter' }, autosize: true,
            margin: { l: 10, r: 10, t: 40, b: 10 },
            annotations: [{ text: `${totalFaculty}<br><span style="font-size:11px;color:#666;">Total</span>`, x: 0.5, y: 0.5, showarrow: false, font: { family: 'Inter', size: 20, weight: 700, color: '#1f1f1f' } }],
            paper_bgcolor: 'white', plot_bgcolor: 'white', showlegend: true,
            legend: { orientation: 'h', y: 1.15, x: 0.1, xanchor: 'left', font: { family: 'Inter', size: 11 }, itemclick: false, itemdoubleclick: false }
        };
        Plotly.newPlot(empDiv, empData, empLayout, {
            responsive: true, displaylogo: false,
            modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d'],
            toImageButtonOptions: { format: 'png', filename: 'employment_status', height: 500, width: 700, scale: 1 }
        });
    }

    // ============================================
    // 3. FACULTY STATUS (Donut with center total)
    // ============================================
    const statusDiv = document.getElementById('statusChart');
    if (statusDiv) {
        statusDiv.innerHTML = '';
        const activeCount  = {{ $activeCount }};
        const onLeaveCount = {{ $onLeaveCount }};
        const totalStatusFaculty = activeCount + onLeaveCount;
        const statusData = [{
            values: [activeCount, onLeaveCount].filter(v => v > 0),
            labels: ['Active', 'On Leave'].filter((_, i) => [activeCount, onLeaveCount][i] > 0),
            type: 'pie', hole: 0.7,
            marker: { colors: ['#009539', '#e74c3c'].filter((_, i) => [activeCount, onLeaveCount][i] > 0) },
            textinfo: 'percent', textposition: 'outside',
            textfont: { family: 'Inter', size: 11, color: '#1f1f1f' },
            hovertemplate: '<b>%{label}</b><br>Count: %{value}<extra></extra>',
            showlegend: true
        }];
        const statusLayout = {
            font: { family: 'Inter' }, autosize: true,
            margin: { l: 10, r: 10, t: 40, b: 10 },
            annotations: [{ text: `${totalStatusFaculty}<br><span style="font-size:11px;color:#666;">Total</span>`, x: 0.5, y: 0.5, showarrow: false, font: { family: 'Inter', size: 20, weight: 700, color: '#1f1f1f' } }],
            paper_bgcolor: 'white', plot_bgcolor: 'white', showlegend: true,
            legend: { orientation: 'h', y: 1.15, x: 0.1, xanchor: 'left', font: { family: 'Inter', size: 11 } }
        };
        Plotly.newPlot(statusDiv, statusData, statusLayout, {
            responsive: true, displaylogo: false,
            modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d'],
            toImageButtonOptions: { format: 'png', filename: 'faculty_availability', height: 500, width: 700, scale: 1 }
        });
    }

    // ============================================
    // 4. SECTOR DISTRIBUTION (Donut with center total)
    // ============================================
    const sectorDiv = document.getElementById('sectorChart');
    if (sectorDiv) {
        sectorDiv.innerHTML = '';
        const sectorData   = [{{ $sectorDistribution['Academic'] ?? 0 }}, {{ $sectorDistribution['Research'] ?? 0 }}, {{ $sectorDistribution['Admin'] ?? 0 }}, {{ $sectorDistribution['Others'] ?? 0 }}];
        const sectorLabels = ['Academic', 'Research', 'Admin', 'Others'];
        const totalSector  = {{ $totalSectorFaculty }};
        const sectorColors = ['#009539', '#2c7be5', '#f6c343', '#e74c3c'];
        const nonZeroSectorData = [], nonZeroSectorLabels = [], nonZeroSectorColors = [];
        for (let i = 0; i < sectorData.length; i++) {
            if (sectorData[i] > 0) { nonZeroSectorData.push(sectorData[i]); nonZeroSectorLabels.push(sectorLabels[i]); nonZeroSectorColors.push(sectorColors[i]); }
        }
        const sectorPlotData = [{
            values: nonZeroSectorData, labels: nonZeroSectorLabels, type: 'pie', hole: 0.7,
            marker: { colors: nonZeroSectorColors },
            textinfo: 'percent', textposition: 'outside',
            textfont: { family: 'Inter', size: 11, color: '#1f1f1f' },
            hovertemplate: '<b>%{label}</b><br>Count: %{value}<extra></extra>',
            showlegend: true
        }];
        const sectorLayout = {
            font: { family: 'Inter' }, autosize: true,
            margin: { l: 10, r: 10, t: 40, b: 10 },
            annotations: [{ text: `${totalSector}<br><span style="font-size:11px;color:#666;">Total</span>`, x: 0.5, y: 0.5, showarrow: false, font: { family: 'Inter', size: 20, weight: 700, color: '#1f1f1f' } }],
            paper_bgcolor: 'white', plot_bgcolor: 'white', showlegend: true,
            legend: { orientation: 'h', y: 1.15, x: 0.1, xanchor: 'left', font: { family: 'Inter', size: 11 } }
        };
        Plotly.newPlot(sectorDiv, sectorPlotData, sectorLayout, {
            responsive: true, displaylogo: false,
            modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d'],
            toImageButtonOptions: { format: 'png', filename: 'sector_distribution', height: 500, width: 700, scale: 1 }
        });
    }

    // ============================================
    // 5. FACULTY QUALIFICATION DISTRIBUTION (Stacked Bar)
    // ============================================
    const qualDiv = document.getElementById('qualificationChart');
    if (qualDiv) {
        qualDiv.innerHTML = '';
        @php
            $qualLabels = []; $phdPercentages = []; $mastersPercentages = []; $bachelorsPercentages = [];
            $phdCounts  = []; $mastersCounts  = []; $bachelorsCounts    = [];
            foreach($phdByDepartment as $dept) {
                $qualLabels[]          = $dept->department_acro;
                $total                 = $dept->total_faculty;
                $phdPercentages[]      = $total > 0 ? round(($dept->phd_count     / $total) * 100, 1) : 0;
                $mastersPercentages[]  = $total > 0 ? round(($dept->masters_count / $total) * 100, 1) : 0;
                $bachelorsPercentages[]= $total > 0 ? round((($total - $dept->phd_count - $dept->masters_count) / $total) * 100, 1) : 0;
                $phdCounts[]      = $dept->phd_count;
                $mastersCounts[]  = $dept->masters_count;
                $bachelorsCounts[]= $dept->bachelors_count;
            }
        @endphp

        const qualData = [
            {
                name: 'PhD',
                x: {!! json_encode($qualLabels) !!}, y: {!! json_encode($phdPercentages) !!},
                type: 'bar', marker: { color: '#1565c0' },
                text: {!! json_encode($phdCounts) !!},
                texttemplate: '%{y}%', textposition: 'inside',
                insidetextfont: { color: 'white', family: 'Inter', size: 10 },
                hovertemplate: '<b>%{x}</b><br>PhD: %{y}% (%{text})<extra></extra>'
            },
            {
                name: 'Masters',
                x: {!! json_encode($qualLabels) !!}, y: {!! json_encode($mastersPercentages) !!},
                type: 'bar', marker: { color: '#009539' },
                text: {!! json_encode($mastersCounts) !!},
                texttemplate: '%{y}%', textposition: 'inside',
                insidetextfont: { color: 'white', family: 'Inter', size: 10 },
                hovertemplate: '<b>%{x}</b><br>Masters: %{y}% (%{text})<extra></extra>'
            },
            {
                name: 'Bachelors',
                x: {!! json_encode($qualLabels) !!}, y: {!! json_encode($bachelorsPercentages) !!},
                type: 'bar', marker: { color: '#f6c343' },
                text: {!! json_encode($bachelorsCounts) !!},
                texttemplate: '%{y}%', textposition: 'inside',
                insidetextfont: { color: 'white', family: 'Inter', size: 10 },
                hovertemplate: '<b>%{x}</b><br>Bachelors: %{y}% (%{text})<extra></extra>'
            }
        ];
        const qualLayout = {
            font: { family: 'Inter' }, autosize: true, barmode: 'stack',
            margin: { l: 50, r: 20, t: 50, b: 80 },
            xaxis: { tickfont: { family: 'Inter', size: 10, weight: 600, color: '#1f1f1f' }, tickangle: -30, gridcolor: 'transparent' },
            yaxis: { title: { text: 'Percentage (%)', font: { family: 'Inter', size: 11, weight: 600 } }, range: [0, 100], tickfont: { family: 'Inter', size: 10, color: '#666' }, gridcolor: '#e0e0e0' },
            paper_bgcolor: 'white', plot_bgcolor: 'white', showlegend: true,
            legend: { orientation: 'h', y: 1.15, x: 0.1, xanchor: 'left', font: { family: 'Inter', size: 11 } }
        };
        Plotly.newPlot(qualDiv, qualData, qualLayout, {
            responsive: true, displaylogo: false,
            modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d'],
            toImageButtonOptions: { format: 'png', filename: 'qualification_distribution', height: 500, width: 900, scale: 1 }
        });
    }

    // Window resize handler
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const charts = ['facultyRankingChart', 'employmentChart', 'statusChart', 'sectorChart', 'qualificationChart'];
            charts.forEach(function(chartId) {
                const div = document.getElementById(chartId);
                if (div && div.data) { Plotly.relayout(div, { autosize: true }); }
            });
        }, 250);
    });

});

// ============================================
// FILTER HANDLERS (outside DOMContentLoaded so they're globally accessible)
// ============================================
function toggleDepartmentFilter() {
    const sector    = document.getElementById('sectorFilter').value;
    const college   = document.getElementById('collegeFilter').value;
    const deptGroup = document.getElementById('departmentFilterGroup');
    deptGroup.style.display = (sector === 'Academic' && college !== 'all') ? 'flex' : 'none';
}

function updateDepartments() {
    const college = document.getElementById('collegeFilter').value;
    const params  = new URLSearchParams(window.location.search);
    params.set('college', college);
    params.delete('department');
    window.location.href = window.location.pathname + '?' + params.toString();
}

function applyFilters() {
    const params     = new URLSearchParams();
    const sector     = document.getElementById('sectorFilter').value;
    const semester   = document.getElementById('semesterFilter').value;
    const college    = document.getElementById('collegeFilter').value;
    const department = document.getElementById('departmentFilter')?.value || 'all';
    if (sector)              params.append('sector',     sector);
    if (semester !== 'all')  params.append('semester',   semester);
    if (college  !== 'all')  params.append('college',    college);
    if (department !== 'all') params.append('department', department);
    const url = new URL(window.location.href);
    url.search = params.toString();
    window.location.href = url.toString();
}

function clearFilters() {
    window.location.href = '{{ route('stzfaculty.overview') }}';
}

document.querySelectorAll('#sectorFilter, #semesterFilter, #collegeFilter, #departmentFilter')
    .forEach(function(select) {
        if (select) { select.addEventListener('change', applyFilters); }
    });
</script>
</body>
</html>