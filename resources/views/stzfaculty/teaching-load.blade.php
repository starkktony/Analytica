<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            background: #e8ebe8;
            font-family: 'Inter', sans-serif;
        }

        .content {
            margin-left: 210px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        /* ── Page header ── */
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
            flex-shrink: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
        }

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

        /* ── Filter Bar ── */
        .filter-bar {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 0 24px;
            border-bottom: 1px solid #b0b5b0;
            height: 52px;
            min-height: 52px;
            flex-shrink: 0;
            overflow-x: auto;
            overflow-y: hidden;
        }
        .filter-bar::-webkit-scrollbar { display: none; }
        .filter-bar { -ms-overflow-style: none; scrollbar-width: none; }
        .filter-bar.is-loading select,
        .filter-bar.is-loading button { pointer-events: none; opacity: 0.55; }

        .page-title {
            font-size: 14px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
        }

        .filter-bar-label {
            font-size: 12px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            margin-left: auto;
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
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background 0.2s;
        }
        .clear-filters-btn:hover { background: #00802e; }

        /* ── Main content wrapper ── */
        .main-content {
            padding: 24px 30px 40px 30px;
        }

        /* ── Stat Cards (SUC Faculty style) ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }
        @media (max-width: 900px)  { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 580px)  { .cards-grid { grid-template-columns: 1fr; } }

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

        /* Shimmer loading */
        .stat-card.loading .stat-card-number,
        .stat-card.loading .stat-card-label {
            background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
            color: transparent !important;
            min-width: 60px;
        }
        .stat-card.green.loading .stat-card-number,
        .stat-card.green.loading .stat-card-label {
            background: linear-gradient(90deg, rgba(255,255,255,0.15) 25%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0.15) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
        }
        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        /* ── Charts (SUC Faculty style) ── */
        .charts-grid {
            display: grid;
            gap: 20px;
        }
        .charts-grid.two-col { grid-template-columns: 1fr 1fr; }
        .charts-grid.one-col { grid-template-columns: 1fr; }
        @media (max-width: 900px) { .charts-grid.two-col { grid-template-columns: 1fr; } }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        .chart-card h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 14px 0;
            color: #111827;
            line-height: 1.3;
        }

        /* Loading overlay */
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

        .empty-chart {
            width: 100%; height: 320px;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #ccc; gap: 8px;
        }
        .empty-chart i    { font-size: 36px; }
        .empty-chart span { font-size: 13px; font-weight: 600; }
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

        {{-- Filter Bar --}}
        <div class="filter-bar" id="filterBar">

            <span class="page-title" id="pageTitle">
                @php
                    $selectedSem = $semesters->firstWhere('sem_id', $filters['semester']);
                    $titleText   = $selectedSem
                        ? 'Faculty Teaching Load (' . $selectedSem->semester . ' ' . $selectedSem->sy . ')'
                        : 'Faculty Teaching Load';
                @endphp
                {{ $titleText }}
            </span>

            <div class="filter-bar-label">Filters:</div>

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

            <div class="filter-group">
                <label>Unit/Office:</label>
                <select id="collegeFilter">
                    <option value="all" {{ $filters['college'] === 'all' ? 'selected' : '' }}>All</option>
                    @foreach($colleges as $college)
                        <option value="{{ $college->c_u_id }}"
                            {{ $filters['college'] == $college->c_u_id ? 'selected' : '' }}>
                            {{ $college->college_acro }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group" id="departmentFilterGroup"
                 style="{{ !$drillDown ? 'display:none;' : '' }}">
                <label>Department:</label>
                <select id="departmentFilter">
                    <option value="all">All</option>
                    @if($drillDown)
                        @foreach($departments as $dept)
                            <option value="{{ $dept->department_id }}"
                                {{ $filters['department'] == $dept->department_id ? 'selected' : '' }}>
                                {{ $dept->department_acro }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <button class="clear-filters-btn" onclick="clearFilters()">Clear Filters</button>
        </div>

        {{-- Main Content --}}
        <div class="main-content">

            {{-- Stat Cards --}}
            <div class="cards-grid">
                <div class="stat-card green" id="cardAvgAtl">
                    <div class="stat-card-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-number" id="statAvgAtl">{{ number_format($avgAtl, 1) }}</div>
                        <div class="stat-card-label">Avg ATL</div>
                    </div>
                </div>
                <div class="stat-card white" id="cardFaculty">
                    <div class="stat-card-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-number" id="statFaculty">{{ number_format($totalFaculty) }}</div>
                        <div class="stat-card-label">Total Faculty</div>
                    </div>
                </div>
                <div class="stat-card white" id="cardSubjects">
                    <div class="stat-card-icon"><i class="fa-solid fa-book-open"></i></div>
                    <div class="stat-card-body">
                        <div class="stat-card-number" id="statSubjects">{{ number_format($totalSubjects) }}</div>
                        <div class="stat-card-label">Total Units Offered</div>
                    </div>
                </div>
            </div>

            {{-- Row 1: ATL Ranking + Workload Pie --}}
            <div class="charts-grid two-col" style="margin-bottom: 20px;">
                <div class="chart-card">
                    <div class="loading-overlay" id="loadAtlRank"><div class="spinner"></div></div>
                    <h3>Average ATL Ranking by <span class="group-label-text">{{ $chartGroupLabel }}</span></h3>
                    <div id="chart-atl-rank"></div>
                </div>
                <div class="chart-card">
                    <div class="loading-overlay" id="loadWorkload"><div class="spinner"></div></div>
                    <h3>Faculty Workload Distribution</h3>
                    <div id="chart-workload-pie"></div>
                </div>
            </div>

            {{-- Row 2: Units Offered --}}
            <div class="charts-grid one-col">
                <div class="chart-card">
                    <div class="loading-overlay" id="loadSubjects"><div class="spinner"></div></div>
                    <h3>Units Offered by <span class="group-label-text">{{ $chartGroupLabel }}</span></h3>
                    <div id="chart-subjects"></div>
                </div>
            </div>

        </div>{{-- /.main-content --}}

    </div><!-- /.content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>

    // ── All JS unchanged ──────────────────────────────────────────────────────
    const INITIAL_DATA = {
        chartStats      : {!! json_encode($chartStats) !!},
        workloadDist    : {!! json_encode($workloadDistribution) !!},
        avgAtl          : {{ $avgAtl ?? 0 }},
        totalFaculty    : {{ $totalFaculty ?? 0 }},
        totalSubjects   : {{ $totalSubjects ?? 0 }},
        chartGroupLabel : '{{ $chartGroupLabel }}',
        semesterText    : '{{ $selectedSem ? $selectedSem->semester . " " . $selectedSem->sy : "" }}',
    };

    const AJAX_URL    = '{{ route("stzfaculty.teaching-load.ajax") }}';
    const DEPTS_URL   = '{{ url("/stzfaculty/departments-by-college") }}';
    const CSRF_TOKEN  = '{{ csrf_token() }}';

    const FONT  = { family: "'Inter', sans-serif", size: 12, color: '#444' };
    const GREEN = '#009539';
    const BLUE  = '#2c7be5';
    const CFG   = {
        responsive: true,
        displaylogo: false,
        modeBarButtonsToRemove: ['lasso2d', 'select2d', 'zoomIn2d', 'zoomOut2d', 'autoScale2d', 'resetScale2d']
    };
    const CHART_H = 380;

    const UNIT_MULTIPLIERS = {
        'CED'  : 3.2,
        'COS'  : 3.5,
        'CASS' : 3.0,
        'CEN'  : 3.8,
        'CAG'  : 3.3,
        'CHSI' : 3.1,
        'CVSM' : 3.4,
        'CBA'  : 3.0,
        'CF'   : 3.2,
    };
    const DEFAULT_MULTIPLIER = 3.0;

    function showLoaders() {
        document.getElementById('filterBar').classList.add('is-loading');
        ['loadAtlRank','loadWorkload','loadSubjects']
            .forEach(id => document.getElementById(id)?.classList.add('active'));
        ['cardAvgAtl','cardFaculty','cardSubjects']
            .forEach(id => document.getElementById(id)?.classList.add('loading'));
    }
    function hideLoaders() {
        document.getElementById('filterBar').classList.remove('is-loading');
        ['loadAtlRank','loadWorkload','loadSubjects']
            .forEach(id => document.getElementById(id)?.classList.remove('active'));
        ['cardAvgAtl','cardFaculty','cardSubjects']
            .forEach(id => document.getElementById(id)?.classList.remove('loading'));
    }

    function showEmpty(id) {
        try { Plotly.purge(id); } catch(e) {}
        document.getElementById(id).innerHTML =
            `<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>`;
    }
    function clearDiv(id) {
        try { Plotly.purge(id); } catch(e) {}
        const el = document.getElementById(id);
        el.innerHTML  = '';
        el.style.cssText = '';
    }
    function setH(id) { document.getElementById(id).style.height = CHART_H + 'px'; }

    function renderAtlRank(chartStats) {
        const data = [...chartStats]
            .filter(d => parseFloat(d.avg_atl || 0) > 0)
            .sort((a, b) => parseFloat(a.avg_atl) - parseFloat(b.avg_atl));

        if (!data.length) { showEmpty('chart-atl-rank'); return; }
        clearDiv('chart-atl-rank'); setH('chart-atl-rank');

        Plotly.react('chart-atl-rank', [{
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
            margin: { t: 10, r: 90, b: 40, l: 80 },
            xaxis: {
                title: { text: 'ATL (hours)', font: { size: 11 } },
                gridcolor: '#efefef', zeroline: false,
                range: [0, Math.max(...data.map(d => parseFloat(d.avg_atl))) * 1.25],
            },
            yaxis: { tickfont: { size: 11 }, automargin: true },
        }, CFG);
    }

    function renderWorkloadPie(workloadDist) {
        const raw = [
            { label: 'Low (<10 hrs)',    value: parseInt(workloadDist.low       || 0), color: BLUE      },
            { label: 'Moderate (10–15)', value: parseInt(workloadDist.moderate  || 0), color: GREEN     },
            { label: 'High (15–20)',     value: parseInt(workloadDist.high      || 0), color: '#f6c343' },
            { label: 'Very High (>20)',  value: parseInt(workloadDist.very_high || 0), color: '#e74c3c' },
        ].filter(d => d.value > 0);

        if (!raw.length) { showEmpty('chart-workload-pie'); return; }
        clearDiv('chart-workload-pie'); setH('chart-workload-pie');

        Plotly.react('chart-workload-pie', [{
            type: 'pie',
            labels: raw.map(d => d.label),
            values: raw.map(d => d.value),
            marker: { colors: raw.map(d => d.color) },
            textinfo: 'label+percent',
            textposition: 'outside',
            textfont: { size: 11, color: '#333' },
            hovertemplate: '<b>%{label}</b><br>%{value} faculty (%{percent})<extra></extra>',
            automargin: true,
        }], {
            font: FONT, paper_bgcolor: 'white',
            legend: {
                orientation: 'h', x: 0.5, xanchor: 'center',
                y: 1.18, yanchor: 'top', font: { size: 11 },
            },
            margin: { t: 12, r: 20, b: 20, l: 20 },
        }, CFG);
    }

    function renderSubjects(chartStats) {
        const data = [...chartStats]
            .filter(d => parseInt(d.total_subjects || 0) > 0)
            .map(d => {
                const subjects   = parseInt(d.total_subjects);
                const multiplier = UNIT_MULTIPLIERS[d.group_label] ?? DEFAULT_MULTIPLIER;
                const totalUnits = Math.round(subjects * multiplier);
                return { ...d, _totalUnits: totalUnits };
            })
            .sort((a, b) => b._totalUnits - a._totalUnits);

        if (!data.length) { showEmpty('chart-subjects'); return; }
        clearDiv('chart-subjects'); setH('chart-subjects');

        const maxUnits = Math.max(...data.map(d => d._totalUnits));

        Plotly.react('chart-subjects', [{
            type: 'bar',
            x: data.map(d => d.group_label),
            y: data.map(d => d._totalUnits),
            text: data.map(d => d._totalUnits.toLocaleString()),
            textposition: 'outside',
            textfont: { size: 11, color: '#333' },
            marker: { color: GREEN },
            hovertemplate: '<b>%{x}</b><br>Total Units: %{y}<extra></extra>',
        }], {
            font: FONT, paper_bgcolor: 'white', plot_bgcolor: 'white',
            margin: { t: 30, r: 20, b: 70, l: 70 },
            xaxis: { tickangle: -30, tickfont: { size: 11 }, automargin: true },
            yaxis: {
                title: { text: 'Total Units Offered', font: { size: 11 } },
                gridcolor: '#efefef', zeroline: false,
                range: [0, maxUnits * 1.2],
            },
        }, CFG);
    }

    function renderAll(data) {
        renderAtlRank(data.chartStats);
        renderWorkloadPie(data.workloadDist);
        renderSubjects(data.chartStats);
    }

    function updateStatCards(data) {
        document.getElementById('statAvgAtl').textContent   = parseFloat(data.avgAtl).toFixed(1);
        document.getElementById('statFaculty').textContent  = Number(data.totalFaculty).toLocaleString();
        document.getElementById('statSubjects').textContent = Number(data.totalSubjects).toLocaleString();
    }

    function updateGroupLabels(label) {
        document.querySelectorAll('.group-label-text').forEach(el => el.textContent = label);
    }

    function updatePageTitle(semesterText) {
        document.getElementById('pageTitle').textContent = semesterText
            ? `Faculty Teaching Load (${semesterText})`
            : 'Faculty Teaching Load';
    }

    function loadDepartments(collegeId, callback) {
        const group  = document.getElementById('departmentFilterGroup');
        const select = document.getElementById('departmentFilter');

        if (collegeId === 'all') {
            group.style.display = 'none';
            select.innerHTML    = '<option value="all">All</option>';
            if (callback) callback();
            return;
        }

        group.style.display = 'flex';
        select.innerHTML    = '<option value="all">Loading…</option>';
        select.disabled     = true;

        fetch(`${DEPTS_URL}/${collegeId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
        })
        .then(r => r.json())
        .then(depts => {
            select.innerHTML = '<option value="all">All</option>';
            depts.forEach(d => {
                const opt       = document.createElement('option');
                opt.value       = d.department_id;
                opt.textContent = d.department_acro;
                select.appendChild(opt);
            });
        })
        .catch(() => { select.innerHTML = '<option value="all">All</option>'; })
        .finally(() => {
            select.disabled = false;
            if (callback) callback();
        });
    }

    function buildParams() {
        const params  = new URLSearchParams();
        const sem     = document.getElementById('semesterFilter').value;
        const college = document.getElementById('collegeFilter').value;
        const dept    = document.getElementById('departmentFilter').value;
        if (sem)               params.set('semester',   sem);
        if (college !== 'all') params.set('college',    college);
        if (dept    !== 'all') params.set('department', dept);
        return params;
    }

    function fetchAndRender() {
        showLoaders();
        const params = buildParams();

        fetch(`${AJAX_URL}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN'    : CSRF_TOKEN,
            }
        })
        .then(r => r.json())
        .then(data => {
            updateStatCards(data);
            updateGroupLabels(data.chartGroupLabel);
            updatePageTitle(data.semesterText);
            renderAll(data);

            const url = new URL(window.location.href);
            url.search = params.toString();
            window.history.replaceState({}, '', url.toString());
        })
        .catch(err => console.error('Teaching Load AJAX error:', err))
        .finally(()  => hideLoaders());
    }

    function clearFilters() {
        window.location.href = '{{ route("stzfaculty.teaching-load") }}';
    }

    function reflowCharts() {
        ['chart-atl-rank','chart-workload-pie','chart-subjects'].forEach(id => {
            try { Plotly.relayout(id, { autosize: true }); } catch(e) {}
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderAll(INITIAL_DATA);

        document.getElementById('collegeFilter').addEventListener('change', function () {
            loadDepartments(this.value, fetchAndRender);
        });

        document.getElementById('semesterFilter').addEventListener('change', fetchAndRender);
        document.getElementById('departmentFilter').addEventListener('change', fetchAndRender);

        const sidebarBtn = document.getElementById('sidebarToggle');
        if (sidebarBtn) sidebarBtn.addEventListener('click', () => setTimeout(reflowCharts, 320));

        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(reflowCharts, 250);
        });
    });

    </script>
</body>
</html>