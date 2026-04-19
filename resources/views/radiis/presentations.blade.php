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
                Research Presentations ({{$selectedYear}})
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>

                //Year filter
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

                    //Group by filter for the time series chart
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

    //START OF THE DASHBOARD CONTENT------------------------------------------------------
    <div class="px-6 pt-4">
        //Card for total number of presentations in the selected year, with percentage change from the previous year and an icon representing presentations, styled with Tailwind CSS for a modern look and responsive design, providing a quick overview of the presentation statistics for the selected year at a glance
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

        //Cards for presentations per category, level, type, and unit, each containing a Plotly pie chart to visually represent the distribution of presentations across different categories, levels, types, and units for the selected year, styled with Tailwind CSS for a clean and modern appearance, and designed to be responsive for optimal viewing on various devices, providing insights into the characteristics of the presentations conducted in the selected year
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

        //Card for annual presentations trend over a 10-year period, featuring a Plotly line chart to illustrate the trend of presentations initiated each year, styled with Tailwind CSS for a modern and clean look, and designed to be responsive for optimal viewing on various devices, providing insights into the overall trend of research presentations over the past decade and allowing users to identify any significant increases or decreases in presentation activity over time
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
    //JavaScript code to render the Plotly charts for presentations per category, level, type, unit, and annual trend, using the data passed from the backend and applying a consistent configuration for responsiveness and display options across all charts, while also implementing a resize observer to ensure that the charts resize correctly when the window size changes or when the sidebar is toggled, enhancing the user experience by maintaining the readability and visual integrity of the charts across different screen sizes and layout changes
    <script>
        // Wait for the DOM to fully load before executing the script to ensure that all elements are available for manipulation and that the charts can be rendered correctly without encountering any issues related to missing elements or data, providing a smoother user experience and preventing potential errors during the chart rendering process
        document.addEventListener('DOMContentLoaded', function () {
            // Define a common configuration object for all charts to ensure consistency in responsiveness, display options, and mode bar buttons across all the charts rendered on the page, allowing for a cohesive user experience when interacting with the charts and ensuring that they adapt well to different screen sizes and devices
            const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

            //most parts here are the same as what was in iprights.blade.php, just with different data and different chart types (pie charts instead of bar charts), so I won't add comments for the repeated parts, but I will add comments for the complex parts to explain what they do
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
            const unitlabels = @js($charts['per_unit_labels']);//Gets the label to be shown in the legends
            const unitvalues = @js($charts['per_unit_values']);//Gets the values for the pie chart segments
            const unitFullNames = @js($charts['per_unit_names']); //Gets the full names of the units for hover information

            //Create a custom data array for the pie chart segments by mapping the unit full names to an array format that can be used in the hovertemplate to display the full unit name when hovering over each segment of the pie chart, enhancing the user experience by providing more detailed information about each segment in a clear and organized manner
            const customDataArray = unitFullNames.map(name => [name]);

            //Define a color mapping object that associates each unit label with a specific color code, which will be used to color the segments of the pie chart according to their corresponding unit labels, allowing for a visually distinct representation of each unit in the chart and making it easier for users to differentiate between the segments based on their labels
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

            //Create an array of colors for the pie chart segments by mapping the unit labels to their corresponding colors using the color mapping object, and if a label does not have a defined color in the mapping, it defaults to black ('#000000'), ensuring that all segments of the pie chart are colored appropriately based on their labels while also providing a fallback color for any labels that may not be explicitly defined in the color mapping, thus maintaining the visual integrity of the chart even when encountering unexpected or undefined labels
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
            const colors = ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760'];// Define an array of colors to be used for the bars in the stacked bar chart, which will help differentiate between the different series in the chart and enhance the visual appeal, making it easier for users to interpret the data by associating each color with a specific series or category represented in the chart

            //Create an array of trace objects for the stacked bar chart by mapping over the series data in the stackedData object, where each trace represents a series in the chart with its corresponding x and y values, name, type, width, and marker color, allowing for a clear and organized representation of the data in the stacked bar chart while also ensuring that each series is visually distinct through the use of different colors
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

            //Create a trace object for the total line in the chart, which will represent the total count of presentations for each year as a line with markers, allowing users to easily visualize the overall trend of presentations over time and compare it against the individual series represented by the bars in the stacked bar chart, while also providing a clear distinction between the total line and the individual series through the use of a different color and line style
            const totalLine = {
                x: stackedData.labels.map(String),
                y: stackedData.total_line,
                name: 'Total',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' }
            };

            //Combine the bar traces and the total line trace into a single data array for the chart, allowing both the individual series represented by the bars and the overall trend represented by the total line to be displayed together in the same chart, providing a comprehensive view of the presentation data over time and enabling users to analyze both the individual contributions of each series and the overall trend in presentations across the years
            const yearData = [
                ...barTraces, 
                totalLine
            ];

            //Define the layout for the annual presentations trend chart, specifying properties such as height, bar mode for stacking, background colors, margins, legend configuration, and axis styling to ensure that the chart is visually appealing, easy to read, and effectively communicates the trends in presentation data over the 10-year period, while also maintaining consistency with the overall design of the dashboard and enhancing the user experience when interacting with the chart
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

            // Add a resize observer to ensure that the charts resize correctly when the window size changes or when the sidebar is toggled, enhancing the user experience by maintaining the readability and visual integrity of the charts across different screen sizes and layout changes, while also ensuring that the charts adapt seamlessly to any changes in the available space within the dashboard, providing a consistent and visually appealing presentation of the data regardless of the user's device or window size
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