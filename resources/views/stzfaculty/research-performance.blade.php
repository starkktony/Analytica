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
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        /* ── Filter bar ── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 0 24px;
            border-bottom: 1px solid #b0b5b0;
            height: 52px;
            min-height: 52px;
            overflow-x: auto;
            overflow-y: hidden;
        }
        .filter-bar::-webkit-scrollbar { display: none; }
        .filter-bar { -ms-overflow-style: none; scrollbar-width: none; }
        .filter-bar.is-loading select,
        .filter-bar.is-loading button { pointer-events: none; opacity: 0.5; }

        .page-title {
            font-size: 14px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .filter-bar-label {
            font-size: 12px;
            font-weight: 700;
            color: #2d2d2d;
            margin-left: auto;
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
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
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
            flex-shrink: 0;
        }
        .college-badge.visible { display: flex; }

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
        .charts-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .chart-row {
            display: grid;
            gap: 20px;
        }
        .two-col { grid-template-columns: 1fr 1fr; }
        @media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

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
    </style>
</head>
<body>

@include('components.sidebar')

<div class="content">

    {{-- Page header --}}
    <div class="header">Research &amp; Non-Teaching Load</div>

    {{-- Filter bar --}}
    <div class="filter-bar" id="filterBar">

        <div class="page-title" id="pageTitle">
            Research &amp; Non-Teaching Load
            @if(isset($activeSemObj))
                ({{ $activeSemObj->semester }} {{ $activeSemObj->sy }})
            @endif
        </div>

        <span class="college-badge {{ $filters['college'] !== 'all' && isset($selectedCollegeObj) ? 'visible' : '' }}"
              id="collegeBadge">
            <i class="bi bi-building"></i>
            <span id="collegeBadgeText">
                {{ isset($selectedCollegeObj) ? $selectedCollegeObj->college_acro : '' }}
            </span>
        </span>

        <div class="filter-bar-label">Filters:</div>

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

    {{-- Main Content --}}
    <div class="main-content">

        {{-- Stat Cards --}}
        <div class="cards-grid">
            <div class="stat-card green" id="cardResearch">
                <div class="stat-card-icon"><i class="fa-solid fa-flask"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-number" id="statResearch">
                        {{ number_format($researchLoad->sum('research_count')) }}
                    </div>
                    <div class="stat-card-label">Total Research Assignments</div>
                </div>
            </div>
            <div class="stat-card white" id="cardEtl">
                <div class="stat-card-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-number" id="statEtl">
                        {{ number_format($researchLoad->sum('total_etl'), 0) }}
                    </div>
                    <div class="stat-card-label">Total Equivalent Teaching Load Hours</div>
                </div>
            </div>
            <div class="stat-card white" id="cardPubs">
                <div class="stat-card-icon"><i class="fa-solid fa-newspaper"></i></div>
                <div class="stat-card-body">
                    <div class="stat-card-number" id="statPubs">
                        {{ number_format($publications->sum('publication_count')) }}
                    </div>
                    <div class="stat-card-label">Publications</div>
                </div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="charts-section">

            <div class="chart-row two-col">
                <div class="chart-card">
                    <div class="loading-overlay" id="loadAssignments"><div class="spinner"></div></div>
                    <h3>Research Assignments by Department</h3>
                    <div id="chart-assignments"></div>
                </div>
                <div class="chart-card">
                    <div class="loading-overlay" id="loadEtl"><div class="spinner"></div></div>
                    <h3>Total Research ETL Hours by Department</h3>
                    <div id="chart-etl"></div>
                </div>
            </div>

            <div class="chart-row two-col">
                <div class="chart-card">
                    <div class="loading-overlay" id="loadPublications"><div class="spinner"></div></div>
                    <h3>Faculty Research Output by Department</h3>
                    <div id="chart-publications"></div>
                </div>
                <div class="chart-card">
                    <div class="loading-overlay" id="loadPubTypes"><div class="spinner"></div></div>
                    <h3>Research Output by Types</h3>
                    <div id="chart-pub-types"></div>
                </div>
            </div>

        </div>{{-- /.charts-section --}}

    </div>{{-- /.main-content --}}

</div><!-- /.content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── All JS unchanged ──────────────────────────────────────────────────────────
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
const CAP = 10;

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

function updateStatCards(totals) {
    document.getElementById('statResearch').textContent =
        Number(totals.researchCount).toLocaleString();
    document.getElementById('statPubs').textContent =
        Number(totals.pubCount).toLocaleString();
    document.getElementById('statEtl').textContent =
        Number(totals.etlHours).toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function updatePageTitleSub(semesterText) {
    const el    = document.getElementById('pageTitleMain');
    if (!el) return;
    const clean = (semesterText || '').trim();
    el.innerHTML = '<strong>Research &amp; Non-Teaching Load'
        + (clean ? ' (' + clean + ')' : '') + '</strong>';
}

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

function buildParams() {
    const params  = new URLSearchParams();
    const sem     = document.getElementById('semesterFilter').value;
    const college = document.getElementById('collegeFilter').value;
    if (sem     !== 'all') params.set('semester', sem);
    if (college !== 'all') params.set('college',  college);
    return params;
}

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

function clearFilters() {
    document.getElementById('semesterFilter').value = 'all';
    document.getElementById('collegeFilter').value  = 'all';
    fetchAndRender();
}

function reflowCharts() {
    ['chart-assignments','chart-publications','chart-etl','chart-pub-types']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el && el.data) Plotly.relayout(id, { autosize: true });
        });
}

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