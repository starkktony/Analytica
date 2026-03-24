<?php

namespace App\Http\Controllers\EIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eis\EISBid;
use App\Models\Eis\EISInfra;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function index(Request $request){

        $bid_year = EISBid::selectRaw('YEAR(date_posted) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');

        $bidpermMaxYear = $bid_year->first();

        $bidselectedYear = $request->input('bidyear') ?? $bidpermMaxYear;

        $bidquery = EISBid::query();

        if ($bidselectedYear) {
            $bidquery->whereYear('date_posted', $bidselectedYear);
        }
        $bidmaxYear = $bidselectedYear ?? $bid_year->first();

        $biddefaultStart = $bidpermMaxYear - 4;
        $biddefaultEnd = $bidpermMaxYear;

        if (($bidselectedYear >= $biddefaultStart && $bidselectedYear <= $biddefaultEnd) || !$bidselectedYear) {
            $bidstart = $biddefaultStart;
            $bidend = $biddefaultEnd;
        } else {
            $bidstart = $bidselectedYear;
            $bidend = $bidselectedYear + 4;
        }

        $bidfilteredData = (clone $bidquery)
        ->when($bidselectedYear, fn($q) => $q->where(DB::raw('YEAR(date_posted)'), $bidselectedYear))
        ->get();

        $bid_amount = $bidfilteredData->whereIn('status',['completed','on-going'])->sum('bid_amt');
        $appr_budget = $bidfilteredData->whereIn('status',['completed','on-going'])->sum('appr_budget');

        $bidratio = ($appr_budget/$bid_amount)*100;

        $bidComp = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(status) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->where('status', 'completed')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $bidOngoing = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->where('status', 'on-going')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $bidFail = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->where('status', 'failed')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $bidTotal = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $bidAppr = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(appr_budget) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $bidAmt = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(bid_amt) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        //------------------------------------------------------------------------------------------

        $infra_year = EISInfra::selectRaw('YEAR(date_posted) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');
        
        $infrapermMaxYear = $infra_year->first();

        $infraselectedYear = $request->input('infrayear') ?? $infrapermMaxYear;

        $infraquery = EISInfra::query();

        if ($infraselectedYear) {
            $infraquery->whereYear('date_posted', $infraselectedYear);
        }
        $inframaxYear = $infraselectedYear ?? $infra_year->first();

        $infradefaultStart = $infrapermMaxYear - 4;
        $infradefaultEnd = $infrapermMaxYear;

        if (($infraselectedYear >= $infradefaultStart && $infraselectedYear <= $infradefaultEnd) || !$infraselectedYear) {
            $infrastart = $infradefaultStart;
            $infraend = $infradefaultEnd;
        } else {
            $infrastart = $infraselectedYear;
            $infraend = $infraselectedYear + 4;
        }

        $infrafilteredData = (clone $infraquery)
        ->when($infraselectedYear, fn($q) => $q->where(DB::raw('YEAR(date_posted)'), $infraselectedYear))
        ->get();

        $infra_amount = $infrafilteredData->whereIn('status',['completed','on-going'])->sum('bid_amt');
        $infra_budget = $infrafilteredData->whereIn('status',['completed','on-going'])->sum('appr_budget');

        $infraratio = ($infra_budget/$infra_amount)*100;

        $infracomplete = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->where('status', 'completed')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        $infraComp = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infracomplete) {
            return [
                'year' => $year,
                'total' => isset($infracomplete[$year]) ? (float)$infracomplete[$year]->total : 0
            ];
        });

        $infraongo = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->where('status', 'on-going')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        $infraOngoing = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infraongo) {
            return [
                'year' => $year,
                'total' => isset($infraongo[$year]) ? (float)$infraongo[$year]->total : 0
            ];
        });

        $infrafailed = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->where('status', 'failed')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        $infraFail = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infrafailed) {
            return [
                'year' => $year,
                'total' => isset($infrafailed[$year]) ? (float)$infrafailed[$year]->total : 0
            ];
        });

        $infratotal1 = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        $infraTotal = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infratotal1) {
            return [
                'year' => $year,
                'total' => isset($infratotal1[$year]) ? (float)$infratotal1[$year]->total : 0
            ];
        });

        $infraAppr = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(appr_budget) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $infraAmt = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(bid_amt) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        return view('eis.bid', [
            'stats' => [
                'total_bid' => $bidfilteredData->count(),
                'bidmax_year' => $bidfilteredData->max('YEAR(date_posted'),
                'completed_bid' => $bidfilteredData->where('status', 'completed')->count(),
                'ongoing_bid' => $bidfilteredData->where('status', 'on-going')->count(),
                'failed_bid' => $bidfilteredData->where('status', 'failed')->count(),
                'bid_amount' => number_format($bid_amount,2),
                'appr_budget' => number_format($appr_budget,2),
                'bid_ratio' => number_format($bidratio,2),
                'bidyear' => $bid_year,

                'total_infra' => $infrafilteredData->count(),
                'inframax_year' => $infrafilteredData->max('YEAR(date_posted'),
                'completed_infra' => $infrafilteredData->where('status', 'completed')->count(),
                'ongoing_infra' => $infrafilteredData->where('status', 'on-going')->count(),
                'failed_infra' => $infrafilteredData->where('status', 'failed')->count(),
                'infra_amount' => number_format($infra_amount,2),
                'infra_budget' => number_format($infra_budget,2),
                'infra_ratio' => number_format($infraratio,2),
                'infrayear' => $infra_year,
            ],
            'charts' => [
                'bidComp' => $bidComp->pluck('total'),
                'bidOngoing' => $bidOngoing->pluck('total'),
                'bidFail' => $bidFail->pluck('total'),
                'bidTotal' => $bidTotal->pluck('total'),
                'bidLabel' => $bidTotal->pluck('year'),
                'bidAppr' => $bidAppr->pluck('total'),
                'bidAmt' => $bidAmt->pluck('total'),
                'bidYear' => $bidAmt->pluck('year'),

                'infraComp' => $infraComp->pluck('total'),
                'infraOngoing' => $infraOngoing->pluck('total'),
                'infraFail' => $infraFail->pluck('total'),
                'infraTotal' => $infraTotal->pluck('total'),
                'infraLabel' => $infraTotal->pluck('year'),
                'infraAppr' => $infraAppr->pluck('total'),
                'infraAmt' => $infraAmt->pluck('total'),
                'infraYear' => $infraAmt->pluck('year'),
            ],
            'bidSelectedYear' => $bidselectedYear,
            'infraSelectedYear' => $infraselectedYear,
        ]);
    }
}
