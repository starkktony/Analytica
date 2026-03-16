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
            gap: 10px;
            background: #c9cec9;
            padding: 0 20px;
            border-bottom: 1px solid #b0b5b0;
            height: 52px;
            min-height: 52px;
            width: 100%;
            box-sizing: border-box;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
        }
        .filter-bar::-webkit-scrollbar { display: none; }
        .filter-bar { -ms-overflow-style: none; scrollbar-width: none; }

        .filter-bar-label {
            font-size: 12px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }
        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #2d2d2d;
            white-space: nowrap;
        }
        .filter-group select {
            font-size: 12px;
            padding: 4px 24px 4px 10px;
            border-radius: 20px;
            border: 1px solid #8a8f8a;
            background-color: #f5f5f5;
            color: #2d2d2d;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%232d2d2d' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 8px;
            min-width: 80px;
            max-width: 160px;
            cursor: pointer;
            flex-shrink: 0;
        }
        .filter-group select:focus {
            outline: none;
            border-color: #009539;
            background-color: white;
        }
        .page-title {
            font-size: 14px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0;
            margin-right: auto;
        }
        .filter-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .clear-filters-btn {
            background: #009539;
            color: white;
            border: none;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
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
            grid-template-columns: 1fr 1fr 1fr;
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
            font-size: 16px;
            font-weight: 700;
            color: #1f1f1f;
            margin-bottom: 15px;
            white-space: normal;
            word-break: break-word;
            line-height: 1.3;
            padding-right: 0;
        }
        .chart-wrapper {
            height: 420px;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .chart-wrapper > div { width: 100%; height: 100%; }

        @media (max-width: 1400px) {
            .stats-container { grid-template-columns: repeat(2, 1fr); }
            .charts-container { grid-template-columns: 1fr 1fr; }
            .chart-card.full-width { grid-column: 1 / -1; }
            .chart-title { font-size: 16px; white-space: normal; padding-right: 0; }
        }
        @media (max-width: 900px) {
            .charts-container { grid-template-columns: 1fr; }
            .chart-card.full-width { grid-column: 1; }
        }

        /* Loading / skeleton */
        .loading-overlay {
            position: absolute; inset: 0;
            background: rgba(255,255,255,0.82);
            border-radius: inherit;
            display: flex; align-items: center; justify-content: center;
            z-index: 10;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .loading-overlay.active { opacity: 1; pointer-events: all; }
        .spinner {
            width: 36px; height: 36px;
            border: 4px solid #d4ead4;
            border-top-color: #009539;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .stat-card.loading .stat-number,
        .stat-card.loading .stat-label {
            background: linear-gradient(90deg,#e0e0e0 25%,#f0f0f0 50%,#e0e0e0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
            color: transparent !important;
            min-width: 60px;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
        .stat-card   { position: relative; }
        .chart-card  { position: relative; }
        .filter-bar.is-loading select,
        .filter-bar.is-loading button { pointer-events: none; opacity: 0.5; }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">
        <div class="header" id="pageHeader">
            FACULTY PROFILE
        </div>

        <!-- FILTER BAR -->
        <div class="filter-bar">

            {{-- Left: dynamic page title --}}
            <div class="page-title" id="dynamicTitle">
                @php
                    $selectedSemesterObj = $semesters->firstWhere('sem_id', $filters['semester']);
                    $semesterDisplay = $selectedSemesterObj ? $selectedSemesterObj->semester . ' ' . $selectedSemesterObj->sy : '';

                    if ($filters['college'] != 'all' && $filters['department'] != 'all') {
                        $selectedDept = $departments->firstWhere('department_id', $filters['department']);
                        $deptDisplay  = $selectedDept ? $selectedDept->department_acro : '';
                        echo $deptDisplay . ' Faculty Profile (' . $semesterDisplay . ')';
                    } elseif ($filters['college'] != 'all') {
                        $selectedUnit = $colleges->firstWhere('c_u_id', $filters['college']);
                        $unitDisplay  = $selectedUnit ? $selectedUnit->college_acro : '';
                        echo $unitDisplay . ' Faculty Profile (' . $semesterDisplay . ')';
                    } else {
                        echo 'Faculty Profile (' . $semesterDisplay . ')';
                    }
                @endphp
            </div>

            {{-- Right: filter controls --}}
            <div class="filter-right">

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
                    <label>Unit/Office:</label>
                    <select id="collegeFilter">
                        <option value="all">All</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->c_u_id }}" {{ $filters['college'] == $college->c_u_id ? 'selected' : '' }}>
                                {{ $college->college_acro }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group" id="departmentFilterGroup"
                     style="{{ $filters['college'] != 'all' ? '' : 'display: none;' }}">
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

            </div>{{-- /.filter-right --}}
        </div>

        <!-- Statistics Cards -->
        <div class="stats-container" id="statsContainer">
            <div class="stat-card green" id="statTotal">
                <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statTotalNum">{{ $totalFaculty }}</div>
                    <div class="stat-label">Total Faculty</div>
                </div>
            </div>
            <div class="stat-card blue" id="statActive">
                <div class="icon-box"><i class="bi bi-person-check-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statActiveNum">{{ $activeCount }}</div>
                    <div class="stat-label">Active Faculty</div>
                </div>
            </div>
            <div class="stat-card orange" id="statPhd">
                <div class="icon-box"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statPhdNum">{{ $phdHolders }}</div>
                    <div class="stat-label">PhD Holders</div>
                </div>
            </div>
            <div class="stat-card purple" id="statMasters">
                <div class="icon-box"><i class="bi bi-book-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statMastersNum">{{ $mastersHolders }}</div>
                    <div class="stat-label">Masters Holders</div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-container">

            {{-- ① Faculty Count Ranking --}}
            <div class="chart-card" id="cardRanking">
                <div class="loading-overlay" id="loadRanking"><div class="spinner"></div></div>
                {{-- CHANGED: title now always says "by College" when no college filter is selected --}}
                <div class="chart-title" id="rankingTitle">
                    @if($filters['college'] != 'all')
                        @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                        {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Count by Department
                    @else
                        Ranking of Faculty Count by College
                    @endif
                </div>
                <div class="chart-wrapper"><div id="facultyRankingChart"></div></div>
            </div>

            {{-- ② Employment Status (Donut) --}}
            <div class="chart-card" id="cardEmployment">
                <div class="loading-overlay" id="loadEmployment"><div class="spinner"></div></div>
                <div class="chart-title" id="employmentTitle">
                    @if($filters['college'] != 'all')
                        @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                        {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Employment Status
                    @else
                        Faculty Employment Status
                    @endif
                </div>
                <div class="chart-wrapper"><div id="employmentChart"></div></div>
            </div>

            {{-- ③ Faculty Availability (Donut) --}}
            <div class="chart-card" id="cardStatus">
                <div class="loading-overlay" id="loadStatus"><div class="spinner"></div></div>
                <div class="chart-title" id="statusTitle">
                    @if($filters['college'] != 'all')
                        @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                        {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Availability Status
                    @else
                        Faculty Availability Status
                    @endif
                </div>
                <div class="chart-wrapper"><div id="statusChart"></div></div>
            </div>

            {{-- ⑤ Faculty Qualification Distribution (Stacked Bar) - full width --}}
            <div class="chart-card full-width" id="cardQual">
                <div class="loading-overlay" id="loadQual"><div class="spinner"></div></div>
                <div class="chart-title" id="qualificationTitle">
                    @if($filters['college'] != 'all')
                        @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                        {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Qualification Distribution
                    @else
                        Faculty Qualification Distribution
                    @endif
                </div>
                <div class="chart-wrapper"><div id="qualificationChart"></div></div>
            </div>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Inline data from server (initial load) ──────────────────────────────────
const INITIAL_DATA = {
    totalFaculty   : {{ $totalFaculty }},
    activeCount    : {{ $activeCount }},
    phdHolders     : {{ $phdHolders }},
    mastersHolders : {{ $mastersHolders }},
    categoryLabels : {!! json_encode($categories->pluck('category')) !!},
    categoryData   : {!! json_encode($categories->pluck('count')) !!},
    onLeaveCount   : {{ $onLeaveCount }},
    rankingLabels  : {!! json_encode($rankingLabels) !!},
    rankingCounts  : {!! json_encode($rankingCounts) !!},
    selectedDept   : @json($selectedDeptAcro),
    qualLabels     : {!! json_encode($qualLabels) !!},
    phdPct         : {!! json_encode($phdPercentages) !!},
    mastersPct     : {!! json_encode($mastersPercentages) !!},
    bachelorsPct   : {!! json_encode($bachelorsPercentages) !!},
    phdCounts      : {!! json_encode($phdCounts) !!},
    mastersCounts  : {!! json_encode($mastersCounts) !!},
    bachelorsCounts: {!! json_encode($bachelorsCounts) !!},
};

const AJAX_URL   = '{{ route("stzfaculty.overview.ajax") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// ── Helpers ──────────────────────────────────────────────────────────────────
function showLoaders() {
    document.querySelector('.filter-bar').classList.add('is-loading');
    ['loadRanking','loadEmployment','loadStatus','loadQual'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('active');
    });
    ['statTotal','statActive','statPhd','statMasters'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.add('loading');
    });
}

function hideLoaders() {
    document.querySelector('.filter-bar').classList.remove('is-loading');
    ['loadRanking','loadEmployment','loadStatus','loadQual'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('active');
    });
    ['statTotal','statActive','statPhd','statMasters'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('loading');
    });
}

function donutLayout(totalVal) {
    return {
        font: { family: 'Inter' },
        autosize: true,
        margin: { l: 20, r: 20, t: 70, b: 20 },
        annotations: [
            {
                text: '<b>Total</b>',
                x: 0.5, y: 0.57,
                showarrow: false,
                font: { family: 'Inter', size: 13, color: '#666' }
            },
            {
                text: '<b>' + totalVal + '</b>',
                x: 0.5, y: 0.43,
                showarrow: false,
                font: { family: 'Inter', size: 34, color: '#1f1f1f' }
            }
        ],
        paper_bgcolor: 'white',
        plot_bgcolor : 'white',
        showlegend   : true,
        legend: {
            orientation    : 'h',
            x              : 0.5,
            y              : 1.05,
            xanchor        : 'center',
            yanchor        : 'bottom',
            font           : { family: 'Inter', size: 11 },
            itemclick      : false,
            itemdoubleclick: false
        }
    };
}

function showNoData(div) {
    let overlay = div.parentNode.querySelector('.no-data-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'no-data-overlay';
        overlay.style.cssText = [
            'position:absolute','inset:0','display:flex',
            'flex-direction:column','align-items:center','justify-content:center',
            'background:white','border-radius:inherit','z-index:5','gap:10px'
        ].join(';');
        overlay.innerHTML =
            '<i class="bi bi-inbox" style="font-size:40px;color:#ccc;"></i>' +
            '<span style="font-family:Inter;font-size:14px;font-weight:600;color:#999;">No record found</span>';
        div.parentNode.appendChild(overlay);
    }
    overlay.style.display = 'flex';
    div.style.visibility = 'hidden';
}

function clearNoData(div) {
    const overlay = div.parentNode.querySelector('.no-data-overlay');
    if (overlay) overlay.style.display = 'none';
    div.style.visibility = 'visible';
}

function colorWithOpacity(baseColor, opacities) {
    const r = parseInt(baseColor.slice(1,3), 16);
    const g = parseInt(baseColor.slice(3,5), 16);
    const b = parseInt(baseColor.slice(5,7), 16);
    return opacities.map(o => `rgba(${r},${g},${b},${o})`);
}

const plotCfg = {
    responsive: true,
    displaylogo: false,
    modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d']
};

// ── Chart renderers ───────────────────────────────────────────────────────────
function renderRanking(d) {
    const div = document.getElementById('facultyRankingChart');
    if (!div) return;

    const total = (d.rankingCounts || []).reduce((a, b) => a + b, 0);
    if (!d.rankingLabels || d.rankingLabels.length === 0 || total === 0) {
        showNoData(div); return;
    }
    clearNoData(div);
    const colors = d.rankingLabels.map(l =>
        (d.selectedDept && l === d.selectedDept) ? '#FFA500' : '#009539'
    );
    Plotly.react(div, [{
        x: d.rankingCounts,
        y: d.rankingLabels,
        type: 'bar',
        orientation: 'h',
        marker: { color: colors, line: { color: 'rgba(0,0,0,0.1)', width: 1 } },
        text: d.rankingCounts,
        textposition: 'outside',
        textfont: { family: 'Inter', size: 11, color: '#1f1f1f' },
        hovertemplate: '<b>%{y}</b><br>Faculty Count: %{x}<extra></extra>'
    }], {
        font: { family: 'Inter' },
        autosize: true,
        margin: { l: 60, r: 40, t: 20, b: 40 },
        xaxis: {
            title   : { text: 'Number of Faculty', font: { family: 'Inter', size: 11 } },
            range   : [0, Math.max(...d.rankingCounts) * 1.2],
            gridcolor: '#e0e0e0',
            zeroline: false,
            tickfont: { family: 'Inter', size: 10, color: '#666' }
        },
        yaxis: {
            gridcolor: 'transparent',
            autorange: 'reversed',
            tickfont : { family: 'Inter', size: 11, color: '#1f1f1f' }
        },
        paper_bgcolor: 'white',
        plot_bgcolor : 'white',
        showlegend   : false,
        bargap       : 0.3
    }, plotCfg);
}

function renderEmployment(d) {
    const div = document.getElementById('employmentChart');
    if (!div) return;
    const colorMap = ['#009539','#2c7be5','#f6c343','#e74c3c'];
    const vals=[], labels=[], colors=[];
    d.categoryData.forEach((v, i) => {
        if (v > 0) {
            vals.push(v);
            labels.push(d.categoryLabels[i]);
            colors.push(colorMap[i % colorMap.length]);
        }
    });
    if (vals.length === 0) { showNoData(div); return; }
    clearNoData(div);
    Plotly.react(div, [{
        values       : vals,
        labels       : labels,
        type         : 'pie',
        hole         : 0.65,
        domain       : { x: [0, 1], y: [0, 1] },
        marker       : { colors: colors },
        textinfo     : 'percent',
        textposition : 'inside',
        insidetextorientation: 'horizontal',
        textfont     : { family: 'Inter', size: 11, color: 'white' },
        hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>%{percent}<extra></extra>',
        showlegend   : true,
        direction    : 'clockwise',
        sort         : false
    }], donutLayout(d.totalFaculty), plotCfg);
}

function renderStatus(d) {
    const div = document.getElementById('statusChart');
    if (!div) return;
    const raw  = [d.activeCount, d.onLeaveCount];
    const lbl  = ['Active', 'On Leave'];
    const clr  = ['#009539', '#e74c3c'];
    const vals=[], labels=[], colors=[];
    raw.forEach((v, i) => {
        if (v > 0) { vals.push(v); labels.push(lbl[i]); colors.push(clr[i]); }
    });
    if (vals.length === 0) { showNoData(div); return; }
    clearNoData(div);
    Plotly.react(div, [{
        values       : vals,
        labels       : labels,
        type         : 'pie',
        hole         : 0.65,
        domain       : { x: [0, 1], y: [0, 1] },
        marker       : { colors: colors },
        textinfo     : 'percent',
        textposition : 'inside',
        insidetextorientation: 'horizontal',
        textfont     : { family: 'Inter', size: 11, color: 'white' },
        hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>%{percent}<extra></extra>',
        showlegend   : true
    }], donutLayout(d.activeCount + d.onLeaveCount), plotCfg);
}

function normalizeQualPct(phdArr, mastersArr, bachelorsArr) {
    const normPhd=[], normMasters=[], normBachelors=[];
    phdArr.forEach((_, i) => {
        const total = (phdArr[i] || 0) + (mastersArr[i] || 0) + (bachelorsArr[i] || 0);
        if (total === 0) {
            normPhd.push(0); normMasters.push(0); normBachelors.push(0);
        } else {
            const scale = 100 / total;
            const p = parseFloat((phdArr[i]    * scale).toFixed(4));
            const m = parseFloat((mastersArr[i] * scale).toFixed(4));
            const b = parseFloat((100 - p - m).toFixed(4));
            normPhd.push(p);
            normMasters.push(m);
            normBachelors.push(b < 0 ? 0 : b);
        }
    });
    return { normPhd, normMasters, normBachelors };
}

function renderQual(d) {
    const div = document.getElementById('qualificationChart');
    if (!div) return;

    const totalQual = [...(d.phdCounts||[]), ...(d.mastersCounts||[]), ...(d.bachelorsCounts||[])]
        .reduce((a, b) => a + b, 0);
    if (!d.qualLabels || d.qualLabels.length === 0 || totalQual === 0) {
        showNoData(div); return;
    }
    clearNoData(div);
    const sel = d.selectedDept;
    const opacities = sel
        ? d.qualLabels.map(l => l === sel ? 1 : 0.4)
        : d.qualLabels.map(() => 1);

    const { normPhd, normMasters, normBachelors } =
        normalizeQualPct(d.phdPct, d.mastersPct, d.bachelorsPct);

    Plotly.react(div, [
        {
            name : 'PhD',
            x    : d.qualLabels,
            y    : normPhd,
            type : 'bar',
            marker: { color: colorWithOpacity('#1565c0', opacities) },
            text : d.phdCounts,
            textposition: 'none',
            hovertemplate: '<b>%{x}</b><br>PhD: %{y:.1f}% (%{text})<extra></extra>'
        },
        {
            name : 'Masters',
            x    : d.qualLabels,
            y    : normMasters,
            type : 'bar',
            marker: { color: colorWithOpacity('#009539', opacities) },
            text : d.mastersCounts,
            textposition: 'none',
            hovertemplate: '<b>%{x}</b><br>Masters: %{y:.1f}% (%{text})<extra></extra>'
        },
        {
            name : 'Bachelors',
            x    : d.qualLabels,
            y    : normBachelors,
            type : 'bar',
            marker: { color: colorWithOpacity('#f6c343', opacities) },
            text : d.bachelorsCounts,
            textposition: 'none',
            hovertemplate: '<b>%{x}</b><br>Bachelors: %{y:.1f}% (%{text})<extra></extra>'
        }
    ], {
        font    : { family: 'Inter' },
        autosize: true,
        barmode : 'stack',
        margin  : { l: 50, r: 20, t: 60, b: 80 },
        xaxis: {
            tickfont : { family: 'Inter', size: 10, color: '#1f1f1f' },
            tickangle: -30,
            gridcolor: 'transparent'
        },
        yaxis: {
            title    : { text: 'Percentage (%)', font: { family: 'Inter', size: 11 } },
            range    : [0, 100],
            tickfont : { family: 'Inter', size: 10, color: '#666' },
            gridcolor: '#e0e0e0'
        },
        paper_bgcolor: 'white',
        plot_bgcolor : 'white',
        showlegend   : true,
        legend: {
            orientation: 'h',
            x          : 0.5,
            y          : 1.18,
            xanchor    : 'center',
            yanchor    : 'top',
            font       : { family: 'Inter', size: 11 }
        }
    }, plotCfg);
}

function updateStatCards(d) {
    document.getElementById('statTotalNum').textContent   = d.totalFaculty;
    document.getElementById('statActiveNum').textContent  = d.activeCount;
    document.getElementById('statPhdNum').textContent     = d.phdHolders;
    document.getElementById('statMastersNum').textContent = d.mastersHolders;
}

function updateTitles(d, filters) {
    const col      = filters.college;
    const dept     = filters.department;
    const sem      = filters.semesterText || '';
    const colAcro  = d.collegeAcro || '';
    const deptAcro = d.deptAcro    || '';

    let barTitle = '';
    if (col !== 'all' && dept !== 'all') {
        barTitle = deptAcro + ' Faculty Profile (' + sem + ')';
    } else if (col !== 'all') {
        barTitle = colAcro + ' Faculty Profile (' + sem + ')';
    } else {
        barTitle = 'Faculty Profile (' + sem + ')';
    }
    document.getElementById('dynamicTitle').textContent = barTitle;

    const prefix = col !== 'all' ? colAcro + ' ' : '';

    // CHANGED: "by College" when no college filter, "by Department" when a college is selected
    document.getElementById('rankingTitle').textContent       = col !== 'all'
        ? colAcro + ' Faculty Count by Department'
        : 'Ranking of Faculty Count by College';

    document.getElementById('employmentTitle').textContent    = prefix + 'Faculty Employment Status';
    document.getElementById('statusTitle').textContent        = prefix + 'Faculty Availability Status';
    document.getElementById('qualificationTitle').textContent = prefix + 'Faculty Qualification Distribution';
}

// ── Core AJAX fetch & render ──────────────────────────────────────────────────
function fetchAndRender(params) {
    showLoaders();

    fetch(AJAX_URL + '?' + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN'    : CSRF_TOKEN
        }
    })
    .then(r => r.json())
    .then(data => {
        updateStatCards(data);
        updateTitles(data, {
            college     : params.get('college')    || 'all',
            department  : params.get('department') || 'all',
            semesterText: data.semesterText
        });

        renderRanking(data);
        renderEmployment(data);
        renderStatus(data);
        renderQual(data);

        // Update browser URL without reload
        const url = new URL(window.location.href);
        url.search = params.toString();
        window.history.replaceState({}, '', url.toString());

        const college = params.get('college') || 'all';

        // Rebuild dept dropdown if server sent new list
        if (data.departments) {
            const deptSel     = document.getElementById('departmentFilter');
            const currentDept = params.get('department') || 'all';
            if (deptSel) {
                deptSel.innerHTML = '<option value="all">All</option>';
                data.departments.forEach(dep => {
                    const opt       = document.createElement('option');
                    opt.value       = dep.department_id;
                    opt.textContent = dep.department_acro;
                    if (String(dep.department_id) === String(currentDept)) opt.selected = true;
                    deptSel.appendChild(opt);
                });
            }
        }

        // Show/hide dept filter group
        const deptGrp = document.getElementById('departmentFilterGroup');
        if (deptGrp) {
            deptGrp.style.display = (college !== 'all') ? 'flex' : 'none';
        }
    })
    .catch(err => console.error('Faculty filter error:', err))
    .finally(()  => hideLoaders());
}

// ── Filter logic ──────────────────────────────────────────────────────────────
function buildParams() {
    const params     = new URLSearchParams();
    const semester   = document.getElementById('semesterFilter').value;
    const college    = document.getElementById('collegeFilter').value;
    const department = document.getElementById('departmentFilter')?.value || 'all';
    params.set('semester',   semester);
    params.set('college',    college);
    params.set('department', department);
    return params;
}

function applyFilters() {
    updateDynamicTitle();
    fetchAndRender(buildParams());
}

function updateDepartments() {
    const deptSel = document.getElementById('departmentFilter');
    if (deptSel) deptSel.value = 'all';
    toggleDepartmentFilter();
    updateDynamicTitle();
    fetchAndRender(buildParams());
}

function clearFilters() {
    window.location.href = '{{ route("stzfaculty.overview") }}';
}

// ── UI helpers ────────────────────────────────────────────────────────────────
function updateDynamicTitle() {
    const semEl    = document.getElementById('semesterFilter');
    const semText  = semEl.options[semEl.selectedIndex].text;
    const colEl    = document.getElementById('collegeFilter');
    const colText  = colEl.options[colEl.selectedIndex].text;
    const deptEl   = document.getElementById('departmentFilter');
    const deptText = deptEl ? deptEl.options[deptEl.selectedIndex]?.text : 'All';
    const titleEl  = document.getElementById('dynamicTitle');

    let title = '';
    if (colEl.value !== 'all' && deptEl && deptEl.value !== 'all') {
        title = deptText + ' Faculty Profile (' + semText + ')';
    } else if (colEl.value !== 'all') {
        title = colText + ' Faculty Profile (' + semText + ')';
    } else {
        title = 'Faculty Profile (' + semText + ')';
    }
    titleEl.textContent = title;
}

function toggleDepartmentFilter() {
    const college   = document.getElementById('collegeFilter').value;
    const deptGroup = document.getElementById('departmentFilterGroup');
    if (deptGroup) {
        deptGroup.style.display = (college !== 'all') ? 'flex' : 'none';
    }
}

// ── Sidebar reflow ────────────────────────────────────────────────────────────
function reflowCharts() {
    ['facultyRankingChart','employmentChart','statusChart','qualificationChart']
        .forEach(id => {
            const div = document.getElementById(id);
            if (div && div.data) Plotly.relayout(div, { autosize: true });
        });
}

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // ── Wire up: Semester filter ──────────────────────────────────────────────
    document.getElementById('semesterFilter').addEventListener('change', applyFilters);

    // ── Wire up: Unit/Office filter ───────────────────────────────────────────
    document.getElementById('collegeFilter').addEventListener('change', updateDepartments);

    // ── Wire up: Department filter ────────────────────────────────────────────
    const deptSel = document.getElementById('departmentFilter');
    if (deptSel) deptSel.addEventListener('change', applyFilters);

    // ── Sidebar toggle reflow ─────────────────────────────────────────────────
    const sidebarBtn = document.getElementById('sidebarToggle');
    if (sidebarBtn) sidebarBtn.addEventListener('click', () => setTimeout(reflowCharts, 320));

    // ── Render charts from initial server data (no AJAX on first load) ────────
    renderRanking(INITIAL_DATA);
    renderEmployment(INITIAL_DATA);
    renderStatus(INITIAL_DATA);
    renderQual(INITIAL_DATA);

    // ── Window resize ─────────────────────────────────────────────────────────
    let resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(reflowCharts, 250);
    });
});
</script>
</body>
</html>