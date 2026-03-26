<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;600;700;800&family=Anton&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        /* Main layout - flex column for proper sticky behavior */
        .app-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* Sidebar styles - assuming your sidebar component has its own styling */
        /* If the sidebar component doesn't have fixed positioning, add this: */
        .sidebar {
            flex-shrink: 0;
            height: 100vh;
            overflow-y: auto;
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
            position: sticky;
            top: 0;
            z-index: 100;
            background: #e8ebe8;
        }

        /* ── Page header ── */
        .page-header {
            background: #009539;
            color: white;
            padding: 0 30px;
            font-size: 36px;
            font-weight: 800;
            height: 75px;
            display: flex;
            align-items: center;
            gap: 14px;
            font-family: 'Bricolage Grotesque', sans-serif;
        }

        /* ── Filter bar ── */
        .filter-bar {
            background: #c9cec9;
            border-bottom: 1px solid #b0b5b0;
            height: 52px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 12px;
            overflow-x: auto;
            flex-wrap: nowrap;
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
            padding: 4px 28px 4px 12px;
            border-radius: 20px;
            border: 1px solid #8a8f8a;
            background-color: #f5f5f5;
            color: #2d2d2d;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%232d2d2d' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 8px;
            min-width: 130px;
            cursor: pointer;
        }
        .filter-group select:focus {
            outline: none;
            border-color: #009539;
        }
        .clear-btn {
            background: #009539;
            color: white;
            border: none;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .clear-btn:hover { background: #00802e; color: white; }

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

        /* ── Charts ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 8px;
        }
        @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .chart-card h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 12px 0;
            color: #111827;
        }
        .chart-height {
            height: 320px;
            position: relative;
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

        /* ── No data state ── */
        .no-data-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 96px 24px;
            text-align: center;
        }
        .no-data-icon-wrap {
            width: 96px; height: 96px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.06);
        }
        .no-data-icon-wrap i { font-size: 40px; color: #9ca3af; }
        .no-data-title {
            font-family: 'Bricolage Grotesque', sans-serif;
            font-size: 24px; font-weight: 800;
            color: #374151; margin-bottom: 8px;
        }
        .no-data-text { color: #9ca3af; font-size: 14px; max-width: 360px; margin-bottom: 24px; }
        .reset-btn {
            display: inline-flex; align-items: center; gap: 8px;
            background: #16a34a; color: white;
            font-size: 14px; font-weight: 600;
            padding: 10px 20px; border-radius: 9999px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            transition: background 0.2s;
        }
        .reset-btn:hover { background: #15803d; color: white; }

        /* ── No data inside chart ── */
        .no-data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #ccc;
            gap: 8px;
        }
        .no-data i { font-size: 36px; }
        .no-data span { font-size: 13px; font-weight: 600; }

        /* Ensure sidebar toggles still work */
        body.sidebar-collapsed .content {
            /* This will be handled by your sidebar component */
        }
    </style>
</head>
<body>

    <div class="app-wrapper">
        @include('components.sidebar')

        <div class="content">
            <div class="fixed-header-section">
                {{-- Page Header --}}
                <div class="page-header">
                    Total Faculty of the University
                </div>

                {{-- Filter Bar --}}
                <div class="filter-bar">
                    <form id="facultyFilterForm"
                          method="GET"
                          action="{{ route('suc-faculty.index') }}"
                          style="display:flex;align-items:center;gap:12px;flex-wrap:nowrap;">

                        <span class="filter-bar-label">Filters:</span>

                        @foreach($filter_columns as $col)
                            @php $param = $filter_param_keys[$col] ?? $col; @endphp
                            <div class="filter-group">
                                <label>{{ $col }}:</label>
                                <select name="{{ $param }}">
                                    <option value="All">All</option>
                                    @foreach(($filter_options[$col] ?? []) as $val)
                                        <option value="{{ $val }}" {{ request($param) == $val ? 'selected' : '' }}>
                                            {{ $val }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach

                        @if(count(array_filter(request()->only(array_values($filter_param_keys)))) > 0)
                            <a href="{{ route('suc-faculty.index') }}" class="clear-btn">Clear</a>
                        @endif

                    </form>
                </div>
            </div>

            {{-- Main Content (scrollable) --}}
            <div class="main-content">

                @if($total_faculty > 0)

                    {{-- Stat Cards — Programs-style with Inter font --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">

                        {{-- Total Faculty --}}
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden flex flex-col justify-between">
                            <div class="bg-green-600/80 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-users text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number">{{ number_format($total_faculty) }}</div>
                                <div class="stat-card-label">Total Faculty of the University</div>
                            </div>
                        </div>

                        {{-- Tertiary Faculty --}}
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden flex flex-col justify-between">
                            <div class="bg-green-600/80 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-user-graduate text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number">{{ number_format($tertiary_total) }}</div>
                                <div class="stat-card-label">Total Tertiary Faculty</div>
                            </div>
                        </div>

                        {{-- Elem / Second / TechVoc Faculty --}}
                        <div class="border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden flex flex-col justify-between">
                            <div class="bg-green-600/80 rounded-lg h-12 w-16 flex items-center justify-center">
                                <i class="fa-solid fa-school text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number">{{ number_format($elem_secon_techbo_total) }}</div>
                                <div class="stat-card-label">Total Elem / Second / TechVoc Faculty</div>
                            </div>
                        </div>

                    </div>

                    {{-- Charts --}}
                    <div class="charts-grid">
                        <div class="chart-card">
                            <h3>Faculty Tenure Distribution</h3>
                            <div class="chart-height"><canvas id="tenurePie"></canvas></div>
                        </div>
                        <div class="chart-card">
                            <h3>Faculty Rank Distribution</h3>
                            <div class="chart-height"><canvas id="rankPie"></canvas></div>
                        </div>
                        <div class="chart-card">
                            <h3>Faculty Sex Distribution</h3>
                            <div class="chart-height"><canvas id="genderPie"></canvas></div>
                        </div>
                        <div class="chart-card">
                            <h3>Sex Distribution by College</h3>
                            <div class="chart-height"><canvas id="genderCollegeStacked"></canvas></div>
                        </div>
                    </div>

                @else

                    {{-- No Data --}}
                    <div class="no-data-state">
                        <div class="no-data-icon-wrap">
                            <i class="fa-solid fa-filter-circle-xmark"></i>
                        </div>
                        <div class="no-data-title">No Data Found</div>
                        <p class="no-data-text">
                            No faculty records match the selected filter criteria. Try adjusting or resetting the filters above.
                        </p>
                        <a href="{{ route('suc-faculty.index') }}" class="reset-btn">
                            <i class="fa-solid fa-rotate-left" style="font-size:12px;"></i>
                            Reset Filters
                        </a>
                    </div>

                @endif

            </div>{{-- /.main-content --}}
        </div>{{-- /.content --}}
    </div>{{-- /.app-wrapper --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>

    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("facultyFilterForm");
        if (form) {
            form.querySelectorAll("select").forEach(sel => {
                sel.addEventListener("change", () => form.submit());
            });
        }
        loadFacultyPies();
    });

    const COLORS        = ["#009639","#FFD700","#65FF9C","#FFD05F","#39EDFF","#FFE450","#FFB495","#FFC177","#00FFFF","#494949","#E0DA0D"];
    const GENDER_COLORS = ['#4285F4', '#FF7BAC'];

    let tenureChart, rankChart, genderChart, genderCollegeChart;

    if (window.ChartDataLabels) Chart.register(ChartDataLabels);

    function sum(arr) {
        return (arr || []).reduce((a, b) => a + (Number(b) || 0), 0);
    }

    function hexToRgb(hex) {
        hex = String(hex || '').replace("#", "");
        if (hex.length === 3) hex = hex.split("").map(c => c + c).join("");
        const n = parseInt(hex, 16);
        return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
    }

    function getContrastColor(bgHex) {
        const { r, g, b } = hexToRgb(bgHex);
        return (r * 299 + g * 587 + b * 114) / 1000 > 150 ? "#111827" : "#FFFFFF";
    }

    const doughnutCenterText = {
        id: 'doughnutCenterText',
        afterDraw(chart) {
            if (chart.config.type !== 'doughnut') return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const total   = sum(chart.data.datasets?.[0]?.data || []);
            const centerX = (chartArea.left + chartArea.right)  / 2;
            const centerY = (chartArea.top  + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = '600 11px "Inter", sans-serif'; 
            ctx.fillStyle = '#6B7280';
            ctx.fillText('TOTAL', centerX, centerY - 12);
            ctx.font = '800 22px "Inter", sans-serif'; 
            ctx.fillStyle = '#111827';
            ctx.fillText(String(total), centerX, centerY + 12);
            ctx.restore();
        }
    };
    Chart.register(doughnutCenterText);

    const pieLabelOptions = {
        plugins: {
            legend: { position: "top" },
            datalabels: {
                anchor: "center", align: "center", clamp: true, clip: true,
                font: { weight: "900", size: 11 },
                formatter: (value, ctx) => {
                    const total = sum(ctx.dataset.data || []);
                    if (!total) return '';
                    const pct = (Number(value) / total) * 100;
                    return pct >= 3 ? `${pct.toFixed(1)}%` : '';
                },
                color: (ctx) => getContrastColor(ctx.dataset.backgroundColor?.[ctx.dataIndex]),
                display: (ctx) => {
                    const v = Number(ctx.dataset.data?.[ctx.dataIndex] || 0);
                    const total = sum(ctx.dataset.data || []);
                    return total > 0 && (v / total * 100) >= 3;
                }
            }
        },
        responsive: true,
        maintainAspectRatio: false
    };

    function showNoData(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const wrapper = canvas.parentNode;
        canvas.style.display = 'none';
        let nd = wrapper.querySelector('.no-data');
        if (!nd) {
            nd = document.createElement('div');
            nd.className = 'no-data';
            nd.innerHTML = '<i class="bi bi-inbox"></i><span>No data available</span>';
            wrapper.appendChild(nd);
        }
        nd.style.display = 'flex';
    }

    async function loadFacultyPies() {
        try {
            const qs  = window.location.search || "";
            const res = await fetch(`/faculty/data/faculty-pie${qs}`);
            if (!res.ok) { console.error("API error:", res.status); return; }
            const data = await res.json();

            if (!data.tenure?.values?.length) {
                showNoData('tenurePie');
            } else {
                if (tenureChart) tenureChart.destroy();
                tenureChart = new Chart(document.getElementById("tenurePie"), {
                    type: "pie",
                    data: { labels: data.tenure.labels, datasets: [{ data: data.tenure.values, backgroundColor: COLORS, borderColor: "#fff", borderWidth: 2 }] },
                    options: pieLabelOptions
                });
            }

            if (!data.rank?.values?.length) {
                showNoData('rankPie');
            } else {
                if (rankChart) rankChart.destroy();
                rankChart = new Chart(document.getElementById("rankPie"), {
                    type: "pie",
                    data: { labels: data.rank.labels, datasets: [{ data: data.rank.values, backgroundColor: COLORS, borderColor: "#fff", borderWidth: 2 }] },
                    options: pieLabelOptions
                });
            }

            if (!data.gender?.values?.length) {
                showNoData('genderPie');
            } else {
                if (genderChart) genderChart.destroy();
                genderChart = new Chart(document.getElementById("genderPie"), {
                    type: "doughnut",
                    data: { labels: data.gender.labels, datasets: [{ data: data.gender.values, backgroundColor: GENDER_COLORS, borderColor: "#fff", borderWidth: 2 }] },
                    options: { ...pieLabelOptions, cutout: "65%" }
                });
            }

            const cctx = document.getElementById("genderCollegeStacked");
            if (!cctx || !data.gender_by_college?.labels?.length) {
                showNoData('genderCollegeStacked');
            } else {
                if (genderCollegeChart) genderCollegeChart.destroy();
                const datasets = (data.gender_by_college.datasets || []).map((ds, i) => ({
                    ...ds,
                    backgroundColor: GENDER_COLORS[i % GENDER_COLORS.length],
                    borderSkipped: false,
                    datalabels: {
                        formatter: (value, ctx) => {
                            const xi    = ctx.dataIndex;
                            const total = ctx.chart.data.datasets.reduce((s, d) => s + (Number(d.data?.[xi]) || 0), 0);
                            if (!total) return '';
                            const pct = (Number(value) / total) * 100;
                            return pct >= 5 ? `${pct.toFixed(0)}%` : '';
                        },
                        color: "#FFFFFF", font: { weight: "900", size: 11 },
                        anchor: "center", align: "center", clamp: true, clip: true
                    }
                }));
                genderCollegeChart = new Chart(cctx, {
                    type: "bar",
                    data: { labels: data.gender_by_college.labels, datasets },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { position: "bottom" }, datalabels: {} },
                        scales: {
                            x: { stacked: true, grid: { display: false } },
                            y: { stacked: true, beginAtZero: true }
                        }
                    }
                });
            }

        } catch (err) {
            console.error("loadFacultyPies error:", err);
        }
    }
    </script>
</body>
</html>