<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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

        /* ── Page header (copied from SUC Faculty) ── */
        .header {
            background: #009539;
            color: white;
            padding: 0 30px;
            font-size: 36px;
            font-weight: 800;
            height: 75px;
            display: flex;
            align-items: center;
            gap: 14px;
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        /* ── Filter Bar ── */
        .filter-bar {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 10px;
            background: #c9cec9;
            padding: 0 24px;
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
            gap: 6px;
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
            padding: 4px 28px 4px 12px;
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

        /* ── Main content wrapper ── */
        .main-content {
            padding: 24px 30px 40px 30px;
        }

        /* ── Stat Cards (copied from SUC Faculty) ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }
        @media (max-width: 1200px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px)  { .cards-grid { grid-template-columns: 1fr; } }

        .stat-card {
            position: relative;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            min-height: 130px;
        }
        .stat-card.green {
            background: linear-gradient(to right, #22c55e, #16a34a);
            color: white;
        }
        .stat-card.white {
            background: white;
            color: #111827;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .stat-card-icon {
            position: absolute;
            top: 16px;
            left: 16px;
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        .stat-card.green .stat-card-icon {
            background: rgba(255,255,255,0.9);
            color: #16a34a;
        }
        .stat-card.white .stat-card-icon {
            background: #22c55e;
            color: white;
        }
        .stat-card-body {
            margin-top: 52px;
            text-align: right;
        }
        .stat-card-number {
            font-size: 40px;
            font-weight: 800;
            line-height: 1;
            font-family: 'Inter', sans-serif;
        }
        .stat-card.green .stat-card-number { color: white; }
        .stat-card.white .stat-card-number { color: #111827; }

        .stat-card-label {
            font-size: 14px;
            font-weight: 600;
            margin-top: 4px;
        }
        .stat-card.green .stat-card-label { color: rgba(255,255,255,0.85); }
        .stat-card.white .stat-card-label { color: #6b7280; }

        /* ── Charts Grid (copied from SUC Faculty, adapted to 3-col + full-width) ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 1100px) { .charts-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 700px)  { .charts-grid { grid-template-columns: 1fr; } }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        .chart-card.full-width {
            grid-column: 1 / -1;
        }
        .chart-card h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 14px 0;
            color: #111827;
            line-height: 1.3;
            word-break: break-word;
        }
        .chart-height {
            height: 380px;
            width: 100%;
            position: relative;
        }
        .chart-height > div {
            width: 100%;
            height: 100%;
        }

        /* ── Loading overlay ── */
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.82);
            border-radius: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            opacity: 0;
            pointer-events: none;
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

        /* Loading skeleton for stat cards */
        .stat-card.loading .stat-card-number,
        .stat-card.loading .stat-card-label {
            background: linear-gradient(90deg,#e0e0e0 25%,#f0f0f0 50%,#e0e0e0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
            color: transparent !important;
            min-width: 60px;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        .filter-bar.is-loading select,
        .filter-bar.is-loading button { pointer-events: none; opacity: 0.5; }

        /* ── No data ── */
        .no-data-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: inherit;
            z-index: 5;
            gap: 10px;
        }
        .no-data-overlay i  { font-size: 40px; color: #ccc; }
        .no-data-overlay span {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #999;
        }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">

        {{-- Page Header --}}
        <div class="header" id="pageHeader">
            FACULTY PROFILE
        </div>

        {{-- Filter Bar --}}
        <div class="filter-bar" id="filterBar">

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

                <button class="clear-filters-btn" onclick="clearFilters()">Clear Filters</button>
            </div>

        </div>

        {{-- Main Content --}}
        <div class="main-content">

            {{-- ── Stat Cards ── --}}
            <div class="cards-grid">

                <div class="stat-card green" id="statTotal">
                    <div class="stat-card-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-number" id="statTotalNum">{{ $totalFaculty }}</div>
                        <div class="stat-card-label">Total Faculty</div>
                    </div>
                </div>

                <div class="stat-card white" id="statActive">
                    <div class="stat-card-icon"><i class="fa-solid fa-user-check"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-number" id="statActiveNum">{{ $activeCount }}</div>
                        <div class="stat-card-label">Active Faculty</div>
                    </div>
                </div>

                <div class="stat-card white" id="statPhd">
                    <div class="stat-card-icon"><i class="fa-solid fa-user-graduate"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-number" id="statPhdNum">{{ $phdHolders }}</div>
                        <div class="stat-card-label">PhD Holders</div>
                    </div>
                </div>

                <div class="stat-card white" id="statMasters">
                    <div class="stat-card-icon"><i class="fa-solid fa-book-open"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-number" id="statMastersNum">{{ $mastersHolders }}</div>
                        <div class="stat-card-label">Masters Holders</div>
                    </div>
                </div>

            </div>

            {{-- ── Charts ── --}}
            <div class="charts-grid">

                {{-- ① Submitted Faculty Workload --}}
                <div class="chart-card" id="cardRanking">
                    <div class="loading-overlay" id="loadRanking"><div class="spinner"></div></div>
                    <h3 id="rankingTitle">
                        @if($filters['college'] != 'all')
                            @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                            {{ $collegeName ? $collegeName->college_acro : '' }} Submitted Faculty Workload by Department
                        @else
                            Submitted Faculty Workload per College
                        @endif
                    </h3>
                    <div class="chart-height"><div id="facultyRankingChart"></div></div>
                </div>

                {{-- ② Employment Status --}}
                <div class="chart-card" id="cardEmployment">
                    <div class="loading-overlay" id="loadEmployment"><div class="spinner"></div></div>
                    <h3 id="employmentTitle">
                        @if($filters['college'] != 'all')
                            @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                            {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Employment Status
                        @else
                            Faculty Employment Status
                        @endif
                    </h3>
                    <div class="chart-height"><div id="employmentChart"></div></div>
                </div>

                {{-- ③ Faculty Availability --}}
                <div class="chart-card" id="cardStatus">
                    <div class="loading-overlay" id="loadStatus"><div class="spinner"></div></div>
                    <h3 id="statusTitle">
                        @if($filters['college'] != 'all')
                            @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                            {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Availability Status
                        @else
                            Faculty Availability Status
                        @endif
                    </h3>
                    <div class="chart-height"><div id="statusChart"></div></div>
                </div>

                {{-- ④ Faculty Qualification Distribution (full width) --}}
                <div class="chart-card full-width" id="cardQual">
                    <div class="loading-overlay" id="loadQual"><div class="spinner"></div></div>
                    <h3 id="qualificationTitle">
                        @if($filters['college'] != 'all')
                            @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                            {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Qualification Distribution
                        @else
                            Faculty Qualification Distribution
                        @endif
                    </h3>
                    <div class="chart-height"><div id="qualificationChart"></div></div>
                </div>

            </div>{{-- /.charts-grid --}}

        </div>{{-- /.main-content --}}
    </div>{{-- /.content --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── All your existing JS below is UNCHANGED ──────────────────────────────────

const INITIAL_DATA = {
    totalFaculty      : {{ $totalFaculty }},
    activeCount       : {{ $activeCount }},
    phdHolders        : {{ $phdHolders }},
    mastersHolders    : {{ $mastersHolders }},
    categoryLabels    : {!! json_encode($categories->pluck('category')) !!},
    categoryData      : {!! json_encode($categories->pluck('count')) !!},
    onLeaveCount      : {{ $onLeaveCount }},
    rankingLabels     : {!! json_encode($rankingLabels) !!},
    rankingCounts     : {!! json_encode($rankingCounts) !!},
    rankingTotals     : {!! json_encode($rankingTotals) !!},
    selectedDept      : @json($selectedDeptAcro),
    qualLabels        : {!! json_encode($qualLabels) !!},
    phdPct            : {!! json_encode($phdPercentages) !!},
    mastersPct        : {!! json_encode($mastersPercentages) !!},
    bachelorsPct      : {!! json_encode($bachelorsPercentages) !!},
    phdCounts         : {!! json_encode($phdCounts) !!},
    mastersCounts     : {!! json_encode($mastersCounts) !!},
    bachelorsCounts   : {!! json_encode($bachelorsCounts) !!},
    collegeAcro       : @json(optional($colleges->firstWhere('c_u_id', $filters['college']))->college_acro ?? ''),
    semesterText      : @json(optional($semesters->firstWhere('sem_id', $filters['semester']))->semester . ' ' . optional($semesters->firstWhere('sem_id', $filters['semester']))->sy),
    collegeFilterValue: @json($filters['college']),
};

const AJAX_URL   = '{{ route("stzfaculty.overview.ajax") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

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

function donutLayout() {
    return {
        font: { family: 'Inter' },
        autosize: true,
        margin: { l: 20, r: 20, t: 70, b: 20 },
        annotations: [],
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
        overlay.innerHTML =
            '<i class="bi bi-inbox"></i>' +
            '<span>No record found</span>';
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

function renderRanking(d) {
    const div = document.getElementById('facultyRankingChart');
    if (!div) return;

    if (!d.rankingLabels || d.rankingLabels.length === 0) {
        showNoData(div); return;
    }

    const hasTotals = Array.isArray(d.rankingTotals)
        && d.rankingTotals.length === d.rankingLabels.length
        && d.rankingTotals.some(v => v > 0);

    const isEmpty = hasTotals
        ? !d.rankingTotals.some(v => v > 0)
        : !d.rankingCounts.some(v => v > 0);

    if (isEmpty) { showNoData(div); return; }
    clearNoData(div);

    if (hasTotals) {
        const labels  = [...d.rankingLabels].reverse();
        const counts  = [...d.rankingCounts].reverse();
        const totals  = [...d.rankingTotals].reverse();
        const maxVal  = Math.max(...totals, ...counts, 1);

        Plotly.react(div, [
            {
                name          : 'Submitted Workload',
                x             : counts,
                y             : labels,
                type          : 'bar',
                orientation   : 'h',
                marker        : { color: '#009539', line: { color: 'rgba(0,0,0,0.08)', width: 1 } },
                text          : counts,
                textposition  : 'outside',
                textfont      : { family: 'Inter', size: 11, color: '#1f1f1f' },
                hovertemplate : '<b>%{y}</b><br>Submitted Workload: %{x}<extra></extra>'
            },
            {
                name          : 'Total Faculty',
                x             : totals,
                y             : labels,
                type          : 'bar',
                orientation   : 'h',
                marker        : { color: '#a8d5b8', line: { color: 'rgba(0,149,57,0.25)', width: 1 } },
                text          : totals,
                textposition  : 'outside',
                textfont      : { family: 'Inter', size: 11, color: '#555' },
                hovertemplate : '<b>%{y}</b><br>Total Faculty: %{x}<extra></extra>'
            }
        ], {
            font        : { family: 'Inter' },
            autosize    : true,
            barmode     : 'group',
            margin      : { l: 65, r: 55, t: 50, b: 40 },
            xaxis: {
                title    : { text: 'Number of Faculty', font: { family: 'Inter', size: 11 } },
                range    : [0, maxVal * 1.25],
                gridcolor: '#e0e0e0',
                zeroline : false,
                tickfont : { family: 'Inter', size: 10, color: '#666' }
            },
            yaxis: {
                gridcolor: 'transparent',
                autorange: true,
                tickfont : { family: 'Inter', size: 11, color: '#1f1f1f' }
            },
            paper_bgcolor: 'white',
            plot_bgcolor : 'white',
            showlegend   : true,
            legend: {
                orientation: 'h',
                x: 0.5, y: 1.08,
                xanchor: 'center',
                yanchor: 'bottom',
                font: { family: 'Inter', size: 11 }
            },
            bargap      : 0.25,
            bargroupgap : 0.08
        }, plotCfg);

    } else {
        const labels = [...d.rankingLabels].reverse();
        const counts = [...d.rankingCounts].reverse();
        const maxVal = Math.max(...counts, 1);

        const colors = labels.map(l =>
            (d.selectedDept && l === d.selectedDept) ? '#FFA500' : '#009539'
        );

        Plotly.react(div, [{
            x             : counts,
            y             : labels,
            type          : 'bar',
            orientation   : 'h',
            marker        : { color: colors, line: { color: 'rgba(0,0,0,0.1)', width: 1 } },
            text          : counts,
            textposition  : 'outside',
            textfont      : { family: 'Inter', size: 11, color: '#1f1f1f' },
            hovertemplate : '<b>%{y}</b><br>Submitted: %{x}<extra></extra>'
        }], {
            font        : { family: 'Inter' },
            autosize    : true,
            margin      : { l: 60, r: 50, t: 20, b: 40 },
            xaxis: {
                title    : { text: 'Number of Faculty', font: { family: 'Inter', size: 11 } },
                range    : [0, maxVal * 1.25],
                gridcolor: '#e0e0e0',
                zeroline : false,
                tickfont : { family: 'Inter', size: 10, color: '#666' }
            },
            yaxis: {
                gridcolor: 'transparent',
                autorange: true,
                tickfont : { family: 'Inter', size: 11, color: '#1f1f1f' }
            },
            paper_bgcolor: 'white',
            plot_bgcolor : 'white',
            showlegend   : false,
            bargap       : 0.3
        }, plotCfg);
    }
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
        hole         : 0.50,
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
    }], donutLayout(), plotCfg);
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
        hole         : 0.50,
        domain       : { x: [0, 1], y: [0, 1] },
        marker       : { colors: colors },
        textinfo     : 'percent',
        textposition : 'inside',
        insidetextorientation: 'horizontal',
        textfont     : { family: 'Inter', size: 11, color: 'white' },
        hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>%{percent}<extra></extra>',
        showlegend   : true
    }], donutLayout(), plotCfg);
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
    const col     = filters.college      || d.collegeFilterValue || 'all';
    const sem     = filters.semesterText || d.semesterText       || '';
    const colAcro = d.collegeAcro || '';

    const barTitle = col !== 'all'
        ? colAcro + ' Faculty Profile (' + sem + ')'
        : 'Faculty Profile (' + sem + ')';
    document.getElementById('dynamicTitle').textContent = barTitle;

    const prefix = col !== 'all' ? colAcro + ' ' : '';

    document.getElementById('rankingTitle').textContent = col !== 'all'
        ? colAcro + ' Submitted Faculty Workload by Department'
        : 'Submitted Faculty Workload per College';

    document.getElementById('employmentTitle').textContent    = prefix + 'Faculty Employment Status';
    document.getElementById('statusTitle').textContent        = prefix + 'Faculty Availability Status';
    document.getElementById('qualificationTitle').textContent = prefix + 'Faculty Qualification Distribution';
}

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

        const url = new URL(window.location.href);
        url.search = params.toString();
        window.history.replaceState({}, '', url.toString());
    })
    .catch(err => console.error('Faculty filter error:', err))
    .finally(()  => hideLoaders());
}

function buildParams() {
    const params   = new URLSearchParams();
    const semester = document.getElementById('semesterFilter').value;
    const college  = document.getElementById('collegeFilter').value;
    params.set('semester', semester);
    params.set('college',  college);
    params.set('department', 'all');
    return params;
}

function applyFilters() {
    updateDynamicTitle();
    fetchAndRender(buildParams());
}

function updateDepartments() {
    updateDynamicTitle();
    fetchAndRender(buildParams());
}

function clearFilters() {
    window.location.href = '{{ route("stzfaculty.overview") }}';
}

function updateDynamicTitle() {
    const semEl   = document.getElementById('semesterFilter');
    const semText = semEl.options[semEl.selectedIndex].text;
    const colEl   = document.getElementById('collegeFilter');
    const colText = colEl.options[colEl.selectedIndex].text;
    const titleEl = document.getElementById('dynamicTitle');

    titleEl.textContent = colEl.value !== 'all'
        ? colText + ' Faculty Profile (' + semText + ')'
        : 'Faculty Profile (' + semText + ')';
}

function reflowCharts() {
    ['facultyRankingChart','employmentChart','statusChart','qualificationChart']
        .forEach(id => {
            const div = document.getElementById(id);
            if (div && div.data) Plotly.relayout(div, { autosize: true });
        });
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('semesterFilter').addEventListener('change', applyFilters);
    document.getElementById('collegeFilter').addEventListener('change', updateDepartments);

    const sidebarBtn = document.getElementById('sidebarToggle');
    if (sidebarBtn) sidebarBtn.addEventListener('click', () => setTimeout(reflowCharts, 320));

    updateTitles(INITIAL_DATA, {
        college     : INITIAL_DATA.collegeFilterValue,
        semesterText: INITIAL_DATA.semesterText
    });
    renderRanking(INITIAL_DATA);
    renderEmployment(INITIAL_DATA);
    renderStatus(INITIAL_DATA);
    renderQual(INITIAL_DATA);

    let resizeTimeout;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(reflowCharts, 250);
    });
});
</script>
</body>
</html>