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
                EIS Finances
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <div class="hidden xl:block font-[650] text-sm md:text-xs border-r border-gray-500 pr-4">
                    Year Filters
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">Trust Fund:</span>
                        <form action="{{ route('eis.fund') }}" method="GET" id="filterTrust" class="m-0">
                            <input type="hidden" name="year161" value="{{ $selected161 }}">
                            <input type="hidden" name="year163" value="{{ $selected163 }}">
                            <input type="hidden" name="year163" value="{{ $selected164 }}">
                            <input type="hidden" name="year101" value="{{ $selected101 }}">
                            <select name="yeartrust" onchange="this.form.submit()" 
                                class="block pl-3 pr-8 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-sm cursor-pointer">
                                @foreach($stats['all_year'] as $year)
                                    <option value="{{ $year }}" {{ $year == $selectedTrust ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">Fund 101:</span>
                        <form action="{{ route('eis.fund') }}" method="GET" id="filterForm">
                            <input type="hidden" name="year161" value="{{ $selected161 }}">
                            <input type="hidden" name="year163" value="{{ $selected163 }}">
                            <input type="hidden" name="year163" value="{{ $selected164 }}">
                            <input type="hidden" name="yeartrust" value="{{ $selectedTrust }}">
                            <select name="year101" onchange="document.getElementById('filterForm').submit()" class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                @foreach($stats['all_101year'] as $year)
                                <option class="text-xs" value="{{ $year }}" {{ $year == $selected101 ? 'selected' : '' }}>
                                {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">Fund 161:</span>
                        <form action="{{ route('eis.fund') }}#f161" method="GET" id="filterGroup">
                            <input type="hidden" name="year101" value="{{ $selected101 }}">
                            <input type="hidden" name="year163" value="{{ $selected163 }}">
                            <input type="hidden" name="year164" value="{{ $selected164 }}">
                            <input type="hidden" name="yeartrust" value="{{ $selectedTrust }}">
                            <select name="year161" onchange="document.getElementById('filterGroup').submit()" class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                @foreach($stats['all_161year'] as $year)
                                <option class="text-xs" value="{{ $year }}" {{ $year == $selected161 ? 'selected' : '' }}>
                                {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">Fund 163:</span>
                        <form action="{{ route('eis.fund') }}#f163" method="GET" id="filter163">
                            <input type="hidden" name="year101" value="{{ $selected101 }}">
                            <input type="hidden" name="year161" value="{{ $selected161 }}">
                            <input type="hidden" name="year164" value="{{ $selected164 }}">
                            <input type="hidden" name="yeartrust" value="{{ $selectedTrust }}">
                            <select name="year163" onchange="document.getElementById('filter163').submit()" class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                @foreach($stats['all_163year'] as $year)
                                <option class="text-xs" value="{{ $year }}" {{ $year == $selected163 ? 'selected' : '' }}>
                                {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium">Fund 164:</span>
                        <form action="{{ route('eis.fund') }}#f164" method="GET" id="filter164">
                            <input type="hidden" name="year161" value="{{ $selected161 }}">
                            <input type="hidden" name="year163" value="{{ $selected163 }}">
                            <input type="hidden" name="year101" value="{{ $selected101 }}">
                            <input type="hidden" name="yeartrust" value="{{ $selectedTrust }}">
                            <select name="year164" onchange="document.getElementById('filter164').submit()" class="block pl-3 pr-3 py-1 bg-slate-100 border border-gray-300 text-xs text-gray-900 rounded-md focus:ring-brand focus:border-brand shadow-xs placeholder:text-body">
                                @foreach($stats['all_164year'] as $year)
                                <option class="text-xs" value="{{ $year }}" {{ $year == $selected164 ? 'selected' : '' }}>
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

    <div class="flex items-center justify-between py-2 px-6 min-h-10 font-(family-name: --font-inter)">
        <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-60 flex item-center justify-center">
        <div class="font-[650] text-white text-md">Trust Fund</div>
        </div>
    </div>

    <div class="px-6 pt-2">
        <div class="grid grid-cols-4 lg:grid-cols-12 gap-3 mb-2">
            <div class="col-span-4 lg:col-span-4 h-80">
                <div class='grid grid-rows-4'>
                    <div class='row-span-2'>
                        <div class="grid grid-rows-8 h-39 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                            <div class='bg-white row-span-4 rounded-lg h-12 w-16 flex items-center justify-center'>
                                <i class="fa-solid fa-money-bill-wave text-green-600 text-3xl"></i>
                            </div>
                            <div class='row-span-4 pt-2'>
                                <p class='text-white text-right text-md xs:text-xl sm:text-3xl lg:text-xl xl:text-3xl font-[650] pr-1'>₱ {{number_format($stats['trustcurr_bal'], 2)}}</p>
                                <p class='text-right text-white text-[9px] xs:text-[10px] sm:text-[12px] md:text-[11px] xl:text-[13px] font-[550] pr-1'>Trust Fund End. Balance ({{$selectedTrust}})</p>
                            </div>
                        </div>
                    </div>
                    <div class='row-span-2'>
                        <div class='border-l-5 h-39 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 mt-1 overflow-hidden'>
                            <div class='grid grid-rows-8'>
                                <div class='bg-green-600/80 row-span-2 rounded-lg h-12 w-16 flex items-center justify-center'>
                                    <i class="fa-solid fa-money-bill-transfer text-white text-3xl"></i>
                                </div>
                                <div class='row-span-6 pt-2'>
                                    <p class='text-lg xs:text-xl sm:text-3xl lg:text-xl xl:text-3xl text-right font-[550] pr-1'> <span class="{{ $stats['trust_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                                        {{$stats['trust_ratio']}}</span></p>
                                    <p class=' text-right pr-1 font-medium leading-[0.8]'><span class="{{ $stats['trust_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                                        <span class="{{ $stats['trust_ratio'] >= 1 ? 'text-[9px] xs:text-[12px] md:text-[11px] lg:text-[9px] xl:text-[10px]' : 'text-[12px] md:text-[11px]' }}">
                                        {{ $stats['trust_ratio'] >= 1 ? 'Collection exceeded or equal to expenditure ' : 'Expenditure exceeded the collection ' }}</span></span></p>
                                    <p class='text-[9px] xs:text-[10px] sm:text-[12px] md:text-[11px] xl:text-[13px] text-right font-[550] pr-1 pt-1'>Cash Flow Ratio ({{$selectedTrust}})</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-4 lg:col-span-8 border-t-4 h-80 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-5 sm:pl-7'>
                    Trust Fund Balance Over Time
                </div>
                <div>
                   <div id="trustBalance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6">
                    <i>Note: Only dates with recorded initiations are displayed.</i>
                </div>  
            </div>
        </div>

        <div class="flex items-center justify-between py-2 min-h-10 font-(family-name: --font-inter)">
            <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-60 flex item-center justify-center">
            <div class="font-[650] text-white text-md">Fund 101</div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-3 mt-3">
            <div class="col-span-12 lg:col-span-4">
                <div class="grid grid-rows-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                    <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-users text-green-600 text-3xl"></i>
                    </div>
                    <div class='row-span-1 pt-2'>
                        <p class='text-white text-right text-sm xs:text-lg md:text-2xl lg:text-lg xl:text-2xl font-[650] pr-2'>₱ 834,388,220.00</p>
                        <p class='text-right text-white text-[11px] md:text-[11px] lg:text-[10px] xl:text-[12px] font-[550] pr-2'>Fixed Allotment for Personnel Services</p>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-4">
                <div class="grid grid-rows-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                    <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-building text-green-600 text-3xl"></i>
                    </div>
                    <div class='row-span-1 pt-2'>
                        <p class='text-white text-right text-sm xs:text-lg md:text-2xl lg:text-lg xl:text-2xl font-[650] pr-2'>₱ 152,471,000.00</p>
                        <p class='text-right text-white text-[11px] md:text-[11px] lg:text-[10px] xl:text-[12px] font-[550] pr-2'>Fixed Allotment for Capital Outlays</p>
                    </div>
                </div>         
            </div>
            <div class="col-span-12 lg:col-span-4">
                <div class="grid grid-rows-2 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                    <div class='bg-white row-span-1 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-gears text-green-600 text-3xl"></i>
                    </div>
                    <div class='row-span-1 pt-2'>
                        <p class='text-white text-right text-sm xs:text-lg md:text-2xl lg:text-lg xl:text-2xl font-[650] pr-2'>₱ 313,089,798.00</p>
                        <p class='text-right text-white text-[11px] md:text-[11px] lg:text-[10px] xl:text-[12px] font-[550] pr-2'>Fixed Allotment for MOOE</p>
                    </div>
                </div>         
            </div>
            
            <div class="col-span-12 md:col-span-4">               
                <div class='grid grid-rows-5 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='bg-green-600/80 row-span-2 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bills text-white text-3xl"></i>
                    </div>
                    <div class='row-span-3 pt-2'>
                        <p class='text-xl xs:text-lg md:text-xl lg:text-lg xl:text-2xl text-right font-[650] pr-1 align-bottom text-gray-800'> <span class="{{ $stats['psutil'] >= 60 ? 'text-green-600' : 'text-red-500' }}">
                            {{$stats['psutil']}}%</span></p>
                        <p class='text-[11px] md:text-[8px] lg:text-[10px] xl:text-[12px] text-right font-medium pr-1'>Personnel Services Allotment Utilization Rate ({{$selected101}})</p>
                    </div>
                </div>
            </div>
            <div class="col-span-12 md:col-span-4">               
                <div class='grid grid-rows-5 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='bg-green-600/80 row-span-2 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bills text-white text-3xl"></i>
                    </div>
                    <div class='row-span-3 pt-2'>
                        <p class='text-xl xs:text-lg md:text-xl lg:text-lg xl:text-2xl text-right font-[650] pr-1 align-bottom text-gray-800'> <span class="{{ $stats['coutil'] >= 60 ? 'text-green-600' : 'text-red-500' }}">
                            {{$stats['coutil']}}%</span></p>
                        <p class='text-[11px] md:text-[8px] lg:text-[10px] xl:text-[12px] text-right font-medium pr-1'>Capital Outlays Allotment Utilization Rate ({{$selected101}})</p>
                    </div>
                </div>
            </div>
            <div class="col-span-12 md:col-span-4">               
                <div class='grid grid-rows-5 border-l-5 border-green-600 bg-white/50 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='bg-green-600/80 row-span-2 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bills text-white text-3xl"></i>
                    </div>
                    <div class='row-span-3 pt-2'>
                        <p class='text-xl xs:text-lg md:text-xl lg:text-lg xl:text-2xl text-right font-[650] pr-1 align-bottom text-gray-800'> <span class="{{ $stats['moutil'] >= 60 ? 'text-green-600' : 'text-red-500' }}">
                            {{$stats['moutil']}}%</span></p>
                        <p class='text-[11px] md:text-[8px] lg:text-[10px] xl:text-[12px] text-right font-medium pr-1'>MOOE Allotment Utilization Rate ({{$selected101}})</p>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4">               
                <div class="border-t-[6px] border-green-600 bg-white rounded-[1vw] inset-shadow-xl h-[320px] xs:h-[300px] sm:h-[400px] shadow-xl">
                    <div class='grid grid-rows-7 h-full'>
                        <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-4 sm:pl-7'>Personnel Services Liquidation Breakdown</div>
                        <div class='row-span-6 h-full w-full pt-2'>
                            <div id="psPieChart" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-4">               
                <div class="border-t-[6px] border-green-600 bg-white rounded-[1vw] inset-shadow-xl h-[320px] xs:h-[300px] sm:h-[400px] shadow-xl">
                    <div class='grid grid-rows-7 h-full'>
                        <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-4 sm:pl-7'>Capital Overlay Liquidation Breakdown</div>
                        <div class='row-span-6 h-full w-full pt-2'>
                            <div id="coPieChart" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 lg:col-span-4">               
                <div class="border-t-[6px] border-green-600 bg-white rounded-[1vw] inset-shadow-xl h-[320px] xs:h-[300px] sm:h-[400px] shadow-xl">
                    <div class='grid grid-rows-7 h-full'>
                        <div class='row-span-1 font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-3 pl-4 sm:pl-7'>MOOE Liquidation Breakdown</div>
                        <div class='row-span-6 h-full w-full pt-2'>
                            <div id="mooePieChart" style="width: 100%; height: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='col-span-12'>
                <div class="border-t-4 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                    <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Fund 101 Expenditures Over Time</div>
                    <div>
                    <div id="psExpChart" style="width: 100%; "></div>
                    </div>    
                    <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6 pb-2">
                        <i>Note: Only dates with recorded initiations are displayed.</i>
                    </div> 
                </div>
            </div>
    
        </div>

        <div id="f161" class="flex items-center justify-between py-2 min-h-10 font-(family-name: --font-inter)">
            <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-60 flex item-center justify-center">
            <div class="font-[650] text-white text-md">Fund 161</div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-3 mb-2 mt-2">
            <div class="col-span-12 lg:col-span-6 xl:col-span-3">
                <div class="grid grid-rows-8 h-35 sm:h-42 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                    <div class='bg-white row-span-4 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bill-wave text-green-600 text-3xl"></i>
                    </div>
                    <div class='row-span-4 pt-2'>
                        <p class='text-white text-right text-sm xs:text-2xl sm:text-3xl lg:text-3xl xl:text-[22px] font-[650] pr-1'>₱ {{$stats['curr_161']}}</p>
                        <p class='text-right text-white text-[11px] md:text-[12px] font-[550] pr-1'>Current Fund 161 Balance</p>
                    </div>
                </div> 
            </div>
            <div class="col-span-12 lg:col-span-6 xl:col-span-3">               
                <div class='grid grid-rows-8 border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-42 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='bg-green-600/80 row-span-4 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bill-transfer text-white text-3xl"></i>
                    </div>
                    <div class='row-span-4 mb-5'>
                            <p class='text-xl xs:text-xl sm:text-3xl lg:text-2xl xl:text-3xl text-right font-[650] pr-1 align-bottom text-gray-800'> <span class="{{ $stats['f161_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                            {{$stats['f161_ratio']}}</span></p>
                        <p class='text-[11px] md:text-[11px] text-right pr-1 font-medium leading-[0.8]'><span class="{{ $stats['f161_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                            <span class="{{ $stats['f161_ratio'] >= 1 ? 'text-[9px] xs:text-[12px] md:text-[11px] lg:text-[12px] xl:text-[8px] ' : 'text-[9px] md:text-[11px]' }}">
                            {{ $stats['f161_ratio'] >= 1 ? 'Collection is greater than or equal to expenditure ' : 'Expenditure exceeded the collection ' }}</span></span></p>
                        <p class='text-[8px] xs:text-[10px] md:text-[12px] mb-8 text-right font-medium pr-1'>Collections to Expenditures Ratio</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-2 mt-2">
            <div class="col-span-6 border-t-4 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Fund 161 Balance Over Time</div>
                <div>
                    <div id="fund161Balance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6 pb-2">
                    <i>Note: Only dates with recorded initiations are displayed.</i>
                </div> 
            </div>
            <div class="col-span-6 border-t-4 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Fund 161 Available Balance and Expenditures</div>
                <div>
                <div id="colexp161Balance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6 pb-2">
                    <i>Note: Only dates with recorded initiations are displayed.</i>
                </div> 
            </div>
        </div>

        <div id="f163" class="flex items-center justify-between py-2 min-h-10 font-(family-name: --font-inter)">
            <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-60 flex item-center justify-center">
            <div class="font-[650] text-white text-md">Fund 163</div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-3 mb-2 mt-2">
            <div class="col-span-12 lg:col-span-6 xl:col-span-3">
                <div class="grid grid-rows-8 h-35 sm:h-42 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                    <div class='bg-white row-span-4 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bill-wave text-green-600 text-3xl"></i>
                    </div>
                    <div class='row-span-4 pt-2'>
                        <p class='text-white text-right text-sm xs:text-2xl sm:text-3xl lg:text-3xl xl:text-[22px] font-[650] pr-1'>₱ {{$stats['curr_163']}}</p>
                        <p class='text-right text-white text-[11px] md:text-[12px] font-[550] pr-1'>Current Fund 163 Balance</p>
                    </div>
                </div> 
            </div>
            <div class="col-span-12 lg:col-span-6 xl:col-span-3">               
                <div class='grid grid-rows-8 border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-42 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='bg-green-600/80 row-span-4 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bill-transfer text-white text-3xl"></i>
                    </div>
                    <div class='row-span-4 mb-5'>
                            <p class='text-xl xs:text-xl sm:text-3xl lg:text-2xl xl:text-3xl text-right font-[650] pr-1 align-bottom text-gray-800'> <span class="{{ $stats['f163_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                            {{$stats['f163_ratio']}}</span></p>
                        <p class='text-[11px] md:text-[11px] text-right pr-1 font-medium leading-[0.8]'><span class="{{ $stats['f163_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                            <span class="{{ $stats['f163_ratio'] >= 1 ? 'text-[9px] xs:text-[12px] md:text-[11px] lg:text-[12px] xl:text-[8px] ' : 'text-[9px] md:text-[11px]' }}">
                            {{ $stats['f163_ratio'] >= 1 ? 'Collection is greater than or equal to expenditure ' : 'Expenditure exceeded the collection ' }}</span></span></p>
                        <p class='text-[8px] xs:text-[10px] md:text-[12px] mb-8 text-right font-medium pr-1'>Collections to Expenditures Ratio</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-2 mt-2">
            <div class="col-span-6 border-t-4 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Fund 163 Balance Over Time</div>
                <div>
                    <div id="fund163Balance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6 pb-2">
                    <i>Note: Only dates with recorded initiations are displayed.</i>
                </div> 
            </div>
            <div class="col-span-6 border-t-4 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Fund 163 Available Balance and Expenditures</div>
                <div>
                <div id="colexp163Balance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6 pb-2">
                    <i>Note: Only dates with recorded initiations are displayed.</i>
                </div> 
            </div>
        </div>

        <div id="f164" class="flex items-center justify-between py-2 min-h-10 font-(family-name: --font-inter)">
            <div class="rounded-3xl mt-2 p-2 bg-green-600/80 w-60 flex item-center justify-center">
            <div class="font-[650] text-white text-md">Fund 164</div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-3 mb-2 mt-2">
            <div class="col-span-12 lg:col-span-6 xl:col-span-3">
                <div class="grid grid-rows-8 h-35 sm:h-42 bg-green-600/80 backdrop-blur-md rounded-lg inset-shadow-xs shadow-xl p-3">
                    <div class='bg-white row-span-4 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bill-wave text-green-600 text-3xl"></i>
                    </div>
                    <div class='row-span-4 pt-2'>
                        <p class='text-white text-right text-sm xs:text-2xl sm:text-3xl lg:text-3xl xl:text-[22px] font-[650] pr-1'>₱ {{$stats['curr_164']}}</p>
                        <p class='text-right text-white text-[11px] md:text-[12px] font-[550] pr-1'>Current Fund 164 Balance</p>
                    </div>
                </div> 
            </div>
            <div class="col-span-12 lg:col-span-6 xl:col-span-3">               
                <div class='grid grid-rows-8 border-l-5 border-green-600 bg-white/50 backdrop-blur-md h-42 rounded-lg inset-shadow-xs shadow-xl p-3 overflow-hidden'>
                    <div class='bg-green-600/80 row-span-4 rounded-lg h-12 w-16 flex items-center justify-center'>
                        <i class="fa-solid fa-money-bill-transfer text-white text-3xl"></i>
                    </div>
                    <div class='row-span-4 mb-5'>
                            <p class='text-xl xs:text-xl sm:text-3xl lg:text-2xl xl:text-3xl text-right font-[650] pr-1 align-bottom text-gray-800'> <span class="{{ $stats['f164_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                            {{$stats['f164_ratio']}}</span></p>
                        <p class='text-[11px] md:text-[11px] text-right pr-1 font-medium leading-[0.8]'><span class="{{ $stats['f164_ratio'] >= 1 ? 'text-green-600' : 'text-red-500' }}">
                            <span class="{{ $stats['f164_ratio'] >= 1 ? 'text-[9px] xs:text-[12px] md:text-[11px] lg:text-[12px] xl:text-[8px] ' : 'text-[9px] md:text-[11px]' }}">
                            {{ $stats['f164_ratio'] >= 1 ? 'Collection is greater than or equal to expenditure ' : 'Expenditure exceeded the collection ' }}</span></span></p>
                        <p class='text-[8px] xs:text-[10px] md:text-[12px] mb-8 text-right font-medium pr-1'>Collections to Expenditures Ratio</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-6 xl:grid-cols-12 gap-3 mb-2 mt-2">
            <div class="col-span-6 border-t-4 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Fund 164 Balance Over Time</div>
                <div>
                    <div id="fund164Balance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6 pb-2">
                    <i>Note: Only dates with recorded initiations are displayed.</i>
                </div> 
            </div>
            <div class="col-span-6 border-t-4 border-green-600 bg-white rounded-[1vw] inset-shadow-xl shadow-xl">
                <div class='font-[750] text-sm sm:text-lg text-gray-700 w-full rounded-t-[1vw] align-middle pt-4 pl-4 sm:pl-7'>Fund 164 Available Balance and Expenditures</div>
                <div>
                <div id="colexp164Balance" style="width: 100%; "></div>
                </div>    
                <div class="text-[7px] sm:text-[10px] text-gray-500/90 pl-3 xs:pl-6 pb-2">
                    <i>Note: Only dates with recorded initiations are displayed.</i>
                </div> 
            </div>
        </div>
        
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

        const commonConfig = { responsive: true, displaylogo: false, modeBarButtonsToRemove: ['lasso2d','zoomIn2d','zoomOut2d', 'select2d'] };

        xData = @js($charts['trust_labels']),
        yData = @js($charts['trust_values']),
        max = @js($charts['trust_max'] +2000000),

        bankData = {   
            x: xData,
            y: yData,
            hovertemplate: '<b>Balance:</b> %{y} <br><b>Date:</b> %{x}<extra></extra>',
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
            x: [xData[0]],
            y: [yData[0]],
            hovertemplate: '<b>Balance:</b> %{y} <br><b>Date:</b> %{x}<extra></extra>',
            mode: 'markers',
            marker: {
                color: '#00702B', 
                size: 8     
            },
            name: 'Recent Balance',
            showlegend: false 
            };

    const trustData = [bankData, lastData];

    const trustLayout = {
        height: 230,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 60, r: 20, b: 30, t: 10 },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels:true, tickfont: { color: '#00702B', size: 9 } },
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', range:[0, max], tickprefix: '₱', tickformat: ',.2s'}
    };

    Plotly.newPlot('trustBalance', trustData, trustLayout, commonConfig);

    //-------------------------------------------------------------------------------------------

    x161Data = @js($charts['f161_labels']),
        y161Data = @js($charts['f161_values']),
        max161 = @js($charts['f161_max'] +2000000),
        min161 = @js($charts['f161_min'] -2000000),

        f161BalData = {   
            x: x161Data,
            y: y161Data,
            hovertemplate: '<b>Balance:</b> %{y} <br><b>Date:</b> %{x}<extra></extra>',
            type: 'scatter',
            mode: 'lines',
            line:{width: 2},
            fill: 'tozeroy',
            fillcolor: 'rgba(1, 172, 66, 0.5)',
            marker: { color: '#00702B' },
            showlegend: false,
            name: 'Balance'
        }

        f161lastData = {
            x: [x161Data[0]],
            y: [y161Data[0]],
            hovertemplate: '<b>Balance:</b> %{y} <br><b>Date:</b> %{x}<extra></extra>',
            mode: 'markers',
            marker: {
                color: '#00702B', 
                size: 8     
            },
            name: 'Recent Balance',
            showlegend: false 
            };

    const f161Data = [f161BalData, f161lastData];

    const f161Layout = {
        height: 270,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 60, r: 30, b: 30, t: 20 },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels:true, tickfont: { color: '#00702B', size: 9 }},
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', range:[0, max161], tickprefix: '₱', tickformat: ',.2s'}
    };

    Plotly.newPlot('fund161Balance', f161Data, f161Layout, commonConfig);

    //-------------------------------------------------------------------------------------------

    xDateData = @js($charts['f161_labels']),
        ycolData = @js($charts['f161_avail']),
        yexpData = @js($charts['f161_exps']),
        maxavail161 = @js($charts['f161_avail_max'] +2000000),
        maxexp161 = @js($charts['f161_exp_max'] +2000000),

        finMax = Math.max(maxavail161, maxexp161),

        f161ColData = {   
            x: xDateData,
            y: ycolData,
            hovertemplate: '<b>Avail. Bal.:</b> %{y} <br><b>Date:</b> %{x}<extra></extra>',
            type: 'scatter',
            mode: 'lines',
            line:{width: 3},
            marker: { color: '#00702B' },
            fill: 'tozeroy',
            fillcolor: 'rgba(1, 172, 66, 0.5)',
            name: 'Available Balance'
        }

        f161ExpData = {
            x: xDateData,
            y: yexpData,
            hovertemplate: '<b>Exp. Bal.:</b> %{y} <br><b>Date:</b> %{x}<extra></extra>',
            type: 'scatter',
            mode: 'lines',
            line:{width: 3},
            marker: { color: '#F2EA00' },
            fill: 'tozeroy',
            fillcolor: 'rgba(242, 234, 0, 0.6)',
            name: 'Expenditures'
            };

    const f161ColExpData = [f161ColData, f161ExpData];

    const f161ColExpLayout = {
        height: 270,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 60, r: 30, b: 30, t: 20 },
        legend: {
                    font: {size: 10, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels:true, tickfont: { color: '#00702B', size: 9 } },
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', range:[0, finMax], tickprefix: '₱', tickformat: ',.2s' }
    };

    Plotly.newPlot('colexp161Balance', f161ColExpData, f161ColExpLayout, commonConfig);

    //-------------------------------------------------------------------------------------------

    psexpData = {   
    x: @js($charts['f101_labels']),
    y: @js($charts['f101_psexp']),
    hovertemplate: '<b>PS Expenditure:</b> %{y}<extra></extra>',
    type: 'scatter',
    mode: 'lines+markers',
    line: { width: 3, color: '#00702B' },
    marker: { size: 8, color: '#00702B' },
    showlegend: true, // Recommended for 3 different types
    name: 'PS'
};

coexpData = {   
    x: @js($charts['f101_labels']),
    y: @js($charts['f101_coexp']),
    hovertemplate: '<b>CO Expenditure:</b> %{y}<extra></extra>',
    type: 'scatter',
    mode: 'lines+markers', 
    line: { width: 3, color: '#F2EA00' },
    marker: { size: 8, color: '#F2EA00' },
    showlegend: true,
    name: 'CO'
};

moexpData = {   
    x: @js($charts['f101_labels']),
    y: @js($charts['f101_mooeexp']),
    hovertemplate: '<b>MOOE Expenditure:</b> %{y}<extra></extra>',
    type: 'scatter',
    mode: 'lines+markers',
    line: { width: 3, color: '#4e98ff' },
    marker: { size: 8, color: '#4e98ff' },
    showlegend: true,
    name: 'MOOE'
};

const psData = [psexpData, coexpData, moexpData];

const psLayout = {
    height: 250,
    plot_bgcolor: 'rgba(0,0,0,0)',
    paper_bgcolor: 'rgba(0,0,0,0)',
    margin: { l: 60, r: 20, b: 40, t: 10 },
    legend: {
        font: {size: 9, color: 'black'},
        orientation: 'h',
        x: 0.5,
        y: 1,
        xanchor: 'center', 
        yanchor: 'bottom'   
    },
    xaxis: { type: 'date', linecolor: '#ccc', linewidth: 1, showline: true, tickfont: { color: '#00702B', size: 9 } },
    yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', title: 'Amount', tickprefix: '₱', tickformat: ',.2s'  }
};

Plotly.newPlot('psExpChart', psData, psLayout, commonConfig);

    //-------------------------------------------------------------------------------------------

    const psPieData = [{
        values: [@js($charts['f101_psdisbperc']),@js($charts['f101_psobliperc'])],
        labels: ["Disbursement", "Obligations"],
        hovertemplate: '<b>Type:</b> %{label} <br><b>Amount:</b>₱ %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
        type: 'pie',
        outsidetextfont: {color: 'transparent'},
        marker: { colors: ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760'] }
    }];

    const psPieLayout = {
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 15, r: 15, b: 15, t: 15 },
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

    Plotly.newPlot('psPieChart', psPieData, psPieLayout, commonConfig);

    //-------------------------------------------------------------------------------------------------

    const coPieData = [{
        values: [@js($charts['f101_codisbperc']),@js($charts['f101_coobliperc'])],
        labels: ["Disbursement", "Obligations"],
        hovertemplate: '<b>Type:</b> %{label} <br><b>Amount:</b>₱ %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
        type: 'pie',
        outsidetextfont: {color: 'transparent'},
        marker: { colors: ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760'] }
    }];

    const coPieLayout = {
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 15, r: 15, b: 15, t: 15 },
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

    Plotly.newPlot('coPieChart', coPieData, coPieLayout, commonConfig);

    //-------------------------------------------------------------------------------------------------

    const moPieData = [{
        values: [@js($charts['f101_modisbperc']),@js($charts['f101_moobliperc'])],
        labels: ["Disbursement", "Obligations"],
        hovertemplate: '<b>Type:</b> %{label} <br><b>Amount:</b>₱ %{value}<br><b>Percent: </b>%{percent}<extra></extra>',
        type: 'pie',
        outsidetextfont: {color: 'transparent'},
        marker: { colors: ['#01ac42', '#F2EA00', '#4E98FF', '#D84CFF', '#FF6284', '#FF9760'] },
    }];

    const moPieLayout = {
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 15, r: 15, b: 15, t: 15 },
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

    Plotly.newPlot('mooePieChart', moPieData, moPieLayout, commonConfig);

    //-------------------------------------------------------------------------------------------------

    x163Data = @js($charts['f163_labels']),
        y163Data = @js($charts['f163_values']),
        max163 = @js($charts['f163_max'] +2000000),

        console.log(y163Data);

        f163BalData = {   
            x: x163Data,
            y: y163Data,
            hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
            type: 'scatter',
            mode: 'lines',
            line:{width: 2},
            fill: 'tozeroy',
            fillcolor: 'rgba(1, 172, 66, 0.5)',
            marker: { color: '#00702B' },
            showlegend: false,
            name: 'Balance'
        }

        f163lastData = {
            x: [x163Data[0]],
            y: [y163Data[0]],
            hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
            mode: 'markers',
            marker: {
                color: '#00702B', 
                size: 8     
            },
            name: 'Recent Balance',
            showlegend: false 
            };

    const f163Data = [f163BalData, f163lastData];

    const f163Layout = {
        height: 250,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 60, r: 30, b: 30, t: 20 },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels:true, tickfont: { color: '#00702B', size: 9 } },
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', range:[0, max163], tickprefix: '₱', tickformat: ',.2s'}
    };

    Plotly.newPlot('fund163Balance', f163Data, f163Layout, commonConfig);

    //-------------------------------------------------------------------------------------------------

    const x163Date = @js($charts['f163_labels']);
    const y163Col = @js($charts['f163_avail']);
    const y163Exp = @js($charts['f163_exps']);
    const maxavail163 = @js($charts['f163_avail_max'] + 2000000);
    const maxexp163 = @js($charts['f163_exp_max'] + 2000000);

    const finMax163 = Math.max(maxavail163, maxexp163);

    const f163ColData = {   
        x: x163Date, 
        y: y163Col,  
        hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
        type: 'scatter',
        mode: 'lines',
        line: { width: 3, color: '#00702B' }, 
        fill: 'tozeroy',
        fillcolor: 'rgba(1, 172, 66, 0.5)',
        name: 'Available Balance'
    };

    const f163ExpData = {
        x: x163Date,
        y: y163Exp,  
        hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
        type: 'scatter',
        mode: 'lines',
        line: { width: 3, color: '#F2EA00' }, 
        fill: 'tozeroy',
        fillcolor: 'rgba(242, 234, 0, 0.6)',
        name: 'Expenditures'
    };

    const f163ColExpData = [f163ColData, f163ExpData];

    const f163ColExpLayout = {
        height: 250,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 60, r: 30, b: 30, t: 20 },
        legend: {
                    font: {size: 10, color: 'black'},
                    orientation: 'h',
                    x: 0.5,
                    y: 1,
                    xanchor: 'center', 
                    yanchor: 'bottom'   
                },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels: true, tickfont: { color: '#00702B', size: 9 } },
        yaxis: { 
            gridcolor: '#f0f0f0', 
            rangemode: 'tozero',
            range: [0, finMax163],
            tickprefix: '₱', tickformat: ',.2s' 
        }
    };

    Plotly.newPlot('colexp163Balance', f163ColExpData, f163ColExpLayout, commonConfig);

    //---------------------------------------------------------------------------------------------------

    x164Data = @js($charts['f164_labels']),
        y164Data = @js($charts['f164_values']),
        max164 = @js($charts['f164_max'] +2000000),

        console.log(y164Data);

        f164BalData = {   
            x: x164Data,
            y: y164Data,
            hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
            type: 'scatter',
            mode: 'lines',
            line:{width: 2},
            fill: 'tozeroy',
            fillcolor: 'rgba(1, 172, 66, 0.5)',
            marker: { color: '#00702B' },
            showlegend: false,
            name: 'Balance'
        }

        f164lastData = {
            x: [x164Data[0]],
            y: [y164Data[0]],
            hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
            mode: 'markers',
            marker: {
                color: '#00702B', 
                size: 8     
            },
            name: 'Recent Balance',
            showlegend: false 
            };

    const f164Data = [f164BalData, f164lastData];

    const f164Layout = {
        height: 250,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 60, r: 30, b: 30, t: 20 },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels:true, tickfont: { color: '#00702B', size: 9 } },
        yaxis: { gridcolor: '#f0f0f0', rangemode: 'tozero', range:[0, max164], tickprefix: '₱', tickformat: ',.2s'  }
    };

    Plotly.newPlot('fund164Balance', f164Data, f164Layout, commonConfig);

    //----------------------------------------------------------------------------------------------------

    const x164Date = @js($charts['f164_labels']);
    const y164Col = @js($charts['f164_avail']);
    const y164Exp = @js($charts['f164_exps']);
    const maxavail164 = @js($charts['f164_avail_max'] + 2000000);
    const maxexp164 = @js($charts['f164_exp_max'] + 2000000);

    const finMax164 = Math.max(maxavail164, maxexp164);

    const f164ColData = {   
        x: x164Date, 
        y: y164Col,  
        hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
        type: 'scatter',
        mode: 'lines',
        line: { width: 3, color: '#00702B' }, 
        fill: 'tozeroy',
        fillcolor: 'rgba(1, 172, 66, 0.5)',
        name: 'Available Balance'
    };

    const f164ExpData = {
        x: x164Date,
        y: y164Exp,  
        hovertemplate: 'Date:</b> %{x}<br><b>Balance: </b>₱%{y:,.2f}<extra></extra>',
        type: 'scatter',
        mode: 'lines',
        line: { width: 3, color: '#F2EA00' }, 
        fill: 'tozeroy',
        fillcolor: 'rgba(242, 234, 0, 0.6)',
        name: 'Expenditures'
    };

    const f164ColExpData = [f164ColData, f164ExpData];

    const f164ColExpLayout = {
        height: 250,
        plot_bgcolor: 'rgba(0,0,0,0)',
        paper_bgcolor: 'rgba(0,0,0,0)',
        margin: { l: 60, r: 30, b: 30, t: 20 },
        legend: {
            font: {size: 10, color: 'black'},
            orientation: 'h',
            x: 0.5,
            y: 1,
            xanchor: 'center', 
            yanchor: 'bottom'   
        },
        xaxis: { type: 'date', linecolor: '#00702B', linewidth: 2, showline: true, showticklabels: true, tickfont: { color: '#00702B', size: 9 } },
        yaxis: { 
            gridcolor: '#f0f0f0', 
            rangemode: 'tozero',
            range: [0, finMax164],
            tickprefix: '₱', tickformat: ',.2s' 
        }
    };

    Plotly.newPlot('colexp164Balance', f164ColExpData, f164ColExpLayout, commonConfig);

});
    </script> 
</body>
</html>