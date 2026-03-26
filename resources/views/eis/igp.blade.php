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

        header {
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
            <span class="text-2xl font-[650] text-white">Executive Information</span>
        </header>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 font-(family-name: --font-inter) gap-4">
            <div class="font-[650] text-sm md:text-lg">
                EIS Income Generating Project
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden xl:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                    Year Filters
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">Cash on Bank:</span>
                        <form action="{{ route('eis.igp') }}" method="GET" id="filterYear" class="m-0">
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
    </div>

    <div class="px-6 pt-2">
        <div class='grid grid-cols-12 mb-2'>
            <div class="col-span-12 lg:col-span-5 xl:col-span-4 rounded-3xl mt-2 p-2 bg-green-600/80 flex item-center justify-center">
                <div class="font-[650] text-white text-md">Cash on Bank</div>
            </div>
        </div>

        <div class="grid grid-cols-4 lg:grid-cols-12 gap-2 mb-2">
            <div class="col-span-4 xl:col-span-3 h-80">
                        <div class="grid grid-rows-2 h-35 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                            <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                                <i class="fa-solid fa-money-bills text-green-600 text-3xl"></i>
                            </div>
                            <div class='row-span-1'>
                                <p class='text-white text-right text-xl sm:text-3xl lg:text-xl xl:text-2xl font-[650] pr-1'>₱ {{number_format($stats['currbal'])}}</p>
                                <p class='text-right text-white text-[12px] md:text-[11px] xl:text-[13px] font-[550] pr-1'>Cash on Bank Balance</p>
                                <p class='text-right text-white text-[10px] md:text-[11px] font-[550] pr-1'>as of {{$stats['currdate']}}</p>
                            </div>
                        </div>
                        <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-35 rounded-lg mt-2 inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                            <div class='grid grid-rows-2 '>
                                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                                    <i class="fa-solid fa-money-bill-transfer text-white text-3xl"></i>
                                </div>
                                <div class='row-span-1 pt-1'>
                                    <p class='text-lg sm:text-2xl lg:text-lg xl:text-2xl text-right pr-1 font-[650] align-bottom text-gray-800'> <span class="{{ $stats['netflow'] > 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $stats['netflow'] > 0 ? '+ ' : ' ' }} ₱ {{number_format($stats['netflow'])}} </span></p>
                                    <p class='text-[10px] md:text-[12px] text-right font-medium pr-1'>Net Cash Flow</p>
                                </div>
                            </div>
                        </div>        
            </div>
            <div class="col-span-4 lg:col-span-8 xl:col-span-9 border-t-[4px] h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-md xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
                Bank Balance Over Time
                </div>
                <div>
                <div id="bankBalance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
                <i>Note: Only dates with recorded initiations are displayed.</i>
                </div>  
            </div>
        </div>

        <div class='grid grid-cols-12'>
            <div class="col-span-12 lg:col-span-5 xl:col-span-4 rounded-3xl mt-2 mb-2 p-2 bg-green-600/80 flex item-center justify-center">
                <div class="font-[650] text-white text-md">Existing MOAs</div>
            </div>
        </div>

        <div class="grid grid-cols-4 lg:grid-cols-12 gap-3 mb-2">
            <div class='col-span-4'>
                <div class='bg-green-600/80 backdrop-blur-md h-36 lg:h-40 rounded-lg inset-shadow-xs shadow-xl p-3'>
                    <div class='grid grid-rows-2'>
                        <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-building text-green-600 text-3xl"></i>
                        </div>
                        <div class='row-span-1 pt-1'>
                            <p class='text-white text-right text-4xl xs:text-xl sm:text-4xl font-[650] pr-1'>{{$stats['priv_total']}}</p>
                            <p class='text-right text-white text-[9px] xs:text-[11px] md:text-[10px] xl:text-[12px] font-[550] pr-1'>Private Comapanies Landholdings</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-36 lg:h-40 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-2'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-calendar-check text-white text-3xl"></i>
                        </div>
                        <div class='row-span-1 pt-2'>
                            <p class='text-2xl xs:text-xl sm:text-3xl text-right font-[650] align-bottom text-gray-800 pr-1'>{{number_format($stats['priv_sqm'])}}</p>
                            <p class='text-[8px] xs:text-[10px] md:text-[10px] xl:text-[12px] text-right font-medium pr-1'>Total Landholdings with Active MOAs</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-36 lg:h-40 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='grid grid-rows-2'>
                        <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-money-bill text-white text-3xl"></i>
                        </div>
                        <div class='row-span-1 pt-2'>
                            <p class='text-2xl xs:text-xl sm:text-3xl text-right font-[650] pr-1 align-bottom text-gray-800'>₱ {{number_format($stats['priv_rate'])}}</p>
                            <p class='text-[9px] xs:text-[10px] md:text-[10px] xl:text-[11px] text-right font-medium pr-1'>Expected Total Rate per Month</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 lg:grid-cols-12 gap-3 mb-2">
            <div class='col-span-4'>
                <div class='border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[300px] sm:h-[400px] lg:h-[380px] xl:h-[500px] rounded-[1vw] shadow-inner shadow-xl'>
                <div class='w-full grid grid-cols-12 grid-rows-7'>
                    <div class='col-span-12 row-span-1 font-[750] text-xs sm:text-sm xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 xs:pl-7'>Landholdings per Status</div>
                    <div class='col-span-12 row-span-6'>
                        <div id='privStatusChart' class='w-full h-full'></div>
                    </div>
                </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[300px] sm:h-[400px] lg:h-[380px] xl:h-[500px] rounded-[1vw] shadow-inner shadow-xl'>
                <div class='w-full grid grid-cols-12 grid-rows-7'>
                    <div class='col-span-12 row-span-1 font-[750] text-xs sm:text-sm xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 xs:pl-7'>Landholdings per Sqm</div>
                    <div class='col-span-12 row-span-6'>
                        <div id='privSqmChart' class='w-full h-full'></div>
                    </div>
                </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='border-t-[6px] border-green-600 bg-linear-to-br bg-white flex flex-wrap h-[300px] sm:h-[400px] lg:h-[380px] xl:h-[500px] rounded-[1vw] shadow-inner shadow-xl'>
                <div class='w-full grid grid-cols-12 grid-rows-7'>
                    <div class='col-span-12 row-span-1 font-[750] text-xs sm:text-sm xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-5 xs:pl-7'>Landholdings per Rate</div>
                    <div class='col-span-12 row-span-6'>
                        <div id='privRateChart' class='w-full h-full'></div>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-3 mb-2">
            <div class="col-span-12 lg:col-span-6 xl:col-span-4">
                <div class='bg-green-600/80 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3'>
                    <div class='grid grid-rows-2 '>
                        <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-landmark-dome text-green-600 text-3xl"></i>
                        </div>
                        <div class='row-span-1'>
                            <p class='text-white text-right text-4xl xs:text-xl sm:text-4xl font-[650] pr-2'>{{$stats['gov_total']}}</p>
                            <p class='text-right text-white text-[8px] xs:text-[11px] md:text-[10px] xl:text-[12px] font-[550] pr-2'>Government Agencies Landholdings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class='grid grid-cols-12'>
            <div class="col-span-12 lg:col-span-5 xl:col-span-4 rounded-3xl mt-6 mb-2 p-2 bg-green-600/80 flex item-center justify-center">
                <div class="font-[650] text-white text-md">Business Development Office</div>
            </div>
        </div>

        <div class="grid grid-cols-4 lg:grid-cols-12 gap-3 mb-2">
            <div class='col-span-4'>
                <div class='bg-green-600/80 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3'>
                    <div class='grid grid-rows-2'>
                        <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-store text-green-600 text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-white text-right text-3xl xs:text-xl sm:text-3xl font-[650] pr-2'>{{$stats['gateway_total']}}</p>
                            <p class='text-right text-white text-[8px] xs:text-[11px] md:text-[10px] xl:text-[12px] font-[550] pr-2'>Number of Stalls in Gateway</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='bg-green-600/80 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3'>
                    <div class='grid grid-rows-2 '>
                        <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-store text-green-600 text-3xl"></i>
                        </div>
                        <div class='row-span-1'>
                            <p class='text-white text-right text-3xl xs:text-xl sm:text-3xl font-[650] pr-2'>{{$stats['marketing_total']}}</p>
                            <p class='text-right text-white text-[8px] xs:text-[11px] md:text-[10px] xl:text-[12px] font-[550] pr-2'>Number of Stalls in Marketing Center</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class='col-span-4'>
                <div class='bg-green-600/80 backdrop-blur-md h-36 rounded-lg inset-shadow-xs shadow-xl p-3'>
                    <div class='grid grid-rows-1 '>
                        <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-store text-green-600 text-3xl"></i>
                        </div>
                        <div class='row-span-1'>
                            <p class='text-white text-right text-3xl xs:text-xl sm:text-3xl font-[650] pr-2'>{{$stats['commart_total']}}</p>
                            <p class='text-right text-white text-[8px] xs:text-[11px] md:text-[10px] xl:text-[12px] font-[550] pr-2'>Number of Stals in Community Market</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-2">
            <div class="col-span-12 xl:col-span-2">                
                <div class='border-l-4 border-green-600 bg-white/50 backdrop-blur-md rounded-lg h-[150px] xl:h-[190px] shadow-sm p-4 mb-2 overflow-hidden'>
                    <div class='grid grid-rows-4'>
                        <div class='bg-green-600/80 row-span-2 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-solid fa-money-bill text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-lg sm:text-2xl xl:text-xl text-right font-[650] align-bottom text-gray-800 pt-[8px] sm:pt-[5px] xl:pt-[15px]'>₱ {{number_format($stats['rental_total'])}}</p>
                            <p class='text-[10px] md:text-[11px] text-right font-medium'>Total Rentals per Month</p>
                        </div>
                    </div>
                </div>
                <div class='border-l-4 border-green-600 bg-white/50 backdrop-blur-md rounded-lg h-[150px] xl:h-[190px] shadow-sm p-4 mb-2 overflow-hidden'>
                    <div class='grid grid-rows-4 '>
                        <div class='bg-green-600/80 row-span-2 rounded-lg h-12 w-16 flex items-center justify-center'>
                            <i class="fa-regular fa-calendar-check text-white text-3xl"></i>
                        </div>
                        <div class='row-span-2'>
                            <p class='text-lg sm:text-2xl xl:text-xl text-right font-[650] align-bottom text-gray-800 pt-[8px] sm:pt-[5px] xl:pt-[15px]'>₱ {{number_format($stats['rental_total'])}}</p>
                            <p class='text-[10px] md:text-[12px] text-right font-medium'>Total SQM</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-6 xl:col-span-5">
                <div class='border-t-[6px] border-green-600 bg-white/50 backdrop-blur-md flex flex-wrap h-[386px] rounded-[1vw] inset-shadow-xl shadow-xl'>
                    <div class='w-full grid grid-cols-12 grid-rows-7'>
                        <div class='col-span-12 row-span-1 font-[750] text-xs sm:text-sm xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-7'>Stalls per Rental</div>
                        <div class='col-span-12 row-span-6'>
                            <div id='stallsRentChart' class='w-full h-full'></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-6 xl:col-span-5">
                <div class='border-t-[6px] border-green-600 bg-white/50 backdrop-blur-md flex flex-wrap h-[386px] rounded-[1vw] inset-shadow-xl shadow-xl'>
                    <div class='w-full grid grid-cols-12 grid-rows-7'>
                        <div class='col-span-12 row-span-1 font-[750] text-xs sm:text-sm xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-7'>Stalls per Sqm</div>
                        <div class='col-span-12 row-span-6'>
                            <div id='stallsSqmChart' class='w-full h-full'></div>
                        </div>
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
       
        xData = @js($charts['balance_labels']),
        yData = @js($charts['balance_values']),
        max = @js($charts['bal_max'] +2000000),

        bankData = {   
            x: xData,
            y: yData,
            hovertemplate: '<b>Balance:</b> ₱%{y:,.2f} <br><b>Date:</b> %{x}<extra></extra>',
            type: 'scatter',
            mode: 'lines',
            line:{width: 2},
            fill: 'tozeroy',
            fillcolor: 'rgba(1, 172, 66, 0.5)',
            marker: { color: '#00702B' },
            showlegend: false,
            name: 'Balance'
        }

        lastData = {
            x: [xData[xData.length - (xData.length )]],
            y: [yData[yData.length - (xData.length )]],
            hovertemplate: '<b>Balance:</b> ₱%{y:,.2f} <br><b>Date:</b> %{x}<extra></extra>',
            mode: 'markers',
            marker: {
                color: '#00702B', 
                size: 8     
            },
            name: 'Recent Balance',
            showlegend: false 
            };

        const balData = [bankData, lastData];

    const balLayout = {
        height: 250,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 50, r: 30, b: 40, t: 20 },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels:true, tickfont: { color: '#00702B', size: 9 } },
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', range:[0, max] }
    };

    Plotly.newPlot('bankBalance', balData, balLayout, commonConfig);

        const statusData = [{
            values: [@js($charts['act_priv']), @js($charts['exp_priv'])],
            labels: ['Active', 'Expired'],
            hovertemplate: '<b>Status:</b> %{label} <br><b>Sqm:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
            type: 'pie',
            outsidetextfont: {color: 'transparent'},
            marker: { colors: ['#FFEB00', '#01ac42', '#4e98ff', '#D84CFF', '#f77995']}
        }];

        const statusLayout = {
            plot_bgcolor: 'rgba(0,0,0,0)',
            paper_bgcolor: 'rgba(0,0,0,0)',
            margin: { l: 25, r: 25, b: 15, t: 10 },
            legend: {
            font: {size: 11, color: 'black'},
            orientation: 'h',
            x: 0.5,
            y: 1,
            xanchor: 'center', 
            yanchor: 'bottom',   
        },
            font: { family: 'Inter, sans-serif' }
        };

        Plotly.newPlot('privStatusChart', statusData, statusLayout, commonConfig);
        
        const sqmData = [{
            values: @js($charts['sqm_values']),
            labels: @js($charts['sqm_labels']),
            hovertemplate: '<b>Company:</b> %{label} <br><b>Sqm:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
            type: 'pie',
            outsidetextfont: {color: 'transparent'},
            marker: { colors: ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760']}
        }];

        const sqmLayout = {
            plot_bgcolor: 'rgba(0,0,0,0)',
            paper_bgcolor: 'rgba(0,0,0,0)',
            margin: { l: 25, r: 25, b: 15, t: 10 },
            showlegend: false,
            font: { family: 'Inter, sans-serif' }
        };

        Plotly.newPlot('privSqmChart', sqmData, sqmLayout, commonConfig);

        const rateData = [{
            values: @js($charts['rate_values']),
            labels: @js($charts['rate_labels']),
            hovertemplate: '<b>Company:</b> %{label} <br><b>Rate per Month:</b>₱ %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
            type: 'pie',
            outsidetextfont: {color: 'transparent'},
            marker: { colors: ['#01ac42', '#FFEB00', '#4e98ff', '#D84CFF', '#f77995']}
        }];

        const rateLayout = {
            plot_bgcolor: 'rgba(0,0,0,0)',
            paper_bgcolor: 'rgba(0,0,0,0)',
            margin: { l: 25, r: 25, b: 15, t: 10 },
            showlegend: false,
            font: { family: 'Inter, sans-serif' }
        };

        Plotly.newPlot('privRateChart', rateData, rateLayout, commonConfig);

        const rentData = [{
            values: [@js($charts['gateway_rent']), @js($charts['marketing_rent']), @js($charts['commart_rent'])],
            labels: ['Gateway', 'Marketing Center', 'Community Market'],
            hovertemplate: '<b>Stalls Location:</b> %{label} <br><b>Total Rental:</b>₱ %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
            type: 'pie',
            outsidetextfont: {color: 'transparent'},
            marker: { colors: [ '#4e98ff','#FFEB00','#01ac42', '#D84CFF', '#f77995']}
        }];

        const rentLayout = {
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

        Plotly.newPlot('stallsRentChart', rentData, rentLayout, commonConfig);

        const sqmStallsData = [{
            values: [@js($charts['commart_sqm']), @js($charts['marketing_sqm']), @js($charts['gateway_sqm'])],
            labels: ['Community Market', 'Marketing Center', 'Gateway'],
            hovertemplate: '<b>Stalls Location:</b> %{label} <br><b>Sqm:</b> %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
            type: 'pie',
            outsidetextfont: {color: 'transparent'},
            marker: { colors: [ '#FFEB00', '#01ac42', '#4e98ff', '#D84CFF', '#f77995']}
        }];

        const sqmStallsLayout = {
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

        Plotly.newPlot('stallsSqmChart', sqmStallsData, sqmStallsLayout, commonConfig);
});
    </script> 
</body>

</html>