<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Siel Metrics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;750;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #e8ebe8;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            height: 100vh;
        }

        .app-wrapper {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

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
            min-width: 0;
        }

        .fixed-header-section {
            flex-shrink: 0;
            position: sticky;
            top: 0;
            z-index: 100;
            background: #e8ebe8;
        }

        /* ===== HEADER (MATCH TEACHING LOAD) ===== */
        .page-header {
            height: 80px;
            padding: 2rem 3.5rem;
            background-color: #009b33;
            box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.18);
            display: flex;
            justify-content: flex-start;
            align-items: center;
            font-size: 2rem;
            font-weight: 800;
            color: white;
            line-height: 1;
            font-family: 'Inter', sans-serif;
        }

        /* ===== FILTER BAR (MATCH TEACHING LOAD) ===== */
        .filter-bar {
            background: #cfd3d6;
            min-height: 52px;
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 1rem;
            flex-wrap: nowrap;
            overflow-x: auto;
        }

        .filter-bar::-webkit-scrollbar {
            display: none;
        }

        .filter-bar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .filter-bar-label {
            font-size: 1rem;
            font-weight: 750;
            color: #2f2f2f;
            white-space: nowrap;
            flex-shrink: 0;
            margin-right: 6px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 500;
            color: #2f2f2f;
            white-space: nowrap;
            margin: 0;
        }

        .filter-group select {
            font-size: 12px;
            padding: 6px 34px 6px 12px;
            border-radius: 8px;
            border: 1px solid #d6d6d6;
            background-color: #f8fafc;
            color: #374151;
            min-width: 120px;
            cursor: pointer;
            outline: none;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' fill='%236b7280' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 10px;
        }

        .filter-group select:focus {
            border-color: #009539;
            box-shadow: 0 0 0 2px rgba(0, 149, 57, 0.15);
        }

        .clear-btn {
            background: #009539;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background 0.2s ease;
        }

        .clear-btn:hover {
            background: #007a2f;
            color: white;
        }

        /* ===== MAIN CONTENT ===== */
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

        /* ===== STAT CARDS ===== */
        .stat-card {
            border-left: 5px solid #16a34a;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.10);
            padding: 14px;
            overflow: hidden;
            min-height: 170px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-icon-box {
            background: rgba(0,149,57,0.82);
            border-radius: 14px;
            height: 56px;
            width: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-card-number {
            font-family: 'Inter', sans-serif;
            font-weight: 800;
            font-size: 3rem;
            line-height: 1;
            color: #1f2937;
            text-align: right;
            margin-bottom: 4px;
        }

        .stat-card-label {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 12px;
            color: #6b7280;
            text-align: right;
            letter-spacing: 0.2px;
        }

        /* ===== CHART CARDS ===== */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 6px;
        }

        @media (max-width: 980px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: #ffffff;
            border-left: 4px solid #16a34a;
            border-radius: 20px;
            box-shadow: 0 10px 22px rgba(0,0,0,0.10);
            overflow: hidden;
            min-height: 390px;
            display: flex;
            flex-direction: column;
        }

        .chart-card-header {
            padding: 18px 20px 10px 20px;
            font-size: 17px;
            font-weight: 750;
            color: #374151;
            font-family: 'Inter', sans-serif;
        }

        .chart-body {
            flex: 1;
            padding: 0 16px 16px 16px;
            position: relative;
        }

        .chart-height {
            height: 100%;
            min-height: 300px;
            position: relative;
        }

        /* ===== NO DATA PAGE ===== */
        .no-data-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 96px 24px;
            text-align: center;
        }

        .no-data-icon-wrap {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.06);
        }

        .no-data-icon-wrap i {
            font-size: 40px;
            color: #9ca3af;
        }

        .no-data-title {
            font-size: 24px;
            font-weight: 800;
            color: #374151;
            margin-bottom: 8px;
            font-family: 'Inter', sans-serif;
        }

        .no-data-text {
            color: #9ca3af;
            font-size: 14px;
            max-width: 360px;
            margin-bottom: 24px;
        }

        .reset-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #16a34a;
            color: white;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 9999px;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            transition: background 0.2s;
        }

        .reset-btn:hover {
            background: #15803d;
            color: white;
        }

        /* ===== NO DATA INSIDE CHART ===== */
        .no-data {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #ccc;
            gap: 8px;
        }

        .no-data i {
            font-size: 36px;
        }

        .no-data span {
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="app-wrapper">
        @include('components.sidebar')

        <div class="content">
            <div class="fixed-header-section">
                <!-- PAGE HEADER -->
                <div class="page-header">
                    <span>Total Faculty of the University</span>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-bar">
                    <form id="facultyFilterForm"
                          method="GET"
                          action="{{ route('suc-faculty.index') }}"
                          style="display:flex;align-items:center;gap:16px;flex-wrap:nowrap;width:100%;">

                        <span class="filter-bar-label">Filter</span>

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

            <!-- MAIN CONTENT -->
            <div class="main-content">

                @if($total_faculty > 0)

                    <!-- STAT CARDS -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

                        <div class="stat-card">
                            <div class="stat-icon-box">
                                <i class="fa-solid fa-users text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number">{{ number_format($total_faculty) }}</div>
                                <div class="stat-card-label">Total Faculty of the University</div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon-box">
                                <i class="fa-solid fa-user-graduate text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number">{{ number_format($tertiary_total) }}</div>
                                <div class="stat-card-label">Total Tertiary Faculty</div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon-box">
                                <i class="fa-solid fa-school text-white text-3xl"></i>
                            </div>
                            <div class="mt-auto">
                                <div class="stat-card-number">{{ number_format($elem_secon_techbo_total) }}</div>
                                <div class="stat-card-label">Total Elem / Second / TechVoc Faculty</div>
                            </div>
                        </div>

                    </div>

                    <!-- CHARTS -->
                    <div class="charts-grid">

                        <div class="chart-card">
                            <div class="chart-card-header">Faculty Tenure Distribution</div>
                            <div class="chart-body">
                                <div class="chart-height">
                                    <canvas id="tenurePie"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="chart-card">
                            <div class="chart-card-header">Faculty Rank Distribution</div>
                            <div class="chart-body">
                                <div class="chart-height">
                                    <canvas id="rankPie"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="chart-card">
                            <div class="chart-card-header">Faculty Sex Distribution</div>
                            <div class="chart-body">
                                <div class="chart-height">
                                    <canvas id="genderPie"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="chart-card">
                            <div class="chart-card-header">Sex Distribution by College</div>
                            <div class="chart-body">
                                <div class="chart-height">
                                    <canvas id="genderCollegeStacked"></canvas>
                                </div>
                            </div>
                        </div>

                    </div>

                @else

                    <!-- NO DATA -->
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

            </div>
        </div>
    </div>

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

        const COLORS = [
            "#009639", "#FFD700", "#65FF9C", "#FFD05F", "#39EDFF",
            "#FFE450", "#FFB495", "#FFC177", "#00FFFF", "#494949", "#E0DA0D"
        ];

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

                const total = sum(chart.data.datasets?.[0]?.data || []);
                const centerX = (chartArea.left + chartArea.right) / 2;
                const centerY = (chartArea.top + chartArea.bottom) / 2;

                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';

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
                legend: {
                    position: "top",
                    labels: {
                        font: {
                            family: 'Inter',
                            size: 11,
                            weight: '600'
                        }
                    }
                },
                datalabels: {
                    anchor: "center",
                    align: "center",
                    clamp: true,
                    clip: true,
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
                const qs = window.location.search || "";
                const res = await fetch(`/faculty/data/faculty-pie${qs}`);

                if (!res.ok) {
                    console.error("API error:", res.status);
                    return;
                }

                const data = await res.json();

                if (!data.tenure?.values?.length) {
                    showNoData('tenurePie');
                } else {
                    if (tenureChart) tenureChart.destroy();
                    tenureChart = new Chart(document.getElementById("tenurePie"), {
                        type: "pie",
                        data: {
                            labels: data.tenure.labels,
                            datasets: [{
                                data: data.tenure.values,
                                backgroundColor: COLORS,
                                borderColor: "#fff",
                                borderWidth: 2
                            }]
                        },
                        options: pieLabelOptions
                    });
                }

                if (!data.rank?.values?.length) {
                    showNoData('rankPie');
                } else {
                    if (rankChart) rankChart.destroy();
                    rankChart = new Chart(document.getElementById("rankPie"), {
                        type: "pie",
                        data: {
                            labels: data.rank.labels,
                            datasets: [{
                                data: data.rank.values,
                                backgroundColor: COLORS,
                                borderColor: "#fff",
                                borderWidth: 2
                            }]
                        },
                        options: pieLabelOptions
                    });
                }

                if (!data.gender?.values?.length) {
                    showNoData('genderPie');
                } else {
                    if (genderChart) genderChart.destroy();
                    genderChart = new Chart(document.getElementById("genderPie"), {
                        type: "doughnut",
                        data: {
                            labels: data.gender.labels,
                            datasets: [{
                                data: data.gender.values,
                                backgroundColor: GENDER_COLORS,
                                borderColor: "#fff",
                                borderWidth: 2
                            }]
                        },
                        options: {
                            ...pieLabelOptions,
                            cutout: "65%"
                        }
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
                                const xi = ctx.dataIndex;
                                const total = ctx.chart.data.datasets.reduce((s, d) => s + (Number(d.data?.[xi]) || 0), 0);
                                if (!total) return '';
                                const pct = (Number(value) / total) * 100;
                                return pct >= 5 ? `${pct.toFixed(0)}%` : '';
                            },
                            color: "#FFFFFF",
                            font: { weight: "900", size: 11 },
                            anchor: "center",
                            align: "center",
                            clamp: true,
                            clip: true
                        }
                    }));

                    genderCollegeChart = new Chart(cctx, {
                        type: "bar",
                        data: {
                            labels: data.gender_by_college.labels,
                            datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: "bottom",
                                    labels: {
                                        font: {
                                            family: 'Inter',
                                            size: 11,
                                            weight: '600'
                                        }
                                    }
                                },
                                datalabels: {}
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    grid: { display: false },
                                    ticks: {
                                        font: {
                                            family: 'Inter',
                                            size: 11
                                        }
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    ticks: {
                                        font: {
                                            family: 'Inter',
                                            size: 11
                                        }
                                    }
                                }
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