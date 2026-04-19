<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudyController extends Controller
{
    public function index(Request $request)
    {
        // Get all distinct years for the dropdown, ordered in descending order
        $all_year = RDStudy::select('syear')
        ->distinct()
        ->orderBy('syear', 'desc')
        ->pluck('syear');

        // Determine the selected year, defaulting to the maximum year if not provided, and start building the query
        $permMaxYear = RDStudy::max('syear');
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDStudy::query();

        // Apply the year filter to the main query for the other charts and stats, and get the filtered data for further processing
        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('syear', $selectedYear))
        ->get();

        // Determine the range of years to display in the charts based on the selected year and the available years in the dataset, ensuring that if the selected year is not found or is too close to the end of the list, it adjusts to show the last 10 years
        $maxYear = $selectedYear ?? RDStudy::max('syear');
        // Ensure we have a valid max year and calculate the second year for percentage change calculations
        $secondYear = $maxYear - 1;
        
        // Group the filtered data for the various pie charts, count the number of records for each group, and sort them in descending order for better visualization
        $type_res = $filteredData
        ->where('type', 'Research')
        ->count();

        $type_dev = $filteredData
        ->where('type', 'Development')
        ->count();

        $type_resdev = $filteredData
        ->where('type', 'Research and Development')
        ->count();

        $type_bw = $filteredData
        ->where('type', 'Book Writing')
        ->count();
        
        // Logic to determine the 10-year range for the stacked chart based on the position of the selected year in the list of all years
        $targetIndex = $all_year->search($selectedYear);

        // If the selected year is not found or is too close to the end of the list, adjust the starting index to show the last 10 years
        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        // Get the 10-year range of years to display in the charts, ensuring it's in ascending order for proper chart display, and this range will be used to filter the data for the charts to show trends over the last 10 years based on the selected year
        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

        //Fetch the total count of studies for each year in the display range to prepare the data for the line chart showing studies per year, and ensure it's ordered by year in ascending order for proper chart display
        $per_year = RDStudy::select('syear', DB::raw('count(*) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        // Map the display years to their corresponding counts for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of studies over the selected year range in the charts
        $count_year = $displayYears->mapWithKeys(function ($year) use ($per_year) {
            return [$year => (int)($per_year[$year]->total ?? 0)];
        });

        // Fetch the total count of studies for each year in the display range to prepare the data for the line chart showing research studies per year, filtering by type 'Research', and ensure it's ordered by year in ascending order for proper chart display
        $res_year = RDStudy::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        // Map the display years to their corresponding counts for the line chart showing research studies, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of research studies over the selected year range in the charts
        $res_count = $displayYears->mapWithKeys(function ($year) use ($res_year) {
            return [$year => (int)($res_year[$year]->total ?? 0)];
        });

        // Fetch the total count of studies for each year in the display range to prepare the data for the line chart showing research and development studies per year, filtering by type 'Research and Development', and ensure it's ordered by year in ascending order for proper chart display
        $resdev_year = RDStudy::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Research and Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        // Map the display years to their corresponding counts for the line chart showing research and development studies, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of research and development studies over the selected year range in the charts
        $resdev_count = $displayYears->mapWithKeys(function ($year) use ($resdev_year) {
            return [$year => (int)($resdev_year[$year]->total ?? 0)];
        });
        
        //Budget chart
        // Fetch the total budget for each year in the display range to prepare the data for the line chart showing total budget per year, and ensure it's ordered by year in ascending order for proper chart display
         $per_budget = RDStudy::select('syear', DB::raw('sum(budget) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        // Map the display years to their corresponding total budgets for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of total budget over the selected year range in the charts
        $budget_year = $displayYears->mapWithKeys(function ($year) use ($per_budget) {
            return [$year => (float)($per_budget[$year]->total ?? 0)];
        });

        // Fetch the total budget for research studies for each year in the display range to prepare the data for the line chart showing research budget per year, filtering by type 'Research', and ensure it's ordered by year in ascending order for proper chart display
        $budget_res = RDStudy::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        // Map the display years to their corresponding total research budgets for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of research budget over the selected year range in the charts
        $budget_research = $displayYears->mapWithKeys(function ($year) use ($budget_res) {
            return [$year => (float)($budget_res[$year]->total ?? 0)];
        });

        // Fetch the total budget for research and development studies for each year in the display range to prepare the data for the line chart showing research and development budget per year, filtering by type 'Research and Development', and ensure it's ordered by year in ascending order for proper chart display
        $budget_resdev = RDStudy::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Research and Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        // Map the display years to their corresponding total research and development budgets for the line chart, ensuring that if a year has no data, it defaults to 0, and this will be used to display the trend of research and development budget over the selected year range in the charts
        $budget_resdevelop = $displayYears->mapWithKeys(function ($year) use ($budget_resdev) {
            return [$year => (float)($budget_resdev[$year]->total ?? 0)];
        });

        // Calculate the percentage change in the number of studies for the selected year compared to the previous year, ensuring that if the previous year's value is 0, it handles the division by zero case and defaults the percentage change to 0, and this percentage will be displayed in the view to show the year-over-year change in studies
        $mxyear_value = RDStudy::where('syear', $maxYear)->count();
        $prevyear_value_test = RDStudy::where('syear', $secondYear)->count();
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;

        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        
        // Calculate the percentage of completed and ongoing studies for the stats section, ensuring that it handles cases where the total number of studies is 0 to avoid division by zero errors, and these percentages will be displayed in the view to show the distribution of study statuses
        $total_prog = $filteredData->count();
        $complete_prog = $filteredData->where('status', 'Completed')->count();
        $ongoing_prog = $filteredData->where('status', 'Ongoing')->count();

        // Calculate the percentage of completed and ongoing studies, handling the case where total_prog is 0 to avoid division by zero errors, and these percentages will be used in the view to show the distribution of study statuses
        $comp_perc = ($complete_prog/$total_prog)*100;
        $ong_perc = ($ongoing_prog/$total_prog)*100;
        //---------------------------------------------------------

        // Pass all the prepared data to the view for rendering, including stats, chart data, and percentages, as well as the selected year to maintain the state of the dropdown in the view
        return view('radiis.studies', [
            'stats' => [
                'total_study'   => RDStudy::where('syear','<=',$maxYear)->count(),
                'completed_studies' => $filteredData->where('status', 'Completed')->count(),
                'ongoing_studies'   => $filteredData->where('status', 'Ongoing')->count(),
                'new_studies'   => $filteredData->where('syear', $maxYear)->count(),
                'total_budget' => RDStudy::where('syear','<=', $maxYear)->sum('budget'),
                'new_budget' => $filteredData->where('syear', $maxYear)->sum('budget'),
                'max_year' => $filteredData->max('syear'),
                'prev_year' => $secondYear,
                'all_year' => $all_year,
            ],
            'charts' => [
                'type_res' => $type_res,
                'type_resdev' => $type_resdev,
                'year_labels' => $count_year->keys(), 
                'year_counts' => $count_year->values(),
                'res_counts' => $res_count->values(),
                'resdev_counts' => $resdev_count->values(),
                'budget_labels' => $budget_year->keys(), 
                'budget_totals' => $budget_year->values(),
                'res_sums' => $budget_research->values(),
                'resdev_sums' => $budget_resdevelop->values(),
            ],
            'percentages' => [
                'year_percent' => number_format($year_perc, 2),
                'complete_perc' => number_format($comp_perc, 2),
                'ongoing_perc' => number_format($ong_perc, 2),
            ],
            'selectedYear' => $selectedYear,
         ]);
    }
}
