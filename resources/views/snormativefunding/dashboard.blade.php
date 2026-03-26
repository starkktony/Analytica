<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script src="https://cdn.plot.ly/plotly-2.35.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

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

        /* Main layout - flex column for proper fixed header */
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

        /* ── Main content (scrollable area) ── */
        .main-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 24px 30px 40px 30px;
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
        .chart-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .chart-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 1100px) {
            .chart-grid-3 { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .chart-grid-3, .chart-grid-2 { grid-template-columns: 1fr; }
        }

        .chart-inner-card {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            box-shadow: inset 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden;
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
            min-height: 420px;
            overflow: hidden;
        }
        .chart-plot-area > div {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>

    <div class="app-wrapper">
        @include('components.sidebar')

        <div class="content">
            <div class="fixed-header-section">
                {{-- Page Header --}}
                <div class="header">
                    NORMATIVE FUNDING
                </div>

                {{-- Filter Bar --}}
                <div class="filter-bar">
                    <div class="page-title">
                        @if($filter_type === 'allotment_expenditure')
                            Budget Utilization ({{ $year }})
                        @elseif($filter_type === 'suc_income')
                            SUC Income ({{ $year }})
                        @else
                            University Financial Overview ({{ $year }})
                        @endif
                    </div>
                    <div class="filter-right">
                        <span class="filter-bar-label">Filters:</span>
                        <div class="filter-group">
                            <label for="year_filter">Year:</label>
                            <select id="year_filter" onchange="updateFilters()">
                                @foreach($suc_years as $y)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                                @if(empty($suc_years))
                                    <option>No Data Found</option>
                                @endif
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="type_filter">Type:</label>
                            <select id="type_filter" onchange="updateFilters()">
                                <option value="suc_income"            {{ $filter_type === 'suc_income'            ? 'selected' : '' }}>SUC Income</option>
                                <option value="allotment_expenditure" {{ $filter_type === 'allotment_expenditure' ? 'selected' : '' }}>Budget Utilization</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content (scrollable) --}}
            <div class="main-content">

                {{-- ============================================================ --}}
                {{-- SUC INCOME SECTION --}}
                {{-- ============================================================ --}}
                <div id="suc_income_section" {{ !in_array($filter_type, ['all', 'suc_income']) ? 'style=display:none' : '' }}>

                    {{-- Stat Cards — Programs-style design --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 py-6 mb-2">

                        {{-- Total University Income --}}
                        <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                            <div class='grid grid-rows-3 h-full'>
                                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                                    <i class="fa-solid fa-money-bill-wave text-white text-3xl"></i>
                                </div>
                                <div class='row-span-2 pt-2'>
                                    <p class='text-lg sm:text-xl text-right font-[750] pr-4 align-bottom text-gray-800 leading-tight'>{{ $income['grand_total_income'] }}</p>
                                    <p class='text-[10px] md:text-[12px] text-right font-medium pr-4'>Total University Income</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total Academic Fees --}}
                        <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                            <div class='grid grid-rows-3 h-full'>
                                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                                    <i class="fa-solid fa-graduation-cap text-white text-3xl"></i>
                                </div>
                                <div class='row-span-2 pt-2'>
                                    <p class='text-lg sm:text-xl text-right font-[750] pr-4 align-bottom text-gray-800 leading-tight'>{{ $income['tuition_misc_fee'] }}</p>
                                    <p class='text-[10px] md:text-[12px] text-right font-medium pr-4'>Total Academic Fees</p>
                                </div>
                            </div>
                        </div>

                        {{-- Auxiliary & Business Income --}}
                        <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                            <div class='grid grid-rows-3 h-full'>
                                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                                    <i class="fa-solid fa-building text-white text-3xl"></i>
                                </div>
                                <div class='row-span-2 pt-2'>
                                    <p class='text-lg sm:text-xl text-right font-[750] pr-4 align-bottom text-gray-800 leading-tight'>{{ $income['miscellaneous'] }}</p>
                                    <p class='text-[10px] md:text-[12px] text-right font-medium pr-4'>Auxiliary &amp; Business Income</p>
                                </div>
                            </div>
                        </div>

                        {{-- Other Business Income --}}
                        <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                            <div class='grid grid-rows-3 h-full'>
                                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                                    <i class="fa-solid fa-circle-plus text-white text-3xl"></i>
                                </div>
                                <div class='row-span-2 pt-2'>
                                    <p class='text-lg sm:text-xl text-right font-[750] pr-4 align-bottom text-gray-800 leading-tight'>{{ $income['other_income'] }}</p>
                                    <p class='text-[10px] md:text-[12px] text-right font-medium pr-4'>Other Business Income</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Income Charts --}}
                    <div class="chart-section-wrapper">
                        <div class="chart-grid-3">
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">Total University Income Breakdown</div>
                                <div class="chart-plot-area"><div id="mainPieChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">Total Academic Fees Breakdown</div>
                                <div class="chart-plot-area"><div id="tuitionPieChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">Other Business Income Breakdown</div>
                                <div class="chart-plot-area"><div id="otherIncomePieChart"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ --}}
                {{-- BUDGET UTILIZATION SECTION --}}
                {{-- ============================================================ --}}
                <div id="budget_utilization_section" {{ !in_array($filter_type, ['all', 'allotment_expenditure']) ? 'style=display:none' : '' }}>

                    @php
                        $cards = [
                            [
                                'label'       => 'Combined',
                                'icon'        => 'fa-wallet',
                                'allotment'   => $allotment['combined_total'],
                                'expenditure' => $expenditure['combined_total'],
                                'utilization' => isset($allotment_raw['combined']) && $allotment_raw['combined'] > 0
                                                    ? round(($expenditure_raw['combined'] / $allotment_raw['combined']) * 100, 1)
                                                    : null,
                            ],
                            [
                                'label'       => 'GAA',
                                'icon'        => 'fa-landmark',
                                'allotment'   => $allotment['gaa_total'],
                                'expenditure' => $expenditure['gaa_total'],
                                'utilization' => isset($allotment_raw['gaa']) && $allotment_raw['gaa'] > 0
                                                    ? round(($expenditure_raw['gaa'] / $allotment_raw['gaa']) * 100, 1)
                                                    : null,
                            ],
                            [
                                'label'       => 'SUC Income',
                                'icon'        => 'fa-arrow-trend-up',
                                'allotment'   => $allotment['suc_total'],
                                'expenditure' => $expenditure['suc_total'],
                                'utilization' => isset($allotment_raw['suc']) && $allotment_raw['suc'] > 0
                                                    ? round(($expenditure_raw['suc'] / $allotment_raw['suc']) * 100, 1)
                                                    : null,
                            ],
                        ];
                    @endphp

                    {{-- Budget Utilization Cards --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 py-6 mb-2">

                        @foreach ($cards as $card)
                            <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md rounded-xl shadow-md p-4 overflow-hidden'>
                                <div class='flex flex-col gap-3'>
                                    {{-- Top row: icon left, label badge right --}}
                                    <div class='flex items-center justify-between'>
                                        <div class='bg-green-50 border border-green-200 rounded-lg h-10 w-10 flex items-center justify-center'>
                                            <i class="fa-solid {{ $card['icon'] }} text-green-600 text-lg"></i>
                                        </div>
                                        <span class='text-xs font-bold text-green-700 bg-green-50 border border-green-200 px-3 py-1 rounded-full'>
                                            {{ $card['label'] }}
                                        </span>
                                    </div>
                                    {{-- Bottom row: allotment left, expenditure right --}}
                                    <div class='flex items-end justify-between gap-2'>
                                        <div>
                                            <p class='text-[11px] text-gray-400 font-medium mb-0.5'>Allotment</p>
                                            <p class='text-sm sm:text-base font-[750] text-gray-900 leading-tight'>{{ $card['allotment'] }}</p>
                                        </div>
                                        <div class='text-right'>
                                            <p class='text-[11px] text-gray-400 font-medium mb-0.5'>Expenditure</p>
                                            <p class='text-sm sm:text-base font-[750] text-red-500 leading-tight'>{{ $card['expenditure'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    {{-- Budget Charts --}}
                    <div class="chart-section-wrapper">
                        <div class="chart-grid-2">
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">Distribution by Funding Source (GAA)</div>
                                <div class="chart-plot-area"><div id="allotmentPieChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">Distribution of Total Expenditures (GAA)</div>
                                <div class="chart-plot-area"><div id="expenditurePieChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">Total GAA Allotment by Expense Class</div>
                                <div class="chart-plot-area"><div id="allotmentCategoryChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">Total GAA Expenditure by Expense Class</div>
                                <div class="chart-plot-area"><div id="expenditureCategoryChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">GAA Allotment by Expense Class</div>
                                <div class="chart-plot-area"><div id="allotmentGAAChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">GAA Expenditure by Expense Class</div>
                                <div class="chart-plot-area"><div id="expenditureGAAChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">SUC Income Allotment by Expense Class</div>
                                <div class="chart-plot-area"><div id="allotmentSUCChart"></div></div>
                            </div>
                            <div class="chart-inner-card">
                                <div class="chart-inner-title">SUC Income Expenditure by Expense Class</div>
                                <div class="chart-plot-area"><div id="expenditureSUCChart"></div></div>
                            </div>
                            <div class="chart-inner-card full-width">
                                <div class="chart-inner-title">Budget Utilization by Institutional Function</div>
                                <div class="chart-plot-area"><div id="budgetUtilizationFunctionChart"></div></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>{{-- /.main-content --}}
        </div>{{-- /.content --}}
    </div>{{-- /.app-wrapper --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const selectedYear = @json($year);
    const filterType = @json($filter_type);

    const sucYears = @json($suc_years_chart ?? []);
    const sucTotals = @json($suc_totals ?? []);

    const chartColors = [
        '#007B3E', '#FFD700', '#39EDFF', '#FFE450', '#FFB495',
        '#FFC177', '#FFA8F7', '#00FFFF', '#E5E5E5', '#E06B0D',
        '#567F13', '#1A5F30'
    ];

    const $ = (id) => document.getElementById(id);

    function updateFilters() {
        const year = $('year_filter')?.value;
        const type = $('type_filter')?.value ?? 'all';
        window.location.href = `/normative-funding?year=${encodeURIComponent(year)}&type=${encodeURIComponent(type)}`;
    }
    window.updateFilters = updateFilters;

    function n(v) {
        v = Number(v);
        return Number.isFinite(v) ? v : 0;
    }

    function normalizeItems(items) {
        return (items || [])
            .map(i => ({ name: i.name, value: n(i.value) }))
            .filter(i => i.value > 0);
    }

    function toggleChartCard(chartId, show) {
        const el = $(chartId);
        if (!el) return;
        const card = el.closest('.bg-gray-50') || el.parentElement;
        if (card) card.classList.toggle('hidden', !show);
    }

    function peso(v) {
        return '₱' + Number(v || 0).toLocaleString('en-US');
    }

    function compactPeso(v) {
        v = Number(v || 0);
        if (v >= 1_000_000_000) return '₱' + (v / 1_000_000_000).toFixed(1).replace(/\.0$/, '') + 'B';
        if (v >= 1_000_000) return '₱' + (v / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
        if (v >= 1_000) return '₱' + (v / 1_000).toFixed(1).replace(/\.0$/, '') + 'K';
        return '₱' + v.toLocaleString('en-US');
    }

    function safeResize(id) {
        const el = $(id);
        if (!el) return;
        requestAnimationFrame(() => { Plotly.Plots.resize(el); });
        setTimeout(() => { Plotly.Plots.resize(el); }, 250);
    }

    function baseLayout(title = '') {
        return {
            title: title ? {
                text: title,
                font: { family: 'Inter, sans-serif', size: 14, color: '#1f2937' },
                x: 0.5, xanchor: 'center', y: 0.98
            } : undefined,
            margin: { t: 30, r: 30, b: 30, l: 30 },
            paper_bgcolor: 'transparent',
            plot_bgcolor: 'transparent',
            font: { family: 'Inter, sans-serif', color: '#111827', size: 12 },
            legend: {
                orientation: 'h', x: 0.5, xanchor: 'center',
                y: 1.08, yanchor: 'bottom', font: { size: 11 }
            },
            autosize: true
        };
    }

    function renderPie(chartId, labels, values, colors, hole = 0, showPercent = true) {
        const el = $(chartId);
        if (!el) return;

        const cleanData = (labels || []).map((label, i) => ({
            label, value: n(values?.[i])
        })).filter(item => item.value > 0);

        const cleanLabels = cleanData.map(i => i.label);
        const cleanValues = cleanData.map(i => i.value);
        const total = cleanValues.reduce((a, b) => a + b, 0);

        if (!cleanLabels.length || !cleanValues.length || total <= 0) {
            Plotly.purge(chartId);
            toggleChartCard(chartId, false);
            return;
        }

        toggleChartCard(chartId, true);

        const trace = {
            type: 'pie',
            labels: cleanLabels,
            values: cleanValues,
            hole,
            sort: false,
            direction: 'clockwise',
            marker: { colors: colors.slice(0, cleanLabels.length), line: { color: '#ffffff', width: 2 } },
            textinfo: showPercent ? 'percent' : 'none',
            texttemplate: showPercent ? '%{percent:.1%}' : '',
            textposition: 'outside',
            automargin: true,
            hovertemplate: '<b>%{label}</b><br>%{value:,.2f}<br>%{percent}<extra></extra>',
            outsidetextfont: { family: 'Inter, sans-serif', size: 11, color: '#111827' },
            pull: 0,
            domain: { x: [0.10, 0.90], y: [0.02, 0.72] }
        };

        const layout = {
            ...baseLayout(),
            margin: { t: 5, r: 5, b: 5, l: 5 },
            showlegend: total > 0,
            legend: {
                orientation: 'h', x: 0.5, xanchor: 'center',
                y: 1.08, yanchor: 'top', font: { size: 12 }
            },
            uniformtext: { minsize: 10, mode: 'hide' },
            annotations: hole > 0 && total > 0 ? [
                {
                    x: 0.5, y: 0.40, xref: 'paper', yref: 'paper',
                    text: '<b>Total</b>', showarrow: false,
                    font: { size: 13, color: '#6b7280', family: 'Inter, sans-serif' }
                },
                {
                    x: 0.5, y: 0.31, xref: 'paper', yref: 'paper',
                    text: `<b>${peso(total)}</b>`, showarrow: false,
                    font: { size: 16, color: '#111827', family: 'Inter, sans-serif' }
                }
            ] : []
        };

        Plotly.newPlot(chartId, [trace], layout, { responsive: true, displayModeBar: false });
        safeResize(chartId);
    }

    function renderStackedBar(chartId, labels, seriesAName, seriesAData, seriesBName, seriesBData, colorA, colorB) {
        const el = $(chartId);
        if (!el) return;
        if (!labels.length) { toggleChartCard(chartId, false); return; }
        toggleChartCard(chartId, true);

        const totals = labels.map((_, i) => n(seriesAData[i]) + n(seriesBData[i]));

        const trace1 = {
            type: 'bar', name: seriesAName, x: labels, y: seriesAData,
            marker: { color: colorA },
            hovertemplate: '<b>%{x}</b><br>' + seriesAName + ': %{y:,.2f}<extra></extra>'
        };
        const trace2 = {
            type: 'bar', name: seriesBName, x: labels, y: seriesBData,
            marker: { color: colorB },
            hovertemplate: '<b>%{x}</b><br>' + seriesBName + ': %{y:,.2f}<extra></extra>'
        };
        const totalLabels = {
            type: 'scatter', mode: 'text', x: labels, y: totals,
            text: totals.map(v => `<b>${compactPeso(v)}</b>`),
            textposition: 'top center',
            textfont: { family: 'Inter, sans-serif', size: 11, color: '#111827' },
            hoverinfo: 'skip', showlegend: false
        };

        const layout = {
            ...baseLayout(),
            barmode: 'stack',
            margin: { t: 30, r: 40, b: 80, l: 80 },
            legend: {
                orientation: 'h', x: 0.5, xanchor: 'center',
                y: 1.05, yanchor: 'bottom', font: { size: 11 }
            },
            xaxis: { tickangle: -18, automargin: true },
            yaxis: { rangemode: 'tozero', automargin: true, tickformat: '.3s', tickprefix: '₱' }
        };

        Plotly.newPlot(chartId, [trace1, trace2, totalLabels], layout, { responsive: true, displayModeBar: false });
        safeResize(chartId);
    }

    function renderLine(chartId, labels, values, lineName) {
        const el = $(chartId);
        if (!el) return;
        if (!labels.length || !values.length) { toggleChartCard(chartId, false); return; }
        toggleChartCard(chartId, true);

        const trace = {
            type: 'scatter', mode: 'lines+markers', name: lineName,
            x: labels, y: values,
            line: { color: '#16a34a', width: 3, shape: 'spline' },
            marker: { size: 8, color: '#16a34a' },
            fill: 'tozeroy', fillcolor: 'rgba(22,163,74,0.18)',
            hovertemplate: '<b>%{x}</b><br>' + peso('%{y}') + '<extra></extra>'
        };

        const layout = {
            ...baseLayout(),
            margin: { t: 50, r: 30, b: 50, l: 70 },
            xaxis: { automargin: true },
            yaxis: { rangemode: 'tozero', tickprefix: '₱', tickformat: ',' }
        };

        Plotly.newPlot(chartId, [trace], layout, { responsive: true, displayModeBar: false });
        safeResize(chartId);
    }

    async function fetchJson(url) {
        const res = await fetch(url);
        if (!res.ok) throw new Error(`Request failed: ${res.status} ${url}`);
        return res.json();
    }

    // ---------- SUC INCOME ----------
    async function loadIncomeCharts() {
        const data = await fetchJson(`/data/income-data?year=${encodeURIComponent(selectedYear)}`);

        let allItems = [
            ...(data.breakdown?.tuition_details || []),
            ...(data.breakdown?.other_income_details || [])
        ];
        allItems = normalizeItems(allItems).sort((a, b) => b.value - a.value);

        if (allItems.length > 4) {
            const top4 = allItems.slice(0, 4);
            const othersTotal = allItems.slice(4).reduce((s, i) => s + i.value, 0);
            if (othersTotal > 0) top4.push({ name: 'Others', value: othersTotal });
            allItems = top4;
        }
        allItems = allItems.sort((a, b) => {
            if (a.name === 'Others') return 1;
            if (b.name === 'Others') return -1;
            return b.value - a.value;
        });

        renderPie('mainPieChart', allItems.map(i => i.name), allItems.map(i => i.value), chartColors, 0, true);

        const tuitionItems = normalizeItems(data.breakdown?.tuition_details);
        renderPie('tuitionPieChart', tuitionItems.map(i => i.name), tuitionItems.map(i => i.value), chartColors, 0, true);

        const otherItems = normalizeItems(data.breakdown?.other_income_details);
        renderPie('otherIncomePieChart', otherItems.map(i => i.name), otherItems.map(i => i.value), chartColors, 0, true);

        if ($('sucIncomeLineChart')) {
            renderLine('sucIncomeLineChart', sucYears, sucTotals, 'Total SUC Income');
        }
    }

    // ---------- ALLOTMENT ----------
    async function loadAllotmentCharts() {
        const data = await fetchJson(`/data/allotment-data?year=${encodeURIComponent(selectedYear)}`);

        renderPie('allotmentPieChart',
            ['GAA Allotment', 'SUC Income Allotment'],
            [n(data.gaa?.total), n(data.suc_income?.total)],
            ['#007B3E', '#FFD700'], 0, true);

        const totalPS   = n(data.gaa?.ps)   + n(data.suc_income?.ps);
        const totalMOOE = n(data.gaa?.mooe) + n(data.suc_income?.mooe);
        const totalCO   = n(data.gaa?.co)   + n(data.suc_income?.co);

        renderPie('allotmentCategoryChart',
            ['Personal Services (PS)', 'Maintenance and Other Operating Expenses (MOOE)', 'Capital Outlay (CO)'],
            [totalPS, totalMOOE, totalCO],
            ['#007B3E', '#FFD700', '#39EDFF'], 0.55, true);

        renderPie('allotmentGAAChart',
            ['Personal Services (PS)', 'Maintenance and Other Operating Expenses (MOOE)', 'Capital Outlay (CO)'],
            [n(data.gaa?.ps), n(data.gaa?.mooe), n(data.gaa?.co)],
            ['#007B3E', '#FFD700', '#39EDFF'], 0.55, true);

        renderPie('allotmentSUCChart',
            ['Personal Services (PS)', 'Maintenance and Other Operating Expenses (MOOE)', 'Capital Outlay (CO)'],
            [n(data.suc_income?.ps), n(data.suc_income?.mooe), n(data.suc_income?.co)],
            ['#007B3E', '#FFD700', '#39EDFF'], 0.55, true);

        return data;
    }

    // ---------- EXPENDITURE ----------
    async function loadExpenditureCharts() {
        const expData = await fetchJson(`/data/expenditure-data?year=${encodeURIComponent(selectedYear)}`);

        renderPie('expenditurePieChart',
            ['GAA Expenditure', 'SUC Income Expenditure'],
            [n(expData.gaa?.total), n(expData.suc_income?.total)],
            ['#007B3E', '#FFD700'], 0, true);

        const ePS   = n(expData.gaa?.ps)   + n(expData.suc_income?.ps);
        const eMOOE = n(expData.gaa?.mooe) + n(expData.suc_income?.mooe);
        const eCO   = n(expData.gaa?.co)   + n(expData.suc_income?.co);

        renderPie('expenditureCategoryChart',
            ['Personal Services (PS)', 'Maintenance and Other Operating Expenses (MOOE)', 'Capital Outlay (CO)'],
            [ePS, eMOOE, eCO],
            ['#007B3E', '#FFD700', '#39EDFF'], 0.55, true);

        renderPie('expenditureGAAChart',
            ['Personal Services (PS)', 'Maintenance and Other Operating Expenses (MOOE)', 'Capital Outlay (CO)'],
            [n(expData.gaa?.ps), n(expData.gaa?.mooe), n(expData.gaa?.co)],
            ['#007B3E', '#FFD700', '#39EDFF'], 0.55, true);

        renderPie('expenditureSUCChart',
            ['Personal Services (PS)', 'Maintenance and Other Operating Expenses (MOOE)', 'Capital Outlay (CO)'],
            [n(expData.suc_income?.ps), n(expData.suc_income?.mooe), n(expData.suc_income?.co)],
            ['#007B3E', '#FFD700', '#39EDFF'], 0.55, true);

        return expData;
    }

    window.addEventListener('resize', () => {
        [
            'mainPieChart', 'tuitionPieChart', 'otherIncomePieChart',
            'allotmentPieChart', 'allotmentCategoryChart', 'allotmentGAAChart', 'allotmentSUCChart',
            'expenditurePieChart', 'expenditureCategoryChart', 'expenditureGAAChart', 'expenditureSUCChart',
            'sucIncomeLineChart', 'budgetUtilizationFunctionChart',
        ].forEach(id => { if ($(id)) Plotly.Plots.resize($(id)); });
    });

    function renderBudgetComparisonBar(chartId, rows) {
        const el = $(chartId);
        if (!el) return;

        const cleanRows = (rows || []).filter(r =>
            (n(r.gaa_allotment) + n(r.suc_allotment)) > 0 ||
            (n(r.gaa_expenditure) + n(r.suc_expenditure)) > 0
        );

        if (!cleanRows.length) { Plotly.purge(chartId); toggleChartCard(chartId, false); return; }
        toggleChartCard(chartId, true);

        const allotRows = cleanRows.filter(r => n(r.gaa_allotment) + n(r.suc_allotment) > 0);
        const expRows   = cleanRows.filter(r => n(r.gaa_expenditure) + n(r.suc_expenditure) > 0);

        const xA = [allotRows.map(r => wrapLabel(r.fn)), allotRows.map(() => '\u200B')];
        const xE = [expRows.map(r => wrapLabel(r.fn)), expRows.map(() => '\u200C')];

        const gaaA = allotRows.map(r => n(r.gaa_allotment) > 0 ? n(r.gaa_allotment) : null);
        const sucA = allotRows.map(r => n(r.suc_allotment) > 0 ? n(r.suc_allotment) : null);
        const gaaE = expRows.map(r => n(r.gaa_expenditure) > 0 ? n(r.gaa_expenditure) : null);
        const sucE = expRows.map(r => n(r.suc_expenditure) > 0 ? n(r.suc_expenditure) : null);

        const allotTotals = allotRows.map(r => n(r.gaa_allotment) + n(r.suc_allotment));
        const expTotals   = expRows.map(r => n(r.gaa_expenditure) + n(r.suc_expenditure));
        const maxY = Math.max(...allotTotals, ...expTotals, 0);

        const traces = [];

        if (gaaA.some(v => v !== null)) traces.push({ type: 'bar', name: 'GAA Allotment', x: xA, y: gaaA, marker: { color: '#007B3E' }, hovertemplate: '<b>%{x[0]}</b><br>GAA Allotment: %{y:,.2f}<extra></extra>' });
        if (sucA.some(v => v !== null)) traces.push({ type: 'bar', name: 'SUC Income Allotment', x: xA, y: sucA, marker: { color: '#FFD700' }, hovertemplate: '<b>%{x[0]}</b><br>SUC Income Allotment: %{y:,.2f}<extra></extra>' });
        if (gaaE.some(v => v !== null)) traces.push({ type: 'bar', name: 'GAA Expenditure', x: xE, y: gaaE, marker: { color: '#39EDFF' }, hovertemplate: '<b>%{x[0]}</b><br>GAA Expenditure: %{y:,.2f}<extra></extra>' });
        if (sucE.some(v => v !== null)) traces.push({ type: 'bar', name: 'SUC Income Expenditure', x: xE, y: sucE, marker: { color: '#EA7C69' }, hovertemplate: '<b>%{x[0]}</b><br>SUC Income Expenditure: %{y:,.2f}<extra></extra>' });

        traces.push({ type: 'scatter', mode: 'text', x: xA, y: allotTotals.map(v => v > 0 ? v + maxY * 0.03 : null), text: allotTotals.map(v => v > 0 ? `<b>${compactPeso(v)}</b>` : ''), textposition: 'top center', textfont: { family: 'Inter, sans-serif', size: 11, color: '#111827' }, hoverinfo: 'skip', showlegend: false });
        traces.push({ type: 'scatter', mode: 'text', x: xE, y: expTotals.map(v => v > 0 ? v + maxY * 0.03 : null), text: expTotals.map(v => v > 0 ? `<b>${compactPeso(v)}</b>` : ''), textposition: 'top center', textfont: { family: 'Inter, sans-serif', size: 11, color: '#111827' }, hoverinfo: 'skip', showlegend: false });

        const layout = {
            ...baseLayout(),
            barmode: 'stack', bargap: 0.25, bargroupgap: 0.1,
            margin: { t: 60, r: 30, b: 120, l: 80 },
            legend: { orientation: 'h', x: 0.5, xanchor: 'center', y: 1.12, yanchor: 'bottom', font: { size: 11 } },
            xaxis: { type: 'multicategory', tickangle: 0, automargin: true, showgrid: false, zeroline: false, showline: false, ticks: '', tickfont: { size: 11 }, showdividers: false },
            yaxis: { rangemode: 'tozero', automargin: true, tickprefix: '₱', tickformat: '.2s', range: [0, maxY * 1.18] }
        };

        Plotly.newPlot(chartId, traces, layout, { responsive: true, displayModeBar: false });
        safeResize(chartId);
    }

    function wrapLabel(text, maxLength = 12) {
        if (!text) return '';
        const words = text.split(' ');
        let lines = [], current = '';
        words.forEach(word => {
            if ((current + ' ' + word).length > maxLength) { lines.push(current); current = word; }
            else { current = current ? current + ' ' + word : word; }
        });
        if (current) lines.push(current);
        return lines.join('<br>');
    }

    function buildCombinedBudgetRows(allotmentRows, expenditureRows) {
        const map = {};
        (allotmentRows || []).forEach(r => {
            const fn = String(r?.function || '').trim();
            if (!fn) return;
            if (!map[fn]) map[fn] = { fn, gaa_allotment: 0, suc_allotment: 0, gaa_expenditure: 0, suc_expenditure: 0 };
            map[fn].gaa_allotment += n(r.gaa_total);
            map[fn].suc_allotment += n(r.suc_total);
        });
        (expenditureRows || []).forEach(r => {
            const fn = String(r?.function || '').trim();
            if (!fn) return;
            if (!map[fn]) map[fn] = { fn, gaa_allotment: 0, suc_allotment: 0, gaa_expenditure: 0, suc_expenditure: 0 };
            map[fn].gaa_expenditure += n(r.gaa_total);
            map[fn].suc_expenditure += n(r.suc_total);
        });
        return Object.values(map).sort((a, b) =>
            (b.gaa_allotment + b.suc_allotment + b.gaa_expenditure + b.suc_expenditure) -
            (a.gaa_allotment + a.suc_allotment + a.gaa_expenditure + a.suc_expenditure)
        );
    }

    (async function init() {
        try {
            if (filterType === 'all' || filterType === 'suc_income') {
                await loadIncomeCharts();
            }
            if (filterType === 'all' || filterType === 'allotment_expenditure') {
                const allotmentData    = await loadAllotmentCharts();
                const expenditureData  = await loadExpenditureCharts();
                const allotmentRows    = Array.isArray(allotmentData?.breakdown)   ? allotmentData.breakdown   : [];
                const expenditureRows  = Array.isArray(expenditureData?.breakdown) ? expenditureData.breakdown : [];
                const combinedRows     = buildCombinedBudgetRows(allotmentRows, expenditureRows);
                renderBudgetComparisonBar('budgetUtilizationFunctionChart', combinedRows);
            }
        } catch (e) {
            console.error(e);
        }
    })();
</script>
</body>
</html>