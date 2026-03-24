<?php

namespace App\Http\Controllers\EIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Eis\EISInfraProj;
use App\Models\Eis\EISGaaProj;

class FacilitiesController extends Controller
{
    public function index(Request $request)
    {
        $infra_year = EISInfraProj::selectRaw('YEAR(date_commenced) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');
        
        $infrapermMaxYear = $infra_year->first();

        $infraselectedYear = $request->input('infrayear') ?? $infrapermMaxYear; // Keep this null if not in request

        $inquery = EISInfraProj::query();

        if ($infraselectedYear) {
            $inquery->whereYear('date_commenced', $infraselectedYear);
        }
        $inframaxYear = $infraselectedYear ?? $infra_year->first();
        

        $infradefaultStart = $infrapermMaxYear - 4;
        $infradefaultEnd = $infrapermMaxYear;

        if (($infraselectedYear >= $infradefaultStart && $infraselectedYear <= $infradefaultEnd) || !$infraselectedYear) {
            $start = $infradefaultStart;
            $end = $infradefaultEnd;
        } else {
            $start = $infraselectedYear;
            $end = $infraselectedYear + 4;
        }

        $infrafilteredData = (clone $inquery)
        ->when($infraselectedYear, fn($q) => $q->where(DB::raw('YEAR(date_commenced)'), $infraselectedYear))
        ->get();

        $infraPerYear = EISInfraProj::select(DB::raw('YEAR(date_commenced) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_commenced', '>=', $start)
        ->whereYear('date_commenced', '<=', $end)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $infraAmtPerYear = EISInfraProj::select(DB::raw('YEAR(date_commenced) as year'), DB::raw('sum(amount) as total'))
        ->whereYear('date_commenced', '>=', $start)
        ->whereYear('date_commenced', '<=', $end)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $infraRecent = EISInfraProj::select('title', 'contractor', 'perc_complete', 'date_commenced')
        ->where('status', 'On-going')
        ->orderBy('date_commenced', 'desc')
        ->take(3)
        ->get();

        $infraOld = EISInfraProj::select('title', 'contractor', 'perc_complete', 'date_commenced')
        ->where('status', 'On-going')
        ->orderBy('date_commenced', 'asc')
        ->take(3)
        ->get();

        //----------------------------------------------------------------------------------------------

        $gaa_year = EISGaaProj::selectRaw('YEAR(date_commenced) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');
        
        $gaapermMaxYear = $gaa_year->first();

        $gaaselectedYear = $request->input('gaayear') ?? $gaapermMaxYear; // Keep this null if not in request

        $gaaquery = EISGaaProj::query();

        if ($gaaselectedYear) {
            $gaaquery->whereYear('date_commenced', $gaaselectedYear);
        }
        $gaamaxYear = $gaaselectedYear ?? $gaa_year->first();

        $gaadefaultStart = $gaapermMaxYear - 4;
        $gaadefaultEnd = $gaapermMaxYear;

        if (($gaaselectedYear >= $gaadefaultStart && $gaaselectedYear <= $gaadefaultEnd) || !$gaaselectedYear) {
            $gastart = $gaadefaultStart;
            $gaend = $gaadefaultEnd;
        } else {
            $gastart = $gaaselectedYear;
            $gaend = $gaaselectedYear + 4;
        }

        $gaafilteredData = (clone $gaaquery)
        ->when($gaaselectedYear, fn($q) => $q->where(DB::raw('YEAR(date_commenced)'), $gaaselectedYear))
        ->get();

        $gaaPerYear = EISGaaProj::select(DB::raw('YEAR(date_commenced) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_commenced', '>=', $gastart)
        ->whereYear('date_commenced', '<=', $gaend)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $gaaAmtPerYear = EISGaaProj::select(DB::raw('YEAR(date_commenced) as year'), DB::raw('sum(appr_budget) as total'))
        ->whereYear('date_commenced', '>=', $gastart)
        ->whereYear('date_commenced', '<=', $gaend)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        $gaaRecent = EISGaaProj::select('title', 'contractor', 'perc_complete', 'date_commenced')
        ->where('status', 'On-going')
        ->orderBy('date_commenced', 'desc')
        ->take(3)
        ->get();

        $gaaOld = EISGaaProj::select('title', 'contractor', 'perc_complete', 'date_commenced')
        ->where('status', 'On-going')
        ->orderBy('date_commenced', 'asc')
        ->take(3)
        ->get();

        return view('eis.facility', [
            'stats' => [
                'total_infra' => $infrafilteredData->count(),
                'inmax_year' => $infrafilteredData->max('YEAR(date_commenced'),
                'completed_infra' => $infrafilteredData->where('status', 'Completed')->count(),
                'ongoing_infra' => $infrafilteredData->where('status', 'On-going')->count(),
                'infra_amount' => $infrafilteredData->sum('amount'),
                'inyear' => $infra_year,

                'total_gaa' => $gaafilteredData->count(),
                'gamax_year' => $gaafilteredData->max('YEAR(date_commenced'),
                'completed_gaa' => $gaafilteredData->where('status', 'Completed')->count(),
                'ongoing_gaa' => $gaafilteredData->where('status', 'On-going')->count(),
                'gaa_amount' => $gaafilteredData->sum('amount'),
                'gayear' => $gaa_year,
            ],
            'charts' => [
                'infraLabel' => $infraPerYear->pluck('year'),
                'infraValue' => $infraPerYear->pluck('total'),
                'infraAmtLabel' => $infraAmtPerYear->pluck('year'),
                'infraAmtValue' => $infraAmtPerYear->pluck('total'),

                'gaaLabel' => $gaaPerYear->pluck('year'),
                'gaaValue' => $gaaPerYear->pluck('total'),
                'gaaAmtLabel' => $gaaAmtPerYear->pluck('year'),
                'gaaAmtValue' => $gaaAmtPerYear->pluck('total'),
            ],
            'infraselectedYear' => $infraselectedYear,
            'infrarec_title' => $infraRecent->pluck('title'),
            'infrarec_cont' => $infraRecent->pluck('contractor'),
            'infrarec_perc' => $infraRecent->pluck('perc_complete'),
            'infrarec_date' => $infraRecent->pluck('date_commenced'),
            'infraold_title' => $infraOld->pluck('title'),
            'infraold_cont' => $infraOld->pluck('contractor'),
            'infraold_perc' => $infraOld->pluck('perc_complete'),
            'infraold_date' => $infraOld->pluck('date_commenced'),

            'gaaselectedYear' => $gaaselectedYear,
            'gaarec_title' => $gaaRecent->pluck('title'),
            'gaarec_cont' => $gaaRecent->pluck('contractor'),
            'gaarec_perc' => $gaaRecent->pluck('perc_complete'),
            'gaarec_date' => $gaaRecent->pluck('date_commenced'),
            'gaaold_title' => $gaaOld->pluck('title'),
            'gaaold_cont' => $gaaOld->pluck('contractor'),
            'gaaold_perc' => $gaaOld->pluck('perc_complete'),
            'gaaold_date' => $gaaOld->pluck('date_commenced'),
        ]);
    }
}
