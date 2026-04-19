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
        .sidebar-scrollable {
            display: flex;
            flex-direction: column;
            align-items: stretch; /* Ensures children fill width */
        }

        /* Ensure the logo wrapper takes full width so text-center works */
        .sidebar-logo-full {
            width: 100%;
        }
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
                Research Programs ({{$selectedYear}})
            </div>
            <div class="flex flex-wrap items-center gap-4 pr-4 w-full md:w-auto">
                <div class="hidden sm:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                    Filter
                </div>

                //Year filter
                <div class="flex items-center gap-3">
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
        <div class="grid grid-cols-3 md:grid-cols-6 xl:grid-cols-12 gap-3 mb-2">
            //Cards for new programs, completed programs, ongoing programs, and total budget with icons, values, percentages, and descriptions, styled with Tailwind CSS for a modern and clean look, and using conditional classes to indicate positive or negative percentage changes with green or red colors respectively
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-people-group text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2 pb-3'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['new_programs'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'><span class="{{ $percentages['year_percent'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $percentages['year_percent'] > 0 ? '▲ ' : '▼ ' }}{{ $percentages['year_percent'] }}%</span></p>
                            <p class='text-[10px] md:text-[12px] text-right font-medium pr-4'>New Programs in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-circle-check text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['completed_programs'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'>{{ $percentages['complete_perc'] }}%</p>
                            <p class='text-[10px] md:text-[12px] text-right font-medium pr-4'>Completed Programs</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-clock-rotate-left text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['ongoing_programs'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'>{{ $percentages['ongoing_perc'] }}%</p>
                            <p class='text-[10px] md:text-[12px] text-right font-medium pr-4'>Ongoing Programs</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-3'>
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-36 rounded-lg shadow-inner shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-3 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-money-bills text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2 pt-6'>
                            <p class='text-lg sm:text-2xl lg:text-2xl xl:text-2xl text-right font-[650] pr-4 align-bottom'>₱ {{ number_format($stats['new_budget']) }}</p>
                            <p class='text-right text-[10px] md:text-[12px] font-medium pr-4 align-top'>Total Program Budget</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class='grid grid-cols-6 xl:grid-cols-12 gap-2'>
            //Charts for program distribution by type using Plotly.js for interactive visualizations and styled with Tailwind CSS for a cohesive look with the rest of the dashboard, while also including notes about data availability for certain years to provide context to users when interpreting the charts
            <div class='col-span-6 xl:col-span-4'>
                <div class='border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[320px] sm:h-[370px] lg:h-[570px] rounded-[1vw] shadow-inner shadow-xl'>
                <div class='w-full grid grid-cols-12 grid-rows-7'>
                    <div class='col-span-12 row-span-1 font-[750] text-sm md:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-7'>Programs per Type</div>
                    <div class='col-span-12 row-span-6'>
                        <div id='programTypeChart' class='w-full h-full'></div>
                    </div>
                </div>
                </div>
            </div>
            
            //Charts for annual program initiation trend and approved budget allocation over a 10-year period, using stacked bar charts with line overlays to show both the individual contributions of research and development programs as well as the overall totals, while also providing notes about data availability for certain years to ensure users have the necessary context when analyzing the trends displayed in the charts
            <div class='col-span-6 lg:col-span-8'>
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px]">
                    <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                        Annual Program Initiation Trend (10-year period)
                    </div>
                    <div>
                       <div id="programYearChart" style="width: 100%; "></div>
                    </div>    
                    <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6 pr-2">
                        <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                    </div>         
                </div>

                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[342px] sm:h-[300px] md:h-[300px] lg:h-[280px] mt-2 mb-2">
                    <div class="font-[650] text-sm md:text-lg text-gray-700 pl-6 pt-4">
                        Approved Budget Allocation (10-year period)
                    </div>
                    <div>
                       <div id="programBudgetChart" style="width: 100%; "></div>
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
    // JavaScript to initialize Plotly charts for program distribution by type, annual program initiation trend, and approved budget allocation, using data passed from the backend and configured with responsive settings, custom colors, hover templates, and layout options to create interactive and visually appealing charts that effectively communicate the insights derived from the research and development program data, while also ensuring that the charts resize correctly when the window size changes or when the sidebar is toggled for an optimal user experience across different devices and screen sizes
    <script>
    // JavaScript to initialize Plotly charts for program distribution by type, annual program initiation trend, and approved budget allocation, using data passed from the backend and configured with responsive settings, custom colors, hover templates, and layout options to create interactive and visually appealing charts that effectively communicate the insights derived from the research and development program data, while also ensuring that the charts resize correctly when the window size changes or when the sidebar is toggled for an optimal user experience across different devices and screen sizes
    document.addEventListener('DOMContentLoaded', function () {
    // Define common configuration for all charts to ensure consistency in appearance and behavior, including responsiveness, hiding the Plotly logo, and removing specific mode bar buttons to streamline the user interface and focus on the most relevant interactions for analyzing the research and development program data, while also maintaining a clean and professional look across all charts in the dashboard
    const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

    //most parts here are the same as what was in iprights.blade.php, just with different data and different chart types (pie charts instead of bar charts), so I won't add comments for the repeated parts, but I will add comments for the complex parts to explain what they do
    const typeData = [{
        values: [@js($charts['type_res']), @js($charts['type_dev'])],
        labels: ['Research', 'Development'],
        hovertemplate: '<b>Type:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
        type: 'pie',
        // textposition: "outside",
        outsidetextfont: {color: 'transparent'},
        marker: { colors: ['#01ac42', '#FFEB00'] },
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
            yanchor: 'bottom',   
        },
        font: { family: 'Inter, sans-serif' },
    };

    Plotly.newPlot('programTypeChart', typeData, typeLayout, commonConfig);

    //----------------------------------------------------------------------------
    const yearData = [
        //Data for the research data
        {
            x: @js($charts['year_labels']->values()),
            y: @js($charts['res_counts']),
            name: 'Research',
            type: 'bar',
            marker: { color: '#01ac42' },
            hovertemplate: '<b>Type:</b> Research <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
            width: 0.5,
        },
        //Data for the development data
        {
            x: @js($charts['year_labels']->values()),
            y: @js($charts['dev_counts']),
            name: 'Development',
            type: 'bar',
            marker: { color: '#F2EA00' },
            hovertemplate: '<b>Type:</b> Development <br><b>Year:</b> %{x}<br><b>Count: </b>%{y}<extra></extra>',
            width: 0.5,
        },
        //Data for the total count, shown as a line chart overlaying the bar charts to show the overall trend in project initiations across all project types, with a distinct color and hover template to differentiate it from the individual type contributions and provide a clear visualization of how the total number of project initiations has changed over the years in relation to the specific contributions of each project type
        {
            x: @js($charts['year_labels']->values()),
            y: @js($charts['year_counts']),
            type: 'scatter',
            mode: 'lines+markers',
            marker: { color: '#00702B' },
            hovertemplate: 'Year:</b> %{x}<br><b>Total: </b>%{y}<extra></extra>',
            line: { shape: 'spline' },
            name: 'Total',
        }
    ];

    const yearLayout = {
        height: 200,
        barmode: 'stack',
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 40, r: 40, b: 20, t: 0 },
        legend: {
            font: {size: 10, color: 'black'},
            orientation: 'h',
            x: 0.5,
            y: 1,
            xanchor: 'center', 
            yanchor: 'bottom'   
        },
        xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 10 } },
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero' }
    };

    Plotly.newPlot('programYearChart', yearData, yearLayout, commonConfig);

    const budgetData = [
        //Data for the research data
        {
            x: @js($charts['year_labels']->values()),
            y: @js($charts['res_sums']),
            name: 'Research',
            type: 'bar',
            marker: { color: '#01ac42' },
            hovertemplate: '<b>Type:</b> Research <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
            width: 0.5,
        },
        //Data for the development data
        {
            x: @js($charts['year_labels']->values()),
            y: @js($charts['dev_sums']),
            name: 'Development',
            type: 'bar',
            marker: { color: '#F2EA00' },
            hovertemplate: '<b>Type:</b> Development <br><b>Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
            width: 0.5,
        },
        //Data for the total budget, shown as a line chart overlaying the bar charts to show the overall trend in budget allocation across all project types, with a distinct color and hover template to differentiate it from the individual type contributions and provide a clear visualization of how the total budget has changed over the years in relation to the specific contributions of each project type
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
        margin: { l: 50, r: 40, b: 20, t: 0 },
        legend: {
            font: {size: 10, color: 'black'},
            orientation: 'h',
            x: 0.5,
            y: 1,
            xanchor: 'center', 
            yanchor: 'bottom'   
        },
        xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 10 } },
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero',tickprefix: '₱', tickformat: ',.2s' },
    }

    Plotly.newPlot('programBudgetChart', budgetData, budgetLayout, commonConfig);

    // Add a ResizeObserver to ensure charts resize correctly when the window size changes or when the sidebar is toggled, improving the user experience by maintaining the readability and usability of the charts across different screen sizes and layout configurations, especially in a responsive dashboard environment where users may frequently adjust their view
    const charts = ['programTypeChart', 'programYearChart', 'programBudgetChart'];
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