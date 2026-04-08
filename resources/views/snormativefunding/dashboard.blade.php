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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
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

        /* ── Stat card numbers — Inter heavy, right-aligned ── */
        .stat-number-lg {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 1.15rem;
            line-height: 1.2;
            color: #1f2937;
            text-align: right;
        }
        .stat-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 10px;
            color: #6b7280;
            text-align: right;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }

        /* ── Budget utilization card sub-rows ── */
        .budget-card-sub-label {
            font-size: 10px;
            font-weight: 500;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        .budget-card-allotment {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            color: #1f2937;
            line-height: 1.2;
        }
        .budget-card-expenditure {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 0.85rem;
            color: #ef4444;
            line-height: 1.2;
            text-align: right;
        }

        /* ── Chart card wrapper ── */
        .chart-card {
            position: relative;
            overflow: hidden;
        }
        .chart-card-title {
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            padding: 14px 18px 0;
        }
        .chart-plot-area {
            position: relative;
            width: 100%;
            min-height: 420px;
        }
        .chart-plot-area > div {
            width: 100%;
            height: 100%;
            min-height: 420px;
        }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    @include('components.sidebar')

    <div class="content w-100">

        {{-- ── Sticky header + filter bar ── --}}
        <div class="sticky top-0 z-50">
            <header>
                <span class="text-lg md:text-2xl font-[650] text-white">Normative Funding</span>
            </header>

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">
                <div class="font-[650] text-sm md:text-lg">
                    @if($filter_type === 'allotment_expenditure')
                        Budget Utilization ({{ $year }})
                    @elseif($filter_type === 'suc_income')
                        SUC Income ({{ $year }})
                    @else
                        University Financial Overview ({{ $year }})
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                    <div class="hidden sm:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                        Filter
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Year:</span>
                        <select id="year_filter" onchange="updateFilters()"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            @foreach($suc_years as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                            @if(empty($suc_years))
                                <option>No Data Found</option>
                            @endif
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium">Type:</span>
                        <select id="type_filter" onchange="updateFilters()"
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            <option value="suc_income"            {{ $filter_type === 'suc_income'            ? 'selected' : '' }}>SUC Income</option>
                            <option value="allotment_expenditure" {{ $filter_type === 'allotment_expenditure' ? 'selected' : '' }}>Budget Utilization</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        {{-- ── End sticky header ── --}}

        <div class="px-6 pb-10">

            {{-- ============================================================ --}}
            {{-- SUC INCOME SECTION --}}
            {{-- ============================================================ --}}
            <div id="suc_income_section" {{ !in_array($filter_type, ['all', 'suc_income']) ? 'style=display:none' : '' }}>

                {{-- Stat Cards --}}
                <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-2 pt-4">

                    {{-- Total University Income --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                            <div class="grid grid-rows-3 h-full">
                                <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                    <i class="fa-solid fa-money-bill-wave text-white text-2xl"></i>
                                </div>
                                <div class="row-span-2 pb-3">
                                    <p class="stat-number-lg pr-4 pt-2">{{ $income['grand_total_income'] }}</p>
                                    <p class="stat-label pr-4">Total University Income</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Academic Fees --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                            <div class="grid grid-rows-3 h-full">
                                <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                    <i class="fa-solid fa-graduation-cap text-white text-2xl"></i>
                                </div>
                                <div class="row-span-2 pb-3">
                                    <p class="stat-number-lg pr-4 pt-2">{{ $income['tuition_misc_fee'] }}</p>
                                    <p class="stat-label pr-4">Total Academic Fees</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Auxiliary & Business Income --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                            <div class="grid grid-rows-3 h-full">
                                <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                    <i class="fa-solid fa-building text-white text-2xl"></i>
                                </div>
                                <div class="row-span-2 pb-3">
                                    <p class="stat-number-lg pr-4 pt-2">{{ $income['miscellaneous'] }}</p>
                                    <p class="stat-label pr-4">Auxiliary &amp; Business Income</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Other Business Income --}}
                    <div class="col-span-3">
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                            <div class="grid grid-rows-3 h-full">
                                <div class="bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center">
                                    <i class="fa-solid fa-circle-plus text-white text-2xl"></i>
                                </div>
                                <div class="row-span-2 pb-3">
                                    <p class="stat-number-lg pr-4 pt-2">{{ $income['other_income'] }}</p>
                                    <p class="stat-label pr-4">Other Business Income</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Income Charts — 3 columns, border-t like publications 3-pie layout --}}
                <div class="grid grid-cols-4 xl:grid-cols-12 gap-3 mb-3">

                    <div class="col-span-4 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">Total University Income Breakdown</div>
                        <div class="chart-plot-area"><div id="mainPieChart"></div></div>
                    </div>

                    <div class="col-span-4 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">Total Academic Fees Breakdown</div>
                        <div class="chart-plot-area"><div id="tuitionPieChart"></div></div>
                    </div>

                    <div class="col-span-4 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">Other Business Income Breakdown</div>
                        <div class="chart-plot-area"><div id="otherIncomePieChart"></div></div>
                    </div>

                </div>

            </div>
            {{-- ── End SUC Income Section ── --}}


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

                {{-- Budget Utilization Stat Cards --}}
                <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-2 pt-4">

                    @foreach($cards as $card)
                    <div class="col-span-3 xl:col-span-4">
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md rounded-lg shadow-inner shadow-xl p-3 overflow-hidden">
                            <div class="flex items-center justify-between mb-3">
                                <div class="bg-green-600/80 rounded-lg h-12 w-16 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid {{ $card['icon'] }} text-white text-2xl"></i>
                                </div>
                                <span class="text-xs font-bold text-green-700 bg-green-50 border border-green-200 px-3 py-1 rounded-full">
                                    {{ $card['label'] }}
                                    @if($card['utilization'] !== null)
                                        &nbsp;·&nbsp;{{ $card['utilization'] }}%
                                    @endif
                                </span>
                            </div>
                            <div class="flex items-end justify-between gap-2">
                                <div>
                                    <p class="budget-card-sub-label">Allotment</p>
                                    <p class="budget-card-allotment">{{ $card['allotment'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="budget-card-sub-label">Expenditure</p>
                                    <p class="budget-card-expenditure">{{ $card['expenditure'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                </div>

                {{-- Budget Charts — 2-col pairs, border-l for bars, border-t for pies --}}

                {{-- Row 1: Funding Source Pies (2 col) --}}
                <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-3">

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">Distribution by Funding Source (GAA)</div>
                        <div class="chart-plot-area"><div id="allotmentPieChart"></div></div>
                    </div>

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">Distribution of Total Expenditures (GAA)</div>
                        <div class="chart-plot-area"><div id="expenditurePieChart"></div></div>
                    </div>

                </div>

                {{-- Row 2: Total by Expense Class Pies (2 col) --}}
                <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-3">

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">Total GAA Allotment by Expense Class</div>
                        <div class="chart-plot-area"><div id="allotmentCategoryChart"></div></div>
                    </div>

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">Total GAA Expenditure by Expense Class</div>
                        <div class="chart-plot-area"><div id="expenditureCategoryChart"></div></div>
                    </div>

                </div>

                {{-- Row 3: GAA Breakdown (2 col) --}}
                <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-3">

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">GAA Allotment by Expense Class</div>
                        <div class="chart-plot-area"><div id="allotmentGAAChart"></div></div>
                    </div>

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">GAA Expenditure by Expense Class</div>
                        <div class="chart-plot-area"><div id="expenditureGAAChart"></div></div>
                    </div>

                </div>

                {{-- Row 4: SUC Income Breakdown (2 col) --}}
                <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-3">

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">SUC Income Allotment by Expense Class</div>
                        <div class="chart-plot-area"><div id="allotmentSUCChart"></div></div>
                    </div>

                    <div class="col-span-6 h-[380px] sm:h-[480px] border-t-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl chart-card">
                        <div class="chart-card-title">SUC Income Expenditure by Expense Class</div>
                        <div class="chart-plot-area"><div id="expenditureSUCChart"></div></div>
                    </div>

                </div>

                {{-- Row 5: Full-width budget utilization bar — border-l like programs trend charts --}}
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl mb-4 chart-card">
                    <div class="font-[750] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                        Budget Utilization by Institutional Function
                    </div>
                    <div class="chart-plot-area" style="min-height:480px;">
                        <div id="budgetUtilizationFunctionChart" style="min-height:480px;"></div>
                    </div>
                </div>

            </div>
            {{-- ── End Budget Utilization Section ── --}}

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // ── All JavaScript unchanged from original ────────────────────────────────
    const selectedYear = @json($year);
    const filterType   = @json($filter_type);
    const sucYears     = @json($suc_years_chart ?? []);
    const sucTotals    = @json($suc_totals ?? []);

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
        if (v >= 1_000_000)     return '₱' + (v / 1_000_000).toFixed(1).replace(/\.0$/, '') + 'M';
        if (v >= 1_000)         return '₱' + (v / 1_000).toFixed(1).replace(/\.0$/, '') + 'K';
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
            plot_bgcolor:  'transparent',
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
        const xE = [expRows.map(r => wrapLabel(r.fn)),   expRows.map(() => '\u200C')];

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

    window.addEventListener('resize', () => {
        [
            'mainPieChart', 'tuitionPieChart', 'otherIncomePieChart',
            'allotmentPieChart', 'allotmentCategoryChart', 'allotmentGAAChart', 'allotmentSUCChart',
            'expenditurePieChart', 'expenditureCategoryChart', 'expenditureGAAChart', 'expenditureSUCChart',
            'sucIncomeLineChart', 'budgetUtilizationFunctionChart',
        ].forEach(id => { if ($(id)) Plotly.Plots.resize($(id)); });
    });

    // ResizeObserver mirrors programs page pattern
    const contentDiv = document.querySelector('.content');
    if (contentDiv) {
        const ro = new ResizeObserver(() => {
            [
                'mainPieChart', 'tuitionPieChart', 'otherIncomePieChart',
                'allotmentPieChart', 'allotmentCategoryChart', 'allotmentGAAChart', 'allotmentSUCChart',
                'expenditurePieChart', 'expenditureCategoryChart', 'expenditureGAAChart', 'expenditureSUCChart',
                'budgetUtilizationFunctionChart',
            ].forEach(id => { const el = $(id); if (el && el.data) Plotly.Plots.resize(el); });
        });
        ro.observe(contentDiv);
    }

    (async function init() {
        try {
            if (filterType === 'all' || filterType === 'suc_income') {
                await loadIncomeCharts();
            }
            if (filterType === 'all' || filterType === 'allotment_expenditure') {
                const allotmentData   = await loadAllotmentCharts();
                const expenditureData = await loadExpenditureCharts();
                const allotmentRows   = Array.isArray(allotmentData?.breakdown)   ? allotmentData.breakdown   : [];
                const expenditureRows = Array.isArray(expenditureData?.breakdown) ? expenditureData.breakdown : [];
                const combinedRows    = buildCombinedBudgetRows(allotmentRows, expenditureRows);
                renderBudgetComparisonBar('budgetUtilizationFunctionChart', combinedRows);
            }
        } catch (e) {
            console.error(e);
        }
    })();
    </script>
</body>
</html>