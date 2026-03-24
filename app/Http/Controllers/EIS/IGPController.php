<?php

namespace App\Http\Controllers\EIS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Eis\EISCash;
use App\Models\Eis\EISPrivComp;
use App\Models\Eis\EISGovt;
use App\Models\Eis\EISGateway;
use App\Models\Eis\EISMarketing;
use App\Models\Eis\EISCommart;

class IGPController extends Controller
{
    public function index(Request $request)
    {
    //--------------------------------------------Cash on Bank
        $query = EISCash::query();

        $years = EISCash::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        $all_year = $years->pluck('year');

        $permMaxYear = $all_year->max();
        $selectedYear = $request->input('year') ?? $permMaxYear;

        $filteredQuery = $query->when($selectedYear, function($q) use ($selectedYear) {
            return $q->whereYear('date', $selectedYear);
        });

        $balData = (clone $filteredQuery)
            ->select('id', 'date', 'balance')
            ->orderBy('date', 'desc')
            ->get();

        $curr_bal_collection = EISCash::select('balance', 'date')
        ->orderBy('id', 'desc')
        ->get();

        $latest_balance = $curr_bal_collection->first()->balance ?? 0;
        $latest_date = $curr_bal_collection->first()->date ?? null;

        $inflow = EISCash::select('deposit')
        ->sum('deposit');

        $outflow = EISCash::select('withdrawal')
        ->sum('withdrawal');

        $netflow = $inflow - $outflow;

        //------------------------------------------Priv Companies

        $privData = EISCash::select('id', 'date', 'balance')
        ->orderBy('date','desc')
        ->take(150)
        ->get();

        $total_priv = EISPrivComp::count();

        $act_priv = EISPrivComp::where('end','>',date('Y-m-d'))
        ->count();

        $exp_priv = EISPrivComp::where('end','<=',date('Y-m-d'))
        ->count();

        $act_perc = ($act_priv/$total_priv)*100;
        $exp_perc = ($exp_priv/$total_priv)*100;

        $per_sqm = EISPrivComp::select('end', 'name', 'sqm')
        ->where('end', '>', date('Y-m-d'))
        ->orderBy('sqm', 'desc')
        ->get();

        $per_rate = EISPrivComp::select('end', 'name', 'rate_per_month')
        ->where('end', '>', date('Y-m-d'))
        ->orderBy('rate_per_month', 'desc')
        ->get();

        //-------------------------------------------Business Development

        $gateway_sqm = EISGateway::sum('sqm');
        $commart_sqm = EISCommart::sum('sqm');
        $marketing_sqm = EISMarketing::sum('sqm');
        $total_sqm = $gateway_sqm+$commart_sqm+$marketing_sqm;

        $gateway_rent = EISGateway::sum('rental');
        $commart_rent = EISCommart::sum('rental');
        $marketing_rent = EISMarketing::sum('rental');
        $total_rental = $gateway_rent+$commart_rent+$marketing_rent;



        return view('eis.igp', [
            'stats' => [
                'netflow' => $netflow,
                'currbal' => $latest_balance,
                'currdate' => $latest_date,
                'all_year' => $all_year,
                'priv_total' => $total_priv,
                'priv_rate' => EISPrivComp::where('end','>=', date('Y-m-d'))->sum('rate_per_month'),
                'priv_sqm' => EISPrivComp::where('end','>=', date('Y-m-d'))->sum('sqm'),
                'gov_total' => EISGovt::count(),
                'gateway_total' => EISGateway::count(),
                'marketing_total' => EISMarketing::count(),
                'commart_total' => EISCommart::count(),
                'sqm_total' => $total_sqm,
                'rental_total' => $total_rental,
            ],        
            'perc' => [
                'act_perc' => number_format($act_perc, 2),
                'exp_perc' => number_format($exp_perc, 2),
            ],    
            'charts' => [
                'balance_labels' => $balData->pluck('date'),
                'balance_values' => $balData->pluck('balance'),
                'bal_max' => $balData->pluck('balance')->max(),
                'act_priv' => $act_priv,
                'exp_priv' => $exp_priv,
                'sqm_labels' => $per_sqm->pluck('name'),
                'sqm_values' => $per_sqm->pluck('sqm'),
                'rate_labels' => $per_rate->pluck('name'),
                'rate_values' => $per_rate->pluck('rate_per_month'),

                'gateway_sqm' => $gateway_sqm,
                'commart_sqm' => $commart_sqm,
                'marketing_sqm' => $marketing_sqm,
                'gateway_rent' => $gateway_rent,
                'commart_rent' => $commart_rent,
                'marketing_rent' => $marketing_rent,
            ],
            'selectedYear' => $selectedYear,
         ]);
    }
}
