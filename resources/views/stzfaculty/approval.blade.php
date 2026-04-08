<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <title>Siel Metrics</title>

    <style>
        .content {
            margin-left: 250px;
            transition: margin-left 0.3s ease, max-width 0.3s ease;
            max-width: calc(100vw - 250px);
            overflow-x: clip;
        }

        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        .collapse.show {
            visibility: visible !important;
        }

        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow-x: clip;
        }

        header {
            height: 70px;
            padding: 2rem 3rem;
            background-color: #009539;
            box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Chart loader overlay */
        .chart-loader {
            position: absolute;
            inset: 0;
            background: rgba(249,250,251,0.92);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            z-index: 20;
            gap: 14px;
            opacity: 1;
            pointer-events: all;
            transition: opacity 0.35s ease;
        }
        .chart-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .loader-ring {
            width: 40px;
            height: 40px;
            border: 3px solid #e4e4e4;
            border-top-color: #009539;
            border-radius: 50%;
            animation: spin 0.72s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader-label {
            font-size: 12px;
            font-weight: 600;
            color: #aaa;
        }
        .skeleton-stack { display: flex; flex-direction: column; gap: 9px; width: 55%; }
        .skel-bar {
            height: 10px;
            border-radius: 6px;
            background: linear-gradient(90deg, #ececec 25%, #dedede 50%, #ececec 75%);
            background-size: 200% 100%;
            animation: shimmer-anim 1.3s infinite;
        }
        .skel-bar:nth-child(1) { width: 90%; }
        .skel-bar:nth-child(2) { width: 65%; animation-delay: 0.15s; }
        .skel-bar:nth-child(3) { width: 80%; animation-delay: 0.3s; }
        @keyframes shimmer-anim {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

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
        .empty-chart i { font-size: 32px; }
        .empty-chart span { font-size: 13px; font-weight: 600; }

        /* Stat card number uses Inter like programs page */
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
        }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    @include('components.sidebar')

    <div class="content w-100">

        {{-- ── Sticky header + filter bar (mirrors programs page) ── --}}
        <div class="sticky top-0 z-50">
            <header>
                <span class="text-lg md:text-2xl font-[650] text-white">Workload Approval</span>
            </header>

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">
                <div class="font-[650] text-sm md:text-lg" id="barTitle">
                    Faculty Workload Approval
                    @if($filters['main_semester'])
                        @php $sem = $availableSemesters->firstWhere('sem_id', $filters['main_semester']); @endphp
                        @if($sem)({{ $sem->semester }} {{ $sem->sy }})@endif
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <div class="hidden sm:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                        Filter
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Semester:</span>
                        <select id="mainSemester"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            <option value="">All</option>
                            @foreach($availableSemesters as $sem)
                                <option value="{{ $sem->sem_id }}"
                                        data-label="{{ $sem->semester }} {{ $sem->sy }}"
                                        {{ $filters['main_semester'] == $sem->sem_id ? 'selected' : '' }}>
                                    {{ $sem->semester }} {{ $sem->sy }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Office:</span>
                        <select id="mainOffice"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            <option value="">All</option>
                            <option value="dh"       {{ $filters['main_signatory'] == 'dh'       ? 'selected' : '' }}>Dept Head</option>
                            <option value="dean"     {{ $filters['main_signatory'] == 'dean'     ? 'selected' : '' }}>Dean</option>
                            <option value="director" {{ $filters['main_signatory'] == 'director' ? 'selected' : '' }}>Director</option>
                            <option value="dot_uni"  {{ $filters['main_signatory'] == 'dot_uni'  ? 'selected' : '' }}>DOT UNI</option>
                            <option value="nstp"     {{ $filters['main_signatory'] == 'nstp'     ? 'selected' : '' }}>NSTP</option>
                            <option value="eteeap"   {{ $filters['main_signatory'] == 'eteeap'   ? 'selected' : '' }}>ETEEAP</option>
                            <option value="vpaa"     {{ $filters['main_signatory'] == 'vpaa'     ? 'selected' : '' }}>VPAA</option>
                        </select>
                    </div>

                    <a href="{{ route('stzfaculty.approval') }}" id="clearBtn"
                        class="text-xs font-semibold bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700 transition"
                        style="{{ ($filters['main_semester'] || $filters['main_signatory']) ? '' : 'display:none;' }}">
                        Clear Filters
                    </a>
                </div>
            </div>
        </div>
        {{-- ── End sticky header ── --}}

        <div class="px-6 pt-4">

            {{-- ── Stat Cards (identical structure to programs page) ── --}}
            <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-4">

                {{-- Active Faculty --}}
                <div class="col-span-3">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-file-earmark-text-fill text-white text-3xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p class="stat-card-number pr-4 pt-2" id="statTotal">{{ number_format($totalDocuments) }}</p>
                                <p class="stat-card-label pr-4">Active Faculty</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approved --}}
                <div class="col-span-3">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-check-circle-fill text-white text-3xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p class="stat-card-number pr-4 pt-2" id="statApproved">{{ number_format($fullyApproved) }}</p>
                                <p class="stat-card-label pr-4">Approved</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submitted --}}
                <div class="col-span-3">
                    <div class="border-l-[5px] border-yellow-400 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-yellow-400/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-clock-fill text-white text-3xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p class="stat-card-number pr-4 pt-2" id="statSubmitted">{{ number_format($pendingApproval) }}</p>
                                <p class="stat-card-label pr-4">Submitted</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Declined --}}
                <div class="col-span-3">
                    <div class="border-l-[5px] border-red-500 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-red-500/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-x-circle-fill text-white text-3xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                <p class="stat-card-number pr-4 pt-2" id="statDeclined">{{ number_format($declined) }}</p>
                                <p class="stat-card-label pr-4">Declined</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            {{-- ── End Stat Cards ── --}}

            {{-- ── Charts Row 1: Pie + Bar (mirrors programs left/right split) ── --}}
            <div class="grid grid-cols-6 xl:grid-cols-12 gap-2">

                {{-- Pie chart — border-t like "Programs per Type" --}}
                <div class="col-span-6 xl:col-span-4">
                    <div class="border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[320px] sm:h-[370px] lg:h-[570px] rounded-[1vw] shadow-inner shadow-xl">
                        <div class="w-full grid grid-cols-12 grid-rows-7">
                            <div class="col-span-12 row-span-1 font-[750] text-sm md:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-7">
                                Workload Approval Status
                            </div>
                            <div class="col-span-12 row-span-6 relative">
                                <div id="overallStatusChart" class="w-full h-full"></div>
                                <div class="chart-loader" id="loaderPie">
                                    <div class="loader-ring"></div>
                                    <div class="skeleton-stack">
                                        <div class="skel-bar"></div>
                                        <div class="skel-bar"></div>
                                        <div class="skel-bar"></div>
                                    </div>
                                    <div class="loader-label">Loading chart…</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bar + Timeline charts — border-l like programs trend charts --}}
                <div class="col-span-6 lg:col-span-8">

                    <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px] relative">
                        <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                            Workload Status by Office
                        </div>
                        <div>
                            <div id="signatoryTypeChart" style="width: 100%;"></div>
                        </div>
                        <div class="chart-loader" id="loaderBar">
                            <div class="loader-ring"></div>
                            <div class="skeleton-stack">
                                <div class="skel-bar"></div>
                                <div class="skel-bar"></div>
                                <div class="skel-bar"></div>
                                <div class="skel-bar"></div>
                            </div>
                            <div class="loader-label">Loading chart…</div>
                        </div>
                    </div>

                    <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px] mt-2 mb-2 relative">
                        <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                            Annual Faculty Workload Status Breakdown
                        </div>
                        <div>
                            <div id="timelineStackedChart" style="width: 100%;"></div>
                        </div>
                        <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pr-2">
                            <i>Note: Data for certain semesters is unavailable; only semesters with recorded submissions are displayed.</i>
                        </div>
                        <div class="chart-loader" id="loaderTimeline">
                            <div class="loader-ring"></div>
                            <div class="skeleton-stack">
                                <div class="skel-bar"></div>
                                <div class="skel-bar"></div>
                                <div class="skel-bar"></div>
                                <div class="skel-bar"></div>
                            </div>
                            <div class="loader-label">Loading chart…</div>
                        </div>
                    </div>

                </div>

            </div>
            {{-- ── End Charts ── --}}

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ─────────────────────────────────────────────────────────────
    // Server-side initial data
    // ─────────────────────────────────────────────────────────────
    let overallStats = {
        totalDocuments:  {{ $totalDocuments }},
        fullyApproved:   {{ $fullyApproved }},
        pendingApproval: {{ $pendingApproval }},
        declined:        {{ $declined }},
        overallApproved: {{ $overallApproved }},
        overallPending:  {{ $overallPending }},
        overallDeclined: {{ $overallDeclined }}
    };
    let signatoryStats = {
        dh:       @json($dhStats),
        dean:     @json($deanStats),
        director: @json($directorStats),
        dot_uni:  @json($dotUniStats),
        nstp:     @json($nstpStats),
        eteeap:   @json($eteeapStats),
        vpaa:     @json($vpaaStats)
    };
    let timeline = {
        years:          @json($timelineYears),
        documentCounts: @json(array_values($yearlyDocumentCounts)),
        approvedCounts: @json(array_values($yearlyApprovedCounts)),
        declinedCounts: @json(array_values($yearlyDeclinedCounts)),
        pendingCounts:  @json(array_values($yearlyPendingCounts))
    };
    let currentFilter = '{{ $filters['main_signatory'] }}';
    let currentSem    = '{{ $filters['main_semester'] }}';

    // ─────────────────────────────────────────────────────────────
    // Theme
    // ─────────────────────────────────────────────────────────────
    const FONT   = { family: "'Inter', sans-serif", size: 12, color: '#444' };
    const GREEN  = '#009539';
    const YELLOW = '#ffc107';
    const RED    = '#dc3545';
    const GRAY   = '#adb5bd';
    const cfg    = { responsive: true, displayModeBar: false, staticPlot: false };

    // ─────────────────────────────────────────────────────────────
    // Loader helpers
    // ─────────────────────────────────────────────────────────────
    const showLoader = id => document.getElementById(id)?.classList.remove('hidden');
    const hideLoader = id => document.getElementById(id)?.classList.add('hidden');

    function showAllLoaders() {
        ['loaderPie', 'loaderBar', 'loaderTimeline'].forEach(showLoader);
    }
    function hideAllLoaders() {
        ['loaderPie', 'loaderBar', 'loaderTimeline'].forEach(hideLoader);
    }

    // ─────────────────────────────────────────────────────────────
    // Chart renderers — unchanged from original
    // ─────────────────────────────────────────────────────────────
    function renderPieChart() {
        const keys = ['dh','dean','director','dot_uni','nstp','eteeap','vpaa'];
        let approved, submitted, declined, total;

        if (currentFilter && keys.includes(currentFilter)) {
            approved  = signatoryStats[currentFilter].approved;
            submitted = signatoryStats[currentFilter].pending;
            declined  = signatoryStats[currentFilter].declined;
            total     = signatoryStats[currentFilter].total || overallStats.totalDocuments;
        } else {
            approved  = overallStats.fullyApproved;
            submitted = overallStats.pendingApproval;
            declined  = overallStats.declined;
            total     = overallStats.totalDocuments;
        }

        const notYet   = Math.max(0, total - approved - submitted - declined);
        const pieTotal = approved + submitted + declined + notYet;

        const container = document.getElementById('overallStatusChart');

        if (pieTotal === 0) {
            if (container && container.data) Plotly.purge(container);
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-pie-chart"></i><span>No data available</span></div>';
            hideLoader('loaderPie');
            return;
        }

        if (container && container.data) Plotly.purge(container);

        const pieData = [{
            type: 'pie',
            values: [approved, submitted, declined, notYet],
            labels: ['Approved', 'Submitted', 'Declined', 'Not Yet Submitted'],
            marker: { colors: [GREEN, YELLOW, RED, GRAY], line: { color: '#fff', width: 2 } },
            textinfo: 'label+percent',
            textfont: { size: 11 },
            hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>%{percent}<extra></extra>',
            domain: { x: [0.05, 0.95], y: [0, 0.85] }
        }];

        const layout = {
            font: FONT,
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor:  'rgba(0,0,0,0)',
            margin: { t: 30, r: 20, b: 20, l: 20 },
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.05, yanchor: 'bottom', font: { size: 11 } },
            showlegend: true,
            autosize: true,
        };

        Plotly.newPlot(container, pieData, layout, cfg).then(() => {
            hideLoader('loaderPie');
            setTimeout(() => Plotly.Plots.resize(container), 100);
        });
    }

    function renderBarChart() {
        const labels = ['Dept Head','Dean','Director','DOT UNI','NSTP','ETEEAP','VPAA'];
        const keys   = ['dh','dean','director','dot_uni','nstp','eteeap','vpaa'];
        const aP=[], sP=[], dP=[], nP=[], totals=[];

        keys.forEach(k => {
            const a  = signatoryStats[k].approved || 0;
            const s  = signatoryStats[k].pending  || 0;
            const d  = signatoryStats[k].declined || 0;
            const t  = signatoryStats[k].total    || 0;
            const ny = Math.max(0, t - a - s - d);
            totals.push(t);
            if (t > 0) {
                aP.push(+(a/t*100).toFixed(1));  sP.push(+(s/t*100).toFixed(1));
                dP.push(+(d/t*100).toFixed(1));  nP.push(+(ny/t*100).toFixed(1));
            } else { aP.push(0); sP.push(0); dP.push(0); nP.push(0); }
        });

        const container = document.getElementById('signatoryTypeChart');

        if (totals.reduce((a,b) => a+b, 0) === 0) {
            if (container && container.data) Plotly.purge(container);
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>';
            hideLoader('loaderBar');
            return;
        }

        if (container && container.data) Plotly.purge(container);

        const selIdx = currentFilter && keys.includes(currentFilter) ? keys.indexOf(currentFilter) : -1;
        const mkCol  = (base, dim=0.18) =>
            keys.map((_, i) => selIdx === -1 ? base
                : i === selIdx ? base
                : base + Math.round(dim*255).toString(16).padStart(2,'0'));

        const yTickColors = labels.map((_, i) =>
            selIdx === -1 ? '#444' : i === selIdx ? '#009539' : '#bbb');

        const barData = [
            { name:'Approved',          type:'bar', orientation:'h', x:aP, y:labels, marker:{color:mkCol(GREEN)},  hovertemplate:'<b>%{y}</b><br>Approved: %{x:.1f}%<extra></extra>' },
            { name:'Submitted',         type:'bar', orientation:'h', x:sP, y:labels, marker:{color:mkCol(YELLOW)}, hovertemplate:'<b>%{y}</b><br>Submitted: %{x:.1f}%<extra></extra>' },
            { name:'Declined',          type:'bar', orientation:'h', x:dP, y:labels, marker:{color:mkCol(RED)},    hovertemplate:'<b>%{y}</b><br>Declined: %{x:.1f}%<extra></extra>' },
            { name:'Not Yet Submitted', type:'bar', orientation:'h', x:nP, y:labels, marker:{color:mkCol(GRAY)},   hovertemplate:'<b>%{y}</b><br>Not Yet Submitted: %{x:.1f}%<extra></extra>' }
        ];

        const layout = {
            font: FONT,
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor:  'rgba(0,0,0,0)',
            barmode: 'stack',
            height: 200,
            margin: { t: 10, b: 30, l: 100, r: 20 },
            showlegend: true,
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1, yanchor: 'bottom', font: { size: 10, color: 'black' } },
            xaxis: { title: { text: 'Percent (%)', font: { size: 11 } }, range: [0,100], ticksuffix: '%', gridcolor: '#f0f0f0', zeroline: false },
            yaxis: { tickfont: { size: 10, color: yTickColors }, linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B' },
        };

        Plotly.newPlot(container, barData, layout, cfg).then(() => {
            hideLoader('loaderBar');
            setTimeout(() => Plotly.Plots.resize(container), 100);
        });
    }

    function renderTimelineChart() {
        const years  = timeline.years          || [];
        const appr   = timeline.approvedCounts || years.map(()=>0);
        const decl   = timeline.declinedCounts || years.map(()=>0);
        const subm   = timeline.pendingCounts  || years.map(()=>0);
        const total  = timeline.documentCounts || years.map(()=>0);
        const notYet = years.map((_,i) => Math.max(0,(total[i]||0)-(appr[i]||0)-(subm[i]||0)-(decl[i]||0)));

        const container = document.getElementById('timelineStackedChart');

        if (total.reduce((a,b)=>a+b,0) === 0) {
            if (container && container.data) Plotly.purge(container);
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-graph-up"></i><span>No timeline data available</span></div>';
            hideLoader('loaderTimeline');
            return;
        }

        if (container && container.data) Plotly.purge(container);

        const timelineData = [
            { name:'Approved',          type:'bar',     x:years, y:appr,  marker:{color:GREEN},  hovertemplate:'<b>%{x}</b><br>Approved: %{y}<extra></extra>' },
            { name:'Submitted',         type:'bar',     x:years, y:subm,  marker:{color:YELLOW}, hovertemplate:'<b>%{x}</b><br>Submitted: %{y}<extra></extra>' },
            { name:'Declined',          type:'bar',     x:years, y:decl,  marker:{color:RED},    hovertemplate:'<b>%{x}</b><br>Declined: %{y}<extra></extra>' },
            { name:'Not Yet Submitted', type:'bar',     x:years, y:notYet,marker:{color:GRAY},   hovertemplate:'<b>%{x}</b><br>Not Yet Submitted: %{y}<extra></extra>' },
            { name:'Total Documents',   type:'scatter', mode:'lines+markers', x:years, y:total,
              line:{color:'#00702B', width:2}, marker:{color:'#00702B', size:6},
              hovertemplate:'<b>%{x}</b><br>Total: %{y}<extra></extra>' }
        ];

        const layout = {
            font: FONT,
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor:  'rgba(0,0,0,0)',
            barmode: 'stack',
            height: 200,
            margin: { t: 10, b: 30, l: 40, r: 40 },
            showlegend: true,
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1, yanchor: 'bottom', font: { size: 10, color: 'black' } },
            xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 10 }, tickangle: -30, automargin: true },
            yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero' },
        };

        Plotly.newPlot(container, timelineData, layout, cfg).then(() => {
            hideLoader('loaderTimeline');
            setTimeout(() => Plotly.Plots.resize(container), 100);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // AJAX — fires on every filter change
    // ─────────────────────────────────────────────────────────────
    function fetchAndRefresh() {
        const semVal    = document.getElementById('mainSemester').value;
        const officeVal = document.getElementById('mainOffice').value;

        const selOpt   = document.getElementById('mainSemester').selectedOptions[0];
        const semLabel = selOpt?.value ? selOpt.getAttribute('data-label') : null;
        document.getElementById('barTitle').textContent =
            semLabel ? `Faculty Workload Approval (${semLabel})` : 'Faculty Workload Approval';

        document.getElementById('clearBtn').style.display = (semVal || officeVal) ? '' : 'none';

        showAllLoaders();

        const params = new URLSearchParams();
        if (semVal)    params.set('main_semester',  semVal);
        if (officeVal) params.set('main_signatory', officeVal);

        fetch(`{{ route('stzfaculty.approval') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => { if (!res.ok) throw new Error('Network error'); return res.json(); })
        .then(data => {
            overallStats   = data.overallStats;
            signatoryStats = data.signatoryStats;
            timeline       = data.timeline;
            currentFilter  = officeVal;
            currentSem     = semVal;

            animateStat('statTotal',     data.overallStats.totalDocuments);
            animateStat('statApproved',  data.overallStats.fullyApproved);
            animateStat('statSubmitted', data.overallStats.pendingApproval);
            animateStat('statDeclined',  data.overallStats.declined);

            renderPieChart();
            renderBarChart();
            renderTimelineChart();
        })
        .catch(err => {
            console.error('Approval AJAX error:', err);
            hideAllLoaders();
        });
    }

    function animateStat(id, target) {
        const el = document.getElementById(id);
        if (!el) return;
        const duration = 600;
        const startTs  = performance.now();
        function step(ts) {
            const progress = Math.min((ts - startTs) / duration, 1);
            const eased    = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(eased * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.getElementById('mainSemester').addEventListener('change', fetchAndRefresh);
    document.getElementById('mainOffice').addEventListener('change',   fetchAndRefresh);

    // ─────────────────────────────────────────────────────────────
    // Initial render
    // ─────────────────────────────────────────────────────────────
    renderPieChart();
    renderBarChart();
    renderTimelineChart();

    // Resize observer — mirrors programs page pattern
    const charts = ['overallStatusChart', 'signatoryTypeChart', 'timelineStackedChart'];
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
    </script>
</body>
</html>

@php
function getSignatoryName($value) {
    $names = [
        'dh'       => 'Department Head',
        'dean'     => 'Dean',
        'director' => 'Director',
        'dot_uni'  => 'DOT UNI',
        'nstp'     => 'NSTP',
        'eteeap'   => 'ETEEAP',
        'vpaa'     => 'VPAA'
    ];
    return $names[$value] ?? $value;
}
@endphp