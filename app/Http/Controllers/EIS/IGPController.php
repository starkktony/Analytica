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
    // Start with a base query for the EISCash model to prepare for filtering based on the selected year, and this will allow us to fetch the relevant cash data for the charts and stats in the view
        $query = EISCash::query();

        // Fetch all distinct years from the EISCash data to populate the year dropdown in the view, ensuring that the years are ordered in ascending order for better user experience in selecting the year, and this will allow users to filter the cash data based on the selected year in the view
        $years = EISCash::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        // Extract the years into a collection for easier manipulation and to pass to the view
        $all_year = $years->pluck('year');

        // Determine the selected year from the request, defaulting to the maximum year if not provided, and apply the year filter to the main query for fetching cash data, ensuring that we get the relevant cash records for the selected year to display in the charts and stats in the view
        $permMaxYear = $all_year->max();
        $selectedYear = $request->input('year') ?? $permMaxYear;

        // Apply the year filter to the main query for fetching cash data, using a raw SQL expression to extract the year from the date column, and get the filtered data for further processing in the charts and stats in the view
        $filteredQuery = $query->when($selectedYear, function($q) use ($selectedYear) {
            return $q->whereYear('date', $selectedYear);
        });

        // Fetch the cash data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view for cash on bank
        $balData = (clone $filteredQuery)
            ->select('id', 'date', 'balance')
            ->orderBy('date', 'desc')
            ->get();

        // Fetch the current balance and date from the most recent record in the filtered cash data to display in the stats section of the view, ensuring that it gets the latest balance and date based on the selected year and this will provide insights into the current financial status of cash on bank for that year
        $curr_bal_collection = EISCash::select('balance', 'date')
        ->orderBy('id', 'desc')
        ->get();

        // Get the latest balance and date from the collection, ensuring that if there are no records it defaults to 0 for balance and null for date, and this will allow the view to display meaningful values for the current balance and date of cash on bank even if there is no data for the selected year
        $latest_balance = $curr_bal_collection->first()->balance ?? 0;
        $latest_date = $curr_bal_collection->first()->date ?? null;

        // Calculate the total inflow and outflow for cash on bank based on the filtered data, and then calculate the net flow to provide insights into the financial performance of cash on bank for the selected year, ensuring that the calculations are done correctly to reflect the financial trends in the charts and stats in the view for cash on bank
        $inflow = EISCash::select('deposit')
        ->sum('deposit');
        $outflow = EISCash::select('withdrawal')
        ->sum('withdrawal');
        $netflow = $inflow - $outflow;

        //------------------------------------------Priv Companies

        // Fetch the private companies data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view for private companies
        $privData = EISCash::select('id', 'date', 'balance')
        ->orderBy('date','desc')
        ->take(150)
        ->get();

        // Fetch the total count of private companies to display in the stats section of the view, and this count is not affected by the year filter to show the overall total number of private companies, providing insights into the scale of private company involvement in the IGP for the stats section of the view
        $total_priv = EISPrivComp::count();
        $act_priv = EISPrivComp::where('end','>',date('Y-m-d'))
        ->count();
        $exp_priv = EISPrivComp::where('end','<=',date('Y-m-d'))
        ->count();

        // Calculate the percentage of active and expired private companies based on the total count, and this will provide insights into the proportion of active versus expired private companies in the IGP for the stats section of the view
        $act_perc = ($act_priv/$total_priv)*100;
        $exp_perc = ($exp_priv/$total_priv)*100;

        // Fetch the private companies data for the charts, selecting the relevant columns and ordering by sqm and rate per month in descending order to show the highest values first, and this will prepare the data for visualization in the charts in the view for private companies based on their sqm and rate per month
        $per_sqm = EISPrivComp::select('end', 'name', 'sqm')
        ->where('end', '>', date('Y-m-d'))
        ->orderBy('sqm', 'desc')
        ->get();

        // Fetch the private companies data for the charts, selecting the relevant columns and ordering by rate per month in descending order to show the highest values first, and this will prepare the data for visualization in the charts in the view for private companies based on their rate per month
        $per_rate = EISPrivComp::select('end', 'name', 'rate_per_month')
        ->where('end', '>', date('Y-m-d'))
        ->orderBy('rate_per_month', 'desc')
        ->get();

        //-------------------------------------------Business Development

        // Fetch the total count of government entities, gateway tenants, marketing tenants, and commart tenants to display in the stats section of the view, and these counts are not affected by the year filter to show the overall totals for each category, providing insights into the scale of involvement of different types of entities in the IGP for the stats section of the view
        $gateway_sqm = EISGateway::sum('sqm');
        $commart_sqm = EISCommart::sum('sqm');
        $marketing_sqm = EISMarketing::sum('sqm');
        $total_sqm = $gateway_sqm+$commart_sqm+$marketing_sqm;

        // Fetch the total rental for gateway tenants, marketing tenants, and commart tenants to display in the stats section of the view, and these totals are not affected by the year filter to show the overall rental income from each category, providing insights into the financial contributions of different types of entities in the IGP for the stats section of the view
        $gateway_rent = EISGateway::sum('rental');
        $commart_rent = EISCommart::sum('rental');
        $marketing_rent = EISMarketing::sum('rental');
        $total_rental = $gateway_rent+$commart_rent+$marketing_rent;


        // Fetch the government entities, gateway tenants, marketing tenants, and commart tenants data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view for business development entities in the IGP
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
