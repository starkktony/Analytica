<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLSU Analytica - Faculty Approval</title>
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
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        .header {
            background: #009539;
            color: white;
            padding: 0 30px;
            font-size: 36px;
            font-weight: bold;
            height: 75px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .content { margin-left: 210px; }

        /* ── Combined filter bar ── */
        .filter-bar {
            font-family: 'Bricolage Grotesque', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 0 30px;
            border-bottom: 1px solid #b0b5b0;
            height: 48px;
        }
        .filter-bar-title {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .filter-bar-label {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            margin-right: 5px;
            margin-left: auto;
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
            min-width: 90px;
            cursor: pointer;
            transition: border-color 0.2s, opacity 0.2s;
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
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-left: 8px;
            text-decoration: none;
            display: inline-block;
        }
        .clear-filters-btn:hover { background: #00802e; color: white; }

        /* Pulsing dot shown while AJAX is running */
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
            0%, 100% { transform: scale(1);    opacity: 0.65; }
            50%       { transform: scale(1.55); opacity: 1;    }
        }

        /* ── Stats Cards ── */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 28px 30px 18px 30px;
        }
        .stat-card {
            border-radius: 15px;
            padding: 20px 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100px;
        }
        .stat-card.green  { background: #009539; }
        .stat-card.yellow { background: #ffc107; }
        .stat-card.red    { background: #dc3545; }
        .stat-card.white  { background: white;   }

        .stat-card .icon-box {
            width: 48px; height: 48px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 15px; left: 15px;
        }
        .stat-card.green  .icon-box              { background: white;   }
        .stat-card.green  .icon-box i            { font-size: 20px; color: #009539; }
        .stat-card.yellow .icon-box              { background: white;   }
        .stat-card.yellow .icon-box i            { font-size: 20px; color: #ffc107; }
        .stat-card.red    .icon-box              { background: white;   }
        .stat-card.red    .icon-box i            { font-size: 20px; color: #dc3545; }
        .stat-card.white  .icon-box              { background: #009539; }
        .stat-card.white  .icon-box i            { font-size: 20px; color: white;   }

        .stat-content { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; flex: 1; }
        .stat-number  { font-size: 44px; font-weight: 700; line-height: 1; transition: opacity 0.3s; }
        .stat-label   { font-size: 12px; font-weight: 600; margin-top: 4px; }

        .stat-card.green  .stat-number { color: white; }
        .stat-card.yellow .stat-number { color: white; }
        .stat-card.red    .stat-number { color: white; }
        .stat-card.white  .stat-number { color: #1f1f1f; }
        .stat-card.green  .stat-label  { color: rgba(255,255,255,0.85); }
        .stat-card.yellow .stat-label  { color: rgba(255,255,255,0.9);  }
        .stat-card.red    .stat-label  { color: rgba(255,255,255,0.9);  }
        .stat-card.white  .stat-label  { color: #666; }

        /* Shimmer on stat numbers while AJAX is loading */
        .stat-number.shimmer {
            background: linear-gradient(90deg,
                rgba(255,255,255,0.2) 25%,
                rgba(255,255,255,0.5) 50%,
                rgba(255,255,255,0.2) 75%);
            background-size: 200% 100%;
            animation: shimmer-anim 1.2s infinite;
            border-radius: 6px;
            color: transparent !important;
            min-width: 70px;
            min-height: 44px;
            display: inline-block;
        }
        .stat-card.white .stat-number.shimmer {
            background: linear-gradient(90deg, #e8ebe8 25%, #d0d4d0 50%, #e8ebe8 75%);
            background-size: 200% 100%;
        }
        @keyframes shimmer-anim {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ── Charts ── */
        .charts-row {
            display: grid;
            gap: 20px;
            padding: 0 30px 20px 30px;
        }
        .charts-row.two-col { grid-template-columns: 1fr 1fr; }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 24px 24px 14px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.09);
        }
        .chart-title { font-size: 14px; font-weight: 700; color: #1f1f1f; margin-bottom: 10px; }

        /* Chart wrapper — needed for absolute-positioned loader */
        .chart-wrapper { position: relative; }

        /* ── Per-chart loading overlay ── */
        .chart-loader {
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.91);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
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

        /* Spinner ring */
        .loader-ring {
            width: 46px;
            height: 46px;
            border: 4px solid #e4e4e4;
            border-top-color: #009539;
            border-radius: 50%;
            animation: spin 0.72s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .loader-label {
            font-size: 12px;
            font-weight: 600;
            color: #aaa;
            letter-spacing: 0.04em;
        }

        /* Skeleton shimmer bars behind spinner */
        .skeleton-stack {
            display: flex;
            flex-direction: column;
            gap: 9px;
            width: 60%;
        }
        .skel-bar {
            height: 11px;
            border-radius: 6px;
            background: linear-gradient(90deg, #ececec 25%, #dedede 50%, #ececec 75%);
            background-size: 200% 100%;
            animation: shimmer-anim 1.3s infinite;
        }
        .skel-bar:nth-child(1) { width: 90%; animation-delay: 0s;    }
        .skel-bar:nth-child(2) { width: 68%; animation-delay: 0.15s; }
        .skel-bar:nth-child(3) { width: 80%; animation-delay: 0.3s;  }
        .skel-bar:nth-child(4) { width: 52%; animation-delay: 0.45s; }

        .empty-chart {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #ccc; gap: 8px;
        }
        .empty-chart i    { font-size: 36px; }
        .empty-chart span { font-size: 13px; font-weight: 600; }

        .text-success { color: #009539 !important; }
    </style>
</head>
<body>
    @include('components.sidebar')

    <div class="content">

        {{-- Page header --}}
        <div class="header">Workload Approval</div>

        {{-- Combined title + filter bar (no form — AJAX handles changes) --}}
        <div class="filter-bar" id="filterBar">
            <div class="filter-bar-title">
                <span id="barTitle">
                    Faculty Workload Approval
                    @if($filters['main_semester'])
                        @php 
                            $sem = $availableSemesters->firstWhere('sem_id', $filters['main_semester']); 
                        @endphp
                        @if($sem) 
                            ({{ $sem->semester }} {{ $sem->sy }})
                        @endif
                    @endif
                    </span>
                <span class="filter-loading-dot" id="loadingDot"></span>
            </div>

            <div class="filter-bar-label">Filters:</div>

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
                    <option value="ds"       {{ $filters['main_signatory'] == 'ds'       ? 'selected' : '' }}>DS</option>
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

        {{-- Stats Cards --}}
        <div class="stats-container">
            <div class="stat-card white">
                <div class="icon-box"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statTotal">{{ number_format($totalDocuments) }}</div>
                    <div class="stat-label">Total Documents</div>
                </div>
            </div>
            <div class="stat-card green">
                <div class="icon-box"><i class="bi bi-check-circle-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statApproved">{{ number_format($fullyApproved) }}</div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
            <div class="stat-card yellow">
                <div class="icon-box"><i class="bi bi-clock-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statSubmitted">{{ number_format($pendingApproval) }}</div>
                    <div class="stat-label">Submitted</div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="icon-box"><i class="bi bi-x-circle-fill"></i></div>
                <div class="stat-content">
                    <div class="stat-number" id="statDeclined">{{ number_format($declined) }}</div>
                    <div class="stat-label">Declined</div>
                </div>
            </div>
        </div>

        {{-- Charts row --}}
        <div class="charts-row two-col">

            <div class="chart-card">
                <div class="chart-title">Workload Approval Status</div>
                <div class="chart-wrapper">
                    <div id="overallStatusChart" style="height:320px;"></div>
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

            <div class="chart-card">
                <div class="chart-title">Workload Status by Office</div>
                <div class="chart-wrapper">
                    <div id="signatoryTypeChart" style="height:320px;"></div>
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

        {{-- Timeline --}}
        <div style="padding: 0 30px 30px 30px;">
            <div class="chart-card">
                <div class="chart-title">
                    <i class="bi bi-graph-up-arrow me-2"></i>Annual Faculty Workload Status Breakdown
                </div>
                <div class="chart-wrapper">
                    <div id="timelineStackedChart" style="height:420px;"></div>
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

    </div><!-- /.content -->

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
        ds:       @json($dsStats),
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
    // Theme constants
    // ─────────────────────────────────────────────────────────────
    const FONT   = { family: "'Bricolage Grotesque', sans-serif", size: 12, color: '#444' };
    const GREEN  = '#009539';
    const YELLOW = '#ffc107';
    const RED    = '#dc3545';
    const GRAY   = '#adb5bd';
    const cfg    = { responsive: true, displayModeBar: false };

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

    // Shimmer / un-shimmer the stat number elements
    function shimmerStats(on) {
        ['statTotal','statApproved','statSubmitted','statDeclined'].forEach(id => {
            document.getElementById(id)?.classList.toggle('shimmer', on);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Chart renderers
    // ─────────────────────────────────────────────────────────────
    function renderPieChart() {
        const keys = ['dh','dean','director','ds','dot_uni','nstp','eteeap','vpaa'];
        let approved, submitted, declined, total;

        if (currentFilter && keys.includes(currentFilter)) {
            // Per-office view: use signatory-level counts
            approved  = signatoryStats[currentFilter].approved;
            submitted = signatoryStats[currentFilter].pending;
            declined  = signatoryStats[currentFilter].declined;
            total     = signatoryStats[currentFilter].total || overallStats.totalDocuments;
        } else {
            // All offices: use document-level counts (1 count per document, not per signatory)
            approved  = overallStats.fullyApproved;
            submitted = overallStats.pendingApproval;
            declined  = overallStats.declined;
            total     = overallStats.totalDocuments;
        }

        const notYet = Math.max(0, total - approved - submitted - declined);

        const pieTotal = approved + submitted + declined + notYet;

        if (pieTotal === 0) {
            document.getElementById('overallStatusChart').innerHTML =
                '<div class="empty-chart"><i class="bi bi-pie-chart"></i><span>No data available</span></div>';
            hideLoader('loaderPie');
            return;
        }

        Plotly.react('overallStatusChart', [{
            type: 'pie',
            values: [approved, submitted, declined, notYet],
            labels: ['Approved', 'Submitted', 'Declined', 'Not Yet Submitted'],
            marker: { colors: [GREEN, YELLOW, RED, GRAY] },
            textinfo: 'label+percent',
            textfont: { size: 11 },
            hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>%{percent}<extra></extra>'
        }], {
            font: FONT,
            paper_bgcolor: 'white',
            margin: { t: 40, r: 20, b: 10, l: 20 },
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.12, font: { size: 11 } },
            showlegend: true
        }, cfg).then(() => hideLoader('loaderPie'));
    }

    function renderBarChart() {
        const labels = ['Dept Head','Dean','Director','DS','DOT UNI','NSTP','ETEEAP','VPAA'];
        const keys   = ['dh','dean','director','ds','dot_uni','nstp','eteeap','vpaa'];
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

        if (totals.reduce((a,b) => a+b, 0) === 0) {
            document.getElementById('signatoryTypeChart').innerHTML =
                '<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>';
            hideLoader('loaderBar');
            return;
        }

        const selIdx = currentFilter && keys.includes(currentFilter) ? keys.indexOf(currentFilter) : -1;
        const mkCol  = (base, dim=0.18) =>
            keys.map((_, i) => selIdx === -1 ? base
                : i === selIdx ? base
                : base + Math.round(dim*255).toString(16).padStart(2,'0'));

        const yTickColors = labels.map((_, i) =>
            selIdx === -1 ? '#444' : i === selIdx ? '#009539' : '#bbb');

        Plotly.react('signatoryTypeChart', [
            { name:'Approved',          type:'bar', orientation:'h', x:aP, y:labels, marker:{color:mkCol(GREEN)},  hovertemplate:'<b>%{y}</b><br>Approved: %{x:.1f}%<extra></extra>' },
            { name:'Submitted',         type:'bar', orientation:'h', x:sP, y:labels, marker:{color:mkCol(YELLOW)}, hovertemplate:'<b>%{y}</b><br>Submitted: %{x:.1f}%<extra></extra>' },
            { name:'Declined',          type:'bar', orientation:'h', x:dP, y:labels, marker:{color:mkCol(RED)},    hovertemplate:'<b>%{y}</b><br>Declined: %{x:.1f}%<extra></extra>' },
            { name:'Not Yet Submitted', type:'bar', orientation:'h', x:nP, y:labels, marker:{color:mkCol(GRAY)},   hovertemplate:'<b>%{y}</b><br>Not Yet Submitted: %{x:.1f}%<extra></extra>' }
        ], {
            font: FONT, paper_bgcolor:'white', plot_bgcolor:'white', barmode:'stack',
            margin: { t:50, b:40, l:90, r:20 },
            showlegend: true,
            legend: { orientation:'h', x:0.5, xanchor:'center', y:1.15, font:{size:11} },
            xaxis: { title:{text:'Percent (%)',font:{size:11}}, range:[0,100], ticksuffix:'%', gridcolor:'#efefef', zeroline:false },
            yaxis: { title:{text:'Office',font:{size:11}}, tickfont:{size:11, color:yTickColors}, tickcolor: selIdx !== -1 ? '#009539' : '#444' }
        }, cfg).then(() => hideLoader('loaderBar'));
    }

    function renderTimelineChart() {
        const years  = timeline.years          || [];
        const appr   = timeline.approvedCounts || years.map(()=>0);
        const decl   = timeline.declinedCounts || years.map(()=>0);
        const subm   = timeline.pendingCounts  || years.map(()=>0);
        const total  = timeline.documentCounts || years.map(()=>0);
        const notYet = years.map((_,i) => Math.max(0,(total[i]||0)-(appr[i]||0)-(subm[i]||0)-(decl[i]||0)));

        if (total.reduce((a,b)=>a+b,0) === 0) {
            document.getElementById('timelineStackedChart').innerHTML =
                '<div class="empty-chart"><i class="bi bi-graph-up"></i><span>No timeline data available</span></div>';
            hideLoader('loaderTimeline');
            return;
        }

        Plotly.react('timelineStackedChart', [
            { name:'Approved',          type:'bar',     x:years, y:appr,   marker:{color:GREEN},  hovertemplate:'<b>%{x}</b><br>Approved: %{y}<extra></extra>' },
            { name:'Submitted',         type:'bar',     x:years, y:subm,   marker:{color:YELLOW}, hovertemplate:'<b>%{x}</b><br>Submitted: %{y}<extra></extra>' },
            { name:'Declined',          type:'bar',     x:years, y:decl,   marker:{color:RED},    hovertemplate:'<b>%{x}</b><br>Declined: %{y}<extra></extra>' },
            { name:'Not Yet Submitted', type:'bar',     x:years, y:notYet, marker:{color:GRAY},   hovertemplate:'<b>%{x}</b><br>Not Yet Submitted: %{y}<extra></extra>' },
            { name:'Total Documents',   type:'scatter', mode:'lines+markers', x:years, y:total,
              line:{color:'#006400',width:3}, marker:{color:'#006400',size:8},
              hovertemplate:'<b>%{x}</b><br>Total: %{y}<extra></extra>' }
        ], {
            font: FONT, paper_bgcolor:'white', plot_bgcolor:'#fafafa', barmode:'stack',
            margin: { t:60, b:50, l:60, r:40 },
            showlegend: true,
            legend: { orientation:'h', x:0.5, xanchor:'center', y:1.12, font:{size:11} },
            xaxis: { title:{text:'Year',font:{size:11}}, tickmode:'linear', dtick:1, gridcolor:'#e0e0e0' },
            yaxis: { title:{text:'Document Count',font:{size:11}}, gridcolor:'#e0e0e0', zeroline:false },
            hovermode: 'x'
        }, cfg).then(() => hideLoader('loaderTimeline'));
    }

    // ─────────────────────────────────────────────────────────────
    // AJAX — fires on every filter change, no full page reload
    // ─────────────────────────────────────────────────────────────
    function fetchAndRefresh() {
        const semVal    = document.getElementById('mainSemester').value;
        const officeVal = document.getElementById('mainOffice').value;

        // Update title text immediately
        const selOpt   = document.getElementById('mainSemester').selectedOptions[0];
        const semLabel = selOpt?.value ? selOpt.getAttribute('data-label') : null;
        document.getElementById('barTitle').textContent =
            semLabel ? `Faculty Workload Approval (${semLabel})` : 'Faculty Workload Approval';

        // Show/hide Clear button
        document.getElementById('clearBtn').style.display = (semVal || officeVal) ? '' : 'none';

        // Show loaders + shimmer
        showAllLoaders();
        shimmerStats(true);

        const params = new URLSearchParams();
        if (semVal)    params.set('main_semester',  semVal);
        if (officeVal) params.set('main_signatory', officeVal);

        fetch(`{{ route('stzfaculty.approval') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json'
            }
        })
        .then(res => { if (!res.ok) throw new Error('Network error'); return res.json(); })
        .then(data => {
            // Refresh reactive data
            overallStats   = data.overallStats;
            signatoryStats = data.signatoryStats;
            timeline       = data.timeline;
            currentFilter  = officeVal;
            currentSem     = semVal;

            // Animate stat numbers in
            animateStat('statTotal',     data.overallStats.totalDocuments);
            animateStat('statApproved',  data.overallStats.fullyApproved);
            animateStat('statSubmitted', data.overallStats.pendingApproval);
            animateStat('statDeclined',  data.overallStats.declined);
            shimmerStats(false);

            // Re-render all charts
            renderPieChart();
            renderBarChart();
            renderTimelineChart();
        })
        .catch(err => {
            console.error('Approval AJAX error:', err);
            shimmerStats(false);
            hideAllLoaders();
        });
    }

    // Count-up animation for stat numbers
    function animateStat(id, target) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('shimmer');
        const start    = 0;
        const duration = 600;
        const startTs  = performance.now();
        function step(ts) {
            const progress = Math.min((ts - startTs) / duration, 1);
            const eased    = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            el.textContent = Math.round(eased * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    // Bind selects
    document.getElementById('mainSemester').addEventListener('change', fetchAndRefresh);
    document.getElementById('mainOffice').addEventListener('change',   fetchAndRefresh);

    // ─────────────────────────────────────────────────────────────
    // Initial render (PHP data, no AJAX needed)
    // ─────────────────────────────────────────────────────────────
    renderPieChart();
    renderBarChart();
    renderTimelineChart();
    </script>
</body>
</html>

@php
function getSignatoryName($value) {
    $names = [
        'dh'       => 'Department Head',
        'dean'     => 'Dean',
        'director' => 'Director',
        'ds'       => 'DS',
        'dot_uni'  => 'DOT UNI',
        'nstp'     => 'NSTP',
        'eteeap'   => 'ETEEAP',
        'vpaa'     => 'VPAA'
    ];
    return $names[$value] ?? $value;
}
@endphp