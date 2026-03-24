<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com" rel="stylesheet">
    <script src="https://cdn.plot.ly/plotly-3.3.0.min.js" charset="utf-8"></script>
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
        header{
            height: 70px;
            padding: 2rem 3rem;
            background-color: #009539;
            box-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Fix for Tailwind CSS and Bootstrap collapse conflict */
        .collapse.show {
            visibility: visible !important;
        }
    </style>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    @include('components.sidebar')
<div class="content">
    <div class="sticky top-0 z-50">
        <header>
            <span class="text-lg md:text-2xl font-[650] text-white">Research and Development</span>
        </header>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">
            <div class="font-[650] text-sm md:text-lg">
                Research Presentations ({{$selectedYear}})
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm font-medium">Year:</span>
                        <form action="{{ route('radiis.presentations') }}" method="GET" id="filterForm" class="m-0">
                            <select name="year" onchange="this.form.submit()" 
                                class="text-xs md:text-sm block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                                @foreach($stats['all_year'] as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }} class="text-xs md:text-sm">
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm font-medium">Time Series:</span>
                        <form action="{{ route('radiis.presentations') }}#time-series-section" method="GET" id="filterGroup" class="m-0">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            <select name="group_by" onchange="document.getElementById('filterGroup').submit()" 
                                class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                <option class="text-xs" value="category" {{ $selectedGroup == 'category' ? 'selected' : '' }}>Per Category</option>
                                <option class="text-xs" value="level" {{ $selectedGroup == 'level' ? 'selected' : '' }}>Per Level</option>
                                <option class="text-xs" value="type" {{ $selectedGroup == 'type' ? 'selected' : '' }}>Per Type</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 pt-4">
        <div class="grid grid-cols-4 md:grid-cols-12 gap-3 mb-2">
            <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-4">       
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-45 sm:h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden'>
                    <div class='grid grid-rows-4 h-full'>
                        <div class='bg-green-600/80 col-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-person-chalkboard text-white text-2xl"></i>
                        </div>
                        <div class='row-span-2 pt-4'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['new_pres'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'><span class="{{ $percentages['year_percent'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $percentages['year_percent'] > 0 ? '▲ ' : '▼ ' }}{{ $percentages['year_percent'] }}%</span></p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>New Presentations in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-6 md:grid-cols-12 gap-3">
            <div class="col-span-6 h-[350px] md:h-[400px] lg:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Presentations per Category</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="presPerCategory" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-6 h-[350px] md:h-[400px] lg:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Presentations per Level</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="presPerLevel" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-6 h-[350px] md:h-[400px] lg:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Presentations per Type</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="presPerType" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-6 h-[350px] md:h-[400px] lg:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl mb-2">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Presentations per Unit</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="presPerUnit" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="time-series-section">
            <div class="border-l-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl h-97 sm:h-88 md:h-86 mb-8">
                <div class="font-[650] text-sm sm:text-lg text-gray-700 pl-6 pt-4">
                    Annual Presentations Trend (10-year period)
                </div>
                <div>
                   <div id="presPerYear" style="width: 100%; "></div>
                </div>    
                <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6">
                    <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                </div>         
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

            const catData = [{
                values: @js($charts['per_category_values']),
                labels: @js($charts['per_category_labels']),
                hovertemplate: '<b>Category:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            const catLayout = {
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 15, r: 15, b: 15, t: 15 },
                legend: {
                    font: {size: 11, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('presPerCategory', catData, catLayout, commonConfig);

            //--------------------------------------------------------------------

            const levelData = [{
                values: @js($charts['per_level_values']),
                labels: @js($charts['per_level_labels']),
                hovertemplate: '<b>Level:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            const levelLayout = {
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 15, r: 15, b: 15, t: 15 },
                legend: {
                    font: {size: 11, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('presPerLevel', levelData, levelLayout, commonConfig);

            //---------------------------------------------------------------------------

            const typeData = [{
                values: @js($charts['per_type_values']),
                labels: @js($charts['per_type_labels']),
                hovertemplate: '<b>Type:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            const typeLayout = {
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 15, r: 15, b: 15, t: 15 },
                legend: {
                    font: {size: 11, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('presPerType', typeData, typeLayout, commonConfig);

            //---------------------------------------------------------------------------
            const unitlabels = @js($charts['per_unit_labels']);
            const unitvalues = @js($charts['per_unit_values']);
            const unitFullNames = @js($charts['per_unit_names']); // Get the new field

            const customDataArray = unitFullNames.map(name => [name]);

            const colorMapping = {
                'CEn': '#800000',   // Maroon
                'CBA': '#3498db',  // Blue
                'CAg': '#2ecc71',   // Green
                'CEd': '#FFEB00', //Yellow
                'CVSM': '#242424', //Black
                'CoF': '#fc1a0a', //Red
                'CHSI': '#de28b0', //Pink
                'COS': '#02c3c9', //Teal
                'CASS': '#703e00', //Brown
                'Other': '#95a5a6',  // Grey
                'URPO-SRC': '#e8941e', //Orange
                'URPO-CRRDC': '#a51ee8', //Violet
                'URPO-RMCARES': '#c3ff75', //Light Green
                'URPO-FAC': '#5eafff', //Light Blue
            };

            const colors1 = unitlabels.map(label => colorMapping[label] || '#000000');

            const unitData = [{
                values: unitvalues,
                labels: unitlabels,
                customdata: customDataArray,
                hovertemplate: '<b>Unit:</b> %{customdata} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { 
                    colors: colors1 
                }
            }];

            const unitLayout = {
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 15, r: 15, b: 15, t: 15 },
                legend: {
                    font: {size: 11, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('presPerUnit', unitData, unitLayout, commonConfig);

            //---------------------------------------------------------------------------
            const stackedData = @json($charts['stacked']);
            const colors = ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760'];

            const barTraces = (stackedData.series || []).map((s, index) => ({
            x: stackedData.labels.map(String),
            y: s.counts,
            name: s.name,
            type: 'bar',
            width: 0.4,
            marker: { 
                color: colors[index % colors.length] 
            }
            }));

            const totalLine = {
                x: stackedData.labels.map(String),
                y: stackedData.total_line,
                name: 'Total',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' }
            };

            const yearData = [
                ...barTraces, 
                totalLine
            ];
            const yearLayout = {
                height: 270,
                barmode: 'stack',
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 40, r: 40, b: 45, t: 10 },
                legend: {
                    font: {size: 10, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 11 } },
                yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero' }
            };

            Plotly.newPlot('presPerYear', yearData, yearLayout, commonConfig);

            const charts = ['presPerCategory', 'presPerLevel', 'presPerType', 'presPerUnit', 'presPerYear'];
            const contentDiv = document.querySelector('.content');
            if (contentDiv) {
                const ro = new ResizeObserver(() => {
                    charts.forEach(id => {
                        const el = document.getElementById(id);
                        if (el && el.data) {
                            Plotly.Plots.resize(el);
                        }
                    });
                });
                ro.observe(contentDiv);
            }
            });
    </script>
</body>
</html>