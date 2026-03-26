<!DOCTYPE html>
<html>
<head>
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
<div class="content w-100">
    <div class="sticky top-0 z-50">
        <header>
            <span class="text-lg md:text-2xl font-[650] text-white">Research and Development</span>
        </header>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">
            <div class="font-[650] text-sm md:text-lg">
                Research Projects ({{$selectedYear}})
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium">Year:</span>
                    <form action="{{ route('radiis.projects') }}" method="GET" id="filterForm" class="m-0">
                        <select name="year" onchange="this.form.submit()" 
                            class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                            @foreach($stats['all_year'] as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 pt-4">
        <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-2">
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-cubes-stacked text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['new_projects'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'><span class="{{ $percentages['year_percent'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $percentages['year_percent'] > 0 ? '▲ ' : '▼ ' }}{{ $percentages['year_percent'] }}%</span></p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>New Projects in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-circle-check text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['completed_projects'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'>{{ $percentages['complete_perc'] }}%</p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>Completed Projects</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-clock-rotate-left text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['ongoing_projects'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'>{{ $percentages['ongoing_perc'] }}%</p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>Ongoing Projects</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-money-bills text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2 pt-7'>
                            <p class='text-lg sm:text-2xl lg:text-2xl xl:text-2xl text-right font-[650] pr-4 align-bottom'>₱ {{ number_format($stats['new_budget']) }}</p>
                            <p class='text-right text-[10px] md:text-[12px] font-medium pr-4 align-top'>Total Project Budget</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class='grid grid-cols-6 xl:grid-cols-12 gap-2'>
            <div class='col-span-6 xl:col-span-4'>
                <div class='border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[320px] sm:h-[370px] lg:h-[570px] rounded-[1vw] shadow-inner shadow-xl'>
                <div class='w-full grid grid-cols-12 grid-rows-7'>
                    <div class='col-span-12 row-span-1 font-[750] text-sm md:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-7'>Projects per Type</div>
                    <div class='col-span-12 row-span-6'>
                        <div id='projectTypeChart' class='w-full h-full'></div>
                    </div>
                </div>
                </div>
            </div>
            
            <div class='col-span-6 lg:col-span-8'>
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px]">
                    <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                        Annual Project Initiation Trend (10-year period)
                    </div>
                    <div>
                       <div id="projectYearChart" style="width: 100%; "></div>
                    </div>    
                    <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pr-2">
                        <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                    </div>         
                </div>
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px] mt-2 mb-4">
                    <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                        Approved Budget Allocation (10-year period)
                    </div>
                    <div>
                       <div id="projectBudgetChart" style="width: 100%; "></div>
                    </div>    
                    <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pr-2">
                        <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                    </div>         
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

            const typeData = [{
                values: [@js($charts['type_res']), @js($charts['type_dev']), @js($charts['type_resdev']), @js($charts['type_bw'])],
                labels: ['Research', 'Development', 'Research & Development', 'Book Writing'],
                hovertemplate: '<b>Type:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4E98FF', '#D84CFF'] }
            }];

            const typeLayout = {
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 35, r: 35, b: 10, t: 15 },
                showlegend: true,
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

            Plotly.newPlot('projectTypeChart', typeData, typeLayout, commonConfig);

            //--------------------------------------------------------------------
            const yearData = [
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['res_counts']),
                    name: 'Research',
                    type: 'bar',
                    marker: { color: '#01ac42' },
                    hovertemplate: '<b>Type:</b> Research <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['dev_counts']),
                    name: 'Development',
                    type: 'bar',
                    marker: { color: '#F2EA00' },
                    hovertemplate: '<b>Type:</b> Development <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['resdev_counts']),
                    name: 'Research & Development',
                    type: 'bar',
                    marker: { color: '#4E98FF' },
                    hovertemplate: '<b>Type:</b> Research & Development <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['bw_counts']),
                    name: 'Book Writing',
                    type: 'bar',
                    marker: { color: '#D84CFF' },
                    hovertemplate: '<b>Type:</b>Book Writing <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['year_counts']),
                    type: 'scatter',
                    mode: 'lines+markers',
                    marker: { color: '#00702B' },
                    hovertemplate: 'Year:</b> %{x}<br><b>Total: </b>%{y}<extra></extra>',
                    line: { shape: 'spline' },
                    name: 'Total'
                }
            ];

            const yearLayout = {
                height: 200,
                barmode: 'stack',
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 40, r: 40, b: 35, t: 0 },
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

            Plotly.newPlot('projectYearChart', yearData, yearLayout, commonConfig);

            //---------------------------------------------------------------------------
            const budgetData = [
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['res_sums']),
                    name: 'Research',
                    type: 'bar',
                    marker: { color: '#01ac42' },
                    hovertemplate: '<b>Type:</b> Research <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['dev_sums']),
                    name: 'Development',
                    type: 'bar',
                    marker: { color: '#F2EA00' },
                    hovertemplate: '<b>Type:</b> Development <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['resdev_sums']),
                    name: 'Research & Development',
                    type: 'bar',
                    marker: { color: '#4E98FF' },
                    hovertemplate: '<b>Type:</b> Research & Development <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['bw_sums']),
                    name: 'Book Writing',
                    type: 'bar',
                    marker: { color: '#D84CFF' },
                    hovertemplate: '<b>Type:</b> Book Writing <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                    width: 0.5,
                },
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['budget_totals']),
                    type: 'scatter',
                    mode: 'lines+markers',
                    marker: { color: '#00702B' },
                    hovertemplate: 'Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',  
                    line: { shape: 'spline' },
                    name: 'Total'
                }
            ];

            const budgetLayout = {
                height: 200,
                barmode: 'stack',
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 60, r: 40, b: 35, t: 0 },
                legend: {
                    font: {size: 10, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 11 } },
                yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero',tickprefix: '₱', tickformat: ',.2s' },
            };

            Plotly.newPlot('projectBudgetChart', budgetData, budgetLayout, commonConfig);

            const charts = ['projectTypeChart', 'projectYearChart', 'projectBudgetChart'];
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