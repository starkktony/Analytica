
        <div class="">

            <!-- FILTER BAR -->
            <div class="sticky top-0 z-30 flex justify-between items-center h-12 bg-[#BDBDBD] px-6">
                <div class="flex flex-row gap-6 items-center">
                    <h2 class="text-lg lg:text-2xl font-['Bricolage_Grotesque'] font-extrabold text-black leading-tight">
                        Total Graduates
                            @if($selected_college && $selected_college !== 'All')
                                of {{ $selected_college }}
                            @else
                                of the University
                            @endif
                    </h2>
                    <div class="font-['Bricolage_Grotesque'] font-extrabold mr-5">Filters:</div>

                    <form method="GET" action="{{ route('graduates.index') }}" class="flex items-center gap-3">
                        <label class="font-['Bricolage_Grotesque'] font-extrabold text-sm">College:</label>
                        <div class="relative w-48">
                            <select name="college" id="college"
                                class="w-full appearance-none rounded-full bg-gray-100 text-center shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-300 cursor-pointer text-xs p-2"
                                onchange="this.form.submit()">
                                <option class="text-xs" value="All" {{ $selected_college === 'All' ? 'selected' : '' }}>All
                                </option>
                                @foreach($colleges as $c)
                                    <option class="text-xs" value="{{ $c }}" {{ $selected_college === $c ? 'selected' : '' }}>{{ $c }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto px-6">

                <div class="py-6 sm:py-8 animate-card-in">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
                            <div
                                class="relative bg-gradient-to-r from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg">
                                <div
                                    class="absolute top-4 left-4 w-12 h-12 bg-white/90 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-graduation-cap text-green-600 text-2xl"></i>
                                </div>
                                <div class="mt-12 text-right">
                                    <p class="font-[inter] text-[24px] md:text-[28px] font-extrabold leading-tight">{{ $total_graduates }}</p>
                                    <p class="text-[24px] md:text-[20px] font-[inter] font-semibold text-white mt-1">Total Graduates</p>
                                </div>
                            </div>

                            <div class="relative bg-white rounded-2xl p-6 shadow-md">
                                <div
                                    class="absolute top-4 left-4 w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-mars text-white text-xl"></i>
                                </div>
                                <div class="mt-12 text-right">
                                    <p class="font-[inter] text-[24px] md:text-[28px] font-bold text-gray-900">
                                        {{ $total_male}}</p>
                                    <p class="text-[20px] md:text-[16px] font-[inter] text-gray-500 mt-1">Total Male Graduates</p>
                                </div>
                            </div>

                            <div class="relative bg-white rounded-2xl p-6 shadow-md">
                                <div
                                    class="absolute top-4 left-4 w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-venus text-white text-xl"></i>
                                </div>
                                <div class="mt-12 text-right">
                                    <p class="font-[inter] text-[24px] md:text-[28px] font-bold text-gray-900">
                                        {{ $total_female }}</p>
                                    <p class="text-[20px] md:text-[16px] font-[inter] text-gray-500 mt-1">Total Female Graduates</p>
                                </div>
                            </div>

                            {{-- <div class="relative bg-white rounded-2xl p-6 shadow-md">
                                <div
                                    class="absolute top-4 left-4 w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-circle-plus text-white text-xl"></i>
                                </div>
                                <div class="mt-12 text-right">
                                    <p class="font-[inter] text-[20px] md:text-[24px] font-bold text-gray-900">
                                        {{ $income['other_income'] }}</p>
                                    <p class="text-[20px] md:text-[16px] font-[inter] text-gray-500 mt-1">Other Business Income</p>
                                </div>
                            </div> --}}
                        </div>
                    </div>

                <!-- HERO CARD -->
                {{-- <div class="relative w-full max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6
                        bg-gradient-to-br from-[#007a2f] via-[#009539] to-[#00b347]
                        rounded-2xl p-8 overflow-hidden
                        shadow-[0_20px_60px_rgba(0,100,30,0.5),0_4px_12px_rgba(0,0,0,0.3),0_0_0_1px_rgba(255,255,255,0.08)]
                        animate-card-in">

                    <div
                        class="absolute -top-24 -right-16 w-72 h-72 rounded-full border-[36px] border-white/[0.055] pointer-events-none">
                    </div>
                    <div
                        class="absolute -bottom-14 left-16 w-44 h-44 rounded-full border-[28px] border-white/[0.04] pointer-events-none">
                    </div>
                    <div
                        class="absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_50%_-10%,rgba(255,255,255,0.12),transparent)] pointer-events-none">
                    </div>
                    <div
                        class="absolute inset-0 bg-[radial-gradient(ellipse_40%_40%_at_100%_100%,rgba(0,0,0,0.15),transparent)] pointer-events-none">
                    </div>

                    <div class="relative z-10 shrink-0 animate-fade-up-1 group">
                        <img src="{{ asset('images/school 1.png') }}" alt="CLSU Seal"
                            class="w-28 h-28 md:w-36 md:h-36 object-contain drop-shadow-[0_6px_16px_rgba(0,0,0,0.3)] transition-transform duration-300 group-hover:scale-105 group-hover:-rotate-2" />
                    </div>

                    <div class="relative z-10 flex flex-col items-center text-center flex-1 animate-fade-up-2">
                        <p class="text-[0.65rem] font-semibold tracking-[0.2em] uppercase text-white/60 mb-1">University
                            Personnel</p>
                        <h2
                            class="font-bricolage text-3xl md:text-4xl font-extrabold text-white leading-tight drop-shadow-[0_2px_8px_rgba(0,0,0,0.2)]">
                            Total Graduates<br>
                            @if($selected_college && $selected_college !== 'All')
                                of {{ strtoupper($selected_college) }}
                            @else
                                of the University
                            @endif
                        </h2>
                        <div class="w-12 h-0.5 bg-white/30 rounded-full my-4"></div>
                        <div class="relative">
                            <span
                                class="font-['anton'] text-4xl md:text-5xl text-white leading-none tracking-wide drop-shadow-[0_4px_20px_rgba(0,0,0,0.25)] mb-2">
                                {{ $total_graduates }}
                            </span>
                            <div
                                class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-3/5 h-[3px] bg-gradient-to-r from-transparent via-white/50 to-transparent rounded-full">
                            </div>
                        </div>
                    </div>

                    <div class="relative z-10 flex flex-col gap-3 shrink-0 w-full md:w-auto animate-fade-up-3">
                        <div
                            class="group relative bg-white/[0.97] rounded-2xl px-5 py-4 min-w-[170px] shadow-[0_4px_16px_rgba(0,0,0,0.15),inset_0_1px_0_rgba(255,255,255,0.9)] transition-all duration-200 hover:-translate-x-1 hover:scale-[1.02] hover:shadow-xl overflow-hidden">
                            <div
                                class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#009539] to-[#00c44f] rounded-t-2xl">
                            </div>
                            <p class="text-[0.62rem] font-semibold tracking-widest uppercase text-[#009539] mb-0.5">Total Male
                                Graduates</p>
                            <p class="font-anton text-4xl text-[#0f1a12] leading-none">{{ $total_male }}</p>
                            <p class="text-[0.6rem] text-gray-400 mt-1">College-level faculty</p>
                        </div>
                        <div
                            class="group relative bg-white/[0.97] rounded-2xl px-5 py-4 min-w-[170px] shadow-[0_4px_16px_rgba(0,0,0,0.15),inset_0_1px_0_rgba(255,255,255,0.9)] transition-all duration-200 hover:-translate-x-1 hover:scale-[1.02] hover:shadow-xl overflow-hidden">
                            <div
                                class="absolute top-0 left-0 right-0 h-[3px] bg-gradient-to-r from-[#009539] to-[#00c44f] rounded-t-2xl">
                            </div>
                            <p class="text-[0.62rem] font-semibold tracking-widest uppercase text-[#009539] mb-0.5">Total Female
                                Graduates</p>
                            <p class="font-anton text-4xl text-[#0f1a12] leading-none">{{ $total_female }}</p>
                            <p class="text-[0.6rem] text-gray-400 mt-1">Basic &amp; vocational ed.</p>
                        </div>
                    </div>
                </div> --}}

                <!-- Gender Distribution Chart -->
                <div class="mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mt-8 items-center">
                        <!-- Gender Distribution Chart + Distribution by College (ONLY show when All is selected) -->
                        @if($selected_college === 'All')
                            <div class="bg-white rounded-xl p-4 shadow">
                                        <h3 class="font-bold">Gender Distribution</h3>
                                        <div class="h-96"><canvas id="genderBar"></canvas></div>
                                    </div>

                                    <div class="bg-white rounded-xl p-4 shadow">
                                        <h3 class="font-bold mb-2">Distribution of Graduates by College</h3>
                                        <div class="h-96"><canvas id="graduatesByCollege"></canvas></div>
                                    </div>
                        @endif
                    </div>
                </div>

                @if($selected_college === 'All')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mt-8 items-center">
                        <div class="mt-6">
                            <div class="bg-white rounded-xl p-4 shadow">
                                <h3 class="font-bold">Male Graduates per College</h3>
                                <div class="h-96"><canvas id="maleCollegeChart"></canvas></div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <div class="bg-white rounded-xl p-4 shadow">
                                <h3 class="font-bold">Female Graduates per College</h3>
                                <div class="h-96"><canvas id="femaleCollegeChart"></canvas></div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="mt-6">
                        <div class="bg-white rounded-xl p-4 shadow">
                            <h3 class="font-bold mb-2">Distribution of Graduates by College</h3>
                            <div class="h-96"><canvas id="graduatesByCollege"></canvas></div>
                        </div>
                    </div> --}}
                @endif

                @if($selected_college !== 'All')
                    <div class="mt-6 grid grid-cols-1 lg:grid-cols-1 gap-6">
                        <div class="bg-white rounded-xl p-4 shadow">
                            <h3 class="font-bold mb-2">Most populated Programs</h3>
                            <div class="h-96"><canvas id="programChart"></canvas></div>
                        </div>
                        <div class="bg-white rounded-xl p-4 shadow">
                            <h3 class="font-bold mb-3">Total Graduates by Program</h3>
                            <div class="rounded-lg overflow-hidden">
                                <div id="programList" class="divide-y"></div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
        <script>
            const CollegeColors = [
                '#65FF9C', '#FFD05F', '#39EDFF', '#FFE450', '#FFB495',
                '#FFC177', '#FFA8F7', '#00FFFF', '#E5E5E5', '#E06B0D', '#567F13', '#1A5F30',
            ];

            let genderChart = null, collegeDistChart = null, maleChart = null, femaleChart = null, programChart = null;

            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, s => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[s]));
            }

            function renderProgramList(items) {
                const wrap = document.getElementById("programList");
                if (!wrap) return;
                wrap.innerHTML = items.map((it, i) => {
                    const isGreen = i % 2 === 0;
                    const name = escapeHtml(it.program_name);
                    const major = it.major ? escapeHtml(it.major) : "";
                    return `
                    <div class="${isGreen ? 'bg-[#009539] text-white' : 'bg-white text-black'} px-5 py-4 flex items-start justify-between gap-6">
                        <div class="min-w-0">
                            <div class="font-semibold leading-snug">${name}</div>
                            ${major ? `<div class="text-sm opacity-90 leading-snug mt-1">Major in ${major}</div>` : ''}
                        </div>
                        <div class="font-extrabold shrink-0">${it.count}</div>
                    </div>`;
                }).join("");
            }

            function shortCollegeLabel(label) {
                label = String(label).trim();
                const m = label.match(/\(([^)]+)\)\s*$/);
                if (m) return m[1].trim();
                if (/Graduate School/i.test(label)) {
                    if (/master/i.test(label)) return "GS-Masteral";
                    if (/doctor/i.test(label)) return "GS-Doctoral";
                    return "GS";
                }
                if (label.includes("DOT-UNI")) return "DOT-UNI";
                return label.split(/[\s-]+/).filter(Boolean).map(w => w[0].toUpperCase()).join("");
            }

            async function loadProgramSection() {
                const college = document.getElementById("college")?.value || "All";
                const canvas = document.getElementById("programChart");
                const list = document.getElementById("programList");
                if (!canvas || !list) return;

                const res = await fetch(`/api/graduates-by-program?college=${encodeURIComponent(college)}&top=8`);
                const data = await res.json();
                const items = data.items || [];

                const chartLabels = items.map(it => it.major ? `${it.program_name} Major in ${it.major}` : it.program_name);
                const chartValues = items.map(it => it.count);

                renderProgramList(items);

                if (programChart) programChart.destroy();
                programChart = new Chart(canvas, {
                    type: "bar",
                    data: { labels: chartLabels, datasets: [{ label: "Graduates", data: chartValues }] },
                    options: { indexAxis: "y", responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
                });
            }

            async function loadGenderCollegeCharts() {
                const res = await fetch('/api/graduates-gender-by-college');
                const data = await res.json();
                const fullLabels = data.labels || [];
                const shortLabels = fullLabels.map(shortCollegeLabel);

                const maleCtx = document.getElementById("maleCollegeChart");
                if (maleChart) maleChart.destroy();
                if (maleCtx) {
                    maleChart = new Chart(maleCtx, {
                        type: "bar",
                        data: { labels: shortLabels, datasets: [{ label: "", data: data.male, backgroundColor: "#4285F4" }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false }, tooltip: { callbacks: { title: (items) => fullLabels[items[0].dataIndex] } } } }
                    });
                }

                const femaleCtx = document.getElementById("femaleCollegeChart");
                if (femaleChart) femaleChart.destroy();
                if (femaleCtx) {
                    femaleChart = new Chart(femaleCtx, {
                        type: "bar",
                        data: { labels: shortLabels, datasets: [{ label: "", data: data.female, backgroundColor: "#FF7BAC" }] },
                        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false }, tooltip: { callbacks: { title: (items) => fullLabels[items[0].dataIndex] } } } }
                    });
                }
            }

            async function loadGenderChart() {
                const college = document.getElementById("college")?.value || "All";
                const res = await fetch(`/api/graduates-summary?college=${encodeURIComponent(college)}`);
                const data = await res.json();
                const canvas = document.getElementById("genderBar");
                if (!canvas) return;
                if (genderChart) genderChart.destroy();
                genderChart = new Chart(canvas, {
                    type: "bar",
                    data: {
                        labels: [""],
                        datasets: [
                            { label: "Male", backgroundColor: "#4285F4", data: [data.male || 0] },
                            { label: "Female", backgroundColor: "#FF7BAC", data: [data.female || 0] }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins: { legend: { position: "bottom" } } }
                });
            }

            async function loadCollegeDistribution() {
                const canvas = document.getElementById("graduatesByCollege");
                if (!canvas) return;
                const res = await fetch(`/api/graduates-by-college`);
                const data = await res.json();
                const fullLabels = data.labels || [];
                const shortLabels = fullLabels.map(shortCollegeLabel);
                if (collegeDistChart) collegeDistChart.destroy();
                collegeDistChart = new Chart(canvas, {
                    type: "bar",
                    data: { labels: shortLabels, datasets: [{ label: "Number of Graduates", data: data.values, backgroundColor: CollegeColors.slice(0, data.values.length) }] },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        scales: { x: { beginAtZero: true }, },
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { title: (items) => fullLabels[items[0].dataIndex], label: (ctx) => { const v = ctx.raw ?? 0; const pct = data.percents?.[ctx.dataIndex] ?? 0; return ` ${v} (${pct}%)`; } } },
                            datalabels: { anchor: "end", align: "end", formatter: (value, ctx) => { const pct = data.percents?.[ctx.dataIndex] ?? 0; return `${value} (${pct}%)`; } }
                        }
                    },

                });
            }

            document.addEventListener("DOMContentLoaded", () => {
                loadGenderChart();
                loadCollegeDistribution();
                loadGenderCollegeCharts();
                loadProgramSection();
            });
        </script>
