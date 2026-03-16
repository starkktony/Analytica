<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Normative Funding Allocation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <style>
        body {
            background: #e8ebe8;
            margin: 0;
            font-family: 'Bricolage Grotesque', sans-serif;
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

        /* ── Filter bar ─────────────────────────────────────────────────── */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #c9cec9;
            padding: 0 30px;
            border-bottom: 1px solid #b0b5b0;
            height: 50px;
        }
        .page-title {
            font-size: 15px;
            font-weight: 700;
            color: #2d2d2d;
        }
        .filter-bar-label {
            font-size: 13px;
            font-weight: 700;
            color: #2d2d2d;
            margin-left: auto;
            white-space: nowrap;
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
            transition: background 0.2s;
            white-space: nowrap;
        }
        .clear-filters-btn:hover { background: #00802e; }

        /* Active filter badge shown in page title area */
        .active-filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(0,149,57,0.12);
            border: 1px solid #009539;
            color: #006b2b;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 600;
        }

        /* ── Stat cards ─────────────────────────────────────────────────── */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 28px 30px 18px;
        }
        .stat-card {
            border-radius: 15px;
            padding: 20px 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100px;
        }
        .stat-card.green { background: #009539; }
        .stat-card:not(.green) { background: white; }
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 16px;
            left: 16px;
        }
        .stat-card.green .icon-box { background: white; }
        .stat-card.green .icon-box i { font-size: 22px; color: #009539; }
        .stat-card:not(.green) .icon-box { background: #009539; }
        .stat-card:not(.green) .icon-box i { font-size: 22px; color: white; }
        .stat-content {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: center;
            flex: 1;
        }
        .stat-number { font-size: 46px; font-weight: 700; line-height: 1; }
        .stat-card.green .stat-number { color: white; }
        .stat-card:not(.green) .stat-number { color: #1f1f1f; }
        .stat-label { font-size: 12px; font-weight: 600; margin-top: 4px; }
        .stat-card.green .stat-label { color: rgba(255,255,255,0.85); }
        .stat-card:not(.green) .stat-label { color: #777; }
        .stat-context {
            font-size: 10px;
            margin-top: 2px;
            color: rgba(255,255,255,0.65);
        }
        .stat-card:not(.green) .stat-context { color: #aaa; }

        /* ── Chart layout ───────────────────────────────────────────────── */
        .charts-section {
            padding: 0 30px 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .chart-row { display: grid; gap: 20px; }
        .two-col   { grid-template-columns: 1fr 1fr; }
        .three-col { grid-template-columns: repeat(3, 1fr); }

        .chart-card {
            background: white;
            border-radius: 18px;
            padding: 22px 26px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .chart-title {
            font-size: 14px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 2px;
        }
        .chart-subtitle {
            font-size: 11px;
            color: #999;
            margin-bottom: 6px;
            min-height: 16px;
        }
        .section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #009539;
            padding: 16px 30px 0;
        }

        /* Chart container */
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }
    </style>
</head>
<body>

@include('components.sidebar')

<div class="content">

    <div class="header">University Financial Overview</div>

    {{-- ── Filter bar: Year → Type ─────────────────────────────────────── --}}
    <div class="filter-bar">
        <div class="page-title">
            @if($filter_type === 'allotment')
                ALLOTMENT STATEMENT
            @elseif($filter_type === 'expenditure')
                EXPENDITURE STATEMENT
            @elseif($filter_type === 'suc_income')
                SUC INCOME
            @else
                UNIVERSITY FINANCIAL OVERVIEW
            @endif
            {{-- Show active filter badges next to title --}}
            @if($year !== 'None')
                <span class="active-filter-badge ms-2">
                    <i class="bi bi-calendar3" style="font-size:9px;"></i>
                    {{ $year }}
                </span>
            @endif
            @if($filter_type !== 'all')
                <span class="active-filter-badge ms-1">
                    <i class="bi bi-tag" style="font-size:9px;"></i>
                    {{ ucfirst(str_replace('_', ' ', $filter_type)) }}
                </span>
            @endif
        </div>

        <div class="filter-bar-label">Filters:</div>

        {{-- 1. Year Filter --}}
        <div class="filter-group">
            <label>Year:</label>
            <select id="year_filter">
                @foreach($suc_years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
                @if(empty($suc_years))
                    <option>No Data</option>
                @endif
            </select>
        </div>

        {{-- 2. Type Filter --}}
        <div class="filter-group">
            <label>Type:</label>
            <select id="type_filter">
                <option value="all" {{ $filter_type === 'all' ? 'selected' : '' }}>All</option>
                <option value="suc_income" {{ $filter_type === 'suc_income' ? 'selected' : '' }}>SUC Income</option>
                <option value="allotment" {{ $filter_type === 'allotment' ? 'selected' : '' }}>Allotment</option>
                <option value="expenditure" {{ $filter_type === 'expenditure' ? 'selected' : '' }}>Expenditure</option>
            </select>
        </div>

        <button class="clear-filters-btn" onclick="clearFilters()">
            <i class="bi bi-x-circle me-1"></i>Clear Filters
        </button>
    </div>

    <!-- ============================================================ -->
    <!-- SUC INCOME SECTION -->
    <!-- ============================================================ -->
    <div id="suc_income_section" class="{{ !in_array($filter_type, ['all', 'suc_income']) ? 'hidden' : '' }}">
        
        {{-- ── Stat cards for SUC Income ───────────────────────────────── --}}
        <div class="stats-container">
            <div class="stat-card green">
                <div class="icon-box"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $income['grand_total_income'] }}</div>
                    <div class="stat-label">Total University Income</div>
                    <div class="stat-context">{{ $year }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box"><i class="bi bi-mortarboard"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $income['tuition_misc_fee'] }}</div>
                    <div class="stat-label">Total Academic Fees</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box"><i class="bi bi-building"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $income['miscellaneous'] }}</div>
                    <div class="stat-label">Auxiliary & Business Income</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box"><i class="bi bi-plus-circle"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $income['other_income'] }}</div>
                    <div class="stat-label">Other Business Income</div>
                </div>
            </div>
        </div>

        {{-- ── Charts for SUC Income ──────────────────────────────────── --}}
        <div class="section-label">Income Breakdown</div>
        <div class="charts-section" style="padding-top:14px;">
            <div class="chart-row three-col">
                <div class="chart-card">
                    <div class="chart-title">Total University Income</div>
                    <div class="chart-subtitle">Breakdown by category</div>
                    <div class="chart-container"><canvas id="mainPieChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Academic Fees</div>
                    <div class="chart-subtitle">Tuition & misc fees breakdown</div>
                    <div class="chart-container"><canvas id="tuitionPieChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Other Business Income</div>
                    <div class="chart-subtitle">Breakdown by source</div>
                    <div class="chart-container"><canvas id="otherIncomePieChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- ALLOTMENT SECTION -->
    <!-- ============================================================ -->
    <div id="allotment_section" class="{{ !in_array($filter_type, ['all', 'allotment']) ? 'hidden' : '' }}">
        
        {{-- ── Stat cards for Allotment ───────────────────────────────── --}}
        <div class="stats-container">
            <div class="stat-card green">
                <div class="icon-box"><i class="bi bi-wallet2"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $allotment['combined_total'] }}</div>
                    <div class="stat-label">Total University Allotment</div>
                    <div class="stat-context">{{ $year }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box"><i class="bi bi-bank"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $allotment['gaa_total'] }}</div>
                    <div class="stat-label">GAA Allotment</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $allotment['suc_total'] }}</div>
                    <div class="stat-label">SUC Income Allotment</div>
                </div>
            </div>
        </div>

        {{-- ── Charts for Allotment ───────────────────────────────────── --}}
        <div class="section-label">Allotment Analysis</div>
        <div class="charts-section" style="padding-top:14px;">
            <div class="chart-row two-col">
                <div class="chart-card">
                    <div class="chart-title">Distribution by Funding Source</div>
                    <div class="chart-subtitle">GAA vs SUC Income</div>
                    <div class="chart-container"><canvas id="allotmentPieChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Total Allotment by Expense Class</div>
                    <div class="chart-subtitle">PS, MOOE, CO breakdown</div>
                    <div class="chart-container"><canvas id="allotmentCategoryChart"></canvas></div>
                </div>
            </div>
            <div class="chart-row two-col">
                <div class="chart-card">
                    <div class="chart-title">GAA Allotment by Expense Class</div>
                    <div class="chart-subtitle">Personal Services, MOOE, Capital Outlay</div>
                    <div class="chart-container"><canvas id="allotmentGAAChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">SUC Income Allotment by Expense Class</div>
                    <div class="chart-subtitle">Personal Services, MOOE, Capital Outlay</div>
                    <div class="chart-container"><canvas id="allotmentSUCChart"></canvas></div>
                </div>
            </div>
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-title">Total Allotment by Institutional Function</div>
                    <div class="chart-subtitle">GAA vs SUC Income stacked comparison</div>
                    <div class="chart-container" style="height:400px;"><canvas id="allotmentFunctionChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- EXPENDITURE SECTION -->
    <!-- ============================================================ -->
    <div id="expenditure_section" class="{{ !in_array($filter_type, ['all', 'expenditure']) ? 'hidden' : '' }}">
        
        {{-- ── Stat cards for Expenditure ─────────────────────────────── --}}
        <div class="stats-container">
            <div class="stat-card green">
                <div class="icon-box"><i class="bi bi-arrow-trend-down"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $expenditure['combined_total'] }}</div>
                    <div class="stat-label">Total University Expenditure</div>
                    <div class="stat-context">{{ $year }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box"><i class="bi bi-receipt"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $expenditure['gaa_total'] }}</div>
                    <div class="stat-label">GAA Expenditure</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box"><i class="bi bi-credit-card"></i></div>
                <div class="stat-content">
                    <div class="stat-number">{{ $expenditure['suc_total'] }}</div>
                    <div class="stat-label">SUC Income Expenditure</div>
                </div>
            </div>
        </div>

        {{-- ── Charts for Expenditure ─────────────────────────────────── --}}
        <div class="section-label">Expenditure Analysis</div>
        <div class="charts-section" style="padding-top:14px;">
            <div class="chart-row two-col">
                <div class="chart-card">
                    <div class="chart-title">Distribution of Total Expenditures</div>
                    <div class="chart-subtitle">GAA vs SUC Income</div>
                    <div class="chart-container"><canvas id="expenditurePieChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">Total Expenditure by Expense Class</div>
                    <div class="chart-subtitle">PS, MOOE, CO breakdown</div>
                    <div class="chart-container"><canvas id="expenditureCategoryChart"></canvas></div>
                </div>
            </div>
            <div class="chart-row two-col">
                <div class="chart-card">
                    <div class="chart-title">GAA Expenditure by Expense Class</div>
                    <div class="chart-subtitle">Personal Services, MOOE, Capital Outlay</div>
                    <div class="chart-container"><canvas id="expenditureGAAChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title">SUC Income Expenditure by Expense Class</div>
                    <div class="chart-subtitle">Personal Services, MOOE, Capital Outlay</div>
                    <div class="chart-container"><canvas id="expenditureSUCChart"></canvas></div>
                </div>
            </div>
            <div class="chart-row">
                <div class="chart-card">
                    <div class="chart-title">Total Expenditure by Institutional Function</div>
                    <div class="chart-subtitle">GAA vs SUC Income stacked comparison</div>
                    <div class="chart-container" style="height:400px;"><canvas id="expenditureFunctionChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;
    Chart.register(ChartDataLabels);
    Chart.defaults.font.family = 'Bricolage Grotesque';

    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw(chart) {
            if (chart.config.type !== 'doughnut') return;

            const { ctx, chartArea } = chart;
            const dataset = chart.data.datasets[0];
            const total = dataset.data.reduce((a, b) => a + b, 0);

            ctx.save();

            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;

            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            ctx.font = 'bold 12px Bricolage Grotesque';
            ctx.fillStyle = '#6b7280';
            ctx.fillText('Total', centerX, centerY - 12);

            ctx.font = 'bold 16px Bricolage Grotesque';
            ctx.fillStyle = '#111827';
            ctx.fillText('₱' + total.toLocaleString(), centerX, centerY + 12);

            ctx.restore();
        }
    };

    Chart.register(centerTextPlugin);

    const selectedYear = @json($year);
    const filterType = @json($filter_type);

    const chartColors = [
        '#007B3E', '#FFD700', '#39EDFF', '#FFE450', '#FFB495',
        '#FFC177', '#FFA8F7', '#00FFFF', '#E5E5E5', '#E06B0D', '#567F13', '#1A5F30',
    ];

    // ---------- helpers ----------
    const $ = (id) => document.getElementById(id);

    function updateFilters() {
        const year = $('year_filter')?.value;
        const type = $('type_filter')?.value ?? 'all';
        window.location.href = `/normative-funding?year=${encodeURIComponent(year)}&type=${encodeURIComponent(type)}`;
    }
    window.updateFilters = updateFilters;

    function clearFilters() {
        window.location.href = '{{ route("normative-funding.index") }}';
    }

    document.querySelectorAll('#year_filter, #type_filter').forEach(sel => 
        sel.addEventListener('change', updateFilters)
    );

    function n(v) {
        v = Number(v);
        return Number.isFinite(v) ? v : 0;
    }

    function normalizeItems(items) {
        return (items || [])
            .map(i => ({ name: i.name, value: n(i.value) }))
            .filter(i => i.value > 0);
    }

    function toggleChartCard(canvasId, show) {
        const canvas = $(canvasId);
        if (!canvas) return;
        const card = canvas.closest('.chart-card');
        if (card) card.style.display = show ? 'block' : 'none';
    }

    function pieOptions() {
        return {
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 12,
                        font: { size: 11, family: 'Bricolage Grotesque' }
                    }
                },
                datalabels: {
                    anchor: 'center',
                    align: 'center',
                    font: {
                        weight: 'bold',
                        size: 11,
                        family: 'Bricolage Grotesque'
                    },
                    color: function(context) {
                        const bgColor = context.dataset.backgroundColor[context.dataIndex];
                        let r, g, b;

                        if (bgColor.startsWith('#')) {
                            r = parseInt(bgColor.substr(1, 2), 16);
                            g = parseInt(bgColor.substr(3, 2), 16);
                            b = parseInt(bgColor.substr(5, 2), 16);
                        } else if (bgColor.startsWith('rgb')) {
                            const rgb = bgColor.match(/\d+/g);
                            r = parseInt(rgb[0]);
                            g = parseInt(rgb[1]);
                            b = parseInt(rgb[2]);
                        }

                        const brightness = (r * 299 + g * 587 + b * 114) / 1000;
                        return brightness < 128 ? '#ffffff' : '#000000';
                    },
                    formatter: (value, context) => {
                        const data = context.chart.data.datasets[0].data;
                        const total = data.reduce((a, b) => a + b, 0);
                        const percentage = total ? (value / total) * 100 : 0;
                        return percentage >= 3 ? percentage.toFixed(1) + '%' : '';
                    }
                }
            }
        };
    }

    function makeChart(canvasId, type, labels, values, colors) {
        const el = $(canvasId);
        if (!el) return null;

        if (!labels.length || !values.length) {
            toggleChartCard(canvasId, false);
            return null;
        }

        toggleChartCard(canvasId, true);

        return new Chart(el, {
            type,
            data: {
                labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 8,
                }]
            },
            options: {
                ...pieOptions(),
                maintainAspectRatio: false,
                responsive: true,
            }
        });
    }

    async function fetchJson(url) {
        const res = await fetch(url);
        if (!res.ok) throw new Error(`Request failed: ${res.status} ${url}`);
        return res.json();
    }

    // ---------- SUC INCOME ----------
    async function loadIncomeCharts() {
        const data = await fetchJson(`/api/income-data?year=${encodeURIComponent(selectedYear)}`);

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

        makeChart(
            'mainPieChart',
            'pie',
            allItems.map(i => i.name),
            allItems.map(i => i.value),
            chartColors
        );

        const tuitionItems = normalizeItems(data.breakdown?.tuition_details);
        makeChart(
            'tuitionPieChart',
            'pie',
            tuitionItems.map(i => i.name),
            tuitionItems.map(i => i.value),
            chartColors
        );

        const otherItems = normalizeItems(data.breakdown?.other_income_details);
        makeChart(
            'otherIncomePieChart',
            'pie',
            otherItems.map(i => i.name),
            otherItems.map(i => i.value),
            chartColors
        );
    }

    // ---------- ALLOTMENT ----------
    async function loadAllotmentCharts() {
        const data = await fetchJson(`/api/allotment-data?year=${encodeURIComponent(selectedYear)}`);

        makeChart(
            'allotmentPieChart',
            'pie',
            ['GAA Allotment', 'SUC Income Allotment'],
            [n(data.gaa?.total), n(data.suc_income?.total)],
            ['#007B3E', '#FFD700']
        );

        const totalPS = n(data.gaa?.ps) + n(data.suc_income?.ps);
        const totalMOOE = n(data.gaa?.mooe) + n(data.suc_income?.mooe);
        const totalCO = n(data.gaa?.co) + n(data.suc_income?.co);

        makeChart(
            'allotmentCategoryChart',
            'doughnut',
            ['Personal Services (PS)', 'MOOE', 'Capital Outlay (CO)'],
            [totalPS, totalMOOE, totalCO],
            ['#007B3E', '#FFD700', '#39EDFF']
        );

        makeChart(
            'allotmentGAAChart',
            'doughnut',
            ['Personal Services (PS)', 'MOOE', 'Capital Outlay (CO)'],
            [n(data.gaa?.ps), n(data.gaa?.mooe), n(data.gaa?.co)],
            ['#007B3E', '#FFD700', '#39EDFF']
        );

        makeChart(
            'allotmentSUCChart',
            'doughnut',
            ['Personal Services (PS)', 'MOOE', 'Capital Outlay (CO)'],
            [n(data.suc_income?.ps), n(data.suc_income?.mooe), n(data.suc_income?.co)],
            ['#007B3E', '#FFD700', '#39EDFF']
        );

        const rows = (data.breakdown || [])
            .map(r => ({
                fn: String(r.function || '').trim(),
                gaa: n(r.gaa_total),
                suc: n(r.suc_total)
            }))
            .filter(r => (r.gaa + r.suc) > 0);

        if ($('allotmentFunctionChart') && rows.length) {
            new Chart($('allotmentFunctionChart'), {
                type: 'bar',
                data: {
                    labels: rows.map(r => r.fn),
                    datasets: [
                        { label: 'GAA Allotment', data: rows.map(r => r.gaa), backgroundColor: '#007B3E' },
                        { label: 'SUC Income Allotment', data: rows.map(r => r.suc), backgroundColor: '#FFD700' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            color: '#111',
                            font: { weight: 'bold', size: 10, family: 'Bricolage Grotesque' },
                            formatter: function(value, context) {
                                const chart = context.chart;
                                const dataIndex = context.dataIndex;
                                let total = 0;
                                chart.data.datasets.forEach(dataset => {
                                    total += dataset.data[dataIndex] || 0;
                                });
                                if (context.datasetIndex === chart.data.datasets.length - 1) {
                                    return '₱' + total.toLocaleString('en-US');
                                }
                                return '';
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: { 
                            stacked: true, 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1_000_000_000) return '₱' + (value / 1_000_000_000) + 'B';
                                    if (value >= 1_000_000) return '₱' + (value / 1_000_000) + 'M';
                                    if (value >= 1_000) return '₱' + (value / 1_000) + 'K';
                                    return '₱' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // ---------- EXPENDITURE ----------
    async function loadExpenditureCharts() {
        const expData = await fetchJson(`/api/expenditure-data?year=${encodeURIComponent(selectedYear)}`);

        makeChart(
            'expenditurePieChart',
            'pie',
            ['GAA Expenditure', 'SUC Income Expenditure'],
            [n(expData.gaa?.total), n(expData.suc_income?.total)],
            ['#007B3E', '#FFD700']
        );

        const ePS = n(expData.gaa?.ps) + n(expData.suc_income?.ps);
        const eMOOE = n(expData.gaa?.mooe) + n(expData.suc_income?.mooe);
        const eCO = n(expData.gaa?.co) + n(expData.suc_income?.co);

        makeChart(
            'expenditureCategoryChart',
            'doughnut',
            ['Personal Services (PS)', 'MOOE', 'Capital Outlay (CO)'],
            [ePS, eMOOE, eCO],
            ['#007B3E', '#FFD700', '#39EDFF']
        );

        makeChart(
            'expenditureGAAChart',
            'doughnut',
            ['Personal Services (PS)', 'MOOE', 'Capital Outlay (CO)'],
            [n(expData.gaa?.ps), n(expData.gaa?.mooe), n(expData.gaa?.co)],
            ['#007B3E', '#FFD700', '#39EDFF']
        );

        makeChart(
            'expenditureSUCChart',
            'doughnut',
            ['Personal Services (PS)', 'MOOE', 'Capital Outlay (CO)'],
            [n(expData.suc_income?.ps), n(expData.suc_income?.mooe), n(expData.suc_income?.co)],
            ['#007B3E', '#FFD700', '#39EDFF']
        );

        const rows = (expData.breakdown || [])
            .map(r => ({
                fn: String(r.function || '').trim(),
                gaa: n(r.gaa_total),
                suc: n(r.suc_total)
            }))
            .filter(r => (r.gaa + r.suc) > 0);

        if ($('expenditureFunctionChart') && rows.length) {
            new Chart($('expenditureFunctionChart'), {
                type: 'bar',
                data: {
                    labels: rows.map(r => r.fn),
                    datasets: [
                        { label: 'GAA Expenditure', data: rows.map(r => r.gaa), backgroundColor: '#007B3E' },
                        { label: 'SUC Income Expenditure', data: rows.map(r => r.suc), backgroundColor: '#FFD700' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        datalabels: {
                            anchor: 'end',
                            align: 'end',
                            color: '#111',
                            font: { weight: 'bold', size: 10, family: 'Bricolage Grotesque' },
                            formatter: function(value, context) {
                                const chart = context.chart;
                                const dataIndex = context.dataIndex;
                                let total = 0;
                                chart.data.datasets.forEach(dataset => {
                                    total += dataset.data[dataIndex] || 0;
                                });
                                if (context.datasetIndex === chart.data.datasets.length - 1) {
                                    return '₱' + total.toLocaleString('en-US');
                                }
                                return '';
                            }
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: { 
                            stacked: true, 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    if (value >= 1_000_000_000) return '₱' + (value / 1_000_000_000) + 'B';
                                    if (value >= 1_000_000) return '₱' + (value / 1_000_000) + 'M';
                                    if (value >= 1_000) return '₱' + (value / 1_000) + 'K';
                                    return '₱' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // ---------- boot ----------
    (async function init() {
        try {
            if (filterType === 'all' || filterType === 'suc_income') {
                await loadIncomeCharts();
            }

            if (filterType === 'all' || filterType === 'allotment') {
                await loadAllotmentCharts();
            }

            if (filterType === 'all' || filterType === 'expenditure') {
                await loadExpenditureCharts();
            }
        } catch (e) {
            console.error(e);
        }
    })();
</script>
</body>
</html>