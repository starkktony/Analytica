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
                EIS Facilities and Development
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden xl:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                    Year Filters
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">Infra. Projects:</span>
                        <form action="{{ route('eis.facility') }}" method="GET" id="filterForm">
                          <input type="hidden" name="gaayear" value="{{ $gaaselectedYear }}">
                          <select name="infrayear" onchange="document.getElementById('filterForm').submit()" class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                            @foreach($stats['inyear'] as $year)
                            <option class="text-xs" value="{{ $year }}" {{ $year == $infraselectedYear ? 'selected' : '' }}>
                              {{ $year }}
                            </option>
                            @endforeach
                          </select>
                        </form>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">GAA Projects:</span>
                        <form action="{{ route('eis.facility') }}" method="GET" id="filterYear">
                          <input type="hidden" name="infrayear" value="{{ $infraselectedYear }}">
                          <select name="gaayear" onchange="document.getElementById('filterYear').submit()" class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                            @foreach($stats['gayear'] as $year)
                            <option class="text-xs" value="{{ $year }}" {{ $year == $gaaselectedYear ? 'selected' : '' }}>
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
      <div class="flex items-center justify-between py-2 min-h-10">
        <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-60 flex item-center justify-center">
          <div class="font-[650] text-white text-md">Infrastructure Projects</div>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-2 mb-2">
          <div class="col-span-12 md:col-span-6 xl:col-span-3 ">
            <div class="grid grid-rows-2 h-39 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
              <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                <i class="fa-solid fa-building text-green-600 text-3xl"></i>
              </div>
              <div class='row-span-2'>
                <p class='text-white text-right text-4xl md:text-3xl lg:text-4xl font-[650] pr-1'>{{$stats['total_infra']}}</p>
                <p class='text-right text-white text-[11px] md:text-[11px] font-[550] pr-1'>Total Infrastructure Projects</p>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <div class='border-l-5 h-38 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-money-bill text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm: pt-0'>
                  <p class='text-lg sm:text-2xl md:text-xl lg:text-3xl xl:text-xl text-right pt-2 font-[650] pr-1 align-bottom text-gray-800'>₱ {{number_format($stats['infra_amount'], 2)}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-1'>Total Contract Amount</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <div class='border-l-5  h-38 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-regular fa-circle-check text-white text-3xl"></i>
                </div>
                <div class='row-span-1'>
                  <p class='text-4xl md:text-3xl lg:text-4xl text-right font-[650] pr-1 align-bottom text-gray-800'>{{$stats['completed_infra']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-1'>Completed Projects</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <div class='border-l-5 h-38 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-clock-rotate-left text-white text-3xl"></i>
                </div>
                <div class='row-span-1'>
                  <p class='text-4xl md:text-3xl lg:text-4xl text-right font-[650] pr-1 align-bottom text-gray-800'>{{$stats['ongoing_infra']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-1'>Ongoing Projects</p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Infrastructure Projects Per Year
            </div>
            <div>
              <div id="infraTotalGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Infrastructure Projects' Budget per Year
            </div>
            <div>
              <div id="infraAmtGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>

          <div class="col-span-12 lg:col-span-6">
                <div class="grid grid-rows-12 h-80 gap-y-1">
                    <div class='row-span-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden flex flex-col justify-center items-center'>
                        <p class='text-[10px] md:text-[14px] text-white font-[750] px-2'>Recent Ongoing Projects</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-1 bg-white/50 backdrop-blur-md rounded-sm inset-shadow-xs shadow-xl overflow-hidden items-center'> 
                      <p class='col-span-6 text-[11px] md:text-[12px] text-gray-800 font-semibold flex justify-center items-center'>Project</p>
                      <p class='col-span-3 text-[11px] md:text-[12px] text-gray-800 font-semibold flex justify-center items-center'>Contractor</p>
                      <p class='col-span-3 text-[11px] md:text-[12px] text-gray-800 font-semibold flex justify-center items-center'>Date</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>        
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($infrarec_title[0],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infrarec_cont[0]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infrarec_date[0]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($infrarec_title[1],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infrarec_cont[1]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infrarec_date[1]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($infrarec_title[2],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infrarec_cont[2]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infrarec_date[2]}}</p>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-6">
                <div class="grid grid-rows-12 h-80 gap-y-1">
                    <div class='row-span-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden flex flex-col justify-center items-center'>
                      <p class='text-[10px] md:text-[14px] text-white font-[750] px-2'>Oldest Ongoing Projects</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-1 bg-white/50 backdrop-blur-md rounded-sm inset-shadow-xs shadow-xl overflow-hidden'>        
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>Project</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>Contractor</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>Date</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>        
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($infraold_title[0],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infraold_cont[0]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infraold_date[0]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($infraold_title[1],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infraold_cont[1]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infraold_date[1]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($infraold_title[2],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infraold_cont[2]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$infraold_date[2]}}</p>
                    </div>
                </div>
              </div>
        </div>

        <div class="flex items-center justify-between px-1 py-2 min-h-10 font-(family-name: --font-inter)">
          <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-60 flex item-center justify-center">
            <div class="font-[650] text-white text-md">GAA Approved Projects</div>
          </div>
        </div>

        <div class="grid grid-cols-12 gap-2 mb-2">
          <div class="col-span-12 md:col-span-6 xl:col-span-3 ">
            <div class="grid grid-rows-2 h-38 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
              <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                <i class="fa-solid fa-building text-green-600 text-3xl"></i>
              </div>
              <div class='row-span-2'>
                <p class='text-white text-right text-4xl md:text-3xl lg:text-4xl font-[650] pr-1'>{{$stats['total_gaa']}}</p>
                <p class='text-right text-white text-[11px] md:text-[11px] font-[550] pr-1'>Total Projects</p>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <div class='border-l-5 h-38 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-money-bill text-white text-3xl"></i>
                </div>
                <div class='row-span-1 pt-1 sm: pt-0'>
                  <p class='text-lg sm:text-2xl md:text-xl lg:text-3xl xl:text-xl text-right pt-2 font-[650] pr-1 align-bottom text-gray-800'>₱ {{number_format($stats['gaa_amount'], 2)}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-1'>Total Contract Amount</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <div class='border-l-5  h-38 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-regular fa-circle-check text-white text-3xl"></i>
                </div>
                <div class='row-span-1'>
                  <p class='text-4xl md:text-3xl lg:text-4xl text-right font-[650] pr-1 align-bottom text-gray-800'>{{$stats['completed_gaa']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-1'>Completed Projects</p>
                </div>
              </div>
            </div>
          </div>
          <div class="col-span-12 md:col-span-6 xl:col-span-3">
            <div class='border-l-5 h-38 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
              <div class='grid grid-rows-2'>
                <div class='bg-green-600/80 row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                  <i class="fa-solid fa-clock-rotate-left text-white text-3xl"></i>
                </div>
                <div class='row-span-1'>
                  <p class='text-4xl md:text-3xl lg:text-4xl text-right font-[650] pr-1 align-bottom text-gray-800'>{{$stats['ongoing_gaa']}}</p>
                  <p class='text-[10px] md:text-[12px] text-right font-medium pr-1'>Ongoing Projects</p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Infrastructure Projects Per Year
            </div>
            <div>
              <div id="gaaTotalGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>

          <div class="col-span-12 lg:col-span-6 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
            <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
              Infrastructure Projects' Budget per Year
            </div>
            <div>
              <div id="gaaAmtGraph" style="width: 100%; "></div>
            </div>    
            <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
              <i>Note: Only dates with recorded initiations are displayed.</i>
            </div>  
          </div>

          <div class="col-span-12 lg:col-span-6">
                <div class="grid grid-rows-12 h-80 gap-y-1">
                    <div class='row-span-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden flex flex-col justify-center items-center'>
                        <p class='text-[10px] md:text-[14px] text-white font-[750] px-2'>Recent Ongoing Projects</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-1 bg-white/50 backdrop-blur-md rounded-sm inset-shadow-xs shadow-xl overflow-hidden items-center'> 
                      <p class='col-span-6 text-[11px] md:text-[12px] text-gray-800 font-semibold flex justify-center items-center'>Project</p>
                      <p class='col-span-3 text-[11px] md:text-[12px] text-gray-800 font-semibold flex justify-center items-center'>Contractor</p>
                      <p class='col-span-3 text-[11px] md:text-[12px] text-gray-800 font-semibold flex justify-center items-center'>Date</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>        
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($gaarec_title[0],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaarec_cont[0]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaarec_date[0]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($gaarec_title[1],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaarec_cont[1]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaarec_date[1]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($gaarec_title[2],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaarec_cont[2]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaarec_date[2]}}</p>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-6">
                <div class="grid grid-rows-12 h-80 gap-y-1">
                    <div class='row-span-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden flex flex-col justify-center items-center'>
                      <p class='text-[10px] md:text-[14px] text-white font-[750] px-2'>Oldest Ongoing Projects</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-1 bg-white/50 backdrop-blur-md rounded-sm inset-shadow-xs shadow-xl overflow-hidden'>        
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>Project</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>Contractor</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>Date</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>        
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($gaaold_title[0],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaaold_cont[0]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaaold_date[0]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($gaaold_title[1],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaaold_cont[1]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaaold_date[1]}}</p>
                    </div>
                    <div class='grid grid-cols-12 gap-1 row-span-3 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl overflow-hidden'>
                        <p class='col-span-6 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{substr($gaaold_title[2],0,60)}}...</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaaold_cont[2]}}</p>
                        <p class='col-span-3 text-[11px] md:text-[12px] mb-8 text-right text-gray-800 font-semibold flex justify-center items-center'>{{$gaaold_date[2]}}</p>
                    </div>
                </div>
              </div>
        </div>
    </div>
  </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

      const infraData = [
        {
            x: @js($charts['infraLabel']),
            y: @js($charts['infraValue']),
            hovertemplate: '<b>Year:</b> %{x} <br><b>Total:</b> %{y}<extra></extra>',
            type: 'bar',
            marker: { color: '#01ac42' },
            width: 0.5,
        }
      ];

      const infraLayout = {
          height: 240,
          barmode: 'stack',
          plot_bgcolor: 'rgba(0,0,0,0)',
          paper_bgcolor: 'rgba(0,0,0,0)',
          margin: { l: 40, r: 40, b: 40, t: 20 },
          legend: {font: {size: 10, color: 'black'}},
          xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 9 } },
          yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero' }
      };

      Plotly.newPlot('infraTotalGraph', infraData, infraLayout, commonConfig);

      const amtInfraData = [
        {
            x: @js($charts['infraAmtLabel']),
            y: @js($charts['infraAmtValue']),
            hovertemplate: '<b>Year:</b> %{x} <br><b>Total:</b>₱%{y:,.2f}<extra></extra>',
            type: 'bar',
            marker: { color: '#01ac42' },
            width: 0.5,
        }
      ];

      const amtInfraLayout = {
          height: 240,
          barmode: 'stack',
          plot_bgcolor: 'rgba(0,0,0,0)',
          paper_bgcolor: 'rgba(0,0,0,0)',
          margin: { l: 55, r: 40, b: 40, t: 20 },
          legend: {font: {size: 10, color: 'black'}},
          xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 9 } },
          yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', tickprefix: '₱', tickformat: ',.2s' }
      };

      Plotly.newPlot('infraAmtGraph', amtInfraData, amtInfraLayout, commonConfig);

       const gaaData = [
        {
            x: @js($charts['gaaLabel']),
            y: @js($charts['gaaValue']),
            hovertemplate: '<b>Year:</b> %{x} <br><b>Total:</b>%{y}<extra></extra>',
            type: 'bar',
            marker: { color: '#01ac42' },
            width: 0.5,
        }
      ];

      const gaaLayout = {
          height: 240,
          barmode: 'stack',
          plot_bgcolor: 'rgba(0,0,0,0)',
          paper_bgcolor: 'rgba(0,0,0,0)',
          margin: { l: 40, r: 40, b: 40, t: 20 },
          legend: {font: {size: 10, color: 'black'}},
          xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 9 } },
          yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero' }
      };

      Plotly.newPlot('gaaTotalGraph', gaaData, gaaLayout, commonConfig);

      const amtGaaData = [
        {
            x: @js($charts['gaaAmtLabel']),
            y: @js($charts['gaaAmtValue']),
            hovertemplate: '<b>Year:</b> %{x} <br><b>Total:</b> %{y}<extra></extra>',
            type: 'bar',
            marker: { color: '#01ac42' },
            width: 0.5,
        }
      ];

      const amtGaaLayout = {
          height: 240,
          barmode: 'stack',
          plot_bgcolor: 'rgba(0,0,0,0)',
          paper_bgcolor: 'rgba(0,0,0,0)',
          margin: { l: 60, r: 40, b: 40, t: 20 },
          legend: {font: {size: 10, color: 'black'}},
          xaxis: { type: 'category', linecolor: '#00702B', linewidth: 2, showline: true, tickcolor: '#00702B', tickfont: { color: '#00702B', size: 9 } },
          yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', tickprefix: '₱', tickformat: ',.2s' }
      };

      Plotly.newPlot('gaaAmtGraph', amtGaaData, amtGaaLayout, commonConfig);

    </script>
</body>
</html>