<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siel Metrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400,600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        /* Main layout - flex column for proper sticky behavior */
        .app-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        /* ── Fixed header section container ── */
        .fixed-header-section {
            flex-shrink: 0;
            background: #e8ebe8;
            z-index: 100;
        }

        /* ── Header ── */
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

        /* ── Filter bar ── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
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

        .page-title {
            font-size: 15px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0;
            margin-right: auto;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-loading-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #009539;
            flex-shrink: 0;
            opacity: 0;
            transform: scale(0.5);
            transition: opacity 0.25s, transform 0.25s;
        }
        .filter-loading-dot.active {
            opacity: 1;
            transform: scale(1);
            animation: pulse-dot 0.85s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 0.65; }
            50%       { transform: scale(1.55); opacity: 1; }
        }

        .filter-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .filter-bar-label {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
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
            text-decoration: none;
            display: inline-block;
            flex-shrink: 0;
        }
        .clear-filters-btn:hover {
            background: #00802e;
            color: white;
        }

        /* ── Main content (scrollable area) ── */
        .main-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0 30px 40px 30px;
        }

        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        .main-content::-webkit-scrollbar-track {
            background: #d4d9d4;
            border-radius: 4px;
        }
        .main-content::-webkit-scrollbar-thumb {
            background: #009539;
            border-radius: 4px;
        }
        .main-content::-webkit-scrollbar-thumb:hover {
            background: #016531;
        }

        /* ── Chart sections ── */
        .chart-section-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 24px;
            margin-bottom: 24px;
        }
        .chart-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .chart-grid-1 {
            display: grid;
            grid-template-columns: 1fr;
        }
        @media (max-width: 900px) {
            .chart-grid-2 { grid-template-columns: 1fr; }
        }
        .chart-inner-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            box-shadow: inset 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden;
            position: relative;
        }
        .chart-inner-card.full-width {
            grid-column: 1 / -1;
        }
        .chart-inner-title {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 12px;
            text-align: center;
        }
        .chart-plot-area {
            position: relative;
            width: 100%;
            min-height: 320px;
            overflow: visible;
        }
        .chart-plot-area > div {
            width: 100%;
            height: 100%;
            min-height: 320px;
        }

        /* Inter font styling for stat cards */
        .stat-card-number {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 2.8rem;
            line-height: 1;
            color: #1f2937;
            text-align: right;
            margin-bottom: 4px;
        }

        .stat-card-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 11px;
            color: #6b7280;
            text-align: right;
            letter-spacing: 0.3px;
        }

        /* ── Chart loader overlay ── */
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
        
        /* Ensure plotly containers maintain proper dimensions */
        .js-plotly-plot, .plotly-graph-div {
            width: 100% !important;
            height: 100% !important;
            min-height: 320px !important;
        }
    </style>
</head>
<body>
    <div class="app-wrapper">
        @include('components.sidebar')

        <div class="content">
            <div class="fixed-header-section">
                {{-- Page Header --}}
                <div class="header">WORKLOAD APPROVAL</div>

                {{-- Filter Bar --}}
                <div class="filter-bar">
                    <div class="page-title">
                        <span id="barTitle">
                            Faculty Workload Approval
                            @if($filters['main_semester'])
                                @php $sem = $availableSemesters->firstWhere('sem_id', $filters['main_semester']); @endphp
                                @if($sem) ({{ $sem->semester }} {{ $sem->sy }}) @endif
                            @endif
                        </span>
                        <span class="filter-loading-dot" id="loadingDot"></span>
                    </div>
                    <div class="filter-right">
                        <span class="filter-bar-label">Filters:</span>

                        <div class="filter-group">
                            <label>Semester:</label>
                            <select id="mainSemester">
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

                        <div class="filter-group">
                            <label>Office:</label>
                            <select id="mainOffice">
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

                        <a href="{{ route('stzfaculty.approval') }}" class="clear-filters-btn" id="clearBtn"
                           style="{{ ($filters['main_semester'] || $filters['main_signatory']) ? '' : 'display:none;' }}">
                            Clear Filters
                        </a>
                    </div>
                </div>
            </div>

            {{-- Main Content (scrollable) --}}
            <div class="main-content">

                {{-- ── Stat Cards (programs-style with Inter font) ── --}}
                <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 pt-6 pb-4">

                    {{-- Active Faculty --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden flex flex-col justify-between">
                            <div class="bg-green-600/80 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-file-earmark-text-fill text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number" id="statTotal">{{ number_format($totalDocuments) }}</div>
                                <div class="stat-card-label">Active Faculty</div>
                            </div>
                        </div>
                    </div>

                    {{-- Approved --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden flex flex-col justify-between">
                            <div class="bg-green-600/80 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-check-circle-fill text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number" id="statApproved">{{ number_format($fullyApproved) }}</div>
                                <div class="stat-card-label">Approved</div>
                            </div>
                        </div>
                    </div>

                    {{-- Submitted --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-yellow-400 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden flex flex-col justify-between">
                            <div class="bg-yellow-400/80 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-clock-fill text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number" id="statSubmitted">{{ number_format($pendingApproval) }}</div>
                                <div class="stat-card-label">Submitted</div>
                            </div>
                        </div>
                    </div>

                    {{-- Declined --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-red-500 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden flex flex-col justify-between">
                            <div class="bg-red-500/80 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-x-circle-fill text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number" id="statDeclined">{{ number_format($declined) }}</div>
                                <div class="stat-card-label">Declined</div>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- ── End Stat Cards ── --}}

                {{-- Charts row 1: Pie + Bar --}}
                <div class="chart-section-wrapper">
                    <div class="chart-grid-2">

                        <div class="chart-inner-card">
                            <div class="chart-inner-title">Workload Approval Status</div>
                            <div class="chart-plot-area" style="min-height:320px;">
                                <div id="overallStatusChart" style="width:100%; min-height:320px;"></div>
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

                        <div class="chart-inner-card">
                            <div class="chart-inner-title">Workload Status by Office</div>
                            <div class="chart-plot-area" style="min-height:320px;">
                                <div id="signatoryTypeChart" style="width:100%; min-height:320px;"></div>
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
                        </div>

                    </div>
                </div>

                {{-- Chart row 2: Timeline --}}
                <div class="chart-section-wrapper">
                    <div class="chart-grid-1">
                        <div class="chart-inner-card full-width">
                            <div class="chart-inner-title">Annual Faculty Workload Status Breakdown</div>
                            <div class="chart-plot-area" style="min-height:420px;">
                                <div id="timelineStackedChart" style="width:100%; min-height:420px;"></div>
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
                </div>

            </div>{{-- /.main-content --}}
        </div>{{-- /.content --}}
    </div>{{-- /.app-wrapper --}}

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
    const FONT   = { family: "'Bricolage Grotesque', sans-serif", size: 12, color: '#444' };
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
        document.getElementById('loadingDot').classList.add('active');
    }
    function hideAllLoaders() {
        ['loaderPie', 'loaderBar', 'loaderTimeline'].forEach(hideLoader);
        document.getElementById('loadingDot').classList.remove('active');
    }

    // ─────────────────────────────────────────────────────────────
    // Chart renderers with proper container management
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
            // Clear any existing plot and show empty message
            if (container && container.data) {
                Plotly.purge(container);
            }
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-pie-chart"></i><span>No data available</span></div>';
            hideLoader('loaderPie');
            return;
        }

        // Purge the container to ensure clean slate
        if (container && container.data) {
            Plotly.purge(container);
        }

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
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent',
            margin: { t: 30, r: 20, b: 20, l: 20 },
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.05, yanchor: 'bottom', font: { size: 11 } },
            showlegend: true,
            autosize: true,
            height: null,
            width: null
        };

        Plotly.newPlot(container, pieData, layout, cfg).then(() => {
            hideLoader('loaderPie');
            // Force resize after render to ensure proper dimensions
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
            if (container && container.data) {
                Plotly.purge(container);
            }
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>';
            hideLoader('loaderBar');
            return;
        }

        if (container && container.data) {
            Plotly.purge(container);
        }

        const selIdx = currentFilter && keys.includes(currentFilter) ? keys.indexOf(currentFilter) : -1;
        const mkCol  = (base, dim=0.18) =>
            keys.map((_, i) => selIdx === -1 ? base
                : i === selIdx ? base
                : base + Math.round(dim*255).toString(16).padStart(2,'0'));

        const yTickColors = labels.map((_, i) =>
            selIdx === -1 ? '#444' : i === selIdx ? '#009539' : '#bbb');

        const barData = [
            { name:'Approved', type:'bar', orientation:'h', x:aP, y:labels, marker:{color:mkCol(GREEN)}, hovertemplate:'<b>%{y}</b><br>Approved: %{x:.1f}%<extra></extra>' },
            { name:'Submitted', type:'bar', orientation:'h', x:sP, y:labels, marker:{color:mkCol(YELLOW)}, hovertemplate:'<b>%{y}</b><br>Submitted: %{x:.1f}%<extra></extra>' },
            { name:'Declined', type:'bar', orientation:'h', x:dP, y:labels, marker:{color:mkCol(RED)}, hovertemplate:'<b>%{y}</b><br>Declined: %{x:.1f}%<extra></extra>' },
            { name:'Not Yet Submitted', type:'bar', orientation:'h', x:nP, y:labels, marker:{color:mkCol(GRAY)}, hovertemplate:'<b>%{y}</b><br>Not Yet Submitted: %{x:.1f}%<extra></extra>' }
        ];

        const layout = {
            font: FONT,
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent',
            barmode: 'stack',
            margin: { t: 50, b: 40, l: 100, r: 20 },
            showlegend: true,
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.12, font: { size: 11 } },
            xaxis: { title: { text: 'Percent (%)', font: { size: 11 } }, range: [0,100], ticksuffix: '%', gridcolor: '#efefef', zeroline: false },
            yaxis: { title: { text: 'Office', font: { size: 11 } }, tickfont: { size: 11, color: yTickColors } },
            autosize: true,
            height: null,
            width: null
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
            if (container && container.data) {
                Plotly.purge(container);
            }
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-graph-up"></i><span>No timeline data available</span></div>';
            hideLoader('loaderTimeline');
            return;
        }

        if (container && container.data) {
            Plotly.purge(container);
        }

        const timelineData = [
            { name:'Approved', type:'bar', x:years, y:appr, marker:{color:GREEN}, hovertemplate:'<b>%{x}</b><br>Approved: %{y}<extra></extra>' },
            { name:'Submitted', type:'bar', x:years, y:subm, marker:{color:YELLOW}, hovertemplate:'<b>%{x}</b><br>Submitted: %{y}<extra></extra>' },
            { name:'Declined', type:'bar', x:years, y:decl, marker:{color:RED}, hovertemplate:'<b>%{x}</b><br>Declined: %{y}<extra></extra>' },
            { name:'Not Yet Submitted', type:'bar', x:years, y:notYet, marker:{color:GRAY}, hovertemplate:'<b>%{x}</b><br>Not Yet Submitted: %{y}<extra></extra>' },
            { name:'Total Documents', type:'scatter', mode:'lines+markers', x:years, y:total,
              line:{color:'#006400',width:3}, marker:{color:'#006400',size:8},
              hovertemplate:'<b>%{x}</b><br>Total: %{y}<extra></extra>' }
        ];

        const layout = {
            font: FONT,
            paper_bgcolor: 'transparent',
            plot_bgcolor: '#fafafa',
            barmode: 'stack',
            margin: { t: 60, b: 100, l: 60, r: 40 },
            showlegend: true,
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.08, font: { size: 11 } },
            xaxis: { title: { text: 'Semester', font: { size: 11 } }, tickangle: -30, gridcolor: '#e0e0e0', automargin: true },
            yaxis: { title: { text: 'Document Count', font: { size: 11 } }, gridcolor: '#e0e0e0', zeroline: false },
            hovermode: 'closest',
            autosize: true,
            height: null,
            width: null
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

    // Debounced resize handler to prevent excessive calls
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            ['overallStatusChart', 'signatoryTypeChart', 'timelineStackedChart'].forEach(id => {
                const el = document.getElementById(id);
                if (el && el.data && !el.innerHTML.includes('empty-chart')) {
                    Plotly.Plots.resize(el);
                }
            });
        }, 200);
    });
    
    // Additional resize after any layout changes
    setTimeout(() => {
        ['overallStatusChart', 'signatoryTypeChart', 'timelineStackedChart'].forEach(id => {
            const el = document.getElementById(id);
            if (el && el.data && !el.innerHTML.includes('empty-chart')) {
                Plotly.Plots.resize(el);
            }
        });
    }, 500);
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