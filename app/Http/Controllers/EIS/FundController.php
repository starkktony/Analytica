<?php

namespace App\Http\Controllers\EIS;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Eis\EISTrustFund;
use App\Models\Eis\EISFund161;
use App\Models\Eis\EISFund163;
use App\Models\Eis\EISFund164;
use App\Models\Eis\EISFund101;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $years = EISTrustFund::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        $all_year = $years->pluck('year');

        $trust_year = EISTrustFund::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        $all_trustyear = $trust_year->pluck('year');
        $permMaxtrustYear = $all_trustyear->max();
        $selectedtrustYear = $request->input('yeartrust') ?? $permMaxtrustYear;

        $query = EISTrustFund::query();
        $filteredtrust = $query->when($selectedtrustYear, function($q) use ($selectedtrustYear) {
            return $q->whereYear('date', $selectedtrustYear); 
        });

        $trustData = $filteredtrust->select( 'id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->get();

        $curr_bal_collection = $filteredtrust->select('end_bal', 'date')
        ->orderBy('id', 'desc')
        ->get();

        $latest_balance = $curr_bal_collection->first()->end_bal ?? 0;

        $trustinflow = $filteredtrust->select('collections')
        ->avg('collections');

        $trustoutflow = $filteredtrust->select('expenditures')
        ->avg('expenditures');

        $trustratio = ($trustinflow/$trustoutflow);

        //------------------------------------------------------FUND 161
        $f161_year = EISFund161::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        $all_161year = $f161_year->pluck('year');
        $permMax161Year = $all_161year->max();
        $selected161Year = $request->input('year161') ?? $permMax161Year;

        $query = EISFund161::query();
        $filtered161 = $query->when($selected161Year, function($q) use ($selected161Year) {
            return $q->whereYear('date', $selected161Year); 
        });

        $curr_bal_161 = $filtered161->select('end_bal')
        ->orderBy('id', 'desc')
        ->get();

        $latest_161 = $curr_bal_161->first()->end_bal ?? 0;

        $fund161Data = $filtered161->select('id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        $avail_exp_Data = $filtered161->select('id', 'date', 'avail_bal', 'expenditures')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        $f161inflow = $filtered161->select('collections')
        ->avg('collections');

        $f161outflow = $filtered161->select('expenditures')
        ->avg('expenditures');

        $f161ratio = ($f161inflow/$f161outflow);

        //--------------------------------------------------------FUND 101
        $f101_year = EISFund101::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        $all_101year = $f101_year->pluck('year');
        $permMax101Year = $all_101year->max();
        $selected101Year = $request->input('year101') ?? $permMax101Year;

        $query = EISFund101::query();
        $filtered101 = $query->when($selected101Year, function($q) use ($selected101Year) {
            return $q->whereYear('date', $selected101Year); 
        });

        $psExpAvg = $filtered101->select('ps_exp')
        ->avg('ps_exp');
        $psburnratio = ($psExpAvg/834388220.00)*100;

        $psObliSum = $filtered101->select('ps_obli')
        ->sum('ps_obli');
        $psDisbSum = $filtered101->select('ps_disburse')
        ->sum('ps_disburse');
        $psExpSum = $filtered101->select('ps_exp')
        ->sum('ps_exp');

        $psObliPerc = ($psObliSum/$psExpSum)*100;
        $psDisbPerc = ($psDisbSum/$psExpSum)*100;

        $coExpAvg = $filtered101->select('co_exp')
        ->avg('co_exp');
        $coburnratio = ($coExpAvg/152471000.00)*100;

        $coObliSum = $filtered101->select('co_obli')
        ->sum('co_obli');
        $coDisbSum = $filtered101->select('co_disburse')
        ->sum('co_disburse');
        $coExpSum = $filtered101->select('co_exp')
        ->sum('co_exp');
        $coliqperc = ($coDisbSum/$coExpSum)*100;

        $coObliPerc = ($coObliSum/$coExpSum)*100;
        $coDisbPerc = ($coDisbSum/$coExpSum)*100;

        $moExpAvg = $filtered101->select('mooe_exp')
        ->avg('mooe_exp');
        $moburnratio = ($moExpAvg/313089798.00)*100;

        $moObliSum = $filtered101->select('mooe_obli')
        ->sum('mooe_obli');
        $moDisbSum = $filtered101->select('mooe_disburse')
        ->sum('mooe_disburse');
        $moExpSum = $filtered101->select('mooe_exp')
        ->sum('mooe_exp');
        $moliqperc = ($moDisbSum/$moExpSum)*100;

        $moObliPerc = ($moObliSum/$moExpSum)*100;
        $moDisbPerc = ($moDisbSum/$moExpSum)*100;

        $exp_Data = $filtered101->select('id', 'date', 'ps_exp', 'co_exp', 'mooe_exp')
        ->orderBy('date','desc')
        ->get();

        //-----------------------------------------------------------FUND 163
        $f163_year = EISFund163::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        $all_163year = $f163_year->pluck('year');
        $permMax163Year = $all_163year->max();
        $selected163Year = $request->input('year163') ?? $permMax163Year;

        $query = EISFund163::query();
        $filtered163 = $query->when($selected163Year, function($q) use ($selected163Year) {
            return $q->whereYear('date', $selected163Year); 
        });

        $curr_bal_163 = $filtered163->select('end_bal')
        ->orderBy('id', 'desc')
        ->get();

        $latest_163 = $curr_bal_163->first()->end_bal ?? 0;

        $fund163Data = $filtered163->select('id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        $avail_exp_163Data = $filtered163->select('id', 'date', 'avail_bal', 'expenditures')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        $f163inflow = $filtered163->select('collections')
        ->avg('collections');

        $f163outflow = $filtered163->select('expenditures')
        ->avg('expenditures');

        $f163ratio = ($f163inflow/$f163outflow);

        //-----------------------------------------------------------FUND 164
        $f164_year = EISFund164::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        $all_164year = $f164_year->pluck('year');
        $permMax164Year = $all_164year->max();
        $selected164Year = $request->input('year164') ?? $permMax164Year;

        $query = EISFund164::query();
        $filtered164 = $query->when($selected164Year, function($q) use ($selected164Year) {
            return $q->whereYear('date', $selected164Year); 
        });

        $curr_bal_164 = $filtered164->select('end_bal')
        ->orderBy('id', 'desc')
        ->get();

        $latest_164 = $curr_bal_164->first()->end_bal ?? 0;

        $fund164Data = $filtered164->select('id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        $avail_exp_164Data = $filtered164->select('id', 'date', 'avail_bal', 'expenditures')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        $f164inflow = $filtered164->select('collections')
        ->sum('collections');

        $f164outflow = $filtered164->select('expenditures')
        ->sum('expenditures');

        $f164ratio = ($f164inflow/$f164outflow);

        return view('eis.fund', [
            'stats' => [
                'trustcurr_bal' => $latest_balance,
                'trust_ratio' => number_format($trustratio, 2),
                'trust_inflow' => number_format($trustinflow, 2),
                'trust_outflow' => number_format($trustoutflow, 2),
                'all_year' => $all_year,

                'curr_161' => number_format($latest_161, 2),
                'f161_ratio' => number_format($f161ratio, 2),
                'f161_inflow' => number_format($f161inflow, 2),
                'f161_outflow' => number_format($f161outflow, 2),
                'all_161year' => $all_161year,

                'psutil' => number_format($psburnratio, 2),
                'coutil' => number_format($coburnratio, 2),
                'moutil' => number_format($moburnratio, 2),
                'all_101year' => $all_101year,
                

                'curr_163' => number_format($latest_163, 2),
                'f163_ratio' => number_format($f163ratio, 2),
                'f163_inflow' => number_format($f163inflow, 2),
                'f163_outflow' => number_format($f163outflow, 2),
                'all_163year' => $all_163year,

                'curr_164' => number_format($latest_164, 2),
                'f164_ratio' => number_format($f164ratio, 2),
                'f164_inflow' => number_format($f164inflow, 2),
                'f164_outflow' => number_format($f164outflow, 2),
                'all_164year' => $all_164year,


            ],

            'charts' => [
                'trust_labels' => $trustData->pluck('date'),
                'trust_values' => $trustData->pluck('end_bal'),
                'trust_max' => $trustData->pluck('end_bal')->max(),
                'trust_min' => $trustData->pluck('end_bal')->min(),

                'f161_labels' => $fund161Data->pluck('date'),
                'f161_values' => $fund161Data->pluck('end_bal'),
                'f161_max' => $fund161Data->pluck('end_bal')->max(),
                'f161_min' => $fund161Data->pluck('end_bal')->min(),
                'f161_dates' => $avail_exp_Data->pluck('date'),
                'f161_avail' => $avail_exp_Data->pluck('avail_bal'),
                'f161_exps' => $avail_exp_Data->pluck('expenditures'),
                'f161_avail_max' => $avail_exp_Data->pluck('avail_bal')->max(),
                'f161_exp_max' => $avail_exp_Data->pluck('expenditures')->max(),

                'f101_labels' => $exp_Data->pluck('date'),
                'f101_psexp' => $exp_Data->pluck('ps_exp'),
                'f101_coexp' => $exp_Data->pluck('co_exp'),
                'f101_mooeexp' => $exp_Data->pluck('mooe_exp'),
                'f101_psdisbperc' => $psDisbSum,
                'f101_psobliperc' => $psObliSum,
                'f101_codisbperc' => $coDisbSum,
                'f101_coobliperc' => $coObliSum,
                'f101_modisbperc' => $moDisbSum,
                'f101_moobliperc' => $moObliSum,

                'f163_labels' => $fund163Data->pluck('date'),
                'f163_values' => $fund163Data->pluck('end_bal'),
                'f163_max' => $fund163Data->pluck('end_bal')->max(),
                'f163_min' => $fund163Data->pluck('end_bal')->min(),
                'f163_dates' => $avail_exp_163Data->pluck('date'),
                'f163_avail' => $avail_exp_163Data->pluck('avail_bal'),
                'f163_exps' => $avail_exp_163Data->pluck('expenditures'),
                'f163_avail_max' => $avail_exp_163Data->pluck('avail_bal')->max(),
                'f163_exp_max' => $avail_exp_163Data->pluck('expenditures')->max(),

                'f164_labels' => $fund164Data->pluck('date'),
                'f164_values' => $fund164Data->pluck('end_bal'),
                'f164_max' => $fund164Data->pluck('end_bal')->max(),
                'f164_min' => $fund164Data->pluck('end_bal')->min(),
                'f164_dates' => $avail_exp_164Data->pluck('date'),
                'f164_avail' => $avail_exp_164Data->pluck('avail_bal'),
                'f164_exps' => $avail_exp_164Data->pluck('expenditures'),
                'f164_avail_max' => $avail_exp_164Data->pluck('avail_bal')->max(),
                'f164_exp_max' => $avail_exp_164Data->pluck('expenditures')->max(),
            ],
            'selectedTrust' => $selectedtrustYear,
            'selected101' => $selected101Year,
            'selected161' => $selected161Year,
            'selected163' => $selected163Year,
            'selected164' => $selected164Year,
        ]);
    }
}
