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

    //CSS FOR SIDEBAR AND HEADER
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
    //SECOND HEADER *contains specific page and filter options*-----------------
    <div class="sticky top-0 z-50">
        <header>
            <span class="text-lg md:text-2xl font-[650] text-white">Research and Development</span>
        </header>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">
            <div class="font-[650] text-sm md:text-lg">
                University Researchers
            </div>
            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>

                //Year filter
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium">Year:</span>
                    <form action="{{ route('radiis.researchers') }}" method="GET" id="filterForm" class="m-0">
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

    //START OF THE DASHBOARD CONTENT------------------------------------------------------
    <div class="px-6 pt-4">
        //Card for new researchers hired in the selected year
        <div class="grid grid-cols-4 md:grid-cols-8 lg:grid-cols-12 gap-3 mb-2 z-0">
            <div class="col-span-4 md:col-span-5 lg:col-span-4">
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-calendar-plus text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2 pt-2'>
                            <p class='text-4xl md:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['res_hired'] }}</p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>New Researchers in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        //Cards for researchers per type, status, and degree
        <div class="grid grid-cols-4 lg:grid-cols-12 gap-3 mb-2 z-10">
            <div class='col-span-4'>
                <div class='border-t-6 border-green-600 bg-linear-to-br bg-white/50 flex flex-wrap h-[350px] md:h-[400px] lg:h-[500px] rounded-[1vw] inset-shadow-xl shadow-xl'>
                    <div class='w-full grid grid-cols-12 grid-rows-7'>
                        <div class='col-span-12 row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Researchers per Type</div>
                        <div class='col-span-12 row-span-6'>
                            <div id="resPerType" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='border-t-6 border-green-600 bg-linear-to-br bg-white/50 flex flex-wrap h-[350px] md:h-[400px] lg:h-[500px] rounded-[1vw] inset-shadow-xl shadow-xl'>
                    <div class='w-full grid grid-cols-12 grid-rows-7'>
                        <div class='col-span-12 row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Researchers per Status</div>
                        <div class='col-span-12 row-span-6'>
                            <div id="resPerStatus" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='border-t-6 border-green-600 bg-linear-to-br bg-white/50 flex flex-wrap h-[350px] md:h-[400px] lg:h-[500px] rounded-[1vw] inset-shadow-xl shadow-xl'>
                    <div class='w-full grid grid-cols-12 grid-rows-7'>
                        <div class='col-span-12 row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Researchers per Degree</div>
                        <div class='col-span-12 row-span-6'>
                            <div id="resPerDegree" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        //Card for researchers hired per year (time series)
        <div id="time-series-section">
            <div class="border-l-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl h-[370px] sm:h-[352px] md:h-[354px] mb-8">
                <div class="font-[650] text-sm sm:text-lg text-gray-700 pl-4 sm:pl-6 pt-4">
                    Researchers Hired per Year (10-year period)
                </div>
                <div>
                   <div id="resPerYear" style="width: 100%; "></div>
                </div>    
                <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-3 sm:pl-6">
                    <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                </div>         
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    //PLOTLY CHARTS FOR RESEARCHERS DASHBOARD------------------------------------------------------
    <script>
        //Wait for the DOM to load before initializing the charts to ensure that the chart containers are available and to prevent any potential issues with rendering the charts before the elements are fully loaded. This is especially important in a dynamic dashboard environment where the content may be loaded asynchronously or where there are interactive elements that could affect the timing of when the charts are rendered. By wrapping the chart initialization code inside a DOMContentLoaded event listener, we can ensure that all necessary elements are present and ready for manipulation before we attempt to create and render the Plotly charts, leading to a smoother user experience and preventing errors related to missing elements.
        document.addEventListener('DOMContentLoaded', function () {
            
            // Define a common configuration object for all charts to ensure consistency in appearance and behavior across the different visualizations on the dashboard. This commonConfig object includes settings for responsiveness, logo display, and mode bar button removal, which helps to create a cohesive look and feel for all charts while also optimizing the user interface by removing unnecessary buttons that may not be relevant for the specific use case of the researchers dashboard. By centralizing these configuration settings, we can easily maintain and update the chart configurations in one place, ensuring that any changes are consistently applied across all charts without having to modify each chart's individual configuration.
            const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

            const typeData = [{
                values: @js($charts['type_values']),
                labels: @js($charts['type_labels']),
                hovertemplate: '<b>Type:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF']}
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

            Plotly.newPlot('resPerType', typeData, typeLayout, commonConfig);

            //--------------------------------------------------------------------

            const statData = [{
                values: @js($charts['status_values']),
                labels: @js($charts['status_labels']),
                hovertemplate: '<b>Status:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF']}
            }];

            const statLayout = {
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

            Plotly.newPlot('resPerStatus', statData, statLayout, commonConfig);

            //---------------------------------------------------------------------------

            const degData = [{
                values: @js($charts['degree_values']),
                labels: @js($charts['degree_labels']),
                hovertemplate: '<b>Status:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            const degLayout = {
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

            Plotly.newPlot('resPerDegree', degData, degLayout, commonConfig);

            const yearData = [
                {
                    x: @js($charts['year_label']->values()),
                    y: @js($charts['year_count']),
                    name: 'Researchers',
                    type: 'bar',
                    marker: { color: '#01ac42' },
                    hovertemplate: 'Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
                    width: 0.5,
                },
            ];

            const yearLayout = {
                height: 250,
                barmode: 'stack',
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 40, r: 40, b: 50, t: 0 },
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

            Plotly.newPlot('resPerYear', yearData, yearLayout, commonConfig);
            
            // Add a ResizeObserver to ensure charts resize correctly when the window size changes or when the sidebar is toggled, improving the user experience by maintaining the readability and usability of the charts across different screen sizes and layout configurations, especially in a responsive dashboard environment where users may frequently adjust their view. The ResizeObserver monitors changes to the size of the content area and triggers a resize of all charts whenever a change is detected, ensuring that the charts adapt to the available space and remain visually coherent and easy to interact with regardless of layout adjustments.
            const charts = ['resPerType', 'resPerStatus', 'resPerDegree', 'resPerYear'];
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