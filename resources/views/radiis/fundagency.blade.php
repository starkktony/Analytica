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
                Research Funding Agencies
            </div>
        </div>
    </div>

    <div class="px-6 pt-4">
        <div class="grid grid-cols-4 xl:grid-cols-12 gap-2 mb-2">
            <div class="col-span-4 md:col-span-2 xl:col-span-3">       
                <div class='bg-green-600/80 backdrop-blur-md rounded-lg h-[160px] xl:h-[190px] inset-shadow-xs shadow-xl p-3'>
                    <div class='grid grid-rows-12 h-full'>
                        <div class='bg-white row-span-4 rounded-lg h-12 w-18 flex items-center justify-center'>
                            <i class="fa-solid fa-building-columns text-green-600 text-3xl"></i>
                        </div>
                        <div class='row-span-8 pt-[8px] xl:pt-[28px] pr-[8px]'>
                            <p class='text-white text-right text-4xl md:text-5xl font-[650]'>{{ $total_agency }}</p>
                            <p class='text-right text-white text-[11px] lg:text-[13px] font-[550]'>Total Number of Funding Agencies</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 md:col-span-2 xl:col-span-3">
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-[160px] xl:h-[190px] rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-12 h-full'>
                        <div class='bg-green-600/80 row-span-4 rounded-lg h-12 w-18 flex items-center justify-center'>
                            <i class="fa-solid fa-money-bills text-white text-3xl"></i>
                        </div>
                        <div class='row-span-8 pt-[16px] xs:pt-[8px] xl:pt-[28px] pr-[8px]'>
                            <p class='text-base xs:text-lg sm:text-2xl text-right font-[650] text-gray-800 pt-3 lg:pt-2'>₱{{ number_format($total_fund) }}</p>
                            <p class='text-[10px] lg:text-[12px] mb-8 text-right font-medium'>Total Accumulated Funds</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 md:col-span-6">
                <div class="grid grid-rows-12 h-[200px] xs:h-[160px] xl:h-[190px] gap-y-1">
                    <div class='row-span-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <div class="flex items-center justify-center min-h-7 font-(family-name: --font-inter)">
                            <div class="text-[9px] md:text-[15px] font-[550] text-white">
                               Top 3 Agencies with Highest Given Fund
                            </div>
                        </div>
                    </div>
                    <div class='grid grid-cols-12 row-span-4 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>        
                        <p class='col-span-2 text-[15px] xs:text-[30px] md:text-[40px] font-[650] text-green-700 text-right flex justify-center items-center'>1</p>
                        <p class='col-span-4 text-[14px] xs:text-[18px] md:text-[20px] text-right text-green-700 font-bold flex justify-center items-center'>{{ $top_names[0] }}</p>
                        <p class='col-span-6 text-[11px] xs:text-[14px] md:text-[16px] text-right text-green-700 font-bold flex justify-center items-center'>₱{{ number_format($top_totals[0]) }}</p>
                    </div>
                    <div class='grid grid-cols-12 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-2 text-[14px] xs:text-[25px] md:text-[30px] text-right text-gray-800 font-[650] flex justify-center items-center'>2</p>
                        <p class='col-span-4 text-[11px] xs:text-[14px] md:text-[14px] text-right text-gray-800 font-semibold flex justify-center items-center'>{{ $top_names[1] }}</p>
                        <p class='col-span-6 text-[11px] xs:text-[12px] md:text-[12px] text-right text-gray-800 font-semibold flex justify-center items-center'>₱{{ number_format($top_totals[1]) }}</p>
                    </div>
                    <div class='grid grid-cols-12 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-2 text-[13px] xs:text-[20px] md:text-[28px] text-right text-gray-800 font-[650] flex justify-center items-center'>3</p>
                        <p class='col-span-4 text-[11px] xs:text-[12px] md:text-[12px] text-right text-gray-800 font-medium flex justify-center items-center'>{{ $top_names[2] }}</p>
                        <p class='col-span-6 text-[11px] xs:text-[11px] md:text-[11px] text-right text-gray-800 font-medium flex justify-center items-center'>₱{{ number_format($top_totals[2]) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 lg:grid-cols-12 gap-3 mb-8">
            <div class="col-span-4 h-90 lg:h-110 xl:h-120 border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Agencies per Category</div>
                    <div class='row-span-6 h-full w-full pt-2'>
                        <div id="agencyPerCategory" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 h-90 lg:h-110 xl:h-120 border-t-6 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Agencies per Type</div>
                    <div class='row-span-6 h-full w-full pt-2'>
                        <div id="agencyPerType" style="width: 100%; height: 100%;"></div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 h-90 lg:h-110 xl:h-120 border-t-6 border-green-600 bg-white rounded-[1vw] mb-2 inset-shadow-xl shadow-xl">
                <div class='grid grid-rows-7 h-full'>
                    <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Agencies per Sector</div>
                    <div class='row-span-6 h-full w-full pt-2'>
                        <div id="agencyPerSector" style="width: 100%; height: 100%;"></div>
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

            const catData = [{
                values: @js($charts['per_category_values']),
                labels: @js($charts['per_category_labels']),
                hovertemplate: '<b>Category:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00'] }
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

            Plotly.newPlot('agencyPerCategory', catData, catLayout, commonConfig);

            //--------------------------------------------------------------------

            const typeData = [{
                values: @js($charts['per_type_values']),
                labels: @js($charts['per_type_labels']),
                hovertemplate: '<b>Type:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#01ac42', '#FFEB00'] }
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

            Plotly.newPlot('agencyPerType', typeData, typeLayout, commonConfig);

            //---------------------------------------------------------------------------

            const sectData = [{
                values: @js($charts['per_sect_values']),
                labels: @js($charts['per_sect_labels']),
                hovertemplate: '<b>Sector:</b> %{label} <br><b>Count:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
                type: 'pie',
                outsidetextfont: {color: 'transparent'},
                marker: { colors: ['#FFEB00', '#01ac42'] }
            }];

            const sectLayout = {
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

            Plotly.newPlot('agencyPerSector', sectData, sectLayout, commonConfig);

            const charts = ['agencyPerCategory', 'agencyPerType', 'agencyPerSector'];
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
        })
    </script>
</body>
</html>