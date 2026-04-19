<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        // Get all distinct years for the dropdown, ordered in descending order
        $all_year = RDProgram::select('syear')
        ->distinct()
        ->orderBy('syear', 'desc')
        ->pluck('syear');

        // Determine the selected year, defaulting to the maximum year if not provided, and start building the query
        $permMaxYear = RDProgram::max('syear');
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDProgram::query();

        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('syear', $selectedYear))
        ->get();

        // Determine the range of years to display in the stacked chart based on the selected year and the available years in the dataset
        $maxYear = $selectedYear ?? RDProgram::max('syear');
        // Ensure we have a valid max year and calculate the second year for percentage change calculations
        $secondYear = $maxYear - 1;
        
        //Group the filtered data for the various pie charts, count the number of records for each group, and sort them in descending order for better visualization
        $type_res = $filteredData
        ->where('type', 'Research')
        ->count();

        $type_dev = $filteredData
        ->where('type', 'Development')
        ->count();
        
        // Logic to determine the 10-year range for the stacked chart based on the position of the selected year in the list of all years
        $targetIndex = $all_year->search($selectedYear);

        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        // Get the 10-year range of years to display in the stacked chart, ensuring it's in ascending order for proper chart display
        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

        // Fetch the data for the stacked chart based on the selected grouping and year range
        $per_year = RDProgram::select('syear', DB::raw('count(*) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        // Map the counts for each year in the display range, ensuring that if there's no data for a particular year, it defaults to 0, to prepare the data for the presentations per year line chart
        $count_year = $displayYears->mapWithKeys(function ($year) use ($per_year) {
            return [$year => (int)($per_year[$year]->total ?? 0)];
        });

        // Get the counts for research and development programs separately for each year in the display range to prepare the data for the stacked chart, ensuring that if there's no data for a particular year-type combination, it defaults to 0
        $res_year = RDProgram::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        // Map the counts for research programs for each year in the display range, ensuring that if there's no data for a particular year, it defaults to 0, to prepare the data for the research line in the stacked chart
        $res_count = $displayYears->mapWithKeys(function ($year) use ($res_year) {
            return [$year => (int)($res_year[$year]->total ?? 0)];
        });

        // Get the counts for development programs for each year in the display range to prepare the data for the development line in the stacked chart, ensuring that if there's no data for a particular year, it defaults to 0
        $dev_year = RDProgram::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        // Map the counts for development programs for each year in the display range, ensuring that if there's no data for a particular year, it defaults to 0, to prepare the data for the development line in the stacked chart
        $dev_count = $displayYears->mapWithKeys(function ($year) use ($dev_year) {
            return [$year => (int)($dev_year[$year]->total ?? 0)];
        });

        //-----------------------------------------------------Budget chart
        // Get the total budget for each year in the display range to prepare the data for the total budget line in the stacked chart, ensuring that if there's no data for a particular year, it defaults to 0
        $per_budget = RDProgram::select('syear', DB::raw('sum(budget) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        // Map the total budget for each year in the display range, ensuring that if there's no data for a particular year, it defaults to 0, to prepare the data for the total budget line in the stacked chart
        $budget_year = $displayYears->mapWithKeys(function ($year) use ($per_budget) {
            return [$year => (float)($per_budget[$year]->total ?? 0)];
        });

        // Get the total budget for research programs for each year in the display range to prepare the data for the research budget line in the stacked chart, ensuring that if there's no data for a particular year, it defaults to 0
        $budget_res = RDProgram::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        // Map the total budget for research programs for each year in the display range, ensuring that if there's no data for a particular year, it defaults to 0, to prepare the data for the research budget line in the stacked chart
        $budget_research = $displayYears->mapWithKeys(function ($year) use ($budget_res) {
            return [$year => (float)($budget_res[$year]->total ?? 0)];
        });

        // Get the total budget for development programs for each year in the display range to prepare the data for the development budget line in the stacked chart, ensuring that if there's no data for a particular year, it defaults to 0
        $budget_dev = RDProgram::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        // Map the total budget for development programs for each year in the display range, ensuring that if there's no data for a particular year, it defaults to 0, to prepare the data for the development budget line in the stacked chart
        $budget_develop = $displayYears->mapWithKeys(function ($year) use ($budget_dev) {
            return [$year => (float)($budget_dev[$year]->total ?? 0)];
        });

        //-----------------------------------------------------Budget chart
        $mxyear_value = RDProgram::where('syear', $maxYear)->count();// Get the count of programs for the selected year to prepare for percentage change calculations in the stats section
        $prevyear_value_test = RDProgram::where('syear', $secondYear)->count();// Get the count of programs for the previous year to prepare for percentage change calculations in the stats section, handling cases where the previous year's value is zero to avoid division by zero errors
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;// Ensure that if the previous year's value is zero, it defaults to 0 to avoid division by zero errors in percentage change calculations

        // Calculate the percentage change in programs from the previous year to the selected year for the stats section, handling cases where the previous year's value is zero to avoid division by zero errors
        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        
        // Calculate the total number of programs, as well as the counts and percentages for completed and ongoing programs based on the filtered data for the selected year to prepare for the stats section
        $total_prog = $filteredData->count();
        $complete_prog = $filteredData->where('status', 'Completed')->count();
        $ongoing_prog = $filteredData->where('status', 'Ongoing')->count();

        // Calculate the percentage of completed and ongoing programs out of the total programs for the selected year, handling cases where the total number of programs is zero to avoid division by zero errors in percentage calculations
        $comp_perc = ($total_prog > 0) ? ($complete_prog / $total_prog) * 100 : 0;
        $ong_perc  = ($total_prog > 0) ? ($ongoing_prog / $total_prog) * 100 : 0;
        //---------------------------------------------------------
        
        return view('radiis.programs',[
            'stats' => [
                'total_programs'   => RDProgram::where('syear','<=',$maxYear)->count(),
                'completed_programs' => $filteredData->where('status', 'Completed')->count(),
                'ongoing_programs'   => $filteredData->where('status', 'Ongoing')->count(),
                'new_programs'   => $filteredData->where('syear', $maxYear)->count(),
                'total_budget' => RDProgram::where('syear','<=', $maxYear)->sum('budget'),
                'new_budget' => $filteredData->where('syear', $maxYear)->sum('budget'),
                'max_year' => $filteredData->max('syear'),
                'prev_year' => $secondYear,
                'all_year' => $all_year,
            ],
            'charts' => [
                'type_res' => $type_res,
                'type_dev' => $type_dev,
                'year_labels' => $count_year->keys(), 
                'year_counts' => $count_year->values(),
                'res_counts' => $res_count->values(),
                'dev_counts' => $dev_count->values(),
                'budget_labels' => $budget_year->keys(), 
                'budget_totals' => $budget_year->values(),
                'res_sums' => $budget_research->values(),
                'dev_sums' => $budget_develop->values(),
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
