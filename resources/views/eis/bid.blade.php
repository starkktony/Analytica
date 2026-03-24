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
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-3 py-2 md:py-0 bg-gray-300 min-h-10 gap-2">
              <div class="font-[650] text-sm md:text-lg">
                  EIS Bid and Awards
              </div>

              <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                  <div class="hidden xl:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                      Year Filters
                  </div>

                  <div class="flex flex-wrap items-center gap-2">
                      <div class="flex items-center gap-2">
                          <span class="text-xs font-medium">Goods and Services:</span>
                          <form action="{{ route('eis.bid') }}" method="GET" id="filterYear">
                            <input type="hidden" name="infrayear" value="{{ $infraSelectedYear }}">
                            <select name="bidyear" onchange="document.getElementById('filterYear').submit()" class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                              @foreach($stats['bidyear'] as $year)
                              <option class="text-xs" value="{{ $year }}" {{ $year == $bidSelectedYear ? 'selected' : '' }}>
                                {{ $year }}
                              </option>
                              @endforeach
                            </select>
                          </form>
                      </div>

                      <div class="flex items-center gap-2">
                          <span class="text-xs font-medium">Infrastructure:</span>
                          <form action="{{ route('eis.bid') }}" method="GET" id="filterForm" class="m-0">
                            <input type="hidden" name="bidyear" value="{{ $bidSelectedYear }}">
                            <select name="infrayear" onchange="document.getElementById('filterForm').submit()" 
                              class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs">
                              @foreach($stats['infrayear'] as $year)
                                <option class="text-xs" value="{{ $year }}" {{ $year == $infraSelectedYear ? 'selected' : '' }}>
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

      <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-6 py-2 min-h-10 font-(family-name: --font-inter) gap-4">
          <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-full md:w-60 flex items-center justify-center">
              <div class="font-[650] text-white text-md">Goods and Services</div>
          </div>
      </div>

    <div class="px-6 pt-2">
      <div class="grid grid-cols-12 gap-2 mb-2">
          <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class="grid grid-rows-2 bg-green-600/80  backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
              <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                <i class="fa-solid fa-money-bills text-green-600 text-3xl"></i>
              </div>
              <div class='row-span-1 pt-1 sm:pt-2'>
                <p class='text-white text-right text-2xl sm:text-4xl xl:text-5xl font-[650] pr-2'>{{$stats['total_bid']}}</p>
                <p class='text-right text-white text-[11px] md:text-[12px] font-[550] pr-2'>Total Public Bids</p>
              </div>
            </div>
          </div>
          <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-regular fa-circle-check text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm:pt-2'>
                  <p class='text-2xl sm:text-4xl xl:text-5xl text-right font-[650] pr-2 text-gray-800'>{{$stats['completed_bid']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Completed Bids</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-clock-rotate-left text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm:pt-2'>
                  <p class='text-2xl sm:text-4xl xl:text-5xl text-right font-[650] pr-2 text-gray-800'>{{$stats['ongoing_bid']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Ongoing Bids</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-regular fa-circle-xmark text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm:pt-2'>
                  <p class='text-2xl sm:text-4xl xl:text-5xl text-right font-[650] pr-2 text-gray-800'>{{$stats['failed_bid']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Failed Bids</p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-4 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-clipboard-check text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-2'>
                  <p class='text-sm xs:text-lg sm:text-xl xl:text-3xl text-right font-[650] pr-2 text-gray-800'>₱ {{$stats['appr_budget']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Total Approved Budget</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 lg:col-span-4 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-money-bill text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-2'>
                  <p class='text-sm xs:text-lg sm:text-xl xl:text-3xl text-right font-[650] pr-2 text-gray-800'>₱ {{$stats['bid_amount']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Total Bid Amount</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 lg:col-span-4 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-percent text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-2'>
                  <p class='text-sm xs:text-lg sm:text-xl xl:text-3xl text-right font-[650] pr-2 text-gray-800'>{{$stats['bid_ratio']}}%</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Bid-to-Budget Ratio</p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-md xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Yearly Public Bids per Status
            </div>
            <div>
              <div id="perStatusGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-md xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Approved Budget and Bid Amount per Year
            </div>
            <div>
              <div id="budgetAmtGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>
      </div>

      <div class="flex flex-col md:flex-row items-start md:items-center justify-between px-1 py-2 min-h-10 font-(family-name: --font-inter) gap-4">
          <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-full md:w-60 flex items-center justify-center">
              <div class="font-[650] text-white text-md">Infrastructure</div>
          </div>
      </div>

      <div class="grid grid-cols-12 gap-2 mb-4">
      <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class="grid grid-rows-2 bg-green-600/80  backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
              <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                <i class="fa-solid fa-money-bills text-green-600 text-3xl"></i>
              </div>
              <div class='row-span-1 pt-1 sm:pt-2'>
                <p class='text-white text-right text-2xl sm:text-4xl xl:text-5xl font-[650] pr-2'>{{$stats['total_infra']}}</p>
                <p class='text-right text-white text-[11px] md:text-[12px] font-[550] pr-2'>Total Public Bids</p>
              </div>
            </div>
          </div>
          <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-regular fa-circle-check text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm:pt-2'>
                  <p class='text-2xl sm:text-4xl xl:text-5xl text-right font-[650] pr-2 text-gray-800'>{{$stats['completed_infra']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Completed Bids</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-clock-rotate-left text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm:pt-2'>
                  <p class='text-2xl sm:text-4xl xl:text-5xl text-right font-[650] pr-2 text-gray-800'>{{$stats['ongoing_infra']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Ongoing Bids</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 sm:col-span-6 lg:col-span-3 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-regular fa-circle-xmark text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm:pt-2'>
                  <p class='text-2xl sm:text-4xl xl:text-5xl text-right font-[650] pr-2 text-gray-800'>{{$stats['failed_infra']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Failed Bids</p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-4 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-clipboard-check text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-2'>
                  <p class='text-sm xs:text-lg sm:text-xl xl:text-3xl text-right font-[650] pr-2 text-gray-800'>₱ {{$stats['infra_budget']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Total Approved Budget</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 lg:col-span-4 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-money-bill text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-2'>
                  <p class='text-sm xs:text-lg sm:text-xl xl:text-3xl text-right font-[650] pr-2 text-gray-800'>₱ {{$stats['infra_amount']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Total Bid Amount</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 lg:col-span-4 ">
            <div class='border-l-5  border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3  overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-percent text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-2'>
                  <p class='text-sm xs:text-lg sm:text-xl xl:text-3xl text-right font-[650] pr-2 text-gray-800'>{{$stats['infra_ratio']}}%</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-2'>Bid-to-Budget Ratio</p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-md xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Yearly Public Bids per Status
            </div>
            <div>
              <div id="infraStatusGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-md xl:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Approved Budget and Bid Amount per Year
            </div>
            <div>
              <div id="infrabudgetAmtGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>
        </div>      
    </div>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

        const statusData = [
            {
                x: @js($charts['bidLabel']->values()),
                y: @js($charts['bidComp']),
                name: 'Completed',
                type: 'bar',
                marker: { color: '#01ac42' },
                width: 0.5,
            },
            {
                x: @js($charts['bidLabel']->values()),
                y: @js($charts['bidOngoing']),
                name: 'Ongoing',
                type: 'bar',
                marker: { color: '#F2EA00' },
                width: 0.5,
            },
            {
                x: @js($charts['bidLabel']->values()),
                y: @js($charts['bidFail']),
                name: 'Failed',
                type: 'bar',
                marker: { color: '#4E98FF' },
                width: 0.5,
            },
            {
                x: @js($charts['bidLabel']->values()),
                y: @js($charts['bidTotal']),
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' },
                name: 'Total'
            }
        ];

        const statusLayout = {
            height: 240,
            barmode: 'stack',
            plot_bgcolor: 'rgba(0,0,0,0)',
            paper_bgcolor: 'rgba(0,0,0,0)',
            margin: { l: 40, r: 40, b: 30, t: 10 },
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

        Plotly.newPlot('perStatusGraph', statusData, statusLayout, commonConfig);

        const amtData = [
            {
                x: @js($charts['bidYear']->values()),
                y: @js($charts['bidAppr']),
                hovertemplate: 'Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' },
                name: 'Approved Budget'
            },
            {
                x: @js($charts['bidYear']->values()),
                y: @js($charts['bidAmt']),
                hovertemplate: 'Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#01ac42' },
                line: { shape: 'spline' },
                name: 'Bid Amount'
            }
        ];

        const amtLayout = {
            height: 240,
            plot_bgcolor: 'rgba(0,0,0,0)',
            paper_bgcolor: 'rgba(0,0,0,0)',
            margin: { l: 60, r: 40, b: 30, t: 10 },
            legend: {
                    font: {size: 10, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
            xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 11 } },
            yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', tickprefix: '₱', tickformat: ',.2s' }
        };

        Plotly.newPlot('budgetAmtGraph', amtData, amtLayout, commonConfig);

        console.log(@js($charts['infraComp']));
        console.log(@js($charts['infraTotal']));
        const infrastatData = [
            {
                x: @js($charts['infraLabel']->values()),
                y: @js($charts['infraComp']),
                name: 'Completed',
                type: 'bar',
                marker: { color: '#01ac42' },
                width: 0.6,
            },
            {
                x: @js($charts['infraLabel']->values()),
                y: @js($charts['infraOngoing']),
                name: 'Ongoing',
                type: 'bar',
                marker: { color: '#F2EA00' },
                width: 0.6,
            },
            {
                x: @js($charts['infraLabel']->values()),
                y: @js($charts['infraFail']),
                name: 'Failed',
                type: 'bar',
                marker: { color: '#4E98FF' },
                width: 0.6,
            },
            {
                x: @js($charts['infraLabel']->values()),
                y: @js($charts['infraTotal']),
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' },
                name: 'Total'
            }
        ];

        const infrastatLayout = {
            height: 240,
            barmode: 'stack',
            plot_bgcolor: 'rgba(0,0,0,0)',
            paper_bgcolor: 'rgba(0,0,0,0)',
            margin: { l: 40, r: 40, b: 30, t: 10 },
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

        Plotly.newPlot('infraStatusGraph', infrastatData, infrastatLayout, commonConfig);

        const infraamtData = [
            {
                x: @js($charts['infraYear']->values()),
                y: @js($charts['infraAppr']),
                hovertemplate: 'Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#00702B' },
                line: { shape: 'spline' },
                width: .5,
                name: 'Approved Budget'
            },
            {
                x: @js($charts['infraYear']->values()),
                y: @js($charts['infraAmt']),
                hovertemplate: 'Year:</b> %{x}<br><b>Budget: </b>₱%{y:,.2f}<extra></extra>',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#01ac42' },
                line: { shape: 'spline' },
                width: .5,
                name: 'Bid Amount'
            },
            
        ];

        const infraamtLayout = {
            height: 240,
            plot_bgcolor: 'rgba(0,0,0,0)',
            paper_bgcolor: 'rgba(0,0,0,0)',
            margin: { l: 60, r: 40, b: 30, t: 10 },
            legend: {
              font: {size: 10, color: 'black'},
              orientation: 'h',
              x: 0.5,
              y: 1,
              xanchor: 'center', 
              yanchor: 'bottom'   
            },
            xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 11 } },
            yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', tickprefix: '₱', tickformat: ',.2s'}
        };

        Plotly.newPlot('infrabudgetAmtGraph', infraamtData, infraamtLayout, commonConfig);
    </script>
</body>
</html>