<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Research &amp; Non-Teaching Load</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>

    <style>
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Inter', sans-serif;
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
            display: flex;
            align-items: center;
        }

        /* ── Filter bar ──────────────────────────────────────────────────── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 0 30px;
            border-bottom: 1px solid #b0b5b0;
            height: 50px;
        }
        .filter-bar.is-loading select,
        .filter-bar.is-loading button { pointer-events: none; opacity: 0.5; }

        .page-title {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a1a;
            white-space: nowrap;
            flex-shrink: 0;
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

        /* College badge */
        .college-badge {
            display: none;
            align-items: center;
            gap: 5px;
            background: #006b2b;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 12px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .college-badge.visible { display: flex; }

        /* ── Stat cards ──────────────────────────────────────────────────── */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
            width: 48px; height: 48px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 16px; left: 16px;
        }
        .stat-card.green .icon-box { background: white; }
        .stat-card.green .icon-box i { font-size: 22px; color: #009539; }
        .stat-card:not(.green) .icon-box { background: #009539; }
        .stat-card:not(.green) .icon-box i { font-size: 22px; color: white; }
        .stat-content {
            display: flex; flex-direction: column;
            align-items: flex-end; justify-content: center; flex: 1;
        }
        .stat-number { font-size: 46px; font-weight: 700; line-height: 1; }
        .stat-card.green .stat-number { color: white; }
        .stat-card:not(.green) .stat-number { color: #1f1f1f; }
        .stat-label { font-size: 12px; font-weight: 600; margin-top: 4px; }
        .stat-card.green .stat-label { color: rgba(255,255,255,0.85); }
        .stat-card:not(.green) .stat-label { color: #777; }

        /* Shimmer while loading */
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

        /* ── Chart cards ─────────────────────────────────────────────────── */
        .charts-section {
            padding: 0 30px 30px;
            display: flex; flex-direction: column; gap: 20px;
        }
        .chart-row { display: grid; gap: 20px; }
        .two-col   { grid-template-columns: 1fr 1fr; }

        .chart-card {
            background: white;
            border-radius: 18px;
            padding: 22px 26px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
        }
        .loading-overlay {
            position: absolute; inset: 0;
            background: rgba(255,255,255,0.82);
            border-radius: inherit;
            display: flex; align-items: center; justify-content: center;
            z-index: 10; opacity: 0; pointer-events: none;
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

        .chart-title { font-size: 14px; font-weight: 700; color: #1a1a1a; margin-bottom: 10px; }
    </style>
</head>
<body>

@include('components.sidebar')

<div class="content">

    <div class="header">Research &amp; Non-Teaching Load</div>

    {{-- ── Filter bar ───────────────────────────────────────────────────── --}}
    <div class="filter-bar" id="filterBar">

        <div class="page-title" id="pageTitleMain">
            <strong>Research &amp; Non-Teaching Load
            @if(isset($activeSemObj))
                ({{ $activeSemObj->semester }} {{ $activeSemObj->sy }})
            @endif
            </strong>
        </div>

        {{-- Badge shown when a college is selected --}}
        <span class="college-badge {{ $filters['college'] !== 'all' && isset($selectedCollegeObj) ? 'visible' : '' }}"
              id="collegeBadge">
            <i class="bi bi-building"></i>
            <span id="collegeBadgeText">
                {{ isset($selectedCollegeObj) ? $selectedCollegeObj->college_acro : '' }}
            </span>
        </span>

        <div class="filter-bar-label">Filters:</div>

        {{-- Semester --}}
        <div class="filter-group">
            <label>Semester:</label>
            <select id="semesterFilter">
                <option value="all" {{ $filters['semester'] === 'all' ? 'selected' : '' }}>All</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester->sem_id }}"
                        {{ (string)$filters['semester'] == (string)$semester->sem_id ? 'selected' : '' }}>
                        {{ $semester->semester }} {{ $semester->sy }}
                    </option>
                @endforeach
            </select>
        </div>

        {{--
            Unit / Office — only the 9 academic colleges from table_college_unit.
            The $collegeUnits variable is pre-filtered in the controller to only
            include: CAG, CASS, CBA, CED, CEN, CF, CHSI, COS, CVSM  (c_u_id 1-9).
        --}}
        <div class="filter-group">
            <label>Unit/Office:</label>
            <select id="collegeFilter">
                <option value="all">All</option>
                @foreach($collegeUnits as $cu)
                    <option value="{{ $cu->c_u_id }}"
                        {{ (string)$filters['college'] == (string)$cu->c_u_id ? 'selected' : '' }}>
                        {{ $cu->college_acro }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="clear-filters-btn" id="clearFiltersBtn">
            <i class="bi bi-x-circle me-1"></i>Clear Filters
        </button>
    </div>

    {{-- ── Stat cards ───────────────────────────────────────────────────── --}}
    <div class="stats-container">
        <div class="stat-card green" id="cardResearch">
            <div class="icon-box"><i class="bi bi-flask"></i></div>
            <div class="stat-content">
                <div class="stat-number" id="statResearch">
                    {{ number_format($researchLoad->sum('research_count')) }}
                </div>
                <div class="stat-label">Research Assignments</div>
            </div>
        </div>
        <div class="stat-card" id="cardPubs">
            <div class="icon-box"><i class="bi bi-journal-text"></i></div>
            <div class="stat-content">
                <div class="stat-number" id="statPubs">
                    {{ number_format($publications->sum('publication_count')) }}
                </div>
                <div class="stat-label">Publications</div>
            </div>
        </div>
        <div class="stat-card" id="cardEtl">
            <div class="icon-box"><i class="bi bi-clock-history"></i></div>
            <div class="stat-content">
                <div class="stat-number" id="statEtl">
                    {{ number_format($researchLoad->sum('total_etl'), 0) }}
                </div>
                <div class="stat-label">Total ETL Hours</div>
            </div>
        </div>
    </div>

    {{-- ── Charts ───────────────────────────────────────────────────────── --}}
    <div class="charts-section" style="padding-top:14px;">

        <div class="chart-row two-col">
            <div class="chart-card">
                <div class="loading-overlay" id="loadAssignments"><div class="spinner"></div></div>
                <div class="chart-title">Research Assignments by Department</div>
                <div id="chart-assignments"></div>
            </div>
            <div class="chart-card">
                <div class="loading-overlay" id="loadPublications"><div class="spinner"></div></div>
                <div class="chart-title">Publications by Department</div>
                <div id="chart-publications"></div>
            </div>
        </div>

        <div class="chart-row two-col">
            <div class="chart-card">
                <div class="loading-overlay" id="loadEtl"><div class="spinner"></div></div>
                <div class="chart-title">Total ETL Hours by Department</div>
                <div id="chart-etl"></div>
            </div>
            <div class="chart-card">
                <div class="loading-overlay" id="loadPubTypes"><div class="spinner"></div></div>
                <div class="chart-title">Research Publication Types</div>
                <div id="chart-pub-types"></div>
            </div>
        </div>

    </div>

</div><!-- /.content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Initial server-rendered data ──────────────────────────────────────────────
const INITIAL_DATA = {
    researchLoad             : {!! json_encode($researchLoad) !!},
    publications             : {!! json_encode($publications) !!},
    publicationTypeBreakdown : {!! json_encode($publicationTypeBreakdown) !!},
    totals: {
        researchCount : {{ (int) $researchLoad->sum('research_count') }},
        pubCount      : {{ (int) $publications->sum('publication_count') }},
        etlHours      : {{ (float) round($researchLoad->sum('total_etl'), 0) }},
    },
    semesterText : '{{ isset($activeSemObj) ? $activeSemObj->semester . " " . $activeSemObj->sy : "" }}',
    collegeAcro  : '{{ isset($selectedCollegeObj) ? $selectedCollegeObj->college_acro : "" }}',
};

const AJAX_URL   = '{{ route("stzfaculty.research-performance.ajax") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// ── Plotly constants ──────────────────────────────────────────────────────────
const FONT    = "'Inter', sans-serif";
const GREEN   = '#009539';
const PALETTE = ['#009539','#2c7be5','#f6a623','#e74c3c','#9b59b6',
                 '#1abc9c','#e67e22','#34495e','#e91e63','#00bcd4'];
const BASE = {
    paper_bgcolor: '#ffffff',
    plot_bgcolor : '#ffffff',
    font: { family: FONT, size: 12, color: '#444' },
    xaxis: { gridcolor: '#efefef', linecolor: '#ddd', tickfont: { family: FONT } },
    yaxis: { gridcolor: '#efefef', linecolor: '#ddd', tickfont: { family: FONT } },
};
const CFG = {
    responsive: true,
    displaylogo: false,
    modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d']
};
const CAP = 10;  // top-N cap only when all units are shown

// ── Loaders ───────────────────────────────────────────────────────────────────
function showLoaders() {
    document.getElementById('filterBar').classList.add('is-loading');
    ['loadAssignments','loadPublications','loadEtl','loadPubTypes']
        .forEach(id => document.getElementById(id)?.classList.add('active'));
    ['cardResearch','cardPubs','cardEtl']
        .forEach(id => document.getElementById(id)?.classList.add('loading'));
}
function hideLoaders() {
    document.getElementById('filterBar').classList.remove('is-loading');
    ['loadAssignments','loadPublications','loadEtl','loadPubTypes']
        .forEach(id => document.getElementById(id)?.classList.remove('active'));
    ['cardResearch','cardPubs','cardEtl']
        .forEach(id => document.getElementById(id)?.classList.remove('loading'));
}

// ── No-data placeholder ───────────────────────────────────────────────────────
function noData(divId) {
    const el = document.getElementById(divId);
    if (!el) return;
    try { Plotly.purge(divId); } catch(e) {}
    el.setAttribute('data-nodata','1');
    el.style.cssText = 'height:280px;display:flex;align-items:center;justify-content:center;';
    el.innerHTML = `<div style="text-align:center;color:#ccc;">
        <i class="bi bi-bar-chart" style="font-size:2rem;"></i>
        <p style="margin-top:8px;font-size:0.82rem;">No data for current filters</p>
    </div>`;
}
function clearDiv(divId) {
    const el = document.getElementById(divId);
    if (!el) return;
    try { Plotly.purge(divId); } catch(e) {}
    el.removeAttribute('data-nodata');
    el.style.cssText = '';
    el.innerHTML     = '';
}

function capData(arr, collegeFilter) {
    return collegeFilter === 'all' ? arr.slice(0, CAP) : arr;
}

// ══════════════════════════════════════════════════════════════════════════════
// CHART RENDERERS
// ══════════════════════════════════════════════════════════════════════════════

function renderAssignments(researchLoad, collegeFilter) {
    clearDiv('chart-assignments');
    if (!researchLoad.length) return noData('chart-assignments');

    const sorted = [...researchLoad].sort((a, b) => b.research_count - a.research_count);
    const data   = capData(sorted, collegeFilter);
    const depts  = data.map(d => d.department_acro);
    const counts = data.map(d => parseInt(d.research_count) || 0);

    Plotly.react('chart-assignments', [{
        type: 'bar', x: depts, y: counts,
        text: counts.map(v => v.toLocaleString()),
        textposition: 'outside',
        textfont: { family: FONT, size: 11 },
        marker: { color: GREEN },
        hovertemplate: '<b>%{x}</b><br>Assignments: %{y}<extra></extra>'
    }], {
        ...BASE, height: 320,
        margin: { t: 24, r: 20, b: 80, l: 50 },
        xaxis: { ...BASE.xaxis, tickangle: -35 },
        yaxis: { ...BASE.yaxis, title: { text: 'Assignments', font: { family: FONT, size: 12 } } },
        bargap: 0.35, showlegend: false,
    }, CFG);
}

function renderPublications(publications, collegeFilter) {
    clearDiv('chart-publications');
    if (!publications.length) return noData('chart-publications');

    const sorted  = [...publications].sort((a, b) => b.publication_count - a.publication_count);
    const data    = capData(sorted, collegeFilter);
    const dataRev = [...data].reverse();
    const depts   = dataRev.map(d => d.department_acro);
    const pubs    = dataRev.map(d => parseInt(d.publication_count) || 0);

    Plotly.react('chart-publications', [{
        type: 'bar', orientation: 'h', x: pubs, y: depts,
        text: pubs.map(v => v.toLocaleString()),
        textposition: 'outside',
        textfont: { family: FONT, size: 11, color: '#333' },
        marker: { color: GREEN },
        hovertemplate: '<b>%{y}</b><br>Publications: %{x}<extra></extra>'
    }], {
        ...BASE, height: 320,
        margin: { t: 10, r: 55, b: 40, l: 70 },
        xaxis: { ...BASE.xaxis, title: { text: 'Publications', font: { family: FONT, size: 12 } } },
        yaxis: { ...BASE.yaxis, automargin: true },
        bargap: 0.35,
    }, CFG);
}

function renderEtl(researchLoad, collegeFilter) {
    clearDiv('chart-etl');
    if (!researchLoad.length) return noData('chart-etl');

    const sorted  = [...researchLoad].sort((a, b) => b.total_etl - a.total_etl);
    const data    = capData(sorted, collegeFilter);
    const dataRev = [...data].reverse();
    const depts   = dataRev.map(d => d.department_acro);
    const etls    = dataRev.map(d => parseFloat(d.total_etl) || 0);

    Plotly.react('chart-etl', [{
        type: 'bar', orientation: 'h', x: etls, y: depts,
        text: etls.map(v => v.toFixed(1)),
        textposition: 'outside',
        textfont: { family: FONT, size: 11, color: '#333' },
        marker: { color: GREEN },
        hovertemplate: '<b>%{y}</b><br>ETL: %{x:.2f} hrs<extra></extra>'
    }], {
        ...BASE, height: 320,
        margin: { t: 10, r: 65, b: 40, l: 70 },
        xaxis: { ...BASE.xaxis, title: { text: 'ETL Hours', font: { family: FONT, size: 12 } } },
        yaxis: { ...BASE.yaxis, automargin: true },
        bargap: 0.35,
    }, CFG);
}

function renderPubTypes(publicationTypeBreakdown) {
    clearDiv('chart-pub-types');
    if (!publicationTypeBreakdown.length) return noData('chart-pub-types');

    const labels = publicationTypeBreakdown.map(p => p.pub_type);
    const values = publicationTypeBreakdown.map(p => parseInt(p.type_count) || 0);
    const total  = values.reduce((a, b) => a + b, 0);
    if (total === 0) return noData('chart-pub-types');

    Plotly.react('chart-pub-types', [{
        type: 'pie', labels, values, hole: 0.52,
        textinfo: 'percent', textposition: 'outside',
        textfont: { family: FONT, size: 11 },
        marker: { colors: PALETTE, line: { color: 'white', width: 2 } },
        hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>%{percent}<extra></extra>',
        automargin: true,
    }], {
        ...BASE, height: 320,
        margin: { t: 60, r: 10, b: 10, l: 10 },
        showlegend: true,
        legend: { font: { family: FONT, size: 10 }, orientation: 'h',
                  x: 0.5, xanchor: 'center', y: 1.12, yanchor: 'top' },
        annotations: [{
            text: `<b>Total</b><br><b>${total.toLocaleString()}</b>`,
            x: 0.5, y: 0.5, xref: 'paper', yref: 'paper',
            showarrow: false,
            font: { family: FONT, size: 13, color: '#333' },
            align: 'center',
        }],
    }, CFG);
}

function renderAll(data, collegeFilter) {
    renderAssignments(data.researchLoad,           collegeFilter);
    renderPublications(data.publications,          collegeFilter);
    renderEtl(data.researchLoad,                   collegeFilter);
    renderPubTypes(data.publicationTypeBreakdown);
}

// ── Update stat cards ─────────────────────────────────────────────────────────
function updateStatCards(totals) {
    document.getElementById('statResearch').textContent =
        Number(totals.researchCount).toLocaleString();
    document.getElementById('statPubs').textContent =
        Number(totals.pubCount).toLocaleString();
    document.getElementById('statEtl').textContent =
        Number(totals.etlHours).toLocaleString(undefined, { maximumFractionDigits: 0 });
}

// ── Update page title ─────────────────────────────────────────────────────────
function updatePageTitleSub(semesterText) {
    const el    = document.getElementById('pageTitleMain');
    if (!el) return;
    const clean = (semesterText || '').trim();
    el.innerHTML = '<strong>Research &amp; Non-Teaching Load'
        + (clean ? ' (' + clean + ')' : '') + '</strong>';
}

// ── Update college badge ──────────────────────────────────────────────────────
function updateCollegeBadge(collegeAcro) {
    const badge = document.getElementById('collegeBadge');
    const txt   = document.getElementById('collegeBadgeText');
    if (!badge || !txt) return;
    if (collegeAcro) {
        txt.textContent = collegeAcro;
        badge.classList.add('visible');
    } else {
        badge.classList.remove('visible');
        txt.textContent = '';
    }
}

// ── Build query params ────────────────────────────────────────────────────────
function buildParams() {
    const params  = new URLSearchParams();
    const sem     = document.getElementById('semesterFilter').value;
    const college = document.getElementById('collegeFilter').value;
    if (sem     !== 'all') params.set('semester', sem);
    if (college !== 'all') params.set('college',  college);
    return params;
}

// ── AJAX fetch → render ───────────────────────────────────────────────────────
function fetchAndRender() {
    showLoaders();
    const params        = buildParams();
    const collegeFilter = document.getElementById('collegeFilter').value;

    fetch(AJAX_URL + '?' + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN'    : CSRF_TOKEN,
        }
    })
    .then(r => { if (!r.ok) throw new Error('Server ' + r.status); return r.json(); })
    .then(data => {
        updateStatCards(data.totals);
        updatePageTitleSub(data.semesterText);
        updateCollegeBadge(data.collegeAcro);
        renderAll(data, collegeFilter);

        const url = new URL(window.location.href);
        url.search = params.toString();
        window.history.replaceState({}, '', url.toString());
    })
    .catch(err => console.error('Research AJAX error:', err))
    .finally(()  => hideLoaders());
}

// ── Clear Filters ─────────────────────────────────────────────────────────────
function clearFilters() {
    document.getElementById('semesterFilter').value = 'all';
    document.getElementById('collegeFilter').value  = 'all';
    fetchAndRender();
}

// ── Sidebar reflow ────────────────────────────────────────────────────────────
function reflowCharts() {
    ['chart-assignments','chart-publications','chart-etl','chart-pub-types']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el && el.data) Plotly.relayout(id, { autosize: true });
        });
}

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    ['semesterFilter','collegeFilter'].forEach(id =>
        document.getElementById(id)?.addEventListener('change', fetchAndRender)
    );

    document.getElementById('clearFiltersBtn')
        ?.addEventListener('click', clearFilters);

    const sidebarBtn = document.getElementById('sidebarToggle');
    if (sidebarBtn) sidebarBtn.addEventListener('click', () => setTimeout(reflowCharts, 320));

    renderAll(INITIAL_DATA, document.getElementById('collegeFilter').value);

    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(reflowCharts, 250);
    });
});
</script>
</body>
</html>