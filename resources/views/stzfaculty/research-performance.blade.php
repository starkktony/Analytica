<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Asset bundler for CSS/JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- External CSS libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Plotly for chart rendering --}}
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>

    {{-- Tom Select for enhanced dropdowns --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <title>Siel Metrics</title>

    <style>
        /* Offset content from sidebar width */
        .content {
            margin-left: 250px;
            transition: margin-left 0.3s ease, max-width 0.3s ease;
            max-width: calc(100vw - 250px);
            overflow-x: clip;
        }

        /* Shrink content area when sidebar collapses */
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        /* Fix Bootstrap collapse visibility bug */
        .collapse.show {
            visibility: visible !important;
        }

        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow-x: clip;
        }

        /* Top sticky header bar */
        header {
            height: 70px;
            padding: 2rem 3rem;
            background-color: #009539;
            box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Semi-transparent overlay shown during data fetch */
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
        /* Makes overlay visible */
        .loading-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        /* Spinning animation for loader */
        .spinner {
            width: 36px;
            height: 36px;
            border: 4px solid #d4ead4;
            border-top-color: #009539;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Pulsing skeleton effect while stats load */
        .stat-shimmer .stat-card-number,
        .stat-shimmer .stat-card-label {
            background: linear-gradient(90deg, #e0e0e0 25%, #f0f0f0 50%, #e0e0e0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.2s infinite;
            border-radius: 4px;
            color: transparent !important;
        }
        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Large bold number in each stat card */
        .stat-card-number {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 2.8rem;
            line-height: 1;
            color: #1f2937;
            text-align: right;
        }
        /* Small descriptor below the stat number */
        .stat-card-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 11px;
            color: #6b7280;
            text-align: right;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }

        /* Positions relative for loading overlay placement */
        .chart-card {
            position: relative;
            overflow: hidden;
        }
        .chart-card h3 {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin: 0 0 4px 0;
            padding: 14px 18px 0;
        }

        /* Disables filter controls during fetch */
        .filter-bar-loading select,
        .filter-bar-loading button {
            pointer-events: none;
            opacity: 0.5;
        }

        /* Pill badge showing selected college; hidden by default */
        .college-badge {
            display: none;
            align-items: center;
            gap: 5px;
            background: #006b2b;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        /* Reveals badge when a college is selected */
        .college-badge.visible {
            display: flex;
        }
    </style>

    {{-- Tailwind utility classes via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    {{-- Reusable sidebar component --}}
    @include('components.sidebar')

    <div class="content w-100">

        {{-- ── Sticky header + filter bar ── --}}
        <div class="sticky top-0 z-50">
            <header>
                <span class="text-lg md:text-2xl font-[650] text-white">Research &amp; Non-Teaching Load</span>
            </header>

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4" id="filterBar">
                <div class="flex items-center gap-2 font-[650] text-sm md:text-lg">

                    {{-- Dynamic title updates with active semester --}}
                    <span id="pageTitle">
                        Research &amp; Non-Teaching Load
                        @if(isset($activeSemObj))
                            ({{ $activeSemObj->semester }} {{ $activeSemObj->sy }})
                        @endif
                    </span>

                    {{-- College pill badge, visible only when a college is selected --}}
                    <span class="college-badge {{ $filters['college'] !== 'all' && isset($selectedCollegeObj) ? 'visible' : '' }}"
                          id="collegeBadge">
                        <i class="bi bi-building"></i>
                        <span id="collegeBadgeText">{{ isset($selectedCollegeObj) ? $selectedCollegeObj->college_acro : '' }}</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <div class="hidden sm:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                        Filter
                    </div>

                    {{-- Semester dropdown filter --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Semester:</span>
                        <select id="semesterFilter"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            <option value="all" {{ $filters['semester'] === 'all' ? 'selected' : '' }}>All</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->sem_id }}"
                                    {{ (string)$filters['semester'] == (string)$semester->sem_id ? 'selected' : '' }}>
                                    {{ $semester->semester }} {{ $semester->sy }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- College/unit dropdown filter --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Unit/Office:</span>
                        <select id="collegeFilter"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            <option value="all">All</option>
                            @foreach($collegeUnits as $cu)
                                <option value="{{ $cu->c_u_id }}"
                                    {{ (string)$filters['college'] == (string)$cu->c_u_id ? 'selected' : '' }}>
                                    {{ $cu->college_acro }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        {{-- ── End sticky header ── --}}

        <div class="px-6 pt-4 pb-10">

            {{-- ── Stat Cards: key research metrics at a glance ── --}}
            <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-4">

                {{-- Count of all research assignments --}}
                <div class="col-span-3 xl:col-span-4">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden" id="cardResearch">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-flask text-white text-2xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p class="stat-card-number pr-4 pt-2" id="statResearch">{{ number_format($researchLoad->sum('research_count')) }}</p>
                                <p class="stat-card-label pr-4">Total Research Assignments</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sum of equivalent teaching load hours --}}
                <div class="col-span-3 xl:col-span-4">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden" id="cardEtl">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-clock-rotate-left text-white text-2xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p class="stat-card-number pr-4 pt-2" id="statEtl">{{ number_format($researchLoad->sum('total_etl'), 0) }}</p>
                                <p class="stat-card-label pr-4">Total ETL Hours</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total published research outputs --}}
                <div class="col-span-3 xl:col-span-4">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden" id="cardPubs">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-newspaper text-white text-2xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p class="stat-card-number pr-4 pt-2" id="statPubs">{{ number_format($publications->sum('publication_count')) }}</p>
                                <p class="stat-card-label pr-4">Publications</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            {{-- ── End Stat Cards ── --}}

            {{-- ── Charts: 2×2 grid layout ── --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">

                {{-- ① Vertical bar: research assignments per department --}}
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                    <h3>Research Assignments by Department</h3>
                    <div id="chart-assignments"></div>
                    <div class="loading-overlay" id="loadAssignments"><div class="spinner"></div></div>
                </div>

                {{-- ② Horizontal bar: ETL hours per department --}}
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                    <h3>Total Research ETL Hours by Department</h3>
                    <div id="chart-etl"></div>
                    <div class="loading-overlay" id="loadEtl"><div class="spinner"></div></div>
                </div>

                {{-- ③ Horizontal bar: publications per department --}}
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                    <h3>Faculty Research Output by Department</h3>
                    <div id="chart-publications"></div>
                    <div class="loading-overlay" id="loadPublications"><div class="spinner"></div></div>
                </div>

                {{-- ④ Donut chart: publications broken down by type --}}
                <div class="border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                    <h3>Research Output by Types</h3>
                    <div id="chart-pub-types"></div>
                    <div class="loading-overlay" id="loadPubTypes"><div class="spinner"></div></div>
                </div>

            </div>
            {{-- ── End Charts ── --}}

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ── Seed data from server on initial page load ──
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

    // AJAX endpoint and CSRF token for filter requests
    const AJAX_URL   = '{{ route("stzfaculty.research-performance.ajax") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // Shared style constants for all Plotly charts
    const FONT    = "'Inter', sans-serif";
    const GREEN   = '#009539';
    const PALETTE = ['#009539','#2c7be5','#f6a623','#e74c3c','#9b59b6',
                     '#1abc9c','#e67e22','#34495e','#e91e63','#00bcd4'];

    // Reusable Plotly layout base for transparent backgrounds
    const BASE = {
        paper_bgcolor: 'rgba(0,0,0,0)',
        plot_bgcolor : 'rgba(0,0,0,0)',
        font: { family: FONT, size: 12, color: '#444' },
        xaxis: { gridcolor: '#efefef', linecolor: '#ddd', tickfont: { family: FONT } },
        yaxis: { gridcolor: '#efefef', linecolor: '#ddd', tickfont: { family: FONT } },
    };

    // Shared Plotly config: responsive, minimal toolbar
    const CFG = {
        responsive: true,
        displaylogo: false,
        modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d']
    };

    // Max bars shown when viewing all colleges
    const CAP = 10;

    // Activates spinners and disables filter controls
    function showLoaders() {
        document.getElementById('filterBar').classList.add('filter-bar-loading');
        ['loadAssignments','loadPublications','loadEtl','loadPubTypes']
            .forEach(id => document.getElementById(id)?.classList.add('active'));
        // Apply shimmer to stat cards while loading
        ['cardResearch','cardPubs','cardEtl']
            .forEach(id => document.getElementById(id)?.classList.add('stat-shimmer'));
    }

    // Removes all spinners and re-enables controls
    function hideLoaders() {
        document.getElementById('filterBar').classList.remove('filter-bar-loading');
        ['loadAssignments','loadPublications','loadEtl','loadPubTypes']
            .forEach(id => document.getElementById(id)?.classList.remove('active'));
        ['cardResearch','cardPubs','cardEtl']
            .forEach(id => document.getElementById(id)?.classList.remove('stat-shimmer'));
    }

    // Replaces chart div content with empty-state message
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

    // Purges Plotly and resets div before re-rendering
    function clearDiv(divId) {
        const el = document.getElementById(divId);
        if (!el) return;
        try { Plotly.purge(divId); } catch(e) {}
        el.removeAttribute('data-nodata');
        el.style.cssText = '';
        el.innerHTML     = '';
    }

    // Limits rows to CAP when showing all colleges
    function capData(arr, collegeFilter) {
        return collegeFilter === 'all' ? arr.slice(0, CAP) : arr;
    }

    // Renders vertical bar: research assignments per department
    function renderAssignments(researchLoad, collegeFilter) {
        clearDiv('chart-assignments');
        if (!researchLoad.length) return noData('chart-assignments');

        // Sort descending, then cap if viewing all colleges
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

    // Renders horizontal bar: publications per department
    function renderPublications(publications, collegeFilter) {
        clearDiv('chart-publications');
        if (!publications.length) return noData('chart-publications');

        // Reverse for bottom-to-top horizontal bar ordering
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

    // Renders horizontal bar: ETL hours per department
    function renderEtl(researchLoad, collegeFilter) {
        clearDiv('chart-etl');
        if (!researchLoad.length) return noData('chart-etl');

        // Reverse for bottom-to-top horizontal bar ordering
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

    // Renders donut chart: publications broken down by type
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
            // Center annotation shows total count inside the donut hole
            annotations: [{
                text: `<b>Total</b><br><b>${total.toLocaleString()}</b>`,
                x: 0.5, y: 0.5, xref: 'paper', yref: 'paper',
                showarrow: false,
                font: { family: FONT, size: 13, color: '#333' },
                align: 'center',
            }],
        }, CFG);
    }

    // Calls all four chart render functions at once
    function renderAll(data, collegeFilter) {
        renderAssignments(data.researchLoad,           collegeFilter);
        renderPublications(data.publications,          collegeFilter);
        renderEtl(data.researchLoad,                   collegeFilter);
        renderPubTypes(data.publicationTypeBreakdown);
    }

    // Updates the three stat card numbers from fetched data
    function updateStatCards(totals) {
        document.getElementById('statResearch').textContent =
            Number(totals.researchCount).toLocaleString();
        document.getElementById('statPubs').textContent =
            Number(totals.pubCount).toLocaleString();
        document.getElementById('statEtl').textContent =
            Number(totals.etlHours).toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    // Updates page title with active semester text
    function updatePageTitle(semesterText) {
        const el    = document.getElementById('pageTitle');
        if (!el) return;
        const clean = (semesterText || '').trim();
        el.textContent = 'Research & Non-Teaching Load' + (clean ? ' (' + clean + ')' : '');
    }

    // Shows/hides the college pill badge in the header
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

    // Collects current filter values into URLSearchParams
    function buildParams() {
        const params  = new URLSearchParams();
        const sem     = document.getElementById('semesterFilter').value;
        const college = document.getElementById('collegeFilter').value;
        // Only append non-default values to keep URL clean
        if (sem     !== 'all') params.set('semester', sem);
        if (college !== 'all') params.set('college',  college);
        return params;
    }

    // Fetches filtered data then re-renders all charts
    function fetchAndRender() {
        showLoaders();
        const params        = buildParams();
        const collegeFilter = document.getElementById('collegeFilter').value;

        // Show/hide clear button based on active filters
        const clearBtn = document.getElementById('clearFiltersBtn');
        if (clearBtn) {
            const sem     = document.getElementById('semesterFilter').value;
            const college = document.getElementById('collegeFilter').value;
            clearBtn.style.display = (sem !== 'all' || college !== 'all') ? '' : 'none';
        }

        fetch(AJAX_URL + '?' + params.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN'    : CSRF_TOKEN,
            }
        })
        .then(r => { if (!r.ok) throw new Error('Server ' + r.status); return r.json(); })
        .then(data => {
            updateStatCards(data.totals);
            updatePageTitle(data.semesterText);
            updateCollegeBadge(data.collegeAcro);
            renderAll(data, collegeFilter);

            // Sync URL query string without page reload
            const url = new URL(window.location.href);
            url.search = params.toString();
            window.history.replaceState({}, '', url.toString());
        })
        .catch(err => console.error('Research AJAX error:', err))
        .finally(()  => hideLoaders());
    }

    // Resets both filters to "all" then re-fetches
    function clearFilters() {
        document.getElementById('semesterFilter').value = 'all';
        document.getElementById('collegeFilter').value  = 'all';
        fetchAndRender();
    }

    // Tells Plotly to resize all charts to current container
    function reflowCharts() {
        ['chart-assignments','chart-publications','chart-etl','chart-pub-types']
            .forEach(id => {
                const el = document.getElementById(id);
                if (el && el.data) Plotly.relayout(id, { autosize: true });
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Bind both filter dropdowns to fetch handler
        ['semesterFilter','collegeFilter'].forEach(id =>
            document.getElementById(id)?.addEventListener('change', fetchAndRender)
        );

        // Clear button resets all filters
        document.getElementById('clearFiltersBtn')
            ?.addEventListener('click', clearFilters);

        // Reflow charts after sidebar animation completes
        const sidebarBtn = document.getElementById('sidebarToggle');
        if (sidebarBtn) sidebarBtn.addEventListener('click', () => setTimeout(reflowCharts, 320));

        // Render all charts with server-provided initial data
        renderAll(INITIAL_DATA, document.getElementById('collegeFilter').value);

        // Reflow charts when content area resizes (sidebar toggle)
        const contentDiv = document.querySelector('.content');
        if (contentDiv) {
            const ro = new ResizeObserver(() => reflowCharts());
            ro.observe(contentDiv);
        }

        // Debounced reflow on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(reflowCharts, 250);
        });
    });
    </script>
</body>
</html>