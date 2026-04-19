<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Laravel Vite directive to load compiled CSS/JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- External CDN Dependencies --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Tom Select for searchable/customizable dropdowns --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <title>Siel Metrics</title>

    <style>
        /* ── Main Layout & Sidebar Transition ── */
        .content {
            margin-left: 250px; /* Default sidebar width */
            transition: margin-left 0.3s ease, max-width 0.3s ease;
            max-width: calc(100vw - 250px);
            overflow-x: clip; /* Prevents horizontal scroll on the whole page */
        }
        /* Adjusts main content area when sidebar shrinks */
        body.sidebar-collapsed .content {
            margin-left: 68px;
            max-width: calc(100vw - 68px);
        }
        .collapse.show { visibility: visible !important; }

        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow-x: clip;
        }

        /* ── Top Header Styling ── */
        header {
            height: 70px;
            padding: 2rem 3rem;
            background-color: #009539;
            box-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ── Filter Bar: Fixed Left Title ── */
        /* Keeps the page title static while the filters to the right can scroll */
        .filter-page-title {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0; /* Prevents title from squishing */
            padding: 0 14px;
            min-height: 42px;
            display: flex;
            align-items: center;
            background: #d1d5db;
            border-bottom: 1px solid #b0b5b0;
            border-right: 1px solid #b0b5b0;
        }

        /* ── Filter Bar: Scrollable Right Section ── */
        .filter-bar-scroll {
            background: #d1d5db;
            border-bottom: 1px solid #b0b5b0;
            min-height: 42px;
            display: flex;
            align-items: center;
            overflow-x: auto; /* Allows horizontal scrolling for filters on small screens */
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            scrollbar-width: none; /* Firefox: hide scrollbar */
            -ms-overflow-style: none; /* IE/Edge: hide scrollbar */
            flex: 1;
            min-width: 0; /* Important flexbox fix to allow shrinking inside parent */
        }
        .filter-bar-scroll::-webkit-scrollbar { display: none; } /* Chrome/Safari: hide scrollbar */

        /* Inner row — never wraps, forces horizontal scroll if too wide */
        .filter-bar-inner {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: nowrap;
            flex-shrink: 0;
            padding: 6px 12px;
            min-width: max-content;
        }

        /* Filter label divider (e.g., "Filter |") */
        .filter-label-divider {
            font-size: 11px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0;
            border-right: 1.5px solid #6b7280;
            padding-right: 12px;
            margin-right: 2px;
        }

        /* Each individual filter dropdown container */
        .filter-item {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }
        .filter-item span {
            font-size: 11px;
            font-weight: 600;
            color: #2d2d2d;
            white-space: nowrap;
        }
        /* Custom styled native select dropdowns */
        .filter-item select {
            font-size: 11px;
            padding: 3px 24px 3px 8px;
            border-radius: 20px;
            border: 1px solid #8a8f8a;
            background-color: #f5f5f5;
            color: #2d2d2d;
            appearance: none; /* Removes default OS dropdown arrow */
            /* Custom SVG arrow */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%232d2d2d' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 7px;
            min-width: 90px;
            max-width: 180px;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .filter-item select:focus {
            outline: none;
            border-color: #009539;
            background-color: white;
        }
        .filter-item select:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        /* Scroll fade hint on right edge: Creates a gradient effect to indicate more content to the right */
        .filter-bar-wrapper {
            position: relative;
            flex: 1;
            min-width: 0;
        }
        .filter-bar-wrapper::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 28px;
            background: linear-gradient(to right, transparent, #d1d5db);
            pointer-events: none;
            z-index: 1;
        }

        /* ── Standard Stat Card Typography ── */
        .stat-card-number {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 2.8rem;
            line-height: 1;
            color: #1f2937;
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

        /* ── Gender Split Variant Typography (Used when data is broken down by Male/Female) ── */
        .stat-gender-number {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 1.7rem;
            line-height: 1;
            color: #1f2937;
        }
        .stat-gender-sublabel {
            font-size: 10px;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 2px;
        }

        /* ── Chart Card Wrappers ── */
        .chart-card { position: relative; overflow: hidden; }
        .chart-card-title {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            padding: 14px 18px 0;
        }

        /* Utility to hide chart sections based on dropdown selection */
        .section-hidden { display: none !important; }

        /* ── Empty State / No-data Page ── */
        .no-data-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 24px;
            text-align: center;
        }
        .no-data-page .icon-wrap {
            width: 88px; height: 88px; border-radius: 50%;
            background: #f3f4f6;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .no-data-page .icon-wrap i { font-size: 36px; color: #9ca3af; }
        .no-data-page h2 { font-size: 22px; font-weight: 800; color: #374151; margin-bottom: 8px; }
        .no-data-page p  { color: #9ca3af; font-size: 13px; max-width: 340px; margin-bottom: 20px; }
        .no-data-page a  {
            display: inline-flex; align-items: center; gap: 8px;
            background: #009539; color: white;
            font-size: 13px; font-weight: 600;
            padding: 9px 18px; border-radius: 9999px;
            text-decoration: none; transition: background 0.2s;
        }
        .no-data-page a:hover { background: #007a2f; color: white; }
    </style>

    {{-- Tailwind CSS CDN for rapid UI styling --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    {{-- Sidebar Navigation Component --}}
    @include('components.sidebar')

    <div class="content w-100">

        {{-- ── Sticky header + filter bar: Stays at the top while scrolling ── --}}
        <div class="sticky top-0 z-50">
            <header>
                <span class="text-lg md:text-2xl font-[650] text-white">Graduates</span>
            </header>

            {{-- Two-part filter bar: fixed title left, scrollable filters right ── --}}
            <div class="flex" style="background:#d1d5db; border-bottom:1px solid #b0b5b0; min-height:42px;">

                {{-- Fixed left: Page title dynamically updates based on View Type and Semester --}}
                <div class="filter-page-title">
                    @php
                        $viewLabel = $selected_view_type === 'demographic_profile' ? 'Demographic Profile' : 'Graduate Headcount';
                        echo $viewLabel . ($semester !== 'All' ? ' (' . $semester . ')' : '');
                    @endphp
                </div>

                {{-- Scrollable right: Contains the actual form with dropdowns --}}
                <div class="filter-bar-wrapper">
                    <div class="filter-bar-scroll">
                        <div class="filter-bar-inner">

                            <span class="filter-label-divider">Filter</span>

                            {{-- Form triggers a GET request to update dashboard state --}}
                            <form method="GET" action="{{ route('graduates.index') }}"
                                  id="graduatesFilterForm"
                                  style="display:flex; align-items:center; gap:10px; flex-wrap:nowrap;">

                                {{-- View Type: Toggles between Headcount and Demographic UI --}}
                                <div class="filter-item">
                                    <span>View:</span>
                                    <select name="view_type" id="view_type">
                                        <option value="graduate_headcount" {{ $selected_view_type === 'graduate_headcount' ? 'selected' : '' }}>Headcount</option>
                                        <option value="demographic_profile" {{ $selected_view_type === 'demographic_profile' ? 'selected' : '' }}>Demographic</option>
                                    </select>
                                </div>

                                {{-- Student Level Filter --}}
                                <div class="filter-item">
                                    <span>Level:</span>
                                    <select name="student_level" id="student_level">
                                        <option value="All"           {{ $student_level === 'All'           ? 'selected' : '' }}>All Levels</option>
                                        <option value="Undergraduate" {{ $student_level === 'Undergraduate' ? 'selected' : '' }}>Undergraduate</option>
                                        <option value="Postgraduate"  {{ $student_level === 'Postgraduate'  ? 'selected' : '' }}>Postgraduate</option>
                                    </select>
                                </div>

                                {{-- Semester Filter (populated dynamically from backend) --}}
                                <div class="filter-item">
                                    <span>Semester:</span>
                                    <select name="semester" id="semester">
                                        <option value="All" {{ $semester === 'All' ? 'selected' : '' }}>All</option>
                                        @foreach($semesters as $sem)
                                            <option value="{{ $sem }}" {{ $semester === $sem ? 'selected' : '' }}>{{ $sem }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- College Filter --}}
                                <div class="filter-item">
                                    <span>College:</span>
                                    <select name="college" id="college">
                                        <option value="All" {{ $selected_college === 'All' ? 'selected' : '' }}>All</option>
                                        @foreach($colleges as $c)
                                            <option value="{{ $c }}" {{ $selected_college === $c ? 'selected' : '' }}>{{ $c }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- If "All" colleges are selected, reset the program filter behind the scenes --}}
                                @if($selected_college === 'All')
                                    <input type="hidden" name="program" value="All">
                                @endif

                                {{-- Program Filter (disabled until a specific college is chosen) --}}
                                <div class="filter-item">
                                    <span>Program:</span>
                                    <select name="program" id="program"
                                        {{ $selected_college === 'All' ? 'disabled' : '' }}>
                                        <option value="All" {{ $selected_program === 'All' ? 'selected' : '' }}>All</option>
                                        @foreach($programs as $p)
                                            <option value="{{ $p }}" {{ $selected_program === $p ? 'selected' : '' }}>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        {{-- ── End sticky header ── --}}

        <div class="px-6 pt-4 pb-10">

            {{--
                Data pre-processing block:
                Safely extracts the total count from the first box to determine if charts should render.
                Handles both single integer values and associative arrays (male/female split).
            --}}
            @php
                $firstBoxValue   = $value_boxes[0]['value'] ?? 0;
                $total_graduates = is_array($firstBoxValue)
                    ? (($firstBoxValue['male'] ?? 0) + ($firstBoxValue['female'] ?? 0))
                    : (int) $firstBoxValue;
                $has_data = $total_graduates > 0;

                // Array of FontAwesome icons cycled through for the stat cards
                $icons = [
                    'fa-solid fa-chart-line',
                    'fa-solid fa-users',
                    'fa-solid fa-user-graduate',
                    'fa-solid fa-building-columns',
                    'fa-solid fa-layer-group',
                    'fa-solid fa-circle-info',
                ];
            @endphp

            @if($has_data)

                {{-- ── Stat Cards Top Row ── --}}
                <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-4">
                    @foreach($value_boxes as $index => $box)
                        @php
                            $icon     = $icons[$index % count($icons)];
                            $isGender = is_array($box['value']); // Check if we need to show Male/Female split

                            // Adjust columns based on the total number of cards to fit grid nicely
                            $span     = count($value_boxes) <= 3 ? 4 : 3;
                        @endphp

                        <div class="col-span-3 xl:col-span-{{ $span }}">
                            <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                                <div class="grid grid-rows-3 h-full">
                                    <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                        <i class="{{ $icon }} text-white text-2xl"></i>
                                    </div>

                                    @if($isGender)
                                        {{-- Layout for Gender Split Data (e.g. Male: 50, Female: 60) --}}
                                        <div class="row-span-2 pb-2 flex flex-col justify-end">
                                            <div class="flex justify-between items-end pr-4">
                                                <div>
                                                    <p class="stat-gender-sublabel">Male</p>
                                                    <p class="stat-gender-number">{{ $box['value']['male'] ?? 0 }}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="stat-gender-sublabel">Female</p>
                                                    <p class="stat-gender-number">{{ $box['value']['female'] ?? 0 }}</p>
                                                </div>
                                            </div>
                                            <p class="stat-card-label pr-4 mt-1">{{ $box['title'] }}</p>
                                        </div>
                                    @else
                                        {{-- Layout for Single Value Data (e.g. Total: 110) --}}
                                        <div class="row-span-2 pb-3">
                                            <p class="stat-card-number pr-4 pt-2">{{ $box['value'] }}</p>
                                            <p class="stat-card-label pr-4">{{ $box['title'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                {{-- ── End Stat Cards ── --}}

                {{-- ── Demographic Profile View (Pie Chart) ── --}}
                <div id="demographicSection" class="{{ $selected_view_type === 'demographic_profile' ? '' : 'section-hidden' }}">
                    <div class="border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl mb-3 chart-card">
                        <div class="chart-card-title font-[750] text-sm md:text-lg text-gray-700 pl-5 sm:pl-7 pt-4"
                             id="demographicChartTitle">
                            {{ $pie_chart['title'] ?? 'Percentage of Graduates by Sex' }}
                        </div>
                        <div id="demographicPie" style="height:420px;"></div>
                    </div>
                </div>

                {{-- ── Headcount View (Donut, Ranking Bar, Stacked Bar) ── --}}
                <div id="headcountSection" class="{{ $selected_view_type === 'graduate_headcount' ? '' : 'section-hidden' }}">

                    {{-- Row 1: Donut + Ranking (Side by Side on Large Screens) ── --}}
                    <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-3">

                        {{-- Donut Chart Container --}}
                        <div class="col-span-6 h-[420px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                            <div class="chart-card-title font-[750] text-sm md:text-lg text-gray-700 pl-5 sm:pl-7 pt-4"
                                 id="donutChartTitle">
                                {{ $donut_chart['title'] ?? 'Graduate Distribution' }}
                            </div>
                            <div id="headcountDonut" style="height:calc(100% - 50px);"></div>
                        </div>

                        {{-- Ranking Bar Chart Container --}}
                        <div class="col-span-6 h-[420px] border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                            <div class="chart-card-title font-[750] text-sm md:text-lg text-gray-700 pl-5 sm:pl-7 pt-4"
                                 id="rankingChartTitle">
                                {{ $ranking_chart['title'] ?? 'Ranking of Graduates Count' }}
                            </div>
                            <div id="rankingBar" style="height:calc(100% - 50px);"></div>
                        </div>

                    </div>

                    {{-- Row 2: Full-width stacked sex bar chart ── --}}
                    <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl mb-3 chart-card">
                        <div class="chart-card-title font-[750] text-sm md:text-lg text-gray-700 pl-5 sm:pl-7 pt-4"
                             id="stackedChartTitle">
                            {{ $stacked_chart['title'] ?? 'Graduates Sex Distribution' }}
                        </div>
                        <div id="stackedSexBar" style="height:520px;"></div>
                        <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pb-2">
                            <i>Note: Percentage values shown within bars represent each sex's share within the group.</i>
                        </div>
                    </div>

                </div>
                {{-- ── End Charts ── --}}

            @else

                {{-- ── Empty State UI (Shown if $has_data is false) ── --}}
                <div class="no-data-page">
                    <div class="icon-wrap">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </div>
                    <h2>No Data Found</h2>
                    <p>No graduate records match the selected filters. Try adjusting or resetting the filters.</p>
                    <a href="{{ route('graduates.index') }}">
                        <i class="fa-solid fa-rotate-left" style="font-size:12px;"></i>
                        Reset Filters
                    </a>
                </div>

            @endif

        </div>
    </div>

    @if($has_data)
    {{-- Load Plotly.js for interactive charting --}}
    <script src="https://cdn.plot.ly/plotly-2.32.0.min.js"></script>
    <script>
    // ── Data Formatting & Chart Configuration ────────────────────────────────

    // Hardcoded overrides for long college/department names
    const FALLBACK_ABBREVIATIONS = {
        'Graduate School - Masters':  'GS-Masters',
        'Graduate School - Doctoral': 'GS-Doctoral',
        'DOT-UNI':                    'DOT-UNI',
    };

    // Regex patterns to strip formal degrees down to abbreviations (e.g. "Bachelor of Science" -> "BS")
    const DEGREE_PREFIXES = [
        { pattern: /^Bachelor of Science in\s+/i,                          short: 'BS ' },
        { pattern: /^Bachelor of Science\s*/i,                               short: 'BS ' },
        { pattern: /^Bachelor of Arts in\s+/i,                             short: 'BA ' },
        { pattern: /^Bachelor of Arts\s*/i,                                short: 'BA ' },
        { pattern: /^Bachelor of Technology in\s+/i,                       short: 'BTech ' },
        { pattern: /^Bachelor of Engineering in\s+/i,                      short: 'BEng ' },
        { pattern: /^Master of Science in\s+/i,                            short: 'MS ' },
        { pattern: /^Master of Arts in\s+/i,                               short: 'MA ' },
        { pattern: /^Master of Business Administration\s*/i,               short: 'MBA' },
        { pattern: /^Doctor of Philosophy in\s+/i,                         short: 'PhD ' },
        { pattern: /^Bachelor of Secondary Education\s*/i,                 short: 'BS Secondary Education' },
        { pattern: /^Bachelor of Elementary Education\s*/i,                short: 'BS Elementary Education' },
        { pattern: /^Bachelor of Technology and Livelihood Education\s*/i, short: 'BS Technology and Livelihood Education' },
        { pattern: /^Bachelor of Physical Education\s*/i,                  short: 'BS Physical Education' },
        { pattern: /^Bachelor of Early Childhood Education\s*/i,           short: 'BS Early Childhood Education' },
        { pattern: /^Bachelor of Culture\s*&\s*Arts Education\s*/i,        short: 'BS Culture & Arts Education' },
    ];

    // Standard color palette for charts
    const PALETTE = [
        '#016531','#86090A','#B29A00','#6D430F','#0A6DAF',
        '#00FFFF','#A70062','#FF0000','#4b4b4b',
        '#5A0F8A','#0F6D5A','#C46A00',
    ];

    const MALE_COLOR   = '#3B82F6'; // Blue
    const FEMALE_COLOR = '#EC4899'; // Pink

    // Reusable Plotly config and layout settings to ensure UI consistency
    const BASE_CONFIG  = { responsive: true, displayModeBar: false };
    const BASE_LAYOUT  = {
        font: { family: 'Inter, system-ui, sans-serif', size: 12 },
        paper_bgcolor: 'rgba(0,0,0,0)',
        plot_bgcolor:  'rgba(0,0,0,0)',
    };

    // Inject PHP data directly into JS object using Laravel's Js::from directive
    const initialData = {!! Js::from([
        'selected_view_type' => $selected_view_type,
        'dynamic_title'      => $dynamic_title,
        'value_boxes'        => $value_boxes,
        'pie_chart'          => $pie_chart,
        'donut_chart'        => $donut_chart,
        'major_chart'        => $major_chart ?? null,
        'ranking_chart'      => $ranking_chart,
        'stacked_chart'      => $stacked_chart,
        'selected_college'   => $selected_college,
        'selected_program'   => $selected_program,
    ]) !!};

    /** Removes long degree prefixes using the DEGREE_PREFIXES rules */
    function shortenDegreeName(name) {
        if (!name) return name;
        let cleaned = String(name).trim();
        for (const { pattern, short } of DEGREE_PREFIXES) {
            if (pattern.test(cleaned)) return (short + cleaned.replace(pattern, '')).trim();
        }
        return cleaned;
    }

    /** Cleans up trailing parenthesis notes from programs before abbreviating */
    function abbreviateProgram(name) {
        if (!name) return name;
        let cleaned = String(name).trim();
        cleaned = cleaned.replace(/\s*\((DOT-Uni|DOT UNI|GS-Masters|GS-Doctoral)\)\s*$/i, '');
        return shortenDegreeName(cleaned);
    }

    /** Extracts college acronyms from parenthesis (e.g., "College of Arts (COA)" -> "COA") */
    function abbreviateCollege(name) {
        if (!name) return name;
        let cleaned = String(name).trim();
        const parenMatch = cleaned.match(/\(([^)]+)\)\s*$/);
        if (parenMatch) return parenMatch[1].trim();

        // Fallback to hardcoded dict
        if (FALLBACK_ABBREVIATIONS[cleaned]) return FALLBACK_ABBREVIATIONS[cleaned];
        const ci = Object.keys(FALLBACK_ABBREVIATIONS).find(k => k.toLowerCase() === cleaned.toLowerCase());
        if (ci) return FALLBACK_ABBREVIATIONS[ci];

        return cleaned;
    }

    /** Determines which formatter to use based on the current drill-down state (College vs Program) */
    function getLabelFormatter(data, mode = 'auto') {
        const isCollegeSelected = initialData.selected_college && initialData.selected_college !== 'All';
        const isProgramSelected = initialData.selected_program && initialData.selected_program !== 'All';
        if (mode === 'college') return abbreviateCollege;
        if (mode === 'program') return abbreviateProgram;

        // Auto logic: If viewing all colleges, abbreviate colleges. If college selected, abbreviate programs.
        if (!isCollegeSelected) return abbreviateCollege;
        if (isProgramSelected)  return abbreviateProgram;
        return abbreviateProgram;
    }

    // ── Chart Rendering Functions ───────────────────────────────────────────

    /** Renders the Male/Female Demographics Pie Chart */
    function renderDemographicPie(data) {
        if (!data?.labels?.length) return;
        document.getElementById('demographicChartTitle').textContent = data.title || 'Percentage of Graduates by external Sex';
        Plotly.newPlot('demographicPie', [{
            type: 'pie', labels: data.labels, values: data.values,
            marker: { colors: [MALE_COLOR, FEMALE_COLOR], line: { color: '#fff', width: 2 } },
            texttemplate: '<b>%{percent:.1%}</b>', textposition: 'outside',
            hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>Share: %{percent:.1%}<extra></extra>',
            pull: 0.03, // Slight explosion effect
        }], {
            ...BASE_LAYOUT,
            margin: { t: 60, b: 20, l: 20, r: 20 },
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.12, font: { size: 13, color: '#374151' }, itemsizing: 'constant' },
        }, BASE_CONFIG);
    }

    /** Renders the Headcount Donut Chart (Colleges or Programs distribution) */
    function renderHeadcountDonut(data) {
        if (!data?.labels?.length) return;
        document.getElementById('donutChartTitle').textContent = data.title || 'Graduate Distribution';
        const formatter         = getLabelFormatter(data);
        const shortLabels       = data.labels.map(formatter); // Use abbreviations for cleaner UI
        const programColors     = data.program_colors || {};
        const isCollegeSelected = initialData.selected_college && initialData.selected_college !== 'All';

        // Map colors. Use specific program colors if provided, else fallback to standard palette.
        const colors = data.labels.map((label, i) => {
            if (isCollegeSelected) return programColors[label] || PALETTE[i % PALETTE.length];
            return PALETTE[i % PALETTE.length];
        });

        Plotly.newPlot('headcountDonut', [{
            type: 'pie', labels: shortLabels, values: data.values, customdata: data.labels,
            hole: 0.58, marker: { colors, line: { color: '#fff', width: 2 } },
            texttemplate: '<b>%{percent:.1%}</b>', textposition: 'inside',
            hovertemplate: '<b>%{customdata}</b><br>Count: %{value}<br>Share: %{percent:.1%}<extra></extra>',
        }], {
            ...BASE_LAYOUT,
            margin: { t: 10, b: 10, l: 10, r: 160 },
            legend: { orientation: 'v', x: 1.02, xanchor: 'left', y: 0.5, yanchor: 'middle' },
        }, BASE_CONFIG);
    }

    /** Renders the Horizontal Bar Chart ranking departments/programs */
    function renderRankingBar(data) {
        if (!data?.labels?.length) return;
        document.getElementById('rankingChartTitle').textContent = data.title || 'Ranking of Graduates Count';
        const programColors     = data.program_colors || {};
        const isCollegeSelected = initialData.selected_college && initialData.selected_college !== 'All';
        const formatter         = isCollegeSelected ? abbreviateProgram : abbreviateCollege;

        // Bind full names, short names, and values together so we can sort them accurately
        const rows = data.labels
            .map((full, i) => ({ short: formatter(full), full, val: data.values[i], highlight: data.highlight && full === data.highlight }))
            .sort((a, b) => a.val - b.val); // Sort ascending so largest is on top in Plotly

        const colors = rows.map((row, i) => {
            if (row.highlight) return '#F59E0B'; // Highlight specific row (Yellow)
            if (isCollegeSelected) return programColors[row.full] || PALETTE[i % PALETTE.length];
            return PALETTE[(rows.length - 1 - i) % PALETTE.length];
        });

        Plotly.newPlot('rankingBar', [{
            type: 'bar', orientation: 'h',
            x: rows.map(d => d.val), y: rows.map(d => d.short), customdata: rows.map(d => d.full),
            hovertemplate: '<b>%{customdata}</b><br>Graduates: %{x}<extra></extra>',
            text: rows.map(d => d.val), textposition: 'outside', cliponaxis: false,
            marker: { color: colors, line: { color: 'transparent' } },
        }], {
            ...BASE_LAYOUT,
            margin: { t: 10, b: 50, l: 140, r: 50 },
            xaxis: { title: { text: data.x_axis_label || 'Number of Graduates', font: { size: 12 } }, gridcolor: '#f1f5f9', zeroline: false },
            yaxis: { automargin: true, tickfont: { size: 12, color: '#374151' } },
            showlegend: false
        }, BASE_CONFIG);
    }

    /** Renders the Stacked 100% Bar Chart showing Male/Female ratios per department */
    function renderStackedSexBar(data) {
        if (!data?.labels?.length) return;
        document.getElementById('stackedChartTitle').textContent = data.title || 'Graduates Sex Distribution';
        const formatter   = getLabelFormatter(data);
        const shortLabels = data.labels.map(formatter);

        // Pass full context to customdata so hover tooltips show absolute counts alongside percentages
        const cd = data.labels.map((l, i) => ({
            full: l, malePct: data.male_pct[i], femalePct: data.female_pct[i],
            maleCount: data.male_count[i], femaleCount: data.female_count[i],
        }));

        Plotly.newPlot('stackedSexBar', [
            {
                type: 'bar', name: 'Male', orientation: 'h',
                x: data.male_pct, y: shortLabels, customdata: cd,
                text: data.male_pct.map(v => v > 4 ? `${v}%` : ''), // Hide text if slice is too small
                textposition: 'inside', textfont: { color: '#fff', size: 11 },
                marker: { color: MALE_COLOR },
                hovertemplate: '<b>%{customdata.full}</b><br>Male: %{customdata.malePct}% (%{customdata.maleCount})<br>Female: %{customdata.femalePct}% (%{customdata.femaleCount})<extra></extra>',
            },
            {
                type: 'bar', name: 'Female', orientation: 'h',
                x: data.female_pct, y: shortLabels, customdata: cd,
                text: data.female_pct.map(v => v > 4 ? `${v}%` : ''),
                textposition: 'inside', textfont: { color: '#fff', size: 11 },
                marker: { color: FEMALE_COLOR },
                hovertemplate: '<b>%{customdata.full}</b><br>Male: %{customdata.malePct}% (%{customdata.maleCount})<br>Female: %{customdata.femalePct}% (%{customdata.femaleCount})<extra></extra>',
            }
        ], {
            ...BASE_LAYOUT,
            barmode: 'stack', // Crucial for 100% stacked effect
            margin: { t: 50, b: 50, l: 140, r: 30 },
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.06, font: { size: 13, color: '#374151' }, itemsizing: 'constant' },
            xaxis: { title: { text: 'Percentage (%)', font: { size: 12 } }, range: [0, 100], gridcolor: '#f1f5f9', zeroline: false },
            yaxis: { title: { text: data.y_axis_label || 'College / Department', font: { size: 12 } }, automargin: true, tickfont: { size: 12, color: '#374151' } },
        }, BASE_CONFIG);
    }

    /** Master orchestrator function to render the correct charts based on View Type */
    function renderDashboard(data) {
        const isDemographic = data.selected_view_type === 'demographic_profile';

        // Toggle visibility of the layout sections
        document.getElementById('demographicSection').classList.toggle('section-hidden', !isDemographic);
        document.getElementById('headcountSection').classList.toggle('section-hidden', isDemographic);

        if (isDemographic) {
            renderDemographicPie(data.pie_chart);
        } else {
            // Determine if we should show program-specific donut data or general college donut data
            const isProgramSelected = data.selected_program && data.selected_program !== 'All';
            const donutData = (isProgramSelected && data.major_chart?.labels?.length)
                ? data.major_chart
                : data.donut_chart;

            renderHeadcountDonut(donutData);
            renderRankingBar(data.ranking_chart);
            renderStackedSexBar(data.stacked_chart);
        }
    }

    // Auto-render on script load using the injected PHP data
    renderDashboard(initialData);
    </script>
    @endif
</body>
</html>
