<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Teaching Load</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>

    <style>
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
        }
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        .header {
            background: #009539;
            color: white;
            padding: 0 30px;
            font-size: 36px;
            font-weight: bold;
            height: 75px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .content { margin-left: 210px; }

        /* ── Filter Bar — matches faculty profile design ── */
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

        .page-title {
            font-size: 16px;
            font-weight: 700;
            color: #2d2d2d;
            margin-right: 10px;
        }

        /* "Filters:" pushed to the right */
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

        /* ── Stat Cards ── */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 28px 30px 18px 30px;
        }
        .stat-card {
            border-radius: 15px;
            padding: 20px 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100px;
        }
        .stat-card.green { background: #009539; }
        .stat-card.green .icon-box {
            background: white; width: 48px; height: 48px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 15px; left: 15px;
        }
        .stat-card.green .icon-box i { font-size: 20px; color: #009539; }
        .stat-card.green .stat-content { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; flex: 1; }
        .stat-card.green .stat-number { font-size: 44px; font-weight: 700; color: white; line-height: 1; }
        .stat-card.green .stat-label  { font-size: 12px; color: rgba(255,255,255,0.85); font-weight: 600; margin-top: 4px; }
        .stat-card.white { background: white; }
        .stat-card.white .icon-box {
            background: #009539; width: 48px; height: 48px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 15px; left: 15px;
        }
        .stat-card.white .icon-box i { font-size: 20px; color: white; }
        .stat-card.white .stat-content { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; flex: 1; }
        .stat-number { font-size: 44px; font-weight: 700; color: #1f1f1f; line-height: 1; }
        .stat-label  { font-size: 12px; color: #666; font-weight: 600; margin-top: 4px; }

        /* ── Charts ── */
        .charts-row {
            display: grid;
            gap: 20px;
            padding: 0 30px 20px 30px;
        }
        .charts-row.two-col { grid-template-columns: 1fr 1fr; }
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 24px 24px 14px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
        }
        .chart-title    { font-size: 14px; font-weight: 700; color: #1f1f1f; margin-bottom: 2px; }
        .chart-subtitle { font-size: 11px; color: #bbb; font-weight: 500; margin-bottom: 10px; }

        .empty-chart {
            width: 100%; height: 320px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #ccc; gap: 8px;
        }
        .empty-chart i    { font-size: 36px; }
        .empty-chart span { font-size: 13px; font-weight: 600; }

        /* Header drill badge */
        .drill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.22);
            border: 1px solid rgba(255,255,255,0.45);
            border-radius: 20px;
            padding: 3px 14px 3px 10px;
            font-size: 14px;
            font-weight: 600;
            color: white;
        }
        .drill-badge i { font-size: 13px; }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">

        {{-- Page header --}}
        <div class="header">
            Teaching Load
            @if($drillDown && $selectedCollege)
                <span class="drill-badge">
                    <i class="bi bi-building"></i>
                    {{ $selectedCollege->college_acro }}
                    @if($filters['department'] !== 'all')
                        &rsaquo; {{ $departments->firstWhere('department_id', $filters['department'])?->department_acro }}
                    @endif
                </span>
            @endif
        </div>

        {{-- ── Filter Bar — same design as Faculty Profile ── --}}
        <div class="filter-bar">



            {{-- "Filters:" pushed to right via margin-left: auto --}}
            <div class="filter-bar-label">Filters:</div>

            {{-- Semester --}}
            <div class="filter-group">
                <label>Semester:</label>
                <select id="semesterFilter">
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->sem_id }}"
                            {{ $filters['semester'] == $semester->sem_id ? 'selected' : '' }}>
                            {{ $semester->semester }} {{ $semester->sy }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Unit/Office — ALL units in dropdown --}}
            <div class="filter-group">
                <label>Unit/Office:</label>
                <select id="collegeFilter">
                    <option value="all">All</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->c_u_id }}"
                            {{ $filters['college'] == $college->c_u_id ? 'selected' : '' }}>
                            {{ $college->college_acro }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{--
                Department — only rendered (not just hidden) when a Unit/Office is selected,
                matching the faculty profile pattern where it appears only when needed.
            --}}
            @if($drillDown)
            <div class="filter-group" id="departmentFilterGroup">
                <label>Department:</label>
                <select id="departmentFilter">
                    <option value="all">All</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->department_id }}"
                            {{ $filters['department'] == $dept->department_id ? 'selected' : '' }}>
                            {{ $dept->department_acro }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button class="clear-filters-btn" onclick="clearFilters()">Clear Filters</button>

        </div>

        {{-- Stat Cards --}}
        <div class="stats-container">
            <div class="stat-card green">
                <div class="icon-box"><i class="bi bi-bar-chart-line-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($avgAtl, 1) }}</div>
                    <div class="stat-label">Avg ATL</div>
                </div>
            </div>
            <div class="stat-card white">
                <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalFaculty }}</div>
                    <div class="stat-label">Total Faculty</div>
                </div>
            </div>
            <div class="stat-card white">
                <div class="icon-box"><i class="bi bi-book-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $totalSubjects }}</div>
                    <div class="stat-label">Total Subjects</div>
                </div>
            </div>
            <div class="stat-card white">
                <div class="icon-box"><i class="bi bi-person-video3"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ number_format($totalStudents) }}</div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>

        {{-- Row 1: ATL Ranking + Workload Pie --}}
        <div class="charts-row two-col">
            <div class="chart-card">
                <div class="chart-title">Average ATL Ranking by {{ $chartGroupLabel }}</div>
                <div class="chart-subtitle">
                    Ranked by average actual teaching load (hours)
                    @if(!$drillDown) — select a Unit/Office to see departments @endif
                </div>
                <div id="chart-atl-rank"></div>
            </div>

            <div class="chart-card">
                <div class="chart-title">Faculty Workload Distribution</div>
                <div class="chart-subtitle">Proportion of faculty by ATL load category</div>
                <div id="chart-workload-pie"></div>
            </div>
        </div>

        {{-- Row 2: Subjects + Students --}}
        <div class="charts-row two-col">
            <div class="chart-card">
                <div class="chart-title">Subjects Offered by {{ $chartGroupLabel }}</div>
                <div class="chart-subtitle">Total distinct subjects offered this semester</div>
                <div id="chart-subjects"></div>
            </div>

            <div class="chart-card">
                <div class="chart-title">Total Students Enrolled by {{ $chartGroupLabel }}</div>
                <div class="chart-subtitle">Total enrolled students this semester</div>
                <div id="chart-students"></div>
            </div>
        </div>

    </div><!-- /.content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    const chartStats   = {!! json_encode($chartStats) !!};
    const workloadDist = {!! json_encode($workloadDistribution) !!};

    const FONT  = { family: "'Bricolage Grotesque', sans-serif", size: 12, color: '#444' };
    const GREEN = '#009539';
    const BLUE  = '#2c7be5';
    const cfg   = { responsive: true, displayModeBar: false };
    const CHART_H = 320;

    function showEmpty(id) {
        document.getElementById(id).innerHTML =
            `<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>`;
    }
    function setH(id) {
        document.getElementById(id).style.height = CHART_H + 'px';
    }

    // ── CHART 1: Horizontal Bar — ATL Ranking ────────────────────────────────
    (function () {
        const data = [...chartStats]
            .filter(d => parseFloat(d.avg_atl || 0) > 0)
            .sort((a, b) => a.avg_atl - b.avg_atl);

        if (!data.length) { showEmpty('chart-atl-rank'); return; }
        setH('chart-atl-rank');

        Plotly.newPlot('chart-atl-rank', [{
            type: 'bar', orientation: 'h',
            x: data.map(d => parseFloat(d.avg_atl).toFixed(1)),
            y: data.map(d => d.group_label),
            text: data.map(d => `${parseFloat(d.avg_atl).toFixed(1)} hrs`),
            textposition: 'outside',
            textfont: { size: 11, color: '#333' },
            marker: { color: GREEN },
            hovertemplate: '<b>%{y}</b><br>Avg ATL: %{x} hrs<extra></extra>',
        }], {
            font: FONT, paper_bgcolor: 'white', plot_bgcolor: 'white',
            margin: { t: 24, r: 80, b: 40, l: 80 },
            xaxis: { title: { text: 'ATL (hours)', font: { size: 11 } }, gridcolor: '#efefef', zeroline: false },
            yaxis: { tickfont: { size: 11 }, automargin: true },
        }, cfg);
    })();

    // ── CHART 2: Pie — Workload Distribution ─────────────────────────────────
    (function () {
        const raw = [
            { label: 'Low (<10 hrs)',    value: workloadDist.low       || 0, color: BLUE },
            { label: 'Moderate (10–15)', value: workloadDist.moderate  || 0, color: GREEN },
            { label: 'High (15–20)',     value: workloadDist.high      || 0, color: '#f6c343' },
            { label: 'Very High (>20)',  value: workloadDist.very_high || 0, color: '#e74c3c' },
        ].filter(d => d.value > 0);

        if (!raw.length) { showEmpty('chart-workload-pie'); return; }
        setH('chart-workload-pie');

        Plotly.newPlot('chart-workload-pie', [{
            type: 'pie',
            labels: raw.map(d => d.label),
            values: raw.map(d => d.value),
            marker: { colors: raw.map(d => d.color) },
            textinfo: 'percent',
            textfont: { size: 12, color: 'white' },
            hovertemplate: '<b>%{label}</b><br>%{value} faculty (%{percent})<extra></extra>',
        }], {
            font: FONT, paper_bgcolor: 'white',
            margin: { t: 10, r: 140, b: 10, l: 10 },
            legend: { orientation: 'v', x: 1.02, y: 0.5, xanchor: 'left', font: { size: 11 } },
        }, cfg);
    })();

    // ── CHART 3: Vertical Bar — Subjects ─────────────────────────────────────
    (function () {
        const data = [...chartStats]
            .filter(d => parseInt(d.total_subjects || 0) > 0)
            .sort((a, b) => b.total_subjects - a.total_subjects);

        if (!data.length) { showEmpty('chart-subjects'); return; }
        setH('chart-subjects');

        Plotly.newPlot('chart-subjects', [{
            type: 'bar',
            x: data.map(d => d.group_label),
            y: data.map(d => parseInt(d.total_subjects)),
            text: data.map(d => parseInt(d.total_subjects)),
            textposition: 'outside',
            textfont: { size: 11, color: '#333' },
            marker: { color: GREEN },
            hovertemplate: '<b>%{x}</b><br>Subjects: %{y}<extra></extra>',
        }], {
            font: FONT, paper_bgcolor: 'white', plot_bgcolor: 'white',
            margin: { t: 20, r: 20, b: 60, l: 50 },
            xaxis: { tickangle: -30, tickfont: { size: 11 }, automargin: true },
            yaxis: { title: { text: 'No. of Subjects', font: { size: 11 } }, gridcolor: '#efefef', zeroline: false },
        }, cfg);
    })();

    // ── CHART 4: Horizontal Bar — Students ───────────────────────────────────
    (function () {
        const data = [...chartStats]
            .filter(d => parseInt(d.total_students || 0) > 0)
            .sort((a, b) => a.total_students - b.total_students);

        if (!data.length) { showEmpty('chart-students'); return; }
        setH('chart-students');

        Plotly.newPlot('chart-students', [{
            type: 'bar', orientation: 'h',
            x: data.map(d => parseInt(d.total_students)),
            y: data.map(d => d.group_label),
            text: data.map(d => parseInt(d.total_students).toLocaleString()),
            textposition: 'outside',
            textfont: { size: 11, color: '#333' },
            marker: { color: BLUE },
            hovertemplate: '<b>%{y}</b><br>Students: %{x}<extra></extra>',
        }], {
            font: FONT, paper_bgcolor: 'white', plot_bgcolor: 'white',
            margin: { t: 10, r: 80, b: 40, l: 80 },
            xaxis: { title: { text: 'Number of Students', font: { size: 11 } }, gridcolor: '#efefef', zeroline: false },
            yaxis: { tickfont: { size: 11 }, automargin: true },
        }, cfg);
    })();

    // ── Filter handlers ───────────────────────────────────────────────────────

    // When Unit/Office changes → reload (department select repopulated server-side)
    document.getElementById('collegeFilter').addEventListener('change', function () {
        const params = new URLSearchParams();
        const sem = document.getElementById('semesterFilter').value;
        params.append('semester', sem);
        if (this.value !== 'all') params.append('college', this.value);
        // do NOT carry over department — it resets on college change
        const url = new URL(window.location.href);
        url.search = params.toString();
        window.location.href = url.toString();
    });

    document.getElementById('semesterFilter').addEventListener('change', function () {
        const params  = new URLSearchParams();
        params.append('semester', this.value);
        const college = document.getElementById('collegeFilter').value;
        if (college !== 'all') params.append('college', college);
        @if($drillDown)
        const dept = document.getElementById('departmentFilter').value;
        if (dept !== 'all') params.append('department', dept);
        @endif
        const url = new URL(window.location.href);
        url.search = params.toString();
        window.location.href = url.toString();
    });

    @if($drillDown)
    document.getElementById('departmentFilter').addEventListener('change', function () {
        const params = new URLSearchParams();
        const sem    = document.getElementById('semesterFilter').value;
        const col    = document.getElementById('collegeFilter').value;
        params.append('semester', sem);
        if (col        !== 'all') params.append('college', col);
        if (this.value !== 'all') params.append('department', this.value);
        const url = new URL(window.location.href);
        url.search = params.toString();
        window.location.href = url.toString();
    });
    @endif

    function clearFilters() {
        window.location.href = '{{ route("stzfaculty.teaching-load") }}';
    }

    </script>
</body>
</html>