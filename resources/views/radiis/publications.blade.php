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
                Research Publications ({{$selectedYear}})
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>

                //Year filter
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm font-medium">Year:</span>
                        <form action="{{ route('radiis.publications') }}" method="GET" id="filterForm" class="m-0">
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

                    //Group by filter for the time series chart
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm font-medium">Time Series:</span>
                        <form action="{{ route('radiis.publications') }}#time-series-section" method="GET" id="filterGroup" class="m-0">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            <select name="group_by" onchange="document.getElementById('filterGroup').submit()" 
                                class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                <option class="text-xs" value="category" {{ $selectedGroup == 'category' ? 'selected' : '' }}>Per Category</option>
                                <option class="text-xs" value="level" {{ $selectedGroup == 'level' ? 'selected' : '' }}>Per Level</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    //START OF THE DASHBOARD CONTENT------------------------------------------------------
    <div class="px-6">
        //Card for new publications with percentage change from previous year
        <div class="grid grid-cols-4 md:grid-cols-12 gap-3 mb-2">
            <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-4">       
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-45 sm:h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden'>
                    <div class='grid grid-rows-4 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-book text-white text-3xl"></i>
                        </div>
                        <div class='row-span-3 pt-4'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['new_pub'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'><span class="{{ $percentages['year_percent'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $percentages['year_percent'] > 0 ? '▲ ' : '▼ ' }}{{ $percentages['year_percent'] }}%</span></p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>New Publications in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        //Cards for publications per category, level, and unit
        <div class="grid grid-cols-4 xl:grid-cols-12 gap-3">
            <div class="col-span-4 h-[340px] sm:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Publications per Category</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="pubsPerCategory" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 h-[300px] sm:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Publications per Level</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="pubsPerLevel" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 h-[300px] sm:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl mb-2">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Publications per Unit</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="pubsPerUnit" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
        //Card for annual publications trend with a note about data availability
        <div id="time-series-section">
            <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[380px] sm:h-[352px] md:h-[354px] mb-8">
                <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>
                    Annual Publications Trend (10-year period)
                </div>
                <div>
                   <div id="pubsPerYear" style="width: 100%; "></div>
                </div>    
                <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6">
                    <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                </div>         
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    //JavaScript to initialize the Plotly charts for publications per category, level, unit, and annual trend, with a common configuration for responsiveness and styling, and a ResizeObserver to ensure the charts resize correctly when the window size changes or when the sidebar is toggled, improving the user experience by maintaining the readability and usability of the charts across different screen sizes and layout configurations, especially in a responsive dashboard environment where users may frequently adjust their view
    <script>
        // Wait for the DOM to fully load before executing the script to ensure that all elements are available for manipulation and that the charts can be rendered correctly without encountering any issues related to missing elements or data, providing a smoother user experience and preventing potential errors during the chart rendering process
        document.addEventListener('DOMContentLoaded', function () {

            // Define a common configuration object for all charts to ensure consistency in appearance and behavior, such as responsiveness, logo display, and mode bar button options, allowing for easier maintenance and updates to the chart settings across the entire dashboard by centralizing the configuration in one place
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
                    font: {size: 9, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('pubsPerCategory', catData, catLayout, commonConfig);

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
                    font: {size: 12, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('pubsPerLevel', levelData, levelLayout, commonConfig);

            //---------------------------------------------------------------------------
            const unitlabels = @js($charts['per_unit_labels']);//Gets the label to be shown in the legends
            const unitvalues = @js($charts['per_unit_values']);//Gets the values to be shown in the pie chart
            const unitFullNames = @js($charts['per_unit_names']);//Gets the full names of the units to be shown in the hovertemplate, as the labels are abbreviated for better display in the legend, but the full names provide more context when hovering over the pie chart segments, enhancing the user experience by providing detailed information about each unit without cluttering the visual presentation of the chart. The customDataArray is created to hold these full names and is used in the hovertemplate to display the full unit name when a user hovers over a segment of the pie chart, allowing for a more informative and user-friendly interaction with the chart while keeping the legend concise and visually appealing.

            // Create a custom data array for the hovertemplate to display the full unit names, as the labels used in the legend are abbreviated for better visual fit, but the hovertemplate can provide more detailed information by showing the full names of the units when users interact with the pie chart segments, enhancing the user experience by providing clarity and context without overcrowding the legend with long names.
            const customDataArray = unitFullNames.map(name => [name]);

            // Define a color mapping for each unit to ensure consistent and meaningful colors across the chart, where each unit is assigned a specific color that can be easily associated with it in the legend and the pie chart segments, improving the visual distinction between different units and enhancing the overall readability and interpretability of the chart. The colorMapping object maps each unit label to a specific color code, which is then used to create an array of colors for the pie chart segments based on the unit labels, ensuring that each segment is colored according to its corresponding unit in a visually coherent manner.
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

            // Create an array of colors for the pie chart segments based on the unit labels, using the color mapping defined above to ensure that each segment is colored according to its corresponding unit, which enhances the visual distinction between different units in the chart and helps users quickly identify and associate each segment with its respective unit in both the legend and the pie chart.
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
                margin: { l: 15, r: 15, b: 15, t: 0 },
                legend: {
                    font: {size: 10, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('pubsPerUnit', unitData, unitLayout, commonConfig);

            //---------------------------------------------------------------------------
            const stackedData = @json($charts['stacked']);// Gets the data for the stacked bar chart, which includes the labels for the x-axis (years), the series data for each category (with counts for each year), and the total line data that represents the overall trend in publications across all categories over the years. The stackedData object is structured to provide all the necessary information to create a comprehensive stacked bar chart with an overlaying line chart, allowing for a clear visualization of how publications are distributed across different categories annually, as well as how the total number of publications has evolved over time, giving users insights into both individual category performance and overall trends in research output. The data is passed from the server-side to the client-side in JSON format, making it easy to work with in JavaScript for chart rendering using Plotly.
            const colors = ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760'];// Define a color palette for the bar traces to ensure that each category in the stacked bar chart is visually distinct and easily identifiable, enhancing the readability and interpretability of the chart by allowing users to quickly differentiate between the various categories represented in the data. The colors array provides a set of visually appealing and contrasting colors that can be used to represent different series in the stacked bar chart, making it easier for users to understand the distribution and composition of publications across different categories over the years.

            // Create bar traces for each category in the stacked bar chart using the series data from the stackedData object, where each trace corresponds to a specific category and includes the x-axis labels (years), y-axis values (counts), and styling options such as color and width. The barTraces array is constructed by mapping over the series data, creating a trace object for each category that can be used to render the stacked bars in the chart, allowing for a clear visualization of how publications are distributed across different categories annually. Each trace is styled with a specific color from the colors array to ensure visual distinction between categories, and the hovertemplate is configured to provide detailed information about each category's contribution to the total publications when users interact with the chart.
            const barTraces = (stackedData.series || []).map((s, index) => ({
            x: stackedData.labels.map(String),
            y: s.counts,
            name: s.name,
            type: 'bar',
            width: 0.5,
            marker: { 
                color: colors[index % colors.length] 
            }
            }));

            // Create a trace for the total line, which is shown as a line chart overlaying the bar charts to represent the overall trend in publications across all categories over the years, with a distinct color and hover template to differentiate it from the individual category contributions. The totalLine trace uses the total_line data from the stackedData object for the y-axis values and shares the same x-axis labels (years) as the bar traces, allowing users to easily compare the total number of publications with the contributions of each category. The line is styled with a specific color and configured to display markers at each data point, enhancing the visibility of the overall trend in publications over time.
            const totalLine = {
                x: stackedData.labels.map(String),
                y: stackedData.total_line,
                name: 'Total',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' }
            };

            // Combine the bar traces and the total line trace into a single data array for the Plotly chart, allowing both the stacked bars and the overlaying line to be rendered together in the same chart. The yearData array includes all the individual category contributions as bar traces, as well as the total trend line, providing a comprehensive visualization of how publications are distributed across categories annually and how the total number of publications has evolved over time. This combination allows users to analyze both the specific contributions of each category and the overall trend in research output in a single, cohesive chart.
            const yearData = [
                ...barTraces, 
                totalLine
            ];

            // Define the layout for the annual publications trend chart, which includes settings for the height, bar mode (stacked), background colors, margins, legend configuration, and axis styling. The yearLayout object is structured to create a visually appealing and informative chart that effectively communicates the distribution of publications across categories annually, as well as the overall trend in total publications over time. The layout settings ensure that the chart is easy to read and interpret, with clear distinctions between different categories and a well-defined presentation of the total trend line, enhancing the user experience when analyzing the annual publications data.
            const yearLayout = {
                height: 280,
                barmode: 'stack',
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 40, r: 40, b: 45, t: 0 },
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

            Plotly.newPlot('pubsPerYear', yearData, yearLayout, commonConfig);

            // Add a ResizeObserver to ensure charts resize correctly when the window size changes or when the sidebar is toggled, improving the user experience by maintaining the readability and usability of the charts across different screen sizes and layout configurations, especially in a responsive dashboard environment where users may frequently adjust their view. The ResizeObserver monitors changes to the size of the content area and triggers a resize of all charts whenever a change is detected, ensuring that the charts adapt to the available space and remain visually coherent and easy to interact with regardless of layout adjustments.
            const charts = ['pubsPerCategory', 'pubsPerLevel', 'pubsPerUnit', 'pubsPerYear'];
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