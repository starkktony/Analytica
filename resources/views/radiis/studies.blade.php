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
<div class="content w-100">
    //SECOND HEADER *contains specific page and filter options*-----------------
    <div class="sticky top-0 z-50">
        <header>
            <span class="text-lg md:text-2xl font-[650] text-white">Research and Development</span>
        </header>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">
            <div class="font-[650] text-sm md:text-lg">
                Research Studies ({{$selectedYear}})
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>

                //Year filter
                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium">Year:</span>
                    <form action="{{ route('radiis.programs') }}" method="GET" id="filterForm" class="m-0">
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
        //Card for new studies, completed studies, ongoing studies, and total budget
        <div class="grid grid-cols-3 md:grid-cols-6 lg:grid-cols-12 gap-3 mb-2">
            //Card for new studies, showing the total number of new studies initiated in the selected year, along with a percentage change compared to the previous year
            <div class='col-span-3'>
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-magnifying-glass text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['new_studies'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'><span class="{{ $percentages['year_percent'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $percentages['year_percent'] > 0 ? '▲ ' : '▼ ' }}{{ $percentages['year_percent'] }}%</span></p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>New Studies in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            //Card for completed studies, showing the total number of studies that have been completed in the selected year, along with the percentage of completed studies out of the total number of studies initiated in that year
            <div class='col-span-3'>
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-circle-check text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['completed_studies'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'>{{ $percentages['complete_perc'] }}%</p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>Completed Studies</p>
                        </div>
                    </div>
                </div>
            </div>
            //Card for ongoing studies, showing the total number of studies that are currently ongoing in the selected year, along with the percentage of ongoing studies out of the total number of studies initiated in that year
            <div class='col-span-3'>
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-clock-rotate-left text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['ongoing_studies'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'>{{ $percentages['ongoing_perc'] }}%</p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>Ongoing Studies</p>
                        </div>
                    </div>
                </div>
            </div>
            //Card for total study budget, showing the total budget allocated for studies initiated in the selected year, along with a percentage change compared to the previous year
            <div class='col-span-3'>
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-money-bills text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2 pt-7'>
                            <p class='text-lg sm:text-2xl lg:text-2xl xl:text-2xl text-right font-[650] pr-4 align-bottom'>₱ {{ number_format($stats['new_budget']) }}</p>
                            <p class='text-right text-[10px] md:text-[12px] font-medium pr-4 align-top'>Total Study Budget</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class='grid grid-cols-6 lg:grid-cols-12 gap-2'>
            //Card for studies per type, showing a breakdown of the number of studies initiated in the selected year by type
            <div class='col-span-6 lg:col-span-4'>
                <div class='border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[320px] sm:h-[370px] lg:h-[570px] rounded-[1vw] shadow-inner shadow-xl'>
                <div class='w-full grid grid-cols-12 grid-rows-7'>
                    <div class='col-span-12 row-span-1 font-[750] text-sm md:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-7'>Studies per Type</div>
                    <div class='col-span-12 row-span-6'>
                        <div id='studyTypeChart' class='w-full h-full'></div>
                    </div>
                </div>
                </div>
            </div>
      
            //Card for annual study initiation trend, showing the number of studies initiated each year over the past 10 years, along with a line chart overlaying the bar chart to show the overall trend in study initiations across all types over time.
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px]">
                    <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                        Annual Study Initiation Trend (10-year period)
                    </div>
                    <div>
                       <div id="studyYearChart" style="width: 100%; "></div>
                    </div>    
                    <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pr-2">
                        <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                    </div>         
                </div>

            //Card for approved budget allocation, showing the total budget allocated for studies initiated each year over the past 10 years, along with a line chart overlaying the bar chart to show the overall trend in budget allocation across all types over time.
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px] mt-2 mb-4">
                    <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                        Approved Budget Allocation (10-year period)
                    </div>
                    <div>
                       <div id="studyBudgetChart" style="width: 100%; "></div>
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
    // JavaScript to initialize the charts using Plotly and handle responsive resizing
    <script>
        // Wait for the DOM to load before initializing the charts, ensuring that all necessary elements are available and that the charts are rendered correctly with the provided data and configurations. This event listener ensures that the chart initialization code runs only after the entire page has been loaded, preventing potential issues with accessing DOM elements or rendering charts before the page is fully ready.
        document.addEventListener('DOMContentLoaded', function () {

            // Define a common configuration object for all charts to ensure consistency in appearance and behavior, such as responsiveness, hiding the Plotly logo, and removing specific mode bar buttons that are not needed for the user interface. This commonConfig object is used when initializing each chart to apply these settings uniformly across all charts, enhancing the overall user experience by providing a cohesive look and feel while also simplifying the chart initialization code by centralizing shared configurations.
            const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

            const typeData = [{
                values: [@js($charts['type_res']), @js($charts['type_resdev'])],
                labels: ['Research', 'Research & Development'],
                hovertemplate: '<b>Type:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00'] }
            }];

            const typeLayout = {
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 35, r: 35, b: 10, t: 15 },
                showlegend: true,
                legend: {
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('studyTypeChart', typeData, typeLayout, commonConfig);

            //--------------------------------------------------------------------
            const yearData = [
                //Data for research 
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['res_counts']),
                    name: 'Research',
                    type: 'bar',
                    marker: { color: '#01ac42' },
                    hovertemplate: '<b>Type:</b> Research <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
                    width: 0.5,
                },
                //Data for research and development
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['resdev_counts']),
                    name: 'Research & Development',
                    type: 'bar',
                    marker: { color: '#F2EA00' },
                    hovertemplate: '<b>Type:</b> Research & Development <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
                    width: 0.5,
                },
                //Data for total count
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
                margin: { l: 40, r: 40, b: 20, t: 15 },
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

            Plotly.newPlot('studyYearChart', yearData, yearLayout, commonConfig);

            //---------------------------------------------------------------------------
            const budgetData = [
                //Data for research
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['res_sums']),
                    name: 'Research',
                    type: 'bar',
                    marker: { color: '#01ac42' },
                    hovertemplate: '<b>Type:</b> Research <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                    width: 0.5,
                },
                //Data for research and development
                {
                    x: @js($charts['year_labels']->values()),
                    y: @js($charts['resdev_sums']),
                    name: 'Research & Development',
                    type: 'bar',
                    marker: { color: '#F2EA00' },
                    hovertemplate: '<b>Type:</b> Research & Development <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                    width: 0.5,
                },
                //Data for total budget
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
                margin: { l: 60, r: 40, b: 20, t: 15 },
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

            Plotly.newPlot('studyBudgetChart', budgetData, budgetLayout, commonConfig);

            // Add a ResizeObserver to ensure charts resize correctly when the window size changes or when the sidebar is toggled, improving the user experience by maintaining the readability and usability of the charts across different screen sizes and layout configurations, especially in a responsive dashboard environment where users may frequently adjust their view. The ResizeObserver monitors changes to the size of the content area and triggers a resize of all charts whenever a change is detected, ensuring that the charts adapt to the available space and remain visually coherent and easy to interact with regardless of layout adjustments.
            const charts = ['studyTypeChart', 'studyYearChart', 'studyBudgetChart'];
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