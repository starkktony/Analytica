<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <style>
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
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

        /* ── Header ── */
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

        /* ── Filter Bar ── */
        .filter-bar {
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            gap: 10px;
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
        .filter-bar-label {
            font-size: 12px;
            font-weight: 700;
            color: #2d2d2d;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
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
            padding: 4px 24px 4px 10px;
            border-radius: 20px;
            border: 1px solid #8a8f8a;
            background-color: #f5f5f5;
            color: #2d2d2d;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%232d2d2d' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 8px;
            min-width: 110px;
            cursor: pointer;
        }
        .filter-group select:focus {
            outline: none;
            border-color: #009539;
            background-color: white;
        }
        .filter-group select:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ── Main content ── */
        .main-content { padding: 0; }

        /* ── Page content area ── */
        .page-content { padding: 24px; }

        /* ── Value boxes ── */
        .value-boxes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 1024px) { .value-boxes-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px)  { .value-boxes-grid { grid-template-columns: 1fr; } }

        .value-box {
            position: relative;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            min-height: 130px;
        }
        .value-box.first {
            background: linear-gradient(to right, #22c55e, #16a34a);
            color: white;
        }
        .value-box.other {
            background: white;
            color: #111827;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .value-box-icon {
            position: absolute;
            top: 16px;
            left: 16px;
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .value-box.first .value-box-icon { background: rgba(255,255,255,0.9); color: #16a34a; }
        .value-box.other .value-box-icon { background: #22c55e; color: white; }
        .value-box-body { margin-top: 52px; text-align: right; }
        .value-box-number { font-size: 40px; font-weight: 800; line-height: 1; }
        .value-box.first .value-box-number { color: white; }
        .value-box.other .value-box-number { color: #111827; }
        .value-box-label { font-size: 14px; font-weight: 600; margin-top: 4px; }
        .value-box.first .value-box-label { color: white; }
        .value-box.other .value-box-label { color: #6b7280; }

        /* Gender split inside value box */
        .gender-split { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: right; }
        .gender-split-label { font-size: 11px; margin-bottom: 2px; }
        .value-box.first .gender-split-label { color: rgba(255,255,255,0.8); }
        .value-box.other .gender-split-label { color: #64748b; }
        .gender-split-number { font-size: 36px; font-weight: 800; line-height: 1; }
        .value-box.first .gender-split-number { color: white; }
        .value-box.other .gender-split-number { color: #111827; }

        /* ── Chart cards ── */
        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
            margin-bottom: 24px;
        }
        .chart-card h3 { font-size: 15px; font-weight: 700; margin: 0 0 16px 0; color: #111827; }
        .charts-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
        @media (max-width: 1024px) { .charts-grid-2 { grid-template-columns: 1fr; } }

        /* ── Section toggle ── */
        .section-hidden { display: none !important; }

        /* ── No data state ── */
        .no-data-state {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 96px 24px; text-align: center;
        }
        .no-data-icon-wrap {
            width: 96px; height: 96px; border-radius: 50%;
            background: #f3f4f6; display: flex;
            align-items: center; justify-content: center;
            margin-bottom: 24px; box-shadow: inset 0 2px 8px rgba(0,0,0,0.06);
        }
        .no-data-icon-wrap i { font-size: 40px; color: #9ca3af; }
        .no-data-title { font-family: 'Bricolage Grotesque', sans-serif; font-size: 24px; font-weight: 800; color: #374151; margin-bottom: 8px; }
        .no-data-text { color: #9ca3af; font-size: 14px; max-width: 360px; margin-bottom: 24px; }
        .reset-btn {
            display: inline-flex; align-items: center; gap: 8px;
            background: #16a34a; color: white; font-size: 14px;
            font-weight: 600; padding: 10px 20px; border-radius: 9999px;
            text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            transition: background 0.2s;
        }
        .reset-btn:hover { background: #15803d; color: white; }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="content">

        {{-- Page Header --}}
        <div class="header">GRADUATES</div>

        @php
            $firstBoxValue   = $value_boxes[0]['value'] ?? 0;
            $total_graduates = is_array($firstBoxValue)
                ? (($firstBoxValue['male'] ?? 0) + ($firstBoxValue['female'] ?? 0))
                : (int) $firstBoxValue;
            $has_data = $total_graduates > 0;
        @endphp

        {{-- Filter Bar — all filters here --}}
        <div class="filter-bar">
            <span class="filter-bar-label">Filters:</span>

            <form method="GET" action="{{ route('graduates.index') }}"
                  id="graduatesFilterForm"
                  style="display:flex;align-items:center;gap:10px;flex-wrap:nowrap;">

                <div class="filter-group">
                    <label>View:</label>
                    <select name="view_type" id="view_type">
                        <option value="graduate_headcount" {{ $selected_view_type === 'graduate_headcount' ? 'selected' : '' }}>Headcount</option>
                        <option value="demographic_profile" {{ $selected_view_type === 'demographic_profile' ? 'selected' : '' }}>Demographic</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Level:</label>
                    <select name="student_level" id="student_level">
                        <option value="All"           {{ $student_level === 'All'           ? 'selected' : '' }}>All Levels</option>
                        <option value="Undergraduate" {{ $student_level === 'Undergraduate' ? 'selected' : '' }}>Undergraduate</option>
                        <option value="Postgraduate"  {{ $student_level === 'Postgraduate'  ? 'selected' : '' }}>Postgraduate</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Semester:</label>
                    <select name="semester" id="semester">
                        <option value="All" {{ $semester === 'All' ? 'selected' : '' }}>All</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem }}" {{ $semester === $sem ? 'selected' : '' }}>{{ $sem }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>College:</label>
                    <select name="college" id="college">
                        <option value="All" {{ $selected_college === 'All' ? 'selected' : '' }}>All</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c }}" {{ $selected_college === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                @if($selected_college === 'All')
                    <input type="hidden" name="program" value="All">
                @endif

                <div class="filter-group">
                    <label>Program:</label>
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

        {{-- Main Content --}}
        <div class="main-content">
            <div class="page-content">

                @if($has_data)

                    {{-- Value Boxes --}}
                    <div class="value-boxes-grid">
                        @foreach($value_boxes as $index => $box)
                            @php
                                $isFirst = $index === 0;
                                $icons   = [
                                    'fa-solid fa-chart-line',
                                    'fa-solid fa-users',
                                    'fa-solid fa-user-graduate',
                                    'fa-solid fa-building-columns',
                                    'fa-solid fa-layer-group',
                                    'fa-solid fa-circle-info',
                                ];
                                $icon = $icons[$index % count($icons)];
                            @endphp
                            <div class="value-box {{ $isFirst ? 'first' : 'other' }}">
                                <div class="value-box-icon"><i class="{{ $icon }}"></i></div>
                                <div class="value-box-body">
                                    @if(is_array($box['value']))
                                        <div class="gender-split">
                                            <div>
                                                <div class="gender-split-label">Male</div>
                                                <div class="gender-split-number">{{ $box['value']['male'] ?? 0 }}</div>
                                            </div>
                                            <div>
                                                <div class="gender-split-label">Female</div>
                                                <div class="gender-split-number">{{ $box['value']['female'] ?? 0 }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="value-box-number">{{ $box['value'] }}</div>
                                    @endif
                                    <div class="value-box-label">{{ $box['title'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Demographic Profile View --}}
                    <div id="demographicSection" class="{{ $selected_view_type === 'demographic_profile' ? '' : 'section-hidden' }}">
                        <div class="chart-card">
                            <h3 id="demographicChartTitle">{{ $pie_chart['title'] ?? 'Percentage of Graduates by Sex' }}</h3>
                            <div id="demographicPie" style="height:420px;"></div>
                        </div>
                    </div>

                    {{-- Headcount View --}}
                    <div id="headcountSection" class="{{ $selected_view_type === 'graduate_headcount' ? '' : 'section-hidden' }}">
                        <div class="charts-grid-2">
                            <div class="chart-card" style="margin-bottom:0;">
                                <h3 id="donutChartTitle">{{ $donut_chart['title'] ?? 'Graduate Distribution' }}</h3>
                                <div id="headcountDonut" style="height:420px;"></div>
                            </div>
                            <div class="chart-card" style="margin-bottom:0;">
                                <h3 id="rankingChartTitle">{{ $ranking_chart['title'] ?? 'Ranking of Graduates Count' }}</h3>
                                <div id="rankingBar" style="height:420px;"></div>
                            </div>
                        </div>
                        <div class="chart-card">
                            <h3 id="stackedChartTitle">{{ $stacked_chart['title'] ?? 'Graduates Sex Distribution' }}</h3>
                            <div id="stackedSexBar" style="height:520px;"></div>
                        </div>
                    </div>

                @else

                    <div class="no-data-state">
                        <div class="no-data-icon-wrap">
                            <i class="fa-solid fa-filter-circle-xmark"></i>
                        </div>
                        <div class="no-data-title">No Data Found</div>
                        <p class="no-data-text">No graduate records match the selected filters. Try adjusting or resetting the filters.</p>
                        <a href="{{ route('graduates.index') }}" class="reset-btn">
                            <i class="fa-solid fa-rotate-left" style="font-size:12px;"></i>
                            Reset Filters
                        </a>
                    </div>

                @endif

            </div>
        </div>
    </div>{{-- /.content --}}

    @if($has_data)
        <script src="https://cdn.plot.ly/plotly-2.32.0.min.js"></script>
        <script>
            const FALLBACK_ABBREVIATIONS = {
                'Graduate School - Masters':  'GS-Masters',
                'Graduate School - Doctoral': 'GS-Doctoral',
                'DOT-UNI':                    'DOT-UNI',
            };

            const DEGREE_PREFIXES = [
                { pattern: /^Bachelor of Science in\s+/i,                           short: 'BS ' },
                { pattern: /^Bachelor of Science\s*/i,                              short: 'BS ' },
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

            const PALETTE = [
                '#016531','#86090A','#B29A00','#6D430F','#0A6DAF',
                '#00FFFF','#A70062','#FF0000','#4b4b4b',
                '#5A0F8A','#0F6D5A','#C46A00',
            ];

            const MALE_COLOR   = '#3B82F6';
            const FEMALE_COLOR = '#EC4899';
            const BASE_CONFIG  = { responsive: true, displayModeBar: false };
            const BASE_LAYOUT  = {
                font: { family: 'Inter, system-ui, sans-serif', size: 12 },
                paper_bgcolor: 'rgba(0,0,0,0)',
                plot_bgcolor:  'rgba(0,0,0,0)',
            };

            const initialData = {{ Js::from([
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
            ]) }};

            function shortenDegreeName(name) {
                if (!name) return name;
                let cleaned = String(name).trim();
                for (const { pattern, short } of DEGREE_PREFIXES) {
                    if (pattern.test(cleaned)) return (short + cleaned.replace(pattern, '')).trim();
                }
                return cleaned;
            }

            function abbreviateProgram(name) {
                if (!name) return name;
                let cleaned = String(name).trim();
                cleaned = cleaned.replace(/\s*\((DOT-Uni|DOT UNI|GS-Masters|GS-Doctoral)\)\s*$/i, '');
                return shortenDegreeName(cleaned);
            }

            function abbreviateCollege(name) {
                if (!name) return name;
                let cleaned = String(name).trim();
                const parenMatch = cleaned.match(/\(([^)]+)\)\s*$/);
                if (parenMatch) return parenMatch[1].trim();
                if (FALLBACK_ABBREVIATIONS[cleaned]) return FALLBACK_ABBREVIATIONS[cleaned];
                const ci = Object.keys(FALLBACK_ABBREVIATIONS).find(k => k.toLowerCase() === cleaned.toLowerCase());
                if (ci) return FALLBACK_ABBREVIATIONS[ci];
                return cleaned;
            }

            function getLabelFormatter(data, mode = 'auto') {
                const isCollegeSelected = initialData.selected_college && initialData.selected_college !== 'All';
                const isProgramSelected = initialData.selected_program && initialData.selected_program !== 'All';
                if (mode === 'college') return abbreviateCollege;
                if (mode === 'program') return abbreviateProgram;
                if (!isCollegeSelected) return abbreviateCollege;
                if (isProgramSelected)  return abbreviateProgram;
                return abbreviateProgram;
            }

            function renderDemographicPie(data) {
                if (!data?.labels?.length) return;
                document.getElementById('demographicChartTitle').textContent = data.title || 'Percentage of Graduates by Sex';
                Plotly.newPlot('demographicPie', [{
                    type: 'pie', labels: data.labels, values: data.values,
                    marker: { colors: [MALE_COLOR, FEMALE_COLOR], line: { color: '#fff', width: 2 } },
                    texttemplate: '<b>%{percent:.1%}</b>', textposition: 'outside',
                    hovertemplate: '<b>%{label}</b><br>Count: %{value}<br>Share: %{percent:.1%}<extra></extra>',
                    pull: 0.03,
                }], {
                    ...BASE_LAYOUT,
                    margin: { t: 60, b: 20, l: 20, r: 20 },
                    legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.12, font: { size: 13, color: '#374151' }, itemsizing: 'constant' },
                }, BASE_CONFIG);
            }

            function renderHeadcountDonut(data) {
                if (!data?.labels?.length) return;
                document.getElementById('donutChartTitle').textContent = data.title || 'Graduate Distribution';
                const formatter      = getLabelFormatter(data);
                const shortLabels    = data.labels.map(formatter);
                const programColors  = data.program_colors || {};
                const isCollegeSelected = initialData.selected_college && initialData.selected_college !== 'All';
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

            function renderRankingBar(data) {
                if (!data?.labels?.length) return;
                document.getElementById('rankingChartTitle').textContent = data.title || 'Ranking of Graduates Count';
                const programColors     = data.program_colors || {};
                const isCollegeSelected = initialData.selected_college && initialData.selected_college !== 'All';
                const formatter         = isCollegeSelected ? abbreviateProgram : abbreviateCollege;
                const rows = data.labels
                    .map((full, i) => ({ short: formatter(full), full, val: data.values[i], highlight: data.highlight && full === data.highlight }))
                    .sort((a, b) => a.val - b.val);
                const colors = rows.map((row, i) => {
                    if (row.highlight) return '#F59E0B';
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

            function renderStackedSexBar(data) {
                if (!data?.labels?.length) return;
                document.getElementById('stackedChartTitle').textContent = data.title || 'Graduates Sex Distribution';
                const formatter   = getLabelFormatter(data);
                const shortLabels = data.labels.map(formatter);
                const cd = data.labels.map((l, i) => ({
                    full: l, malePct: data.male_pct[i], femalePct: data.female_pct[i],
                    maleCount: data.male_count[i], femaleCount: data.female_count[i],
                }));
                Plotly.newPlot('stackedSexBar', [
                    {
                        type: 'bar', name: 'Male', orientation: 'h',
                        x: data.male_pct, y: shortLabels, customdata: cd,
                        text: data.male_pct.map(v => v > 4 ? `${v}%` : ''),
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
                    barmode: 'stack',
                    margin: { t: 50, b: 50, l: 140, r: 30 },
                    legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.06, font: { size: 13, color: '#374151' }, itemsizing: 'constant' },
                    xaxis: { title: { text: 'Percentage (%)', font: { size: 12 } }, range: [0, 100], gridcolor: '#f1f5f9', zeroline: false },
                    yaxis: { title: { text: data.y_axis_label || 'College / Department', font: { size: 12 } }, automargin: true, tickfont: { size: 12, color: '#374151' } },
                }, BASE_CONFIG);
            }

            function renderDashboard(data) {
                const isDemographic = data.selected_view_type === 'demographic_profile';
                document.getElementById('demographicSection').classList.toggle('section-hidden', !isDemographic);
                document.getElementById('headcountSection').classList.toggle('section-hidden', isDemographic);
                if (isDemographic) {
                    renderDemographicPie(data.pie_chart);
                } else {
                    const isProgramSelected = data.selected_program && data.selected_program !== 'All';
                    const donutData = (isProgramSelected && data.major_chart?.labels?.length)
                        ? data.major_chart
                        : data.donut_chart;
                    renderHeadcountDonut(donutData);
                    renderRankingBar(data.ranking_chart);
                    renderStackedSexBar(data.stacked_chart);
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                renderDashboard(initialData);
            });
        </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form         = document.getElementById('graduatesFilterForm');
            const college      = document.getElementById('college');
            const program      = document.getElementById('program');
            const viewType     = document.getElementById('view_type');
            const studentLevel = document.getElementById('student_level');
            const semester     = document.getElementById('semester');

            function syncProgramState() {
                const isAll = college.value === 'All';
                if (isAll) { program.value = 'All'; program.setAttribute('disabled', 'disabled'); }
                else { program.removeAttribute('disabled'); }
            }

            syncProgramState();
            college.addEventListener('change', syncProgramState);

            [viewType, studentLevel, semester, college, program].forEach(el => {
                el.addEventListener('change', () => {
                    if (college.value === 'All') program.value = 'All';
                    form.submit();
                });
            });
        });
    </script>
</body>
</html>