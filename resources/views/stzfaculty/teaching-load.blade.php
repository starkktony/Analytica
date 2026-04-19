<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siel Metrics</title>

    {{-- Asset bundler for CSS/JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- External CSS libraries --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Plotly for chart rendering --}}
    <script src="https://cdn.plot.ly/plotly-2.27.0.min.js"></script>

    {{-- Tom Select for enhanced dropdowns --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

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

        /* Shown when a chart has no data to display */
        .empty-chart {
            width: 100%;
            height: 100%;
            min-height: 280px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #ccc;
            gap: 8px;
        }
        .empty-chart i    { font-size: 32px; }
        .empty-chart span { font-size: 13px; font-weight: 600; }

        /* Pulsing skeleton effect while stats load */
        .stat-loading .stat-card-number,
        .stat-loading .stat-card-label {
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

        /* Pill badge in header showing drilled-down college/dept */
        .drill-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 13px;
            font-weight: 600;
            color: white;
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
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-lg md:text-2xl font-[650] text-white">Teaching Load</span>

                    {{-- Drill-down badge: shows college and optional department when filtered --}}
                    @if($drillDown && $selectedCollege)
                        <span class="drill-badge">
                            <i class="bi bi-building"></i>
                            {{ $selectedCollege->college_acro }}
                            @if($filters['department'] !== 'all')
                                <span>&rsaquo; {{ $departments->firstWhere('department_id', $filters['department'])?->department_acro }}</span>
                            @endif
                        </span>
                    @endif
                </div>
            </header>

            <div id="filterBar" class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">

                {{-- Dynamic title updates with active semester --}}
                <div class="font-[650] text-sm md:text-lg" id="pageTitle">
                    @php
                        // Resolve selected semester display text
                        $selectedSem = $semesters->firstWhere('sem_id', $filters['semester']);
                        echo $selectedSem
                            ? 'Faculty Teaching Load (' . $selectedSem->semester . ' ' . $selectedSem->sy . ')'
                            : 'Faculty Teaching Load';
                    @endphp
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
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->sem_id }}"
                                    {{ $filters['semester'] == $semester->sem_id ? 'selected' : '' }}>
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
                            <option value="all" {{ $filters['college'] === 'all' ? 'selected' : '' }}>All</option>
                            @foreach($colleges as $college)
                                <option value="{{ $college->c_u_id }}"
                                    {{ $filters['college'] == $college->c_u_id ? 'selected' : '' }}>
                                    {{ $college->college_acro }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Department filter: hidden until a college is selected --}}
                    <div class="flex items-center gap-2" id="departmentFilterGroup"
                        style="{{ !$drillDown ? 'display:none;' : '' }}">
                        <span class="text-sm font-medium">Department:</span>
                        <select id="departmentFilter"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
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

                    {{-- Redirects to default route, clearing all filters --}}
                    <button onclick="clearFilters()"
                        class="text-xs font-semibold bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700 transition">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
        {{-- ── End sticky header ── --}}

        <div class="px-6 pt-4 pb-8">

            {{-- ── Stat Cards: key teaching load metrics at a glance ── --}}
            <div class="grid grid-cols-4 md:grid-cols-12 gap-3 mb-2">

                {{-- Average actual teaching hours per faculty --}}
                <div class="col-span-4">
                    <div id="cardAvgAtl" class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 mt-2 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-chart-line text-white text-2xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p id="statAvgAtl" class="stat-card-number pr-4 pt-2">{{ number_format($avgAtl, 1) }}</p>
                                <p class="stat-card-label pr-4">Average Actual Teaching Load</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Total faculty with submitted workloads --}}
                <div class="col-span-4">
                    <div id="cardFaculty" class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 mt-2 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-users text-white text-2xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p id="statFaculty" class="stat-card-number pr-4 pt-2">{{ number_format($totalFaculty) }}</p>
                                <p class="stat-card-label pr-4">Total Faculty</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Estimated total credit units offered --}}
                <div class="col-span-4">
                    <div id="cardSubjects" class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 mt-2 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-book-open text-white text-2xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p id="statSubjects" class="stat-card-number pr-4 pt-2">{{ number_format($totalSubjects) }}</p>
                                <p class="stat-card-label pr-4">Total Teaching Units</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            {{-- ── End Stat Cards ── --}}

            {{-- ── Charts grid ── --}}
            <div class="grid grid-cols-6 md:grid-cols-12 gap-3 mb-3">

                {{-- ① Horizontal bar: average ATL ranked by college/dept --}}
                <div class="col-span-6 h-[350px] md:h-[400px] lg:h-[500px] border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl relative">
                    <div class="loading-overlay" id="loadAtlRank"><div class="spinner"></div></div>
                    <div class="grid grid-rows-7 h-full">
                        <div class="row-span-1 font-[750] text-sm sm:text-lg text-gray-700 pt-4 pl-5 sm:pl-7">
                            {{-- Label updates dynamically via JS --}}
                            Average ATL Ranking by <span class="group-label-text">{{ $chartGroupLabel }}</span>
                        </div>
                        <div class="row-span-6 h-full w-full">
                            <div id="chart-atl-rank" style="width:100%; height:100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- ② Pie chart: faculty count per workload bracket --}}
                <div class="col-span-6 h-[350px] md:h-[400px] lg:h-[500px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl relative">
                    <div class="loading-overlay" id="loadWorkload"><div class="spinner"></div></div>
                    <div class="grid grid-rows-7 h-full">
                        <div class="row-span-1 font-[750] text-sm sm:text-lg text-gray-700 pt-4 pl-5 sm:pl-7">
                            Faculty Workload Distribution
                        </div>
                        <div class="row-span-6 h-full w-full">
                            <div id="chart-workload-pie" style="width:100%; height:100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- ③ Full-width vertical bar: estimated units offered per college/dept --}}
                <div class="col-span-6 md:col-span-12 h-[350px] md:h-[400px] lg:h-[500px] border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl relative mb-2">
                    <div class="loading-overlay" id="loadSubjects"><div class="spinner"></div></div>
                    <div class="grid grid-rows-7 h-full">
                        <div class="row-span-1 font-[750] text-sm sm:text-lg text-gray-700 pt-4 pl-5 sm:pl-7">
                            {{-- Label updates dynamically via JS --}}
                            Units Offered by <span class="group-label-text">{{ $chartGroupLabel }}</span>
                        </div>
                        <div class="row-span-6 h-full w-full">
                            <div id="chart-subjects" style="width:100%; height:100%;"></div>
                        </div>
                    </div>
                    {{-- Disclaimer: units are estimated using per-college multipliers --}}
                    <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pb-2">
                        <i>Note: Total units are estimated based on per-college credit unit multipliers.</i>
                    </div>
                </div>

            </div>
            {{-- ── End Charts ── --}}

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ── Seed data from server on initial page load ──
    const INITIAL_DATA = {
        chartStats      : {!! json_encode($chartStats) !!},
        workloadDist    : {!! json_encode($workloadDistribution) !!},
        avgAtl          : {{ $avgAtl ?? 0 }},
        totalFaculty    : {{ $totalFaculty ?? 0 }},
        totalSubjects   : {{ $totalSubjects ?? 0 }},
        chartGroupLabel : '{{ $chartGroupLabel }}',
        semesterText    : '{{ $selectedSem ? $selectedSem->semester . " " . $selectedSem->sy : "" }}',
    };

    // AJAX endpoints and CSRF token for filter requests
    const AJAX_URL   = '{{ route("stzfaculty.teaching-load.ajax") }}';
    const DEPTS_URL  = '{{ url("/stzfaculty/departments-by-college") }}';  // Fetches depts by college ID
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // Shared style constants for all Plotly charts
    const FONT  = { family: "'Inter', sans-serif", size: 12, color: '#444' };
    const GREEN = '#009539';
    const BLUE  = '#2c7be5';

    // Shared Plotly config: responsive, minimal toolbar
    const CFG   = {
        responsive: true,
        displaylogo: false,
        modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d']
    };

    // Fixed pixel height applied to each chart div
    const CHART_H = 380;

    // Per-college multiplier to estimate total credit units from subject count
    const UNIT_MULTIPLIERS = {
        'CED' : 3.2, 'COS'  : 3.5, 'CASS': 3.0,
        'CEN' : 3.8, 'CAG'  : 3.3, 'CHSI': 3.1,
        'CVSM': 3.4, 'CBA'  : 3.0, 'CF'  : 3.2,
    };
    // Fallback multiplier for colleges not in the map
    const DEFAULT_MULTIPLIER = 3.0;

    // Activates spinners and applies shimmer to stat cards
    function showLoaders() {
        ['loadAtlRank','loadWorkload','loadSubjects']
            .forEach(id => document.getElementById(id)?.classList.add('active'));
        ['cardAvgAtl','cardFaculty','cardSubjects']
            .forEach(id => document.getElementById(id)?.classList.add('stat-loading'));
    }

    // Removes all spinners and stat shimmer effects
    function hideLoaders() {
        ['loadAtlRank','loadWorkload','loadSubjects']
            .forEach(id => document.getElementById(id)?.classList.remove('active'));
        ['cardAvgAtl','cardFaculty','cardSubjects']
            .forEach(id => document.getElementById(id)?.classList.remove('stat-loading'));
    }

    // Replaces chart div with an empty-state message
    function showEmpty(id) {
        try { Plotly.purge(id); } catch(e) {}
        document.getElementById(id).innerHTML =
            `<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>`;
    }

    // Purges Plotly instance and resets div before re-rendering
    function clearDiv(id) {
        try { Plotly.purge(id); } catch(e) {}
        const el = document.getElementById(id);
        el.innerHTML   = '';
        el.style.cssText = '';
    }

    // Sets fixed pixel height on chart div before rendering
    function setH(id) { document.getElementById(id).style.height = CHART_H + 'px'; }

    // Renders horizontal bar: average ATL ranked per college/dept
    function renderAtlRank(chartStats) {
        // Filter zero-ATL rows, sort ascending for bottom-to-top display
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
            font: FONT, paper_bgcolor: 'rgba(0,0,0,0)', plot_bgcolor: 'rgba(0,0,0,0)',
            margin: { t: 10, r: 90, b: 40, l: 80 },
            xaxis: {
                title: { text: 'ATL (hours)', font: { size: 11 } },
                gridcolor: '#efefef', zeroline: false,
                // Add 25% padding so outside labels don't clip
                range: [0, Math.max(...data.map(d => parseFloat(d.avg_atl))) * 1.25],
            },
            yaxis: { tickfont: { size: 11 }, automargin: true },
        }, CFG);
    }

    // Renders pie chart: faculty count per workload bracket
    function renderWorkloadPie(workloadDist) {
        // Map workload brackets to labeled slices, exclude zero values
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
            font: FONT,
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor:  'rgba(0,0,0,0)',
            legend: {
                orientation: 'h', x: 0.5, xanchor: 'center',
                y: 1.18, yanchor: 'top', font: { size: 11 },
            },
            margin: { t: 12, r: 20, b: 20, l: 20 },
        }, CFG);
    }

    // Renders vertical bar: estimated units offered per college/dept
    function renderSubjects(chartStats) {
        // Apply per-college multiplier to estimate total credit units
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
            font: FONT, paper_bgcolor: 'rgba(0,0,0,0)', plot_bgcolor: 'rgba(0,0,0,0)',
            margin: { t: 30, r: 20, b: 70, l: 70 },
            xaxis: { tickangle: -30, tickfont: { size: 11 }, automargin: true },
            yaxis: {
                title: { text: 'Total Units Offered', font: { size: 11 } },
                gridcolor: '#efefef', zeroline: false,
                range: [0, maxUnits * 1.2],
            },
        }, CFG);
    }

    // Calls all three chart render functions at once
    function renderAll(data) {
        renderAtlRank(data.chartStats);
        renderWorkloadPie(data.workloadDist);
        renderSubjects(data.chartStats);
    }

    // Updates the three stat card numbers from fetched data
    function updateStatCards(data) {
        document.getElementById('statAvgAtl').textContent   = parseFloat(data.avgAtl).toFixed(1);
        document.getElementById('statFaculty').textContent  = Number(data.totalFaculty).toLocaleString();
        document.getElementById('statSubjects').textContent = Number(data.totalSubjects).toLocaleString();
    }

    // Updates chart title spans with current grouping label (College/Dept)
    function updateGroupLabels(label) {
        document.querySelectorAll('.group-label-text').forEach(el => el.textContent = label);
    }

    // Updates page title with active semester text
    function updatePageTitle(semesterText) {
        document.getElementById('pageTitle').textContent = semesterText
            ? `Faculty Teaching Load (${semesterText})`
            : 'Faculty Teaching Load';
    }

    // Fetches departments for selected college, then triggers callback
    function loadDepartments(collegeId, callback) {
        const group  = document.getElementById('departmentFilterGroup');
        const select = document.getElementById('departmentFilter');

        // Hide dept filter and reset options when no college selected
        if (collegeId === 'all') {
            group.style.display = 'none';
            select.innerHTML    = '<option value="all">All</option>';
            if (callback) callback();
            return;
        }

        // Show dept filter and fetch options from server
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

    // Collects current filter values into URLSearchParams
    function buildParams() {
        const params  = new URLSearchParams();
        const sem     = document.getElementById('semesterFilter').value;
        const college = document.getElementById('collegeFilter').value;
        const dept    = document.getElementById('departmentFilter').value;
        // Only append non-default values to keep URL clean
        if (sem)               params.set('semester',   sem);
        if (college !== 'all') params.set('college',    college);
        if (dept    !== 'all') params.set('department', dept);
        return params;
    }

    // Fetches filtered data then re-renders all charts
    function fetchAndRender() {
        showLoaders();
        const params = buildParams();

        fetch(`${AJAX_URL}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
        })
        .then(r => r.json())
        .then(data => {
            updateStatCards(data);
            updateGroupLabels(data.chartGroupLabel);
            updatePageTitle(data.semesterText);
            renderAll(data);

            // Sync URL query string without page reload
            const url = new URL(window.location.href);
            url.search = params.toString();
            window.history.replaceState({}, '', url.toString());
        })
        .catch(err => console.error('Teaching Load AJAX error:', err))
        .finally(()  => hideLoaders());
    }

    // Redirects to default route, clearing all filters
    function clearFilters() {
        window.location.href = '{{ route("stzfaculty.teaching-load") }}';
    }

    // Tells Plotly to resize all charts to current container
    function reflowCharts() {
        ['chart-atl-rank','chart-workload-pie','chart-subjects'].forEach(id => {
            try { Plotly.relayout(id, { autosize: true }); } catch(e) {}
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Render all charts with server-provided initial data
        renderAll(INITIAL_DATA);

        // College change: fetch departments first, then re-render charts
        document.getElementById('collegeFilter').addEventListener('change', function () {
            loadDepartments(this.value, fetchAndRender);
        });

        // Semester and department changes trigger direct re-fetch
        document.getElementById('semesterFilter').addEventListener('change', fetchAndRender);
        document.getElementById('departmentFilter').addEventListener('change', fetchAndRender);

        // Reflow charts after sidebar animation completes
        const sidebarBtn = document.getElementById('sidebarToggle');
        if (sidebarBtn) sidebarBtn.addEventListener('click', () => setTimeout(reflowCharts, 320));

        // Reflow charts when content area resizes (sidebar toggle)
        const charts     = ['chart-atl-rank','chart-workload-pie','chart-subjects'];
        const contentDiv = document.querySelector('.content');
        if (contentDiv) {
            const ro = new ResizeObserver(() => {
                charts.forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.data) Plotly.Plots.resize(el);
                });
            });
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