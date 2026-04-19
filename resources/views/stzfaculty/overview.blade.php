<!DOCTYPE html>
<html>
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
    <script src="https://cdn.plot.ly/plotly-2.27.1.min.js"></script>

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

        /* ── Stat card numbers ── */
        .stat-card-number {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 2.8rem;
            line-height: 1;
            color: #1f2937;
            text-align: right;
        }
        .stat-card-pct {
            font-size: 12px;
            font-weight: 600;
            text-align: right;
        }
        .stat-card-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 11px;
            color: #6b7280;
            text-align: right;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }

        /* ── Chart card container ── */
        .chart-card {
            position: relative;
            overflow: hidden;
        }
        .chart-card h3 {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin: 0;
            padding: 14px 18px 0;
            word-break: break-word;
            line-height: 1.4;
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

        /* Shown when chart has no data */
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
        .no-data-overlay i   { font-size: 40px; color: #ccc; }
        .no-data-overlay span { font-size: 13px; font-weight: 600; color: #999; }

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

        /* Disables filter controls during fetch */
        .filter-bar-loading select,
        .filter-bar-loading button {
            pointer-events: none;
            opacity: 0.5;
        }
    </style>

    {{-- Tailwind utility classes via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    {{-- Reusable sidebar component --}}
    @include('components.sidebar')

    <div class="content">

        {{-- ── Sticky header + filter bar ── --}}
        <div class="sticky top-0 z-50">
            <header>
                <span class="text-lg md:text-2xl font-[650] text-white">Faculty Profile</span>
            </header>

            {{-- Filter bar: semester and college selectors --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4" id="filterBar">

                {{-- Dynamic title updates based on active filters --}}
                <div class="font-[650] text-sm md:text-lg" id="dynamicTitle">
                    @php
                        // Resolve selected semester display text
                        $selectedSemesterObj = $semesters->firstWhere('sem_id', $filters['semester']);
                        $semesterDisplay = $selectedSemesterObj ? $selectedSemesterObj->semester . ' ' . $selectedSemesterObj->sy : '';
                        // Show college acronym if a specific college is selected
                        if ($filters['college'] != 'all') {
                            $selectedUnit = $colleges->firstWhere('c_u_id', $filters['college']);
                            $unitDisplay  = $selectedUnit ? $selectedUnit->college_acro : '';
                            echo $unitDisplay . ' Faculty Profile (' . $semesterDisplay . ')';
                        } else {
                            echo 'Faculty Profile (' . $semesterDisplay . ')';
                        }
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
                                <option value="{{ $semester->sem_id }}" {{ $filters['semester'] == $semester->sem_id ? 'selected' : '' }}>
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
                            @foreach($colleges as $college)
                                <option value="{{ $college->c_u_id }}" {{ $filters['college'] == $college->c_u_id ? 'selected' : '' }}>
                                    {{ $college->college_acro }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </div>
        {{-- ── End sticky header ── --}}

        <div class="px-6">

            {{-- ── Stat Cards: key faculty metrics at a glance ── --}}
            <div class="grid grid-cols-4 md:grid-cols-12 gap-3 mb-2">

                {{-- Total faculty registered in EWMS --}}
                <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-3">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden" id="cardTotal">
                        <div class="grid grid-rows-4 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-users text-white text-2xl"></i>
                            </div>
                            <div class="row-span-3 pt-4">
                                <p class="stat-card-number pr-4" id="statTotalNum">{{ $totalFaculty }}</p>
                                <p class="stat-card-label pr-4">Total EWMS Faculty</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Faculty with active status --}}
                <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-3">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden" id="cardActive">
                        <div class="grid grid-rows-4 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-user-check text-white text-2xl"></i>
                            </div>
                            <div class="row-span-3 pt-4">
                                <p class="stat-card-number pr-4" id="statActiveNum">{{ $activeCount }}</p>
                                <p class="stat-card-label pr-4">Active Faculty</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Faculty holding doctorate degrees --}}
                <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-3">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden" id="cardPhd">
                        <div class="grid grid-rows-4 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-user-graduate text-white text-2xl"></i>
                            </div>
                            <div class="row-span-3 pt-4">
                                <p class="stat-card-number pr-4" id="statPhdNum">{{ $phdHolders }}</p>
                                <p class="stat-card-label pr-4">PhD Holders</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Faculty holding master's degrees --}}
                <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-3">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden" id="cardMasters">
                        <div class="grid grid-rows-4 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-book-open text-white text-2xl"></i>
                            </div>
                            <div class="row-span-3 pt-4">
                                <p class="stat-card-number pr-4" id="statMastersNum">{{ $mastersHolders }}</p>
                                <p class="stat-card-label pr-4">Masters Holders</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            {{-- ── End Stat Cards ── --}}

            {{-- ── Charts: 3-column grid layout ── --}}
            <div class="grid grid-cols-4 xl:grid-cols-12 gap-3">

                {{-- ① Horizontal bar: submitted workloads per college/dept --}}
                <div class="col-span-4 h-[340px] sm:h-[500px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card" id="cardRanking">
                    <h3 id="rankingTitle">
                        @if($filters['college'] != 'all')
                            @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                            {{ $collegeName ? $collegeName->college_acro : '' }} Submitted Faculty Workload by Department
                        @else
                            Submitted Faculty Workload per College
                        @endif
                    </h3>
                    <div style="height: calc(100% - 46px); width: 100%;">
                        <div id="facultyRankingChart" style="width:100%; height:100%;"></div>
                    </div>
                    <div class="loading-overlay" id="loadRanking"><div class="spinner"></div></div>
                </div>

                {{-- ② Donut chart: faculty by employment category --}}
                <div class="col-span-4 h-[300px] sm:h-[500px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card" id="cardEmployment">
                    <h3 id="employmentTitle">
                        @if($filters['college'] != 'all')
                            @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                            {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Employment Status
                        @else
                            Faculty Employment Status
                        @endif
                    </h3>
                    <div style="height: calc(100% - 46px); width: 100%;">
                        <div id="employmentChart" style="width:100%; height:100%;"></div>
                    </div>
                    <div class="loading-overlay" id="loadEmployment"><div class="spinner"></div></div>
                </div>

                {{-- ③ Donut chart: active vs on-leave faculty --}}
                <div class="col-span-4 h-[300px] sm:h-[500px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card" id="cardStatus">
                    <h3 id="statusTitle">
                        @if($filters['college'] != 'all')
                            @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                            {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Availability Status
                        @else
                            Faculty Availability Status
                        @endif
                    </h3>
                    <div style="height: calc(100% - 46px); width: 100%;">
                        <div id="statusChart" style="width:100%; height:100%;"></div>
                    </div>
                    <div class="loading-overlay" id="loadStatus"><div class="spinner"></div></div>
                </div>

            </div>

            {{-- ④ Full-width stacked bar: degree breakdown per college/dept --}}
            <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[380px] sm:h-[420px] mt-3 mb-8 chart-card" id="cardQual">
                <h3 id="qualificationTitle" class="font-[750] text-sm sm:text-lg text-gray-700 pl-5 sm:pl-7 pt-4">
                    @if($filters['college'] != 'all')
                        @php $collegeName = $colleges->where('c_u_id', $filters['college'])->first(); @endphp
                        {{ $collegeName ? $collegeName->college_acro : '' }} Faculty Qualification Distribution
                    @else
                        Faculty Qualification Distribution
                    @endif
                </h3>
                <div style="height: calc(100% - 54px); width: 100%;">
                    <div id="qualificationChart" style="width:100%; height:100%;"></div>
                </div>
                <div class="loading-overlay" id="loadQual"><div class="spinner"></div></div>
            </div>
            {{-- ── End Charts ── --}}

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ── Seed data from server on initial page load ──
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

    // AJAX endpoint and CSRF token for filter requests
    const AJAX_URL   = '{{ route("stzfaculty.overview.ajax") }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // Activates spinners and disables filter controls
    function showLoaders() {
        document.getElementById('filterBar').classList.add('filter-bar-loading');
        ['loadRanking','loadEmployment','loadStatus','loadQual'].forEach(id => {
            document.getElementById(id)?.classList.add('active');
        });
        // Apply shimmer to stat cards while loading
        ['cardTotal','cardActive','cardPhd','cardMasters'].forEach(id => {
            document.getElementById(id)?.classList.add('stat-shimmer');
        });
    }

    // Removes all spinners and re-enables controls
    function hideLoaders() {
        document.getElementById('filterBar').classList.remove('filter-bar-loading');
        ['loadRanking','loadEmployment','loadStatus','loadQual'].forEach(id => {
            document.getElementById(id)?.classList.remove('active');
        });
        ['cardTotal','cardActive','cardPhd','cardMasters'].forEach(id => {
            document.getElementById(id)?.classList.remove('stat-shimmer');
        });
    }

    // Shared layout config for all donut charts
    function donutLayout() {
        return {
            font: { family: 'Inter' },
            autosize: true,
            margin: { l: 20, r: 20, t: 70, b: 20 },
            annotations: [],
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor : 'rgba(0,0,0,0)',
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

    // Injects "No record found" overlay over empty chart
    function showNoData(div) {
        let overlay = div.parentNode.querySelector('.no-data-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'no-data-overlay';
            overlay.innerHTML = '<i class="bi bi-inbox"></i><span>No record found</span>';
            div.parentNode.appendChild(overlay);
        }
        overlay.style.display = 'flex';
        div.style.visibility = 'hidden';
    }

    // Hides the no-data overlay, restores chart
    function clearNoData(div) {
        const overlay = div.parentNode.querySelector('.no-data-overlay');
        if (overlay) overlay.style.display = 'none';
        div.style.visibility = 'visible';
    }

    // Converts hex color to rgba with per-bar opacity array
    function colorWithOpacity(baseColor, opacities) {
        const r = parseInt(baseColor.slice(1,3), 16);
        const g = parseInt(baseColor.slice(3,5), 16);
        const b = parseInt(baseColor.slice(5,7), 16);
        return opacities.map(o => `rgba(${r},${g},${b},${o})`);
    }

    // Shared Plotly config: responsive, minimal toolbar
    const plotCfg = {
        responsive: true,
        displaylogo: false,
        modeBarButtonsToRemove: ['lasso2d','select2d','zoomIn2d','zoomOut2d','autoScale2d','resetScale2d']
    };

    // Renders horizontal bar chart for workload submission
    function renderRanking(d) {
        const div = document.getElementById('facultyRankingChart');
        if (!div) return;

        if (!d.rankingLabels || d.rankingLabels.length === 0) { showNoData(div); return; }

        // Check if total faculty data exists alongside submissions
        const hasTotals = Array.isArray(d.rankingTotals)
            && d.rankingTotals.length === d.rankingLabels.length
            && d.rankingTotals.some(v => v > 0);

        const isEmpty = hasTotals
            ? !d.rankingTotals.some(v => v > 0)
            : !d.rankingCounts.some(v => v > 0);

        if (isEmpty) { showNoData(div); return; }
        clearNoData(div);

        if (hasTotals) {
            // Grouped bar: submitted workload vs total faculty
            const labels = [...d.rankingLabels].reverse();
            const counts = [...d.rankingCounts].reverse();
            const totals = [...d.rankingTotals].reverse();
            const maxVal = Math.max(...totals, ...counts, 1);

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
                font     : { family: 'Inter' },
                autosize : true,
                barmode  : 'group',
                margin   : { l: 65, r: 55, t: 50, b: 40 },
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
                paper_bgcolor: 'rgba(0,0,0,0)',
                plot_bgcolor : 'rgba(0,0,0,0)',
                showlegend   : true,
                legend: {
                    orientation: 'h', x: 0.5, y: 1.08,
                    xanchor: 'center', yanchor: 'bottom',
                    font: { family: 'Inter', size: 11 }
                },
                bargap      : 0.25,
                bargroupgap : 0.08
            }, plotCfg);

        } else {
            // Single bar: submitted workload only, highlight selected dept
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
                font     : { family: 'Inter' },
                autosize : true,
                margin   : { l: 60, r: 50, t: 20, b: 40 },
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
                paper_bgcolor: 'rgba(0,0,0,0)',
                plot_bgcolor : 'rgba(0,0,0,0)',
                showlegend   : false,
                bargap       : 0.3
            }, plotCfg);
        }
    }

    // Renders donut chart for employment status categories
    function renderEmployment(d) {
        const div = document.getElementById('employmentChart');
        if (!div) return;
        const colorMap = ['#009539','#2c7be5','#f6c343','#e74c3c'];
        const vals=[], labels=[], colors=[];
        // Filter out zero-value categories before plotting
        d.categoryData.forEach((v, i) => {
            if (v > 0) { vals.push(v); labels.push(d.categoryLabels[i]); colors.push(colorMap[i % colorMap.length]); }
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

    // Renders donut chart: active vs on-leave faculty
    function renderStatus(d) {
        const div = document.getElementById('statusChart');
        if (!div) return;
        const raw  = [d.activeCount, d.onLeaveCount];
        const lbl  = ['Active', 'On Leave'];
        const clr  = ['#009539', '#e74c3c'];
        const vals=[], labels=[], colors=[];
        // Exclude zero values from the chart
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

    // Recalculates percentages to always sum to exactly 100%
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
                // Bachelors gets remainder to avoid floating-point drift
                const b = parseFloat((100 - p - m).toFixed(4));
                normPhd.push(p); normMasters.push(m); normBachelors.push(b < 0 ? 0 : b);
            }
        });
        return { normPhd, normMasters, normBachelors };
    }

    // Renders stacked bar chart for degree qualification breakdown
    function renderQual(d) {
        const div = document.getElementById('qualificationChart');
        if (!div) return;

        // Remove colleges/depts with zero faculty across all degrees
        const filteredQualLabels=[], filteredPhdPct=[], filteredMastersPct=[], filteredBachelorsPct=[];
        const filteredPhdCounts=[], filteredMastersCounts=[], filteredBachelorsCounts=[];

        for (let i = 0; i < d.qualLabels.length; i++) {
            const totalCount = (d.phdCounts[i] || 0) + (d.mastersCounts[i] || 0) + (d.bachelorsCounts[i] || 0);
            if (totalCount > 0) {
                filteredQualLabels.push(d.qualLabels[i]);
                filteredPhdPct.push(d.phdPct[i]);
                filteredMastersPct.push(d.mastersPct[i]);
                filteredBachelorsPct.push(d.bachelorsPct[i]);
                filteredPhdCounts.push(d.phdCounts[i]);
                filteredMastersCounts.push(d.mastersCounts[i]);
                filteredBachelorsCounts.push(d.bachelorsCounts[i]);
            }
        }

        const totalQual = [...filteredPhdCounts, ...filteredMastersCounts, ...filteredBachelorsCounts]
            .reduce((a, b) => a + b, 0);
        if (!filteredQualLabels.length || totalQual === 0) { showNoData(div); return; }
        clearNoData(div);

        // Dim non-selected departments when one is active
        const sel = d.selectedDept;
        const opacities = sel
            ? filteredQualLabels.map(l => l === sel ? 1 : 0.4)
            : filteredQualLabels.map(() => 1);

        const { normPhd, normMasters, normBachelors } =
            normalizeQualPct(filteredPhdPct, filteredMastersPct, filteredBachelorsPct);

        Plotly.react(div, [
            {
                name : 'PhD',
                x    : filteredQualLabels,
                y    : normPhd,
                type : 'bar',
                marker: { color: colorWithOpacity('#1565c0', opacities) },
                text : filteredPhdCounts,
                textposition: 'none',
                hovertemplate: '<b>%{x}</b><br>PhD: %{y:.1f}% (%{text})<extra></extra>'
            },
            {
                name : 'Masters',
                x    : filteredQualLabels,
                y    : normMasters,
                type : 'bar',
                marker: { color: colorWithOpacity('#009539', opacities) },
                text : filteredMastersCounts,
                textposition: 'none',
                hovertemplate: '<b>%{x}</b><br>Masters: %{y:.1f}% (%{text})<extra></extra>'
            },
            {
                name : 'Bachelors',
                x    : filteredQualLabels,
                y    : normBachelors,
                type : 'bar',
                marker: { color: colorWithOpacity('#f6c343', opacities) },
                text : filteredBachelorsCounts,
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
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor : 'rgba(0,0,0,0)',
            showlegend   : true,
            legend: {
                orientation: 'h', x: 0.5, y: 1.18,
                xanchor: 'center', yanchor: 'top',
                font: { family: 'Inter', size: 11 }
            }
        }, plotCfg);
    }

    // Updates the four stat card numbers from fetched data
    function updateStatCards(d) {
        document.getElementById('statTotalNum').textContent   = d.totalFaculty;
        document.getElementById('statActiveNum').textContent  = d.activeCount;
        document.getElementById('statPhdNum').textContent     = d.phdHolders;
        document.getElementById('statMastersNum').textContent = d.mastersHolders;
    }

    // Updates all chart/page titles based on active filters
    function updateTitles(d, filters) {
        const col     = filters.college || d.collegeFilterValue || 'all';
        const sem     = filters.semesterText || d.semesterText  || '';
        const colAcro = d.collegeAcro || '';

        document.getElementById('dynamicTitle').textContent = col !== 'all'
            ? colAcro + ' Faculty Profile (' + sem + ')'
            : 'Faculty Profile (' + sem + ')';

        const prefix = col !== 'all' ? colAcro + ' ' : '';

        document.getElementById('rankingTitle').textContent = col !== 'all'
            ? colAcro + ' Submitted Faculty Workload by Department'
            : 'Submitted Faculty Workload per College';

        document.getElementById('employmentTitle').textContent    = prefix + 'Faculty Employment Status';
        document.getElementById('statusTitle').textContent        = prefix + 'Faculty Availability Status';
        document.getElementById('qualificationTitle').textContent = prefix + 'Faculty Qualification Distribution';
    }

    // Fetches filtered data then re-renders all charts
    function fetchAndRender(params) {
        showLoaders();
        fetch(AJAX_URL + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN }
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

            // Sync URL query string without page reload
            const url = new URL(window.location.href);
            url.search = params.toString();
            window.history.replaceState({}, '', url.toString());
        })
        .catch(err => console.error('Faculty filter error:', err))
        .finally(()  => hideLoaders());
    }

    // Collects current filter values into URLSearchParams
    function buildParams() {
        const params = new URLSearchParams();
        params.set('semester',   document.getElementById('semesterFilter').value);
        params.set('college',    document.getElementById('collegeFilter').value);
        params.set('department', 'all');
        return params;
    }

    // Triggered on semester or college change
    function applyFilters() {
        updateDynamicTitle();
        fetchAndRender(buildParams());
    }

    // Triggered specifically on college change (resets dept)
    function updateDepartments() {
        updateDynamicTitle();
        fetchAndRender(buildParams());
    }

    // Redirects to default route, clearing all filters
    function clearFilters() {
        window.location.href = '{{ route("stzfaculty.overview") }}';
    }

    // Instantly updates page title before fetch completes
    function updateDynamicTitle() {
        const semEl   = document.getElementById('semesterFilter');
        const semText = semEl.options[semEl.selectedIndex].text;
        const colEl   = document.getElementById('collegeFilter');
        const colText = colEl.options[colEl.selectedIndex].text;
        document.getElementById('dynamicTitle').textContent = colEl.value !== 'all'
            ? colText + ' Faculty Profile (' + semText + ')'
            : 'Faculty Profile (' + semText + ')';
    }

    // Tells Plotly to resize all charts to current container
    function reflowCharts() {
        ['facultyRankingChart','employmentChart','statusChart','qualificationChart']
            .forEach(id => {
                const div = document.getElementById(id);
                if (div && div.data) Plotly.relayout(div, { autosize: true });
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Bind filter dropdowns to change handlers
        document.getElementById('semesterFilter').addEventListener('change', applyFilters);
        document.getElementById('collegeFilter').addEventListener('change',  updateDepartments);

        // Reflow charts after sidebar animation completes
        const sidebarBtn = document.getElementById('sidebarToggle');
        if (sidebarBtn) sidebarBtn.addEventListener('click', () => setTimeout(reflowCharts, 320));

        // Render all charts with server-provided initial data
        updateTitles(INITIAL_DATA, {
            college     : INITIAL_DATA.collegeFilterValue,
            semesterText: INITIAL_DATA.semesterText
        });
        renderRanking(INITIAL_DATA);
        renderEmployment(INITIAL_DATA);
        renderStatus(INITIAL_DATA);
        renderQual(INITIAL_DATA);

        // Reflow charts when content area resizes (sidebar toggle)
        const contentDiv = document.querySelector('.content');
        if (contentDiv) {
            const ro = new ResizeObserver(() => reflowCharts());
            ro.observe(contentDiv);
        }

        // Debounced reflow on window resize
        let resizeTimeout;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(reflowCharts, 250);
        });
    });
    </script>
</body>
</html>