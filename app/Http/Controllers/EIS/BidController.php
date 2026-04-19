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

        // Fetch all distinct years from the EISBid model to populate the year dropdown in the view, ensuring that the years are ordered in descending order for better user experience
        $bid_year = EISBid::selectRaw('YEAR(date_posted) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');

        // Determine the maximum year from the fetched years to use as the default selected year in the dropdown if no year is provided in the request, and this will help in showing the most recent data by default when the user visits the page
        $bidpermMaxYear = $bid_year->first();

        // Get the selected year from the request, defaulting to the maximum year if not provided, and start building the query for fetching bids based on the selected year
        $bidselectedYear = $request->input('bidyear') ?? $bidpermMaxYear;
        $bidquery = EISBid::query();

        // Apply the year filter to the main query for fetching bids, using a raw SQL expression to extract the year from the date_posted column, and get the filtered data for further processing in the charts and stats
        if ($bidselectedYear) {
            $bidquery->whereYear('date_posted', $bidselectedYear);
        }
        $bidmaxYear = $bidselectedYear ?? $bid_year->first();

        // Logic to determine the default 5-year range for the charts based on the selected year, ensuring that if the selected year is within the last 5 years, it shows the last 5 years, otherwise it shows a range starting from the selected year to 4 years ahead, and this will help in showing relevant data in the charts based on the user's selection
        $biddefaultStart = $bidpermMaxYear - 4;
        $biddefaultEnd = $bidpermMaxYear;

        // Determine the start and end years for the charts based on the selected year and the default range, ensuring that if the selected year is within the default range, it uses the default range, otherwise it adjusts the range to start from the selected year, and this will ensure that the charts display data for a relevant range of years based on the user's selection
        if (($bidselectedYear >= $biddefaultStart && $bidselectedYear <= $biddefaultEnd) || !$bidselectedYear) {
            $bidstart = $biddefaultStart;
            $bidend = $biddefaultEnd;
        } else {
            $bidstart = $bidselectedYear;
            $bidend = $bidselectedYear + 4;
        }

        // Apply the year filter to the main query for fetching bids, using a raw SQL expression to extract the year from the date_posted column, and get the filtered data for further processing in the charts and stats
        $bidfilteredData = (clone $bidquery)
        ->when($bidselectedYear, fn($q) => $q->where(DB::raw('YEAR(date_posted)'), $bidselectedYear))
        ->get();

        // Calculate the total bid amount and approved budget for completed and ongoing bids from the filtered data, and then calculate the ratio of approved budget to bid amount, which will be displayed in the stats section of the view to show the financial performance of the bids
        $bid_amount = $bidfilteredData->whereIn('status',['completed','on-going'])->sum('bid_amt');
        $appr_budget = $bidfilteredData->whereIn('status',['completed','on-going'])->sum('appr_budget');

        // Calculate the ratio of approved budget to bid amount, ensuring that it handles the case where bid_amount is 0 to avoid division by zero errors, and this ratio will be displayed in the view to show the efficiency of the bids in terms of budget utilization
        $bidratio = ($appr_budget/$bid_amount)*100;

        // Fetch the count of completed, ongoing, failed, and total bids for each year in the determined range to prepare the data for the charts, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the trends of bid statuses over the selected year range in the charts
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

        // Fetch the total count of bids for each year in the determined range to prepare the data for the line chart showing total bids per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the overall trend of bid counts over the selected year range in the charts
        $bidTotal = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        // Fetch the total approved budget for completed and ongoing bids for each year in the determined range to prepare the data for the line charts showing approved budget and bid amount per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the financial trends of the bids over the selected year range in the charts
        $bidAppr = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(appr_budget) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        // Fetch the total bid amount for completed and ongoing bids for each year in the determined range to prepare the data for the line charts showing approved budget and bid amount per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the financial trends of the bids over the selected year range in the charts
        $bidAmt = EISBid::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(bid_amt) as total'))
        ->whereYear('date_posted', '>=', $bidstart)
        ->whereYear('date_posted', '<=', $bidend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        // Map the display years to their corresponding total counts for completed, ongoing, failed, and total bids for the line charts, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trends of bid statuses and financials over the selected year range in the charts
        $infra_year = EISInfra::selectRaw('YEAR(date_posted) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');
        
        // Map the display years to their corresponding total counts for completed, ongoing, failed, and total bids for the line charts, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trends of bid statuses and financials over the selected year range in the charts
        $infrapermMaxYear = $infra_year->first();
        // Get the selected year for infrastructure from the request, defaulting to the maximum year if not provided, and start building the query for fetching infrastructure data based on the selected year
        $infraselectedYear = $request->input('infrayear') ?? $infrapermMaxYear;

        // Apply the year filter to the main query for fetching infrastructure data, using a raw SQL expression to extract the year from the date_posted column, and get the filtered data for further processing in the charts and stats
        $infraquery = EISInfra::query();
        if ($infraselectedYear) {
            $infraquery->whereYear('date_posted', $infraselectedYear);
        }

        // Logic to determine the default 5-year range for the infrastructure charts based on the selected year, ensuring that if the selected year is within the last 5 years, it shows the last 5 years, otherwise it shows a range starting from the selected year to 4 years ahead, and this will help in showing relevant data in the infrastructure charts based on the user's selection
        $inframaxYear = $infraselectedYear ?? $infra_year->first();
        $infradefaultStart = $infrapermMaxYear - 4;
        $infradefaultEnd = $infrapermMaxYear;

        // Determine the start and end years for the infrastructure charts based on the selected year and the default range, ensuring that if the selected year is within the default range, it uses the default range, otherwise it adjusts the range to start from the selected year, and this will ensure that the infrastructure charts display data for a relevant range of years based on the user's selection
        if (($infraselectedYear >= $infradefaultStart && $infraselectedYear <= $infradefaultEnd) || !$infraselectedYear) {
            $infrastart = $infradefaultStart;
            $infraend = $infradefaultEnd;
        } else {
            $infrastart = $infraselectedYear;
            $infraend = $infraselectedYear + 4;
        }

        // Apply the year filter to the main query for fetching infrastructure data, using a raw SQL expression to extract the year from the date_posted column, and get the filtered data for further processing in the charts and stats
        $infrafilteredData = (clone $infraquery)
        ->when($infraselectedYear, fn($q) => $q->where(DB::raw('YEAR(date_posted)'), $infraselectedYear))
        ->get();

        // Calculate the total infrastructure amount and approved budget for completed and ongoing infrastructure projects from the filtered data, and then calculate the ratio of approved budget to infrastructure amount, which will be displayed in the stats section of the view to show the financial performance of the infrastructure projects
        $infra_amount = $infrafilteredData->whereIn('status',['completed','on-going'])->sum('bid_amt');
        $infra_budget = $infrafilteredData->whereIn('status',['completed','on-going'])->sum('appr_budget');
        $infraratio = ($infra_budget/$infra_amount)*100;

        // Fetch the count of completed, ongoing, failed, and total infrastructure projects for each year in the determined range to prepare the data for the charts, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the trends of infrastructure project statuses over the selected year range in the charts
        $infracomplete = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->where('status', 'completed')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        // Map the display years to their corresponding total counts for completed infrastructure projects for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of completed infrastructure projects over the selected year range in the charts
        $infraComp = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infracomplete) {
            return [
                'year' => $year,
                'total' => isset($infracomplete[$year]) ? (float)$infracomplete[$year]->total : 0
            ];
        });

        // Fetch the count of ongoing infrastructure projects for each year in the determined range to prepare the data for the line chart showing ongoing infrastructure projects per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the trend of ongoing infrastructure projects over the selected year range in the charts
        $infraongo = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->where('status', 'on-going')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        // Map the display years to their corresponding total counts for ongoing infrastructure projects for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of ongoing infrastructure projects over the selected year range in the charts
        $infraOngoing = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infraongo) {
            return [
                'year' => $year,
                'total' => isset($infraongo[$year]) ? (float)$infraongo[$year]->total : 0
            ];
        });

        // Fetch the count of failed infrastructure projects for each year in the determined range to prepare the data for the line chart showing failed infrastructure projects per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the trend of failed infrastructure projects over the selected year range in the charts
        $infrafailed = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->where('status', 'failed')
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        // Map the display years to their corresponding total counts for failed infrastructure projects for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of failed infrastructure projects over the selected year range in the charts
        $infraFail = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infrafailed) {
            return [
                'year' => $year,
                'total' => isset($infrafailed[$year]) ? (float)$infrafailed[$year]->total : 0
            ];
        });

        // Fetch the total count of infrastructure projects for each year in the determined range to prepare the data for the line chart showing total infrastructure projects per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the overall trend of infrastructure project counts over the selected year range in the charts
        $infratotal1 = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('count(*) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');

        // Map the display years to their corresponding total counts for infrastructure projects for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the overall trend of infrastructure project counts over the selected year range in the charts
        $infraTotal = collect(range($infrastart, $infraend))
            ->map(function ($year) use ($infratotal1) {
            return [
                'year' => $year,
                'total' => isset($infratotal1[$year]) ? (float)$infratotal1[$year]->total : 0
            ];
        });

        // Fetch the total approved budget for completed and ongoing infrastructure projects for each year in the determined range to prepare the data for the line charts showing approved budget and infrastructure amount per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the financial trends of the infrastructure projects over the selected year range in the charts
        $infraAppr = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(appr_budget) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        // Fetch the total infrastructure amount for completed and ongoing infrastructure projects for each year in the determined range to prepare the data for the line charts showing approved budget and infrastructure amount per year, ensuring that the data is grouped by year and ordered in ascending order for proper chart display, and this will help in visualizing the financial trends of the infrastructure projects over the selected year range in the charts
        $infraAmt = EISInfra::select(DB::raw('YEAR(date_posted) as year'), DB::raw('sum(bid_amt) as total'))
        ->whereYear('date_posted', '>=', $infrastart)
        ->whereYear('date_posted', '<=', $infraend)
        ->whereIn('status',['completed','on-going'])
        ->groupBy('year')
        ->orderBy('year', 'asc')
        ->get();

        //Pass all the prepared data to the view for rendering, including stats, chart data, and the selected years to maintain the state of the dropdowns in the view, ensuring that the data is properly formatted for use in the Blade template and the JavaScript charts, and this will allow the view to display all the relevant information and visualizations based on the user's selections
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
