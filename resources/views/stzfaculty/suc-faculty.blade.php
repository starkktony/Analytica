<script src="https://cdn.tailwindcss.com"></script>



<div class="">

    <!-- FILTER BAR -->
    <div class="sticky top-0 z-50 flex justify-between items-center h-12 bg-[#BDBDBD] px-6">
        <form id="facultyFilterForm" method="GET" action="{{ route('suc-faculty.index') }}" class="flex flex-row gap-3 items-center">

            @foreach($filter_columns as $col)
                @php $param = $filter_param_keys[$col] ?? $col; @endphp

                <div class="flex items-center gap-3">
                    <label class="font-['Bricolage_Grotesque'] font-extrabold text-xs">{{ $col }}</label>
                    <div class="relative w-40">
                        <select name="{{ $param }}"
                            class="w-40 appearance-none rounded-full bg-gray-100 text-center shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-300 cursor-pointer text-xs p-2">
                            <option class="text-xs" value="All">All</option>

                            @foreach(($filter_options[$col] ?? []) as $val)
                                <option class="text-xs" value="{{ $val }}"
                                    {{ request($param) == $val ? 'selected' : '' }}>
                                    {{ $val }}
                                </option>
                            @endforeach
                        </select>

                        <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- <button type="submit" class="px-4 py-1 bg-white text-black rounded-full text-sm">Apply</button>

            @if(count(request()->query()) > 0)
                <a href="{{ route('suc-faculty.index') }}" class="px-4 py-1 bg-white text-black rounded-full text-sm">Clear</a>
            @endif --}}
        </form>
    </div>

    <div class="mt-6 overflow-x-auto px-6">

        <!-- HERO CARD -->
        <div class="relative w-full max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6
                bg-gradient-to-br from-[#007a2f] via-[#009539] to-[#00b347]
                rounded-2xl p-8 overflow-hidden
                shadow-[0_20px_60px_rgba(0,100,30,0.5),0_4px_12px_rgba(0,0,0,0.3),0_0_0_1px_rgba(255,255,255,0.08)]
                animate-card-in">

            <div class="absolute -top-24 -right-16 w-72 h-72 rounded-full border-[36px] border-white/[0.055] pointer-events-none"></div>
            <div class="absolute -bottom-14 left-16 w-44 h-44 rounded-full border-[28px] border-white/[0.04] pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_50%_-10%,rgba(255,255,255,0.12),transparent)] pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_40%_40%_at_100%_100%,rgba(0,0,0,0.15),transparent)] pointer-events-none"></div>

            <div class="relative z-10 shrink-0 animate-fade-up-1 group">
                <img src="{{ asset('images/school 1.png') }}" alt="CLSU Seal"
                    class="w-28 h-28 md:w-36 md:h-36 object-contain drop-shadow-[0_6px_16px_rgba(0,0,0,0.3)] transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-2" />
            </div>

            <div class="relative z-10 flex flex-col items-center text-center flex-1 animate-fade-up-2">
                <h2 class="font-bricolage text-3xl md:text-4xl font-extrabold text-white leading-tight drop-shadow-[0_2px_8px_rgba(0,0,0,0.2)]">
                    Total Faculty<br>
                    @if($selected_college)
                        of {{ strtoupper($selected_college) }}
                    @else
                        of the University
                    @endif
                </h2>
                <div class="w-12 h-0.5 bg-white/30 rounded-full my-4"></div>
                <div class="relative">
                    <span class="font-['anton'] text-4xl md:text-5xl text-white leading-none tracking-wide drop-shadow-[0_4px_20px_rgba(0,0,0,0.25)] mb-2">
                        {{ $total_faculty }}
                    </span>
                    <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-3/5 h-[3px] bg-gradient-to-r from-transparent via-white/50 to-transparent rounded-full"></div>
                </div>
            </div>

            <div class="relative z-10 flex flex-col gap-3 shrink-0 w-full md:w-auto animate-fade-up-3">
                <div class="group relative bg-white/[0.97] rounded-2xl px-5 py-4 min-w-[170px] shadow-[0_4px_16px_rgba(0,0,0,0.15),inset_0_1px_0_rgba(255,255,255,0.9)] transition-all duration-200 hover:-translate-x-1 hover:scale-[1.02] hover:shadow-xl overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#009539] to-[#00c44f] rounded-t-2xl"></div>
                    <p class="text-[0.62rem] font-semibold tracking-widest uppercase text-[#009539] mb-0.5">Tertiary</p>
                    <p class="font-anton text-4xl text-[#0f1a12] leading-none">{{ $tertiary_total }}</p>
                    <p class="text-[0.6rem] text-gray-400 mt-1">College-level faculty</p>
                </div>
                <div class="group relative bg-white/[0.97] rounded-2xl px-5 py-4 min-w-[170px] shadow-[0_4px_16px_rgba(0,0,0,0.15),inset_0_1px_0_rgba(255,255,255,0.9)] transition-all duration-200 hover:-translate-x-1 hover:scale-[1.02] hover:shadow-xl overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#009539] to-[#00c44f] rounded-t-2xl"></div>
                    <p class="text-[0.62rem] font-semibold tracking-widest uppercase text-[#009539] mb-0.5">Elem / Second / Tech-Voc</p>
                    <p class="font-anton text-4xl text-[#0f1a12] leading-none">{{ $elem_secon_techbo_total }}</p>
                    <p class="text-[0.6rem] text-gray-400 mt-1">Basic &amp; vocational ed.</p>
                </div>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 px-6 mt-8 items-center">
            <div class="bg-white rounded-xl p-4 shadow">
                <h3 class="font-bold mb-2">Faculty Tenure Distribution</h3>
                <div class="h-80 flex items-center justify-center"><canvas id="tenurePie"></canvas></div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow">
                <h3 class="font-bold mb-2">Faculty Rank Distribution</h3>
                <div class="h-80 flex items-center justify-center"><canvas id="rankPie"></canvas></div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow">
                <h3 class="font-bold mb-2">Faculty Gender Distribution</h3>
                <div class="h-80 flex items-center justify-center"><canvas id="genderPie"></canvas></div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow my-5 px-6">
                    <h3 class="font-bold mb-2">Gender Distribution per College</h3>
                    <div class="h-80"><canvas id="genderCollegeStacked"></canvas></div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("facultyFilterForm");
    if (!form) return;

    form.querySelectorAll("select").forEach(sel => {
        sel.addEventListener("change", () => form.submit());
    });
});

const colors = [
    "#009639","#FFD700","#65FF9C","#FFD05F","#39EDFF",
    "#FFE450","#FFB495","#FFC177","#00FFFF","#494949","#E0DA0D",
];
const GenderColor = ['#4285F4', '#FF7BAC'];

let tenureChart, rankChart, genderChart, genderCollegeChart, maleChart, femaleChart;

if (window.ChartDataLabels) {
    Chart.register(ChartDataLabels);
}

function sum(arr) {
    return (arr || []).reduce((a, b) => a + (Number(b) || 0), 0);
}

function percentFormatter(value, ctx) {
    const dataArr = ctx.chart.data.datasets[ctx.datasetIndex].data || [];
    const total = sum(dataArr);
    if (!total) return '';
    const pct = (Number(value) / total) * 100;
    return pct >= 3 ? `${pct.toFixed(1)}%` : ''; // hide tiny labels
}

function findSeries(ds, label) {
    return (ds || []).find(d => (d.label || "").toLowerCase() === label.toLowerCase());
}

function hexToRgb(hex) {
    if (!hex) return { r: 0, g: 0, b: 0 };
    hex = String(hex).replace("#", "");
    if (hex.length === 3) hex = hex.split("").map(c => c + c).join("");
    const n = parseInt(hex, 16);
    return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 };
}

function getContrastColor(bgHex) {
    const { r, g, b } = hexToRgb(bgHex);
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness > 150 ? "#111827" : "#FFFFFF";
}

const doughnutCenterText = {
    id: 'doughnutCenterText',
    afterDraw(chart) {
        if (chart.config.type !== 'doughnut') return;

        const { ctx, chartArea } = chart;
        if (!chartArea) return;

        const dataset = chart.data.datasets?.[0]?.data || [];
        const total = sum(dataset);

        const centerX = (chartArea.left + chartArea.right) / 2;
        const centerY = (chartArea.top + chartArea.bottom) / 2;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // TOTAL label
        ctx.font = '600 12px Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial';
        ctx.fillStyle = '#6B7280';
        ctx.fillText('TOTAL', centerX, centerY - 12);

        // Total number
        ctx.font = '800 22px Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial';
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
            formatter: percentFormatter,

            // ✅ INSIDE SLICE
            anchor: "center",
            align: "center",
            offset: 0,
            clamp: true,
            clip: true,

            font: { weight: "900", size: 12 },

            // auto white/black for readability
            color: (ctx) => {
                const bg = ctx.dataset.backgroundColor?.[ctx.dataIndex];
                return getContrastColor(bg);
            },

            // subtle stroke only when text is white (helps on bright colors)
            textStrokeColor: "rgba(0,0,0,0.35)",
            textStrokeWidth: (ctx) => {
                const bg = ctx.dataset.backgroundColor?.[ctx.dataIndex];
                return getContrastColor(bg) === "#FFFFFF" ? 2 : 0;
            },

            // hide tiny slices (adjust if you want)
            display: (ctx) => {
                const v = Number(ctx.dataset.data?.[ctx.dataIndex] || 0);
                const total = sum(ctx.dataset.data || []);
                if (!total) return false;
                return (v / total * 100) >= 3;
            }
        }
    },
    responsive: true,
    maintainAspectRatio: false
};

async function loadFacultyPies() {
    try {
        const qs  = window.location.search || "";
        const res = await fetch(`/api/faculty-pie${qs}`);
        if (!res.ok) { console.error("API failed:", res.status); return; }
        const data = await res.json();

        // TENURE PIE
        if (tenureChart) tenureChart.destroy();
        tenureChart = new Chart(document.getElementById("tenurePie"), {
            type: "pie",
            data: {
                labels: data.tenure?.labels || [],
                datasets: [{
                    data: data.tenure?.values || [],
                    backgroundColor: colors,
                    borderColor: "#fff",
                    borderWidth: 2
                }]
            },
            options: pieLabelOptions
        });

        // RANK PIE
        if (rankChart) rankChart.destroy();
        rankChart = new Chart(document.getElementById("rankPie"), {
            type: "pie",
            data: {
                labels: data.rank?.labels || [],
                datasets: [{
                    data: data.rank?.values || [],
                    backgroundColor: colors,
                    borderColor: "#fff",
                    borderWidth: 2
                }]
            },
            options: pieLabelOptions
        });

        if (genderChart) genderChart.destroy();
        genderChart = new Chart(document.getElementById("genderPie"), {
            type: "doughnut",
            data: {
                labels: data.gender?.labels || [],
                datasets: [{
                    data: data.gender?.values || [],
                    backgroundColor: GenderColor,
                    borderColor: "#fff",
                    borderWidth: 2
                }]
            },
            options: {
                ...pieLabelOptions,
                cutout: "65%"
            }
        });

        const cctx = document.getElementById("genderCollegeStacked");
        if (cctx) {
            if (genderCollegeChart) genderCollegeChart.destroy();

            const labels = data.gender_by_college?.labels || [];
            const rawDatasets = data.gender_by_college?.datasets || [];

            const datasets = rawDatasets.map((ds, i) => ({
                ...ds,
                backgroundColor: GenderColor[i % GenderColor.length],
                borderSkipped: false,
                datalabels: {
                    formatter: (value, ctx) => {
                        const xIndex = ctx.dataIndex;

                        // total at this college (sum of all stacks for this label)
                        const all = ctx.chart.data.datasets.map(d => Number(d.data?.[xIndex]) || 0);
                        const totalAtCollege = all.reduce((a, b) => a + b, 0);

                        if (!totalAtCollege) return '';
                        const pct = (Number(value) / totalAtCollege) * 100;
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
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: "bottom" },
                        datalabels: {} // enabled per-dataset above
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true }
                    }
                }
            });
        }

    } catch (err) {
        console.error("loadFacultyPies crashed:", err);
    }
}

document.addEventListener("DOMContentLoaded", loadFacultyPies);
</script>

