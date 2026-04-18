<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Vite assets (CSS at JS) para sa frontend build pipeline --}}
    {{-- Vite assets (CSS and JS) for the frontend build pipeline --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- External CSS libraries: Bootstrap, Bootstrap Icons, Google Fonts, Font Awesome, Plotly, Tom Select --}}
    {{-- Mga external na CSS library: Bootstrap para sa layout, icons, fonts, charts, at dropdowns --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <title>Siel Metrics</title>

    <style>
        /* ── Layout: Content area na nag-aadjust base sa sidebar width ──
           Layout: Content area that adjusts based on sidebar width */
        .content {
            margin-left: 250px;
            transition: margin-left 0.3s ease, max-width 0.3s ease;
            max-width: calc(100vw - 250px);
            overflow-x: clip;
        }

        /* Kapag naka-collapse ang sidebar, binabawasan ang margin
           When sidebar is collapsed, reduce the left margin */
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }

        /* Tinitiyak na visible ang Bootstrap collapse elements
           Ensures Bootstrap collapse elements remain visible when open */
        .collapse.show {
            visibility: visible !important;
        }

        /* Global body styling: background color, font, at walang horizontal scroll
           Global body styling: background color, font, and no horizontal scroll */
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow-x: clip;
        }

        /* Header bar styling: green background, flex layout para sa title at controls
           Header bar styling: green background, flex layout for title and controls */
        header {
            height: 70px;
            padding: 2rem 3rem;
            background-color: #009539;
            box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ── Chart loader overlay — ipinapakita habang nag-loload ang chart data ──
           Chart loader overlay — shown while chart data is loading */
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
            transition: opacity 0.35s ease; /* Fade-out transition kapag na-load na ang chart */
        }

        /* Tinatago ang loader gamit ang opacity (hindi display:none para smooth ang transition)
           Hides the loader using opacity (not display:none so the transition stays smooth) */
        .chart-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        /* Spinning ring animation para sa loader
           Spinning ring animation for the loader */
        .loader-ring {
            width: 40px;
            height: 40px;
            border: 3px solid #e4e4e4;
            border-top-color: #009539; /* Green accent para sa spinning ring / Green accent for the spinning ring */
            border-radius: 50%;
            animation: spin 0.72s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Label text sa ilalim ng loading ring
           Label text below the loading ring */
        .loader-label {
            font-size: 12px;
            font-weight: 600;
            color: #aaa;
        }

        /* Skeleton bars habang nag-loload — gumagaya sa chart shape
           Skeleton bars while loading — mimics the chart shape */
        .skeleton-stack { display: flex; flex-direction: column; gap: 9px; width: 55%; }
        .skel-bar {
            height: 10px;
            border-radius: 6px;
            /* Shimmer gradient effect para sa skeleton loading animation
               Shimmer gradient effect for the skeleton loading animation */
            background: linear-gradient(90deg, #ececec 25%, #dedede 50%, #ececec 75%);
            background-size: 200% 100%;
            animation: shimmer-anim 1.3s infinite;
        }
        /* Iba't ibang lapad at delay para sa natural na shimmer effect
           Different widths and delays for a natural shimmer effect */
        .skel-bar:nth-child(1) { width: 90%; }
        .skel-bar:nth-child(2) { width: 65%; animation-delay: 0.15s; }
        .skel-bar:nth-child(3) { width: 80%; animation-delay: 0.3s; }
        @keyframes shimmer-anim {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Placeholder na ipinapakita kapag walang data ang chart
           Placeholder shown when a chart has no data to display */
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

        /* Styling ng malalaking numero sa stat cards (e.g. total faculty count)
           Styling for the large numbers on stat cards (e.g. total faculty count) */
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
        /* Subtitle/label sa ilalim ng numero sa stat card
           Subtitle/label below the number on a stat card */
        .stat-card-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 11px;
            color: #6b7280;
            text-align: right;
            letter-spacing: 0.3px;
        }
    </style>

    {{-- Tailwind CSS via CDN para sa utility classes na ginagamit sa template --}}
    {{-- Tailwind CSS via CDN for utility classes used throughout the template --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    {{-- Sidebar component (shared sa iba pang pages ng app)
         Sidebar component (shared across other pages of the app) --}}
    @include('components.sidebar')

    <div class="content w-100">

        {{-- ── Sticky header + filter bar na nananatili sa taas habang nag-iscroll
             Sticky header + filter bar that stays at the top while scrolling ── --}}
        <div class="sticky top-0 z-50">
            <header>
                {{-- Page title na ipinapakita sa header bar
                     Page title displayed in the header bar --}}
                <span class="text-lg md:text-2xl font-[650] text-white">Workload Approval</span>
            </header>

            {{-- Filter bar: naglalaman ng semester at office filter dropdowns
                 Filter bar: contains semester and office filter dropdowns --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">

                {{-- Dynamic title na nagbabago base sa napiling semester filter
                     Dynamic title that changes based on the selected semester filter --}}
                <div class="font-[650] text-sm md:text-lg" id="barTitle">
                    Faculty Workload Approval
                    @if($filters['main_semester'])
                        {{-- Hinahanap ang semester object gamit ang sem_id mula sa filters
                             Find the semester object using the sem_id from filters --}}
                        @php $sem = $availableSemesters->firstWhere('sem_id', $filters['main_semester']); @endphp
                        @if($sem)({{ $sem->semester }} {{ $sem->sy }})@endif
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    {{-- "Filter" label — nakatago sa maliliit na screen
                         "Filter" label — hidden on small screens --}}
                    <div class="hidden sm:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                        Filter
                    </div>

                    {{-- Semester dropdown — nagpo-populate mula sa $availableSemesters na variable
                         Semester dropdown — populated from the $availableSemesters variable --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Semester:</span>
                        <select id="mainSemester"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            <option value="">All</option>
                            @foreach($availableSemesters as $sem)
                                <option value="{{ $sem->sem_id }}"
                                        data-label="{{ $sem->semester }} {{ $sem->sy }}"
                                        {{-- Pre-select ang option na tumutugma sa kasalukuyang filter
                                             Pre-select the option matching the current filter --}}
                                        {{ $filters['main_semester'] == $sem->sem_id ? 'selected' : '' }}>
                                    {{ $sem->semester }} {{ $sem->sy }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Office/signatory dropdown — naglalaman ng hardcoded na listahan ng mga opisina
                         Office/signatory dropdown — contains a hardcoded list of offices --}}
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

                    {{-- "Clear Filters" button — nakatago kapag walang aktibong filter
                         "Clear Filters" button — hidden when no filters are active --}}
                    <a href="{{ route('stzfaculty.approval') }}" id="clearBtn"
                        class="text-xs font-semibold bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700 transition"
                        style="{{ ($filters['main_semester'] || $filters['main_signatory']) ? '' : 'display:none;' }}">
                        Clear Filters
                    </a>
                </div>
            </div>
        </div>
        {{-- ── Katapusan ng sticky header ── / End sticky header ── --}}

        <div class="px-6 pt-4">

            {{-- ── Stat Cards: Ipinapakita ang mga summary counts (Total, Approved, Submitted, Declined)
                 Stat Cards: Display summary counts (Total, Approved, Submitted, Declined) ── --}}
            <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-4">

                {{-- Card 1: Total Active Faculty
                     Card 1: Total Active Faculty --}}
                <div class="col-span-3">
                    <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                        <div class="grid grid-rows-3 h-full">
                            <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="bi bi-file-earmark-text-fill text-white text-3xl"></i>
                            </div>
                            <div class="row-span-2 pb-3">
                                {{-- id="statTotal" ginagamit ng JS para i-animate ang numero sa pag-load
                                     id="statTotal" is used by JS to animate the number on load --}}
                                <p class="stat-card-number pr-4 pt-2" id="statTotal">{{ number_format($totalDocuments) }}</p>
                                <p class="stat-card-label pr-4">Active Faculty</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Fully Approved na workloads
                     Card 2: Fully Approved workloads --}}
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

                {{-- Card 3: Mga naka-submit na at naghihintay ng approval
                     Card 3: Submitted and awaiting approval --}}
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

                {{-- Card 4: Mga na-decline na workloads
                     Card 4: Declined workloads --}}
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
            {{-- ── Katapusan ng Stat Cards ── / End Stat Cards ── --}}

            {{-- ── Charts Row: Pie chart (kaliwa) at Bar + Timeline charts (kanan)
                 Charts Row: Pie chart (left) and Bar + Timeline charts (right) ── --}}
            <div class="grid grid-cols-6 xl:grid-cols-12 gap-2">

                {{-- Pie Chart: Nagpapakita ng overall approval status breakdown
                     Pie Chart: Shows overall approval status breakdown --}}
                <div class="col-span-6 xl:col-span-4">
                    <div class="border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[320px] sm:h-[370px] lg:h-[570px] rounded-[1vw] shadow-inner shadow-xl">
                        <div class="w-full grid grid-cols-12 grid-rows-7">
                            <div class="col-span-12 row-span-1 font-[750] text-sm md:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-7">
                                Workload Approval Status
                            </div>
                            <div class="col-span-12 row-span-6 relative">
                                {{-- Container ng pie chart — pino-populate ng Plotly via JS
                                     Pie chart container — populated by Plotly via JS --}}
                                <div id="overallStatusChart" class="w-full h-full"></div>
                                {{-- Loading overlay para sa pie chart
                                     Loading overlay for the pie chart --}}
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

                {{-- Kanang column: naglalaman ng Bar chart at Timeline chart
                     Right column: contains the Bar chart and Timeline chart --}}
                <div class="col-span-6 lg:col-span-8">

                    {{-- Bar Chart: Nagpapakita ng approval status per office/signatory
                         Bar Chart: Shows approval status per office/signatory --}}
                    <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px] relative">
                        <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                            Workload Status by Office
                        </div>
                        <div>
                            {{-- Container ng horizontal bar chart
                                 Horizontal bar chart container --}}
                            <div id="signatoryTypeChart" style="width: 100%;"></div>
                        </div>
                        {{-- Loading overlay para sa bar chart
                             Loading overlay for the bar chart --}}
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

                    {{-- Timeline Chart: Nagpapakita ng annual trend ng workload submissions at approvals
                         Timeline Chart: Shows annual trend of workload submissions and approvals --}}
                    <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px] mt-2 mb-2 relative">
                        <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                            Annual Faculty Workload Status Breakdown
                        </div>
                        <div>
                            {{-- Container ng stacked bar + line chart para sa timeline
                                 Stacked bar + line chart container for the timeline --}}
                            <div id="timelineStackedChart" style="width: 100%;"></div>
                        </div>
                        {{-- Disclaimer note: hindi lahat ng semester ay may available na data
                             Disclaimer note: not all semesters have available data --}}
                        <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pr-2">
                            <i>Note: Data for certain semesters is unavailable; only semesters with recorded submissions are displayed.</i>
                        </div>
                        {{-- Loading overlay para sa timeline chart
                             Loading overlay for the timeline chart --}}
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
            {{-- ── Katapusan ng Charts Row ── / End Charts Row ── --}}

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ─────────────────────────────────────────────────────────────
    // Initial data mula sa server (ini-inject ng Blade)
    // Initial data from the server (injected by Blade)
    // ─────────────────────────────────────────────────────────────
    let overallStats = {
        totalDocuments:  {{ $totalDocuments }},   // Kabuuang bilang ng active faculty / Total active faculty count
        fullyApproved:   {{ $fullyApproved }},    // Fully approved na workloads / Fully approved workloads
        pendingApproval: {{ $pendingApproval }},  // Submitted pero hindi pa approved / Submitted but not yet approved
        declined:        {{ $declined }},          // Na-decline na workloads / Declined workloads
        overallApproved: {{ $overallApproved }},  // Overall approved count (maaaring kasama ang lahat ng signatory)
        overallPending:  {{ $overallPending }},   // Overall pending count
        overallDeclined: {{ $overallDeclined }}   // Overall declined count
    };

    // Stats per signatory/office — ginagamit para sa bar chart at pie chart filtering
    // Stats per signatory/office — used for bar chart and pie chart filtering
    let signatoryStats = {
        dh:       @json($dhStats),
        dean:     @json($deanStats),
        director: @json($directorStats),
        dot_uni:  @json($dotUniStats),
        nstp:     @json($nstpStats),
        eteeap:   @json($eteeapStats),
        vpaa:     @json($vpaaStats)
    };

    // Timeline data para sa annual trend chart (stacked bar + line)
    // Timeline data for the annual trend chart (stacked bar + line)
    let timeline = {
        years:          @json($timelineYears),
        documentCounts: @json(array_values($yearlyDocumentCounts)), // Total per year / Total per year
        approvedCounts: @json(array_values($yearlyApprovedCounts)), // Approved per year
        declinedCounts: @json(array_values($yearlyDeclinedCounts)), // Declined per year
        pendingCounts:  @json(array_values($yearlyPendingCounts))   // Pending per year
    };

    // Kasalukuyang halaga ng mga filter (ginagamit para sa conditional chart logic)
    // Current filter values (used for conditional chart logic)
    let currentFilter = '{{ $filters['main_signatory'] }}';
    let currentSem    = '{{ $filters['main_semester'] }}';

    // ─────────────────────────────────────────────────────────────
    // Theme constants — mga kulay at font na ginagamit sa lahat ng charts
    // Theme constants — colors and font used across all charts
    // ─────────────────────────────────────────────────────────────
    const FONT   = { family: "'Inter', sans-serif", size: 12, color: '#444' };
    const GREEN  = '#009539'; // Approved
    const YELLOW = '#ffc107'; // Submitted / Pending
    const RED    = '#dc3545'; // Declined
    const GRAY   = '#adb5bd'; // Not Yet Submitted
    const cfg    = { responsive: true, displayModeBar: false, staticPlot: false }; // Plotly config

    // ─────────────────────────────────────────────────────────────
    // Loader helper functions
    // ─────────────────────────────────────────────────────────────

    // Ipinapakita ang loader overlay ng isang chart
    // Shows the loader overlay of a chart
    const showLoader = id => document.getElementById(id)?.classList.remove('hidden');

    // Itinatago ang loader overlay ng isang chart
    // Hides the loader overlay of a chart
    const hideLoader = id => document.getElementById(id)?.classList.add('hidden');

    // Ipinapakita ang lahat ng loaders bago mag-fetch ng bagong data
    // Shows all loaders before fetching new data
    function showAllLoaders() {
        ['loaderPie', 'loaderBar', 'loaderTimeline'].forEach(showLoader);
    }

    // Itinatago ang lahat ng loaders pagkatapos ma-render ang charts
    // Hides all loaders after charts are rendered
    function hideAllLoaders() {
        ['loaderPie', 'loaderBar', 'loaderTimeline'].forEach(hideLoader);
    }

    // ─────────────────────────────────────────────────────────────
    // Chart renderer: Pie Chart
    // Nagpapakita ng breakdown ng approval status (Approved, Submitted, Declined, Not Yet Submitted)
    // Shows the breakdown of approval status (Approved, Submitted, Declined, Not Yet Submitted)
    // ─────────────────────────────────────────────────────────────
    function renderPieChart() {
        const keys = ['dh','dean','director','dot_uni','nstp','eteeap','vpaa'];
        let approved, submitted, declined, total;

        // Kung may napiling office filter, gamitin ang stats nito; kung wala, gamitin ang overall
        // If an office filter is selected, use its stats; otherwise use overall stats
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

        // Kinukuwenta ang "Not Yet Submitted" — yung mga walang record pa
        // Calculate "Not Yet Submitted" — those with no record yet
        const notYet   = Math.max(0, total - approved - submitted - declined);
        const pieTotal = approved + submitted + declined + notYet;

        const container = document.getElementById('overallStatusChart');

        // Kapag walang data, ipakita ang empty state placeholder
        // If there's no data, show the empty state placeholder
        if (pieTotal === 0) {
            if (container && container.data) Plotly.purge(container);
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-pie-chart"></i><span>No data available</span></div>';
            hideLoader('loaderPie');
            return;
        }

        // I-purge ang lumang chart bago mag-render ng bago (para maiwasan ang pag-overlap)
        // Purge the old chart before rendering a new one (to avoid overlapping)
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
            paper_bgcolor: 'rgba(0,0,0,0)', // Transparent background
            plot_bgcolor:  'rgba(0,0,0,0)',
            margin: { t: 30, r: 20, b: 20, l: 20 },
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.05, yanchor: 'bottom', font: { size: 11 } },
            showlegend: true,
            autosize: true,
        };

        // I-render ang chart at itago ang loader pagkatapos
        // Render the chart and hide the loader afterward
        Plotly.newPlot(container, pieData, layout, cfg).then(() => {
            hideLoader('loaderPie');
            setTimeout(() => Plotly.Plots.resize(container), 100); // Resize para ma-fit ang container / Resize to fit the container
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Chart renderer: Horizontal Bar Chart
    // Nagpapakita ng approval percentage per office (stacked, 0–100%)
    // Shows approval percentage per office (stacked, 0–100%)
    // ─────────────────────────────────────────────────────────────
    function renderBarChart() {
        const labels = ['Dept Head','Dean','Director','DOT UNI','NSTP','ETEEAP','VPAA'];
        const keys   = ['dh','dean','director','dot_uni','nstp','eteeap','vpaa'];

        // Arrays para sa bawat status na ipi-plot bilang percentage
        // Arrays for each status to be plotted as percentage
        const aP=[], sP=[], dP=[], nP=[], totals=[];

        keys.forEach(k => {
            const a  = signatoryStats[k].approved || 0;
            const s  = signatoryStats[k].pending  || 0;
            const d  = signatoryStats[k].declined || 0;
            const t  = signatoryStats[k].total    || 0;
            const ny = Math.max(0, t - a - s - d);
            totals.push(t);
            if (t > 0) {
                // Kino-convert ang mga count sa percentage para sa stacked bar chart
                // Convert counts to percentages for the stacked bar chart
                aP.push(+(a/t*100).toFixed(1));  sP.push(+(s/t*100).toFixed(1));
                dP.push(+(d/t*100).toFixed(1));  nP.push(+(ny/t*100).toFixed(1));
            } else { aP.push(0); sP.push(0); dP.push(0); nP.push(0); }
        });

        const container = document.getElementById('signatoryTypeChart');

        // Empty state kapag lahat ng office ay 0 ang total
        // Empty state when all offices have a total of 0
        if (totals.reduce((a,b) => a+b, 0) === 0) {
            if (container && container.data) Plotly.purge(container);
            container.innerHTML = '<div class="empty-chart"><i class="bi bi-bar-chart"></i><span>No data available</span></div>';
            hideLoader('loaderBar');
            return;
        }

        if (container && container.data) Plotly.purge(container);

        // Index ng kasalukuyang napiling office filter para sa visual highlight
        // Index of the currently selected office filter for visual highlight
        const selIdx = currentFilter && keys.includes(currentFilter) ? keys.indexOf(currentFilter) : -1;

        // Helper: binabago ang opacity ng kulay para i-dim ang mga hindi napiling bars
        // Helper: adjusts color opacity to dim bars that are not selected
        const mkCol  = (base, dim=0.18) =>
            keys.map((_, i) => selIdx === -1 ? base
                : i === selIdx ? base
                : base + Math.round(dim*255).toString(16).padStart(2,'0'));

        // Nag-iiba ng kulay ng Y-axis tick labels para i-highlight ang napiling office
        // Changes Y-axis tick label colors to highlight the selected office
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
            barmode: 'stack', // Stacked bars para sa 100% breakdown / Stacked bars for 100% breakdown
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

    // ─────────────────────────────────────────────────────────────
    // Chart renderer: Timeline / Annual Trend Chart
    // Nagpapakita ng stacked bar per year kasama ang total line overlay
    // Shows stacked bar per year with a total line overlay
    // ─────────────────────────────────────────────────────────────
    function renderTimelineChart() {
        const years  = timeline.years          || [];
        const appr   = timeline.approvedCounts || years.map(()=>0);
        const decl   = timeline.declinedCounts || years.map(()=>0);
        const subm   = timeline.pendingCounts  || years.map(()=>0);
        const total  = timeline.documentCounts || years.map(()=>0);

        // Kinukuwenta ang "Not Yet Submitted" per year
        // Calculate "Not Yet Submitted" per year
        const notYet = years.map((_,i) => Math.max(0,(total[i]||0)-(appr[i]||0)-(subm[i]||0)-(decl[i]||0)));

        const container = document.getElementById('timelineStackedChart');

        // Walang data — ipakita ang empty state
        // No data — show empty state
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
            // Line overlay para ipakita ang total documents trend
            // Line overlay to show the total documents trend
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
            xaxis: {
                type: 'category',          // Categorical para sa school year labels / Categorical for school year labels
                linecolor: '#00702B', linewidth: 2, showline: true,
                tickcolor: '#00702B', tickfont: { color: '#00702B', size: 10 },
                tickangle: -30, automargin: true
            },
            yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero' },
        };

        Plotly.newPlot(container, timelineData, layout, cfg).then(() => {
            hideLoader('loaderTimeline');
            setTimeout(() => Plotly.Plots.resize(container), 100);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // AJAX fetch — tinatawag sa bawat pagbabago ng filter
    // AJAX fetch — called on every filter change
    // ─────────────────────────────────────────────────────────────
    function fetchAndRefresh() {
        const semVal    = document.getElementById('mainSemester').value;
        const officeVal = document.getElementById('mainOffice').value;

        // I-update ang page title base sa napiling semester
        // Update the page title based on the selected semester
        const selOpt   = document.getElementById('mainSemester').selectedOptions[0];
        const semLabel = selOpt?.value ? selOpt.getAttribute('data-label') : null;
        document.getElementById('barTitle').textContent =
            semLabel ? `Faculty Workload Approval (${semLabel})` : 'Faculty Workload Approval';

        // Ipakita o itago ang "Clear Filters" button base sa active filters
        // Show or hide the "Clear Filters" button based on active filters
        document.getElementById('clearBtn').style.display = (semVal || officeVal) ? '' : 'none';

        // Ipakita ang lahat ng loaders habang nag-fe-fetch
        // Show all loaders while fetching
        showAllLoaders();

        // Buuin ang query string para sa AJAX request
        // Build the query string for the AJAX request
        const params = new URLSearchParams();
        if (semVal)    params.set('main_semester',  semVal);
        if (officeVal) params.set('main_signatory', officeVal);

        // Mag-fetch ng updated data mula sa server gamit ang JSON response
        // Fetch updated data from the server using a JSON response
        fetch(`{{ route('stzfaculty.approval') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => { if (!res.ok) throw new Error('Network error'); return res.json(); })
        .then(data => {
            // I-update ang in-memory data gamit ang bagong response
            // Update in-memory data with the new response
            overallStats   = data.overallStats;
            signatoryStats = data.signatoryStats;
            timeline       = data.timeline;
            currentFilter  = officeVal;
            currentSem     = semVal;

            // I-animate ang mga stat card numbers papunta sa bagong values
            // Animate the stat card numbers to the new values
            animateStat('statTotal',     data.overallStats.totalDocuments);
            animateStat('statApproved',  data.overallStats.fullyApproved);
            animateStat('statSubmitted', data.overallStats.pendingApproval);
            animateStat('statDeclined',  data.overallStats.declined);

            // I-re-render ang lahat ng charts gamit ang bagong data
            // Re-render all charts with the new data
            renderPieChart();
            renderBarChart();
            renderTimelineChart();
        })
        .catch(err => {
            // Sa error, itago lang ang loaders at i-log ang problema
            // On error, just hide the loaders and log the problem
            console.error('Approval AJAX error:', err);
            hideAllLoaders();
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Number animation helper — nagci-count mula 0 papunta sa target value
    // Number animation helper — counts from 0 to the target value
    // ─────────────────────────────────────────────────────────────
    function animateStat(id, target) {
        const el = document.getElementById(id);
        if (!el) return;
        const duration = 600; // Milliseconds para sa animation / Milliseconds for animation
        const startTs  = performance.now();
        function step(ts) {
            const progress = Math.min((ts - startTs) / duration, 1);
            const eased    = 1 - Math.pow(1 - progress, 3); // Ease-out cubic para sa natural na pakiramdam
            el.textContent = Math.round(eased * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    // ─────────────────────────────────────────────────────────────
    // Event listeners para sa filter dropdowns
    // Event listeners for the filter dropdowns
    // ─────────────────────────────────────────────────────────────
    document.getElementById('mainSemester').addEventListener('change', fetchAndRefresh);
    document.getElementById('mainOffice').addEventListener('change',   fetchAndRefresh);

    // ─────────────────────────────────────────────────────────────
    // Initial render sa page load
    // Initial render on page load
    // ─────────────────────────────────────────────────────────────
    renderPieChart();
    renderBarChart();
    renderTimelineChart();

    // ─────────────────────────────────────────────────────────────
    // ResizeObserver — nag-re-resize ng charts kapag nagbago ang laki ng content area
    // ResizeObserver — resizes charts when the content area size changes (e.g. sidebar toggle)
    // ─────────────────────────────────────────────────────────────
    const charts = ['overallStatusChart', 'signatoryTypeChart', 'timelineStackedChart'];
    const contentDiv = document.querySelector('.content');
    if (contentDiv) {
        const ro = new ResizeObserver(() => {
            charts.forEach(id => {
                const el = document.getElementById(id);
                if (el && el.data) Plotly.Plots.resize(el); // I-resize lang kung naka-render na ang chart / Only resize if the chart is already rendered
            });
        });
        ro.observe(contentDiv);
    }
    </script>
</body>
</html>

{{-- ─────────────────────────────────────────────────────────────
     Helper function: ginagamit para i-convert ang signatory key patungong readable na pangalan
     Helper function: converts a signatory key to a human-readable name
     ───────────────────────────────────────────────────────────── --}}
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
    return $names[$value] ?? $value; // Ibalik ang value mismo kung hindi nahanap / Return the value itself if not found
}
@endphp
