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
                Research Linkages ({{$selectedYear}})
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden sm:block font-[650] border-r border-gray-500 pr-4">
                    Filter
                </div>

                //Year filter
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs md:text-sm font-medium">Year:</span>
                        <form action="{{ route('radiis.linkages') }}" method="GET" id="filterForm" class="m-0">
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
                        <form action="{{ route('radiis.linkages') }}#time-series-section" method="GET" id="filterGroup" class="m-0">
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                            <select name="group_by" onchange="document.getElementById('filterGroup').submit()" 
                                class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                <option class="text-xs" value="category" {{ $selectedGroup == 'category' ? 'selected' : '' }}>Per Category</option>
                                <option class="text-xs" value="level" {{ $selectedGroup == 'level' ? 'selected' : '' }}>Per Level</option>
                                <option class="text-xs" value="type" {{ $selectedGroup == 'type' ? 'selected' : '' }}>Per Type</option>
                                <option class="text-xs" value="status" {{ $selectedGroup == 'status' ? 'selected' : '' }}>Per Status</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    //START OF THE DASHBOARD CONTENT------------------------------------------------------
    <div class="px-6">
        //New Linkages Card with percentage change from previous year, styled with Tailwind CSS for a modern and clean look, including a colored border to indicate the positive or negative change, and a clear display of the number of new linkages established in the selected year along with the percentage change compared to the previous year, providing users with an at-a-glance understanding of the trend in linkages establishment over time
        <div class="grid grid-cols-4 md:grid-cols-12 gap-3 mb-2">
            <div class="col-span-4 md:col-span-6 lg:col-span-6 xl:col-span-4">       
                <div class='border-l-[5px] border-green-600 bg-white/50 backdrop-blur-md h-45 sm:h-44 rounded-lg shadow-xl p-3 mt-3 overflow-hidden'>
                    <div class='grid grid-rows-4 h-full'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-handshake text-white text-3xl"></i>
                        </div>
                        <div class='row-span-3 pt-4'>
                            <p class='text-4xl sm:text-5xl text-right font-[750] pr-4 align-bottom text-gray-800'>{{ $stats['new_link'] }}</p>
                            <p class='text-[12px] md:text-[12px] text-right pr-4 font-medium'><span class="{{ $percentages['year_percent'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $percentages['year_percent'] > 0 ? '▲ ' : '▼ ' }}{{ $percentages['year_percent'] }}%</span></p>
                            <p class='text-[10px] md:text-[12px] mb-8 text-right font-medium pr-4'>New Linkages in {{ $stats['max_year'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        //Grid layout for the various pie charts and the time series chart, styled with Tailwind CSS for a modern and clean look, including colored borders to indicate the different categories of linkages, and clear titles for each chart to provide users with an at-a-glance understanding of the distribution of linkages across different categories, types, statuses, levels, and units, as well as the annual trend of linkages establishment over a 10-year period, allowing users to easily analyze the data and identify patterns and trends in linkages establishment over time
        <div class="grid grid-cols-4 lg:grid-cols-12 gap-3">
            <div class="col-span-4 lg:col-span-6 h-100 lg:h-136 border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Linkages per Category</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="linkPerCategory" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 lg:col-span-6 h-100 lg:h-136 border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Linkages per Type</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="linkPerType" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 lg:col-span-6 xl:col-span-4 h-90 lg:h-136 border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Linkages per Status</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="linkPerStatus" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 lg:col-span-6 xl:col-span-4 h-90 lg:h-136 border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Linkages per Level</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="linkPerLevel" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 lg:col-span-6 xl:col-span-4 h-90 lg:h-136 border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>Linkages per Unit</div>
                    <div class='row-span-6 h-full w-full'>
                        <div id="linkPerUnit" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>

        //Time series section for annual linkages establishment trend, styled with Tailwind CSS for a modern and clean look, including a colored border to indicate the positive trend in linkages establishment over time, and a clear title to provide users with an at-a-glance understanding of the annual trend of linkages establishment over a 10-year period, allowing users to easily analyze the data and identify patterns and trends in linkages establishment over time, while also providing a note to inform users about the availability of data for certain years
        <div id="time-series-section" class="grid grid-cols-4 lg:grid-cols-12 gap-3 mt-2">
            <div class="col-span-6 md:col-span-12">
                <div class="border-l-[6px] border-green-600 bg-white rounded-[1vw] shadow-inner shadow-xl h-[400px] sm:h-[352px] md:h-[354px] mb-8">
                <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 sm:pl-7'>
                        Annual Linkages Establishment Trend (10-year period)
                    </div>
                    <div>
                    <div id="linkPerYear" style="width: 100%; "></div>
                    </div>    
                    <div class="text-[8px] sm:text-[10px] text-gray-500/90 pl-6">
                        <i>Note: Data for certain years is unavailable; only years with recorded initiations are displayed.</i>
                    </div>         
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    //JavaScript code to initialize the Plotly charts for linkages per category, type, status, level, unit, and the annual trend of linkages establishment, using the data passed from the backend and applying a common configuration for responsiveness and styling, while also adding a resize observer to ensure that the charts resize correctly when the window size changes or when the sidebar is toggled, enhancing the user experience by maintaining the readability and visual integrity of the charts across different screen sizes and layout changes
    <script>
        // Wait for the DOM to load before initializing the charts to ensure that the HTML elements are available for rendering the charts
        document.addEventListener('DOMContentLoaded', function () {

            // Define a common configuration object for all charts to ensure consistency in responsiveness, display options, and mode bar buttons across all the charts rendered on the page, allowing for a cohesive user experience when interacting with the charts and ensuring that they adapt well to different screen sizes and devices
            const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

            //most parts here are the same as what was in iprights.blade.php, just with different data and different chart types (pie charts instead of bar charts), so I won't add comments for the repeated parts, but I will add comments for the complex parts to explain what they do
            const catData = [{
                values: @js($charts['per_cat_values']),
                labels: @js($charts['per_cat_labels']),
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
                    font: {size: 12, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('linkPerCategory', catData, catLayout, commonConfig);

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
                    font: {size: 12, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
                font: { family: 'Inter, sans-serif' }
            };

            Plotly.newPlot('linkPerType', typeData, typeLayout, commonConfig);

            //---------------------------------------------------------------------------

            const statData = [{
                values: @js($charts['per_status_values']),
                labels: @js($charts['per_status_labels']),
                hovertemplate: '<b>Status:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            const statLayout = {
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

            Plotly.newPlot('linkPerStatus', statData, statLayout, commonConfig);

            //---------------------------------------------------------------------------

            const levData = [{
                values: @js($charts['per_level_values']),
                labels: @js($charts['per_level_labels']),
                hovertemplate: '<b>Level:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
            }];

            const levLayout = {
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

            Plotly.newPlot('linkPerLevel', levData, levLayout, commonConfig);

            //---------------------------------------------------------------------------

            const unitlabels = @js($charts['per_unit_labels']);//Gets the label to be shown in the legends
            const unitvalues = @js($charts['per_unit_values']);//Gets the values to be shown in the pie chart
            const unitFullNames = @js($charts['per_unit_names']); //Gets the full names of the units to be shown in the hovertemplate, since some unit names are too long and need to be shortened for the legends, but we still want to show the full names in the hovertemplate for better understanding

            //Create a custom data array that contains the full names of the units, which will be used in the hovertemplate to show the full name of the unit when hovering over the pie chart, allowing users to easily identify the units without having to rely on the shortened labels in the legends, and providing a better user experience by giving more context and information about each unit when interacting with the chart
            const customDataArray = unitFullNames.map(name => [name]);

            //Define a color mapping object that maps each unit label to a specific color, which will be used to assign consistent colors to the pie chart segments based on the unit labels, enhancing the visual distinction between different units in the chart and making it easier for users to identify and differentiate between them at a glance, while also providing a visually appealing and organized representation of the data in the pie chart
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
                'OP': '#00ad20', //Green2
                'OVPAA': '#420057', //Dark Violet
                'OVPRET': '#57001e', //Burgundy
                'OVPAd': '#ff6161', //Light Red
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
            
            Plotly.newPlot('linkPerUnit', unitData, unitLayout, commonConfig);

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
                margin: { l: 40, r: 40, b: 50, t: 20 },
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

            Plotly.newPlot('linkPerYear', yearData, yearLayout, commonConfig);

            // Add a resize observer to the content div to ensure that the charts resize correctly when the window size changes or when the sidebar is toggled, enhancing the user experience by maintaining the readability and visual integrity of the charts across different screen sizes and layout changes
            const charts = ['linkPerCategory', 'linkPerType', 'linkPerStatus', 'linkPerLevel', 'linkPerUnit', 'linkPerYear'];
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