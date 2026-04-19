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
    //SECOND HEADER *contains specific page and filter options*--------------------
    <div class="sticky top-0 z-50">
        <header>
            <span class="text-lg md:text-2xl font-[650] text-white">Research and Development</span>
        </header>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-4">
            <div class="font-[650] text-sm md:text-lg">
                Research Intellectual Property Rights ({{$selectedYear}})
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>

                //Year filter
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm font-medium">Year:</span>
                        <form action="{{ route('radiis.iprights') }}" method="GET" id="filterForm" class="m-0">
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
                        <form action="{{ route('radiis.iprights') }}#time-series-section" method="GET" id="filterGroup" class="m-0">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            <select name="group_by" onchange="document.getElementById('filterGroup').submit()" 
                                class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                <option class="text-xs" value="utilization" {{ $selectedGroup == 'utilization' ? 'selected' : '' }}>Per Utilization</option>
                                <option class="text-xs" value="type" {{ $selectedGroup == 'type' ? 'selected' : '' }}>Per Type</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    //START OF THE DASHBOARD CONTENT------------------------------------------------------
    <div class="px-6 pt-4">
        //Summary card showing the number of new IPRs in the selected year, with a percentage change indicator compared to the previous year, styled with Tailwind CSS and using conditional classes for the percentage change color
        <div class="grid grid-cols-4 md:grid-cols-12 gap-3 mb-2">
            <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-4">       
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-45 sm:h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden'>
                    <div class='grid grid-rows-4 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-lightbulb text-white text-3xl"></i>
                        </div>
                        <div class='row-span-3 pt-4'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['new_ipr'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'><span class="{{ $percentages['year_percent'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $percentages['year_percent'] > 0 ? '▲ ' : '▼ ' }}{{ $percentages['year_percent'] }}%</span></p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>New IPRs in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        //Three pie charts showing the distribution of IPRs by utilization, type, and unit, each styled with Tailwind CSS and using Plotly for rendering, with customized hover templates and legends
        <div class="grid grid-cols-4 md:grid-cols-12 gap-3">
            <div class="col-span-4 h-[340px] sm:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>IPRs per Utilization</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="iprPerUtil" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 h-[340px] sm:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>IPRs per Type</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="iprPerType" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 h-[380px] sm:h-[500px] border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl mb-2">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>IPRs per Unit</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="iprPerUnit" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
        //A stacked bar chart showing the annual trend of IPR initiations over a 10-year period, styled with Tailwind CSS and using Plotly for rendering, with a note about data availability for certain years
        <div id="time-series-section" class="col-span-4 md:col-span-8">
            <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[410px] sm:h-[352px] md:h-[354px] mb-8">
                <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>
                    Annual IPRs Trend (10-year period)
                </div>
                <div>
                   <div id="iprPerYear" style="width: 100%; "></div>
                </div>    
                <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6">
                    <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                </div>         
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    //JavaScript code to initialize the Plotly charts for IPRs per Utilization, Type, Unit, and Annual Trend, using the data passed from the controller and applying a common configuration for responsiveness and styling, as well as adding a resize observer to ensure charts resize correctly when the window size changes or when the sidebar is toggled
    <script>
        //Wait for the DOM to fully load before executing the chart initialization code, ensuring that all HTML elements are available for manipulation
        document.addEventListener('DOMContentLoaded', function () {
            console.log(@js($charts));

            //Define a common configuration object for all Plotly charts, enabling responsiveness, hiding the Plotly logo, and removing specific mode bar buttons for a cleaner user interface
            const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

            //Data for the IPRs per Utilization pie chart, using the values and labels from the $charts array passed from the controller, and customizing the hover template to show utilization, count, and percentage, as well as setting specific colors for each segment
            const utilData = [{
                values: @js($charts['per_util_values']),
                labels: @js($charts['per_util_labels']),
                hovertemplate: '<b>Utilization:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            //Layout configuration for the IPRs per Utilization pie chart, including transparent background, margins, legend styling, and font settings to ensure a consistent and visually appealing presentation of the chart
            const utilLayout = {
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

            //Render the IPRs per Utilization pie chart in the designated HTML element with the specified data, layout, and common configuration to create an interactive and informative visualization of the distribution of IPRs by utilization
            Plotly.newPlot('iprPerUtil', utilData, utilLayout, commonConfig);

            //---------------------------------------------------------------------------

            //Data for the IPRs per Type pie chart, using the values and labels from the $charts array passed from the controller, and customizing the hover template to show type, count, and percentage, as well as setting specific colors for each segment to differentiate between types of IPRs effectively
            const typeData = [{
                values: @js($charts['per_type_values']),
                labels: @js($charts['per_type_labels']),
                hovertemplate: '<b>Type:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            //Layout configuration for the IPRs per Type pie chart, including transparent background, margins, legend styling, and font settings to ensure a consistent and visually appealing presentation of the chart, while also making it easy for users to understand the distribution of IPRs by type
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

            //Render the IPRs per Type pie chart in the designated HTML element with the specified data, layout, and common configuration to create an interactive and informative visualization of the distribution of IPRs by type, allowing users to easily compare the different types of IPRs and their respective counts and percentages
            Plotly.newPlot('iprPerType', typeData, typeLayout, commonConfig);

            //---------------------------------------------------------------------------

            //
            const unitlabels = @js($charts['per_unit_labels']);//Gets the label to be shown in the legends
            const unitvalues = @js($charts['per_unit_values']);//Gets the values to be shown in the pie chart
            const unitFullNames = @js($charts['per_unit_names']); // Gets the full names for the hover info

            //Creates an array of custom data for the IPRs per Unit pie chart, where each entry is an array containing the full name of the unit. This custom data will be used in the hover template to display the full unit name when hovering over each segment of the pie chart, enhancing the user experience by providing more detailed information about each unit without cluttering the chart with long labels
            const customDataArray = unitFullNames.map(name => [name]);

            //Assign specific colors to each unit based on the provided mapping, with a default color of black for any units not listed in the mapping. This allows for a visually distinct representation of each unit in the pie chart, making it easier for users to differentiate between them and understand the distribution of IPRs across different units
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
                'URPO-PhilSCAT': '#ffc954', //Light Orange
                'URPO-PDAC': '#f3b0ff', //Light Violet
                'URPO-PCC': '#ffb0e1', //Light Pink
            };

            //Create an array of colors for the IPRs per Unit pie chart by mapping each unit label to its corresponding color in the color mapping, using a default color of black for any units that do not have a specified color in the mapping. This ensures that all units are represented with a color in the pie chart, while also maintaining a consistent and visually appealing color scheme that helps users easily identify and differentiate between the various units
            const colors1 = unitlabels.map(label => colorMapping[label] || '#000000');

            //Data for the IPRs per Unit pie chart, using the values and labels from the $charts array passed from the controller, and customizing the hover template to show unit, count, and percentage, as well as setting specific colors for each segment based on the unit to enhance visual differentiation and user understanding of the distribution of IPRs across different units
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

            //Layout configuration for the IPRs per Unit pie chart, including transparent background, margins, legend styling, and font settings to ensure a consistent and visually appealing presentation of the chart, while also making it easy for users to understand the distribution of IPRs by unit and easily identify each unit based on the assigned colors in the legend
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

            //Render the IPRs per Unit pie chart in the designated HTML element with the specified data, layout, and common configuration to create an interactive and informative visualization of the distribution of IPRs by unit, allowing users to easily compare the different units and their respective counts and percentages, while also providing detailed information about each unit through the customized hover template
            Plotly.newPlot('iprPerUnit', unitData, unitLayout, commonConfig);

            //---------------------------------------------------------------------------
            const stackedData = @json($charts['stacked']);
            const colors = ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760'];// Define a set of colors to be used for the stacked bar chart, which will help differentiate between the different series in the chart and enhance visual clarity for users when analyzing the annual trend of IPR initiations across various categories or groups

            //Create an array of traces for the stacked bar chart by mapping over the series data in the stackedData object, where each trace represents
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

            //Create a trace for the total line in the stacked bar chart, which will show the overall trend of total IPR initiations across the years, using a scatter plot with lines and markers to visually distinguish it from the individual series represented by the bars, and applying a specific color and line style to make it easily identifiable in the chart
            const totalLine = {
                x: stackedData.labels.map(String),
                y: stackedData.total_line,
                name: 'Total',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' }
            };

            //Combine the bar traces and the total line trace into a single data array for the stacked bar chart, which will allow us to render both the individual series and the overall trend in the same chart, providing a comprehensive view of the annual IPR initiations and how they contribute to the total over time
            const yearData = [
                ...barTraces, 
                totalLine
            ];

            //Layout configuration for the annual IPRs trend stacked bar chart, including transparent background, margins, legend styling, and font settings to ensure a consistent and visually appealing presentation of the chart, while also making it easy for users to understand the trend of IPR initiations over the years and differentiate between the individual series and the total line through the use of colors and a clear legend
            const yearLayout = {
                height: 275,
                barmode: 'stack',
                plot_bgcolor: 'rgba(0,0,0,0)',
                paper_bgcolor: 'rgba(0,0,0,0)',
                margin: { l: 40, r: 50, b: 50, t: 0 },
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

            //Render the annual IPRs trend stacked bar chart in the designated HTML element with the specified data, layout, and common configuration to create an interactive and informative visualization of the trend of IPR initiations over a 10-year period, allowing users to easily analyze the contributions of different series to the total initiations and understand how the trend has evolved over time, while also providing a clear distinction between the individual series and the overall trend through the use of colors and a well-designed legend
            Plotly.newPlot('iprPerYear', yearData, yearLayout, commonConfig);

            // Add a resize observer to ensure charts resize correctly when the window size changes or when the sidebar is toggled, by observing the content div and resizing each chart accordingly. This will enhance the user experience by maintaining the readability and visual integrity of the charts across different screen sizes and layout changes, ensuring that users can always view the charts in an optimal format regardless of how they interact with the page
            const charts = ['iprPerUtil', 'iprPerType', 'iprPerUnit', 'iprPerYear'];
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