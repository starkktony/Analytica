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
        // Fetch all distinct years from the EISTrustFund dataset to populate the year dropdown in the view, ensuring that the years are ordered in ascending order for better user experience in selecting the year
        $years = EISTrustFund::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        // Extract the years into a collection for easier manipulation and passing to the view, and this will allow the view to display the available years for filtering the data
        $all_year = $years->pluck('year');

        // Determine the maximum year from the dataset to use as the default selected year if none is provided in the request, and this will ensure that the view shows the most recent data by default when the user first visits the page
        $trust_year = EISTrustFund::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();
        $all_trustyear = $trust_year->pluck('year');
        $permMaxtrustYear = $all_trustyear->max();

        // Get the selected year for the trust fund data from the request, defaulting to the maximum year if not provided, and this will allow the user to filter the trust fund data based on the year they select from the dropdown in the view
        $selectedtrustYear = $request->input('yeartrust') ?? $permMaxtrustYear;

        // Start building the query for fetching the trust fund data, and apply the year filter based on the selected year to get the relevant data for the stats and charts in the view, ensuring that the data is filtered correctly to reflect the user's selection
        $query = EISTrustFund::query();
        $filteredtrust = $query->when($selectedtrustYear, function($q) use ($selectedtrustYear) {
            return $q->whereYear('date', $selectedtrustYear); 
        });

        // Fetch the trust fund data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view
        $trustData = $filteredtrust->select( 'id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->get();

        // Fetch the current balance from the most recent record in the filtered trust fund data to display in the stats section of the view, ensuring that it gets the latest balance based on the selected year and this will provide insights into the current financial status of the trust fund for that year
        $curr_bal_collection = $filteredtrust->select('end_bal', 'date')
        ->orderBy('id', 'desc')
        ->get();

        // Get the latest balance from the collection, ensuring that if there are no records it defaults to 0, and this will allow the view to display a meaningful value for the current balance even if there is no data for the selected year
        $latest_balance = $curr_bal_collection->first()->end_bal ?? 0;

        // Calculate the average inflow and outflow for the trust fund based on the filtered data, and then calculate the ratio of inflow to outflow to provide insights into the financial performance of the trust fund for the selected year, ensuring that the calculations are done correctly to reflect the financial trends in the charts and stats in the view
        $trustinflow = $filteredtrust->select('collections')
        ->avg('collections');

        // Calculate the average outflow for the trust fund based on the filtered data, and this will be used in conjunction with the inflow to calculate the ratio, providing insights into the financial performance of the trust fund for the selected year in the charts and stats in the view
        $trustoutflow = $filteredtrust->select('expenditures')
        ->avg('expenditures');

        // Calculate the ratio of inflow to outflow for the trust fund, ensuring that it handles cases where the outflow might be zero to avoid division by zero errors, and this ratio will provide insights into the financial health of the trust fund for the selected year in the charts and stats in the view
        $trustratio = ($trustinflow/$trustoutflow);

        //------------------------------------------------------FUND 161
        // Fetch all distinct years from the EISFund161 dataset to populate the year dropdown for Fund 161 in the view, ensuring that the years are ordered in ascending order for better user experience in selecting the year for Fund 161 data
        $f161_year = EISFund161::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        // Extract the years into a collection for easier manipulation and passing to the view, and this will allow the view to display the available years for filtering the Fund 161 data
        $all_161year = $f161_year->pluck('year');
        $permMax161Year = $all_161year->max();
        $selected161Year = $request->input('year161') ?? $permMax161Year;

        // Start building the query for fetching the Fund 161 data, and apply the year filter based on the selected year to get the relevant data for the stats and charts in the view, ensuring that the data is filtered correctly to reflect the user's selection for Fund 161
        $query = EISFund161::query();
        $filtered161 = $query->when($selected161Year, function($q) use ($selected161Year) {
            return $q->whereYear('date', $selected161Year); 
        });

        // Fetch the current balance from the most recent record in the filtered Fund 161 data to display in the stats section of the view, ensuring that it gets the latest balance based on the selected year and this will provide insights into the current financial status of Fund 161 for that year
        $curr_bal_161 = $filtered161->select('end_bal')
        ->orderBy('id', 'desc')
        ->get();

        // Get the latest balance from the collection, ensuring that if there are no records it defaults to 0, and this will allow the view to display a meaningful value for the current balance of Fund 161 even if there is no data for the selected year
        $latest_161 = $curr_bal_161->first()->end_bal ?? 0;

        // Fetch the Fund 161 data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view for Fund 161
        $fund161Data = $filtered161->select('id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        // Fetch the available balance and expenditures from the filtered Fund 161 data to prepare the data for the charts showing available balance and expenditures over time, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will help in visualizing the financial trends of Fund 161 in the charts in the view
        $avail_exp_Data = $filtered161->select('id', 'date', 'avail_bal', 'expenditures')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        // Calculate the average inflow and outflow for Fund 161 based on the filtered data, and then calculate the ratio of inflow to outflow to provide insights into the financial performance of Fund 161 for the selected year, ensuring that the calculations are done correctly to reflect the financial trends in the charts and stats in the view for Fund 161
        $f161inflow = $filtered161->select('collections')
        ->avg('collections');

        // Calculate the average outflow for Fund 161 based on the filtered data, and this will be used in conjunction with the inflow to calculate the ratio, providing insights into the financial performance of Fund 161 for the selected year in the charts and stats in the view
        $f161outflow = $filtered161->select('expenditures')
        ->avg('expenditures');

        // Calculate the ratio of inflow to outflow for Fund 161, ensuring that it handles cases where the outflow might be zero to avoid division by zero errors, and this ratio will provide insights into the financial health of Fund 161 for the selected year in the charts and stats in the view
        $f161ratio = ($f161inflow/$f161outflow);

        //--------------------------------------------------------FUND 101
        // Fetch all distinct years from the EISFund101 dataset to populate the year dropdown for Fund 101 in the view, ensuring that the years are ordered in ascending order for better user experience in selecting the year for Fund 101 data
        $f101_year = EISFund101::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        // Extract the years into a collection for easier manipulation and passing to the view, and this will allow the view to display the available years for filtering the Fund 101 data
        $all_101year = $f101_year->pluck('year');
        $permMax101Year = $all_101year->max();
        $selected101Year = $request->input('year101') ?? $permMax101Year;

        // Start building the query for fetching the Fund 101 data, and apply the year filter based on the selected year to get the relevant data for the stats and charts in the view, ensuring that the data is filtered correctly to reflect the user's selection for Fund 101
        $query = EISFund101::query();
        $filtered101 = $query->when($selected101Year, function($q) use ($selected101Year) {
            return $q->whereYear('date', $selected101Year); 
        });

        // Calculate the average personal services expenditure for Fund 101 based on the filtered data, and then calculate the burn ratio by comparing it to a predefined benchmark, providing insights into the financial performance of the personal services component of Fund 101 for the selected year in the charts and stats in the view
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

        // Calculate the average capital outlay expenditure for Fund 101 based on the filtered data, and then calculate the burn ratio by comparing it to a predefined benchmark, providing insights into the financial performance of the capital outlay component of Fund 101 for the selected year in the charts and stats in the view
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

        // Calculate the average maintenance and other operating expenses for Fund 101 based on the filtered data, and then calculate the burn ratio by comparing it to a predefined benchmark, providing insights into the financial performance of the maintenance and other operating expenses component of Fund 101 for the selected year in the charts and stats in the view
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

        // Fetch the Fund 101 data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view for Fund 101, including the available balance and expenditures to help in visualizing the financial trends of Fund 101 in the charts in the view
        $exp_Data = $filtered101->select('id', 'date', 'ps_exp', 'co_exp', 'mooe_exp')
        ->orderBy('date','desc')
        ->get();

        //-----------------------------------------------------------FUND 163
        // Fetch all distinct years from the EISFund163 dataset to populate the year dropdown for Fund 163 in the view, ensuring that the years are ordered in ascending order for better user experience in selecting the year for Fund 163 data
        $f163_year = EISFund163::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        // Extract the years into a collection for easier manipulation and passing to the view, and this will allow the view to display the available years for filtering the Fund 163 data
        $all_163year = $f163_year->pluck('year');
        $permMax163Year = $all_163year->max();
        $selected163Year = $request->input('year163') ?? $permMax163Year;

        // Start building the query for fetching the Fund 163 data, and apply the year filter based on the selected year to get the relevant data for the stats and charts in the view, ensuring that the data is filtered correctly to reflect the user's selection for Fund 163
        $query = EISFund163::query();
        $filtered163 = $query->when($selected163Year, function($q) use ($selected163Year) {
            return $q->whereYear('date', $selected163Year); 
        });

        // Fetch the current balance from the most recent record in the filtered Fund 163 data to display in the stats section of the view, ensuring that it gets the latest balance based on the selected year and this will provide insights into the current financial status of Fund 163 for that year
        $curr_bal_163 = $filtered163->select('end_bal')
        ->orderBy('id', 'desc')
        ->get();

        // Get the latest balance from the collection, ensuring that if there are no records it defaults to 0, and this will allow the view to display a meaningful value for the current balance of Fund 163 even if there is no data for the selected year
        $latest_163 = $curr_bal_163->first()->end_bal ?? 0;

        // Fetch the Fund 163 data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view for Fund 163
        $fund163Data = $filtered163->select('id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        // Fetch the available balance and expenditures from the filtered Fund 163 data to prepare the data for the charts showing available balance and expenditures over time, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will help in visualizing the financial trends of Fund 163 in the charts in the view
        $avail_exp_163Data = $filtered163->select('id', 'date', 'avail_bal', 'expenditures')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        // Calculate the average inflow and outflow for Fund 163 based on the filtered data, and then calculate the ratio of inflow to outflow to provide insights into the financial performance of Fund 163 for the selected year, ensuring that the calculations are done correctly to reflect the financial trends in the charts and stats in the view for Fund 163
        $f163inflow = $filtered163->select('collections')
        ->avg('collections');
        $f163outflow = $filtered163->select('expenditures')
        ->avg('expenditures');

        // Calculate the ratio of inflow to outflow for Fund 163, ensuring that it handles cases where the outflow might be zero to avoid division by zero errors, and this ratio will provide insights into the financial health of Fund 163 for the selected year in the charts and stats in the view
        $f163ratio = ($f163inflow/$f163outflow);

        //-----------------------------------------------------------FUND 164
        // Fetch all distinct years from the EISFund164 dataset to populate the year dropdown for Fund 164 in the view, ensuring that the years are ordered in ascending order for better user experience in selecting the year for Fund 164 data
        $f164_year = EISFund164::select(DB::raw('YEAR(date) as year'))
        ->distinct()
        ->orderBy('year', 'asc')
        ->get();

        // Extract the years into a collection for easier manipulation and passing to the view, and this will allow the view to display the available years for filtering the Fund 164 data
        $all_164year = $f164_year->pluck('year');
        $permMax164Year = $all_164year->max();
        $selected164Year = $request->input('year164') ?? $permMax164Year;

        // Start building the query for fetching the Fund 164 data, and apply the year filter based on the selected year to get the relevant data for the stats and charts in the view, ensuring that the data is filtered correctly to reflect the user's selection for Fund 164
        $query = EISFund164::query();
        $filtered164 = $query->when($selected164Year, function($q) use ($selected164Year) {
            return $q->whereYear('date', $selected164Year); 
        });

        // Fetch the current balance from the most recent record in the filtered Fund 164 data to display in the stats section of the view, ensuring that it gets the latest balance based on the selected year and this will provide insights into the current financial status of Fund 164 for that year
        $curr_bal_164 = $filtered164->select('end_bal')
        ->orderBy('id', 'desc')
        ->get();

        // Get the latest balance from the collection, ensuring that if there are no records it defaults to 0, and this will allow the view to display a meaningful value for the current balance of Fund 164 even if there is no data for the selected year
        $latest_164 = $curr_bal_164->first()->end_bal ?? 0;

        // Fetch the Fund 164 data for the charts, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will prepare the data for visualization in the charts in the view for Fund 164
        $fund164Data = $filtered164->select('id', 'date', 'end_bal')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        // Fetch the available balance and expenditures from the filtered Fund 164 data to prepare the data for the charts showing available balance and expenditures over time, selecting the relevant columns and ordering by date in descending order to show the most recent data first, and this will help in visualizing the financial trends of Fund 164 in the charts in the view
        $avail_exp_164Data = $filtered164->select('id', 'date', 'avail_bal', 'expenditures')
        ->orderBy('date','desc')
        ->take(75)
        ->get();

        // Calculate the average inflow and outflow for Fund 164 based on the filtered data, and then calculate the ratio of inflow to outflow to provide insights into the financial performance of Fund 164 for the selected year, ensuring that the calculations are done correctly to reflect the financial trends in the charts and stats in the view for Fund 164
        $f164inflow = $filtered164->select('collections')
        ->sum('collections');
        $f164outflow = $filtered164->select('expenditures')
        ->sum('expenditures');
        $f164ratio = ($f164inflow/$f164outflow);

        // Pass all the prepared data to the view for rendering, including stats, chart data, and the selected years to maintain the state of the dropdowns in the view, ensuring that the data is properly formatted for use in the Blade template and the JavaScript charts, and this will allow the view to display all the relevant information and visualizations based on the user's selections
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
