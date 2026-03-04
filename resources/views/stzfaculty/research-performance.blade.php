<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Research & Non-Teaching Load</title>

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
            font-family: 'Bricolage Grotesque', sans-serif;
            display: flex;
            align-items: center;
        }

        /* ── Filter bar ─────────────────────────────────────────────────── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 0 30px;
            border-bottom: 1px solid #b0b5b0;
            height: 50px;
        }
        .page-title {
            font-size: 15px;
            font-weight: 700;
            color: #2d2d2d;
        }
        .filter-bar-label {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            margin-left: auto;
            white-space: nowrap;
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
            min-width: 130px;
            cursor: pointer;
        }
        .filter-group select:focus {
            outline: none;
            border-color: #009539;
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
            transition: background 0.2s;
            white-space: nowrap;
        }
        .clear-filters-btn:hover { background: #00802e; }

        /* Active filter badge shown in page title area */
        .active-filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(0,149,57,0.12);
            border: 1px solid #009539;
            color: #006b2b;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 600;
        }

        /* ── Stat cards ─────────────────────────────────────────────────── */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 28px 30px 18px;
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
        .stat-card.green { background: #009539; }
        .stat-card:not(.green) { background: white; }
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 16px;
            left: 16px;
        }
        .stat-card.green .icon-box { background: white; }
        .stat-card.green .icon-box i { font-size: 22px; color: #009539; }
        .stat-card:not(.green) .icon-box { background: #009539; }
        .stat-card:not(.green) .icon-box i { font-size: 22px; color: white; }
        .stat-content {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            flex: 1;
        }
        .stat-number { font-size: 46px; font-weight: 700; line-height: 1; }
        .stat-card.green .stat-number { color: white; }
        .stat-card:not(.green) .stat-number { color: #1f1f1f; }
        .stat-label { font-size: 12px; font-weight: 600; margin-top: 4px; }
        .stat-card.green .stat-label { color: rgba(255,255,255,0.85); }
        .stat-card:not(.green) .stat-label { color: #777; }
        /* Small filter context label under the stat number */
        .stat-context {
            font-size: 10px;
            margin-top: 2px;
            color: rgba(255,255,255,0.65);
        }
        .stat-card:not(.green) .stat-context { color: #aaa; }

        /* ── Chart layout ───────────────────────────────────────────────── */
        .charts-section {
            padding: 0 30px 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .chart-row { display: grid; gap: 20px; }
        .two-col   { grid-template-columns: 1fr 1fr; }

        .chart-card {
            background: white;
            border-radius: 18px;
            padding: 22px 26px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .chart-title {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2px;
        }
        .chart-subtitle {
            font-size: 11px;
            color: #999;
            margin-bottom: 6px;
            min-height: 16px;
        }
        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #009539;
            padding: 16px 30px 0;
        }
    </style>
</head>
<body>

@include('components.sidebar')

<div class="content">

    <div class="header">Research &amp; Non-Teaching Load</div>

    {{-- ── Filter bar: Sector → Semester → Unit/Office ─────────────────── --}}
    <div class="filter-bar">
        <div class="page-title">
            RESEARCH &amp; PUBLICATIONS
            {{-- Show active filter badges next to title --}}
            @if($filters['role_type'] !== 'all')
                <span class="active-filter-badge ms-2">
                    <i class="bi bi-funnel-fill" style="font-size:9px;"></i>
                    {{ $filters['role_type'] }}
                </span>
            @endif
            @if($filters['department'] !== 'all')
                @php $activeDept = $departments->firstWhere('department_id', $filters['department']); @endphp
                @if($activeDept)
                    <span class="active-filter-badge ms-1">
                        <i class="bi bi-building" style="font-size:9px;"></i>
                        {{ $activeDept->department_acro }}
                    </span>
                @endif
            @endif
        </div>

        <div class="filter-bar-label">Filters:</div>

        {{-- 1. Sector --}}
        <div class="filter-group">
            <label>Sector:</label>
            <select id="sectorFilter">
                <option value="all"       {{ $filters['role_type'] === 'all'       ? 'selected' : '' }}>All</option>
                <option value="Academic"  {{ $filters['role_type'] === 'Academic'  ? 'selected' : '' }}>Academic</option>
                <option value="Research"  {{ $filters['role_type'] === 'Research'  ? 'selected' : '' }}>Research</option>
                <option value="Admin"     {{ $filters['role_type'] === 'Admin'     ? 'selected' : '' }}>Admin</option>
                <option value="Others"    {{ $filters['role_type'] === 'Others'    ? 'selected' : '' }}>Others</option>
            </select>
        </div>

        {{-- 2. Semester --}}
        <div class="filter-group">
            <label>Semester:</label>
            <select id="semesterFilter">
                <option value="all" {{ (string)$filters['semester'] === 'all' ? 'selected' : '' }}>All</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester->sem_id }}"
                        {{ (string)$filters['semester'] == (string)$semester->sem_id ? 'selected' : '' }}>
                        {{ $semester->semester }} {{ $semester->sy }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 3. Unit/Office --}}
        <div class="filter-group">
            <label>Unit/Office:</label>
            <select id="departmentFilter">
                <option value="all">All</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->department_id }}"
                        {{ (string)$filters['department'] == (string)$dept->department_id ? 'selected' : '' }}>
                        {{ $dept->department_acro }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="clear-filters-btn" onclick="clearFilters()">
            <i class="bi bi-x-circle me-1"></i>Clear Filters
        </button>
    </div>

    {{-- ── Stat cards ───────────────────────────────────────────────────── --}}
    <div class="stats-container">
        <div class="stat-card green">
            <div class="icon-box"><i class="bi bi-flask"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ $researchLoad->sum('research_count') }}</div>
                <div class="stat-label">Research Assignments</div>
                @if($filters['semester'] !== 'all' && $filters['semester'] !== null)
                    @php $sem = $semesters->firstWhere('sem_id', $filters['semester']); @endphp
                    @if($sem)
                        <div class="stat-context">{{ $sem->semester }} {{ $sem->sy }}</div>
                    @endif
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box"><i class="bi bi-journal-text"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ $publications->sum('publication_count') }}</div>
                <div class="stat-label">Publications</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box"><i class="bi bi-person-badge-fill"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ $adminRoles->sum('faculty_count') }}</div>
                <div class="stat-label">Designation Assignments</div>
                @if($filters['role_type'] !== 'all')
                    <div class="stat-context">{{ $filters['role_type'] }} sector</div>
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="icon-box"><i class="bi bi-clock-history"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($researchLoad->sum('total_etl'), 0) }}</div>
                <div class="stat-label">Total ETL Hours</div>
            </div>
        </div>
    </div>

    {{-- ── Charts ────────────────────────────────────────────────────────── --}}
    <div class="section-label">Research Load &amp; Publications</div>
    <div class="charts-section" style="padding-top:14px;">

        <div class="chart-row two-col">

            {{-- CHART 1: Research Assignments by Unit/Office --}}
            <div class="chart-card">
                <div class="chart-title">Research Assignments by Unit/Office</div>
                <div class="chart-subtitle" id="sub1">
                    Number of research assignments per unit/office
                    @if($filters['semester'] !== 'all' && $filters['semester'] !== null)
                        @php $sem = $semesters->firstWhere('sem_id', $filters['semester']); @endphp
                        @if($sem) · {{ $sem->semester }} {{ $sem->sy }} @endif
                    @endif
                </div>
                <div id="chart-assignments"></div>
            </div>

            {{-- CHART 2: Publications by Unit/Office --}}
            <div class="chart-card">
                <div class="chart-title">Publications by Unit/Office</div>
                <div class="chart-subtitle" id="sub2">
                    Total publications per unit/office, ranked highest to lowest
                </div>
                <div id="chart-publications"></div>
            </div>

        </div>

        <div class="chart-row two-col">

            {{-- CHART 3: Total ETL Hours by Unit/Office --}}
            <div class="chart-card">
                <div class="chart-title">Total ETL Hours by Unit/Office</div>
                <div class="chart-subtitle" id="sub3">
                    Equivalent Teaching Load from research assignments per unit/office
                    @if($filters['semester'] !== 'all' && $filters['semester'] !== null)
                        @php $sem = $semesters->firstWhere('sem_id', $filters['semester']); @endphp
                        @if($sem) · {{ $sem->semester }} {{ $sem->sy }} @endif
                    @endif
                </div>
                <div id="chart-etl"></div>
            </div>

            {{-- CHART 4: Publication Types --}}
            <div class="chart-card">
                <div class="chart-title">Publication Types</div>
                <div class="chart-subtitle">
                    Distribution of publication types
                    @if($filters['department'] !== 'all')
                        @php $d = $departments->firstWhere('department_id', $filters['department']); @endphp
                        @if($d) · {{ $d->department_acro }} @endif
                    @endif
                </div>
                <div id="chart-pub-types"></div>
            </div>

        </div>
    </div>

</div><!-- /.content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── PHP → JS data ─────────────────────────────────────────────────────────────
const researchLoad = {!! json_encode($researchLoad) !!};
const publications = {!! json_encode($publications) !!};

// Current active filters (to decide capping logic)
const activeDept     = '{{ $filters['department'] }}';   // 'all' or an id string
const activeSemester = '{{ $filters['semester'] }}';     // 'all' or a sem_id string
const activeSector   = '{{ $filters['role_type'] }}';    // 'all' | Academic | Research | Admin | Others

// ── Shared Plotly constants ────────────────────────────────────────────────────
const FONT    = "'Bricolage Grotesque', sans-serif";
const GREEN   = '#009539';
const PALETTE = ['#009539','#2c7be5','#f6a623','#e74c3c','#9b59b6',
                 '#1abc9c','#e67e22','#34495e','#e91e63','#00bcd4'];

const BASE = {
    paper_bgcolor: '#ffffff',
    plot_bgcolor:  '#ffffff',
    font: { family: FONT, size: 12, color: '#444' },
    xaxis: { gridcolor: '#efefef', linecolor: '#ddd', tickfont: { family: FONT } },
    yaxis: { gridcolor: '#efefef', linecolor: '#ddd', tickfont: { family: FONT } },
    legend: { font: { family: FONT, size: 11 }, bgcolor: 'transparent' },
};
const CFG = { responsive: true, displayModeBar: false };

// ── How many bars to show in default (no unit/office filter) view ─────────────
// When a specific unit is selected the server already limits the data, so cap
// is irrelevant — we show whatever came back.
const CAP = 10;

function shouldCap() {
    return activeDept === 'all';
}

function capData(arr) {
    return shouldCap() ? arr.slice(0, CAP) : arr;
}

// Append "top N of total" note to a subtitle element
function addCapNote(elId, total) {
    if (shouldCap() && total > CAP) {
        const el = document.getElementById(elId);
        if (el) {
            const note = document.createElement('span');
            note.style.cssText = 'color:#bbb;font-size:10px;';
            note.textContent   = ` — showing top ${CAP} of ${total}. Select a Unit/Office to see all.`;
            el.appendChild(note);
        }
    }
}

// Empty-state placeholder
function noData(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.cssText = 'height:280px;display:flex;align-items:center;justify-content:center;';
    el.innerHTML = `<div style="text-align:center;color:#ccc;">
        <i class="bi bi-bar-chart" style="font-size:2rem;"></i>
        <p style="margin-top:8px;font-size:0.82rem;">No data for current filters</p></div>`;
}

// ══════════════════════════════════════════════════════════════════════════════
// CHART 1 — Research Assignments by Unit/Office  ·  Vertical Bar
// Vertical bar is fine here; unit/office acronyms are short enough.
// Sorted descending so highest-load units appear first (left to right).
// ══════════════════════════════════════════════════════════════════════════════
(function () {
    if (!researchLoad.length) return noData('chart-assignments');

    const sorted = [...researchLoad].sort((a, b) => b.research_count - a.research_count);
    addCapNote('sub1', sorted.length);
    const data   = capData(sorted);
    const depts  = data.map(d => d.department_acro);
    const counts = data.map(d => parseInt(d.research_count) || 0);

    Plotly.newPlot('chart-assignments', [{
        type: 'bar',
        x: depts,
        y: counts,
        text: counts.map(String),
        textposition: 'outside',
        textfont: { family: FONT, size: 11 },
        marker: { color: GREEN },
        hovertemplate: '<b>%{x}</b><br>Assignments: %{y}<extra></extra>'
    }], {
        ...BASE,
        height: 320,
        margin: { t: 20, r: 20, b: 80, l: 50 },
        xaxis: { ...BASE.xaxis, tickangle: -35 },
        yaxis: { ...BASE.yaxis, title: { text: 'Assignments', font: { family: FONT, size: 12 } } },
        bargap: 0.35,
        showlegend: false,
    }, CFG);
})();

// ══════════════════════════════════════════════════════════════════════════════
// CHART 2 — Publications by Unit/Office  ·  Horizontal Bar
// Horizontal because unit/office name labels can be long.
// Sorted ascending so the tallest bar (most pubs) appears at the top.
// ══════════════════════════════════════════════════════════════════════════════
(function () {
    if (!publications.length) return noData('chart-publications');

    const sorted = [...publications].sort((a, b) => a.publication_count - b.publication_count);
    addCapNote('sub2', sorted.length);
    const data  = capData(sorted);
    const depts = data.map(d => d.department_acro);
    const pubs  = data.map(d => parseInt(d.publication_count) || 0);

    Plotly.newPlot('chart-publications', [{
        type: 'bar',
        orientation: 'h',
        x: pubs,
        y: depts,
        text: pubs.map(String),
        textposition: 'outside',
        textfont: { family: FONT, size: 11, color: '#333' },
        marker: { color: GREEN },
        hovertemplate: '<b>%{y}</b><br>Publications: %{x}<extra></extra>'
    }], {
        ...BASE,
        height: 320,
        margin: { t: 10, r: 55, b: 40, l: 70 },
        xaxis: { ...BASE.xaxis, title: { text: 'Publications', font: { family: FONT, size: 12 } } },
        yaxis: { ...BASE.yaxis, automargin: true },
        bargap: 0.35,
    }, CFG);
})();

// ══════════════════════════════════════════════════════════════════════════════
// CHART 3 — Total ETL Hours by Unit/Office  ·  Horizontal Bar
// Same logic as Chart 2: horizontal for readability of labels.
// ══════════════════════════════════════════════════════════════════════════════
(function () {
    if (!researchLoad.length) return noData('chart-etl');

    const sorted = [...researchLoad].sort((a, b) => a.total_etl - b.total_etl);
    addCapNote('sub3', sorted.length);
    const data  = capData(sorted);
    const depts = data.map(d => d.department_acro);
    const etls  = data.map(d => parseFloat(d.total_etl) || 0);

    Plotly.newPlot('chart-etl', [{
        type: 'bar',
        orientation: 'h',
        x: etls,
        y: depts,
        text: etls.map(v => v.toFixed(1)),
        textposition: 'outside',
        textfont: { family: FONT, size: 11, color: '#333' },
        marker: { color: GREEN },
        hovertemplate: '<b>%{y}</b><br>ETL: %{x:.1f} hrs<extra></extra>'
    }], {
        ...BASE,
        height: 320,
        margin: { t: 10, r: 60, b: 40, l: 70 },
        xaxis: { ...BASE.xaxis, title: { text: 'ETL Hours', font: { family: FONT, size: 12 } } },
        yaxis: { ...BASE.yaxis, automargin: true },
        bargap: 0.35,
    }, CFG);
})();

// ══════════════════════════════════════════════════════════════════════════════
// CHART 4 — Publication Types  ·  Donut
// Mutually exclusive parts-of-a-whole → donut is the correct choice.
// The GROUP_CONCAT string from Laravel is split and aggregated here.
// ══════════════════════════════════════════════════════════════════════════════
(function () {
    if (!publications.length) return noData('chart-pub-types');

    const typeCount = {};
    publications.forEach(p => {
        if (!p.publication_types) return;
        p.publication_types.split(',').forEach(raw => {
            const t = raw.trim();
            if (!t) return;
            typeCount[t] = (typeCount[t] || 0) + parseInt(p.publication_count || 1);
        });
    });

    const labels = Object.keys(typeCount);
    const values = Object.values(typeCount);
    if (!labels.length) return noData('chart-pub-types');

    Plotly.newPlot('chart-pub-types', [{
        type: 'pie',
        labels,
        values,
        hole: 0.48,
        textinfo: 'percent+label',
        textfont: { family: FONT, size: 11 },
        marker: { colors: PALETTE, line: { color: 'white', width: 2 } },
        hovertemplate: '<b>%{label}</b><br>%{value} pubs (%{percent})<extra></extra>'
    }], {
        ...BASE,
        height: 320,
        margin: { t: 10, r: 10, b: 10, l: 10 },
        showlegend: true,
        legend: { font: { family: FONT, size: 10 }, orientation: 'v', x: 1, xanchor: 'left', y: 0.5 },
    }, CFG);
})();

// ── Filter submission ──────────────────────────────────────────────────────────
// All three dropdowns point to the same handler. On any change we rebuild the
// URL with only the non-"all" params and navigate — letting the controller
// re-query with the correct filters server-side.
document.querySelectorAll('#sectorFilter, #semesterFilter, #departmentFilter')
    .forEach(sel => sel.addEventListener('change', applyFilters));

function applyFilters() {
    const sector = document.getElementById('sectorFilter').value;
    const sem    = document.getElementById('semesterFilter').value;
    const dept   = document.getElementById('departmentFilter').value;

    const params = new URLSearchParams();
    // Only append when NOT "all" — omitting keeps the controller default logic intact
    if (sector !== 'all') params.append('role_type',  sector);
    if (sem    !== 'all') params.append('semester',   sem);
    if (dept   !== 'all') params.append('department', dept);

    const url = new URL(window.location.href);
    url.search = params.toString();
    window.location.href = url.toString();
}

function clearFilters() {
    // Navigate to the clean route with no query params — controller will use defaults
    window.location.href = '{{ route("stzfaculty.research-performance") }}';
}
</script>
</body>
</html>