<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDResearcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResearcherController extends Controller
{
    public function index(Request $request)
    {
        // Get all distinct years from the date_hired column for the dropdown, ordered in descending order, and pluck just the year values into a collection
        $all_year = RDResearcher::selectRaw('YEAR(date_hired) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');

        // Convert the collection of years to an array for easier manipulation, and determine the maximum year to use as the default selected year if none is provided in the request
        $year_col = $all_year->toArray();

        // Determine the selected year from the request, defaulting to the maximum year if not provided, and start building the query for fetching researchers based on the selected year
        $permMaxYear = max($year_col);
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDResearcher::query();

        // Apply the year filter to the main query for fetching researchers, using a raw SQL expression to extract the year from the date_hired column, and get the filtered data for further processing in the charts and stats
        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where(DB::raw('YEAR(date_hired)'), $selectedYear))
        ->get();

        // Fetch the total count of researchers for the stats section, which will be displayed in the view, and this count is not affected by the year filter to show the overall total number of researchers
        $total_res = RDResearcher::count();

        //----------------------------------------------------- CHARTS

        //Search if the selected year is in the list of all years to determine the index for slicing the data for the charts, and if the selected year is not found or is too close to the end of the list, adjust the starting index to show the last 10 years for the charts
        $targetIndex = $all_year->search($selectedYear);

        // Logic to determine the 10-year range for the charts based on the position of the selected year in the list of all years, ensuring that if the selected year is not found or is too close to the end of the list, it adjusts the starting index to show the last 10 years
        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        // Get the 10-year range of years to display in the charts, ensuring it's in ascending order for proper chart display, and this range will be used to filter the data for the charts to show trends over the last 10 years based on the selected year
        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

        // Group the filtered data for the various pie charts by type, status, and degree, count the number of records for each group, and sort them in descending order for better visualization in the charts
        $per_type = $filteredData
        ->groupBy('emp_type')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        $per_status = $filteredData
        ->groupBy('emp_status')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        $per_degree = $filteredData
        ->groupBy('degree')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();;

        // Fetch the total count of researchers hired for each year in the display range to prepare the data for the line chart showing researcher hires per year, using a raw SQL expression to extract the year from the date_hired column, and ensure it's ordered by year in ascending order for proper chart display
        $year_res = RDResearcher::select(DB::raw('YEAR(date_hired) as year'), DB::raw('count(*) as total'))
        ->whereIn(DB::raw('YEAR(date_hired)'), $displayYears)
        ->groupBy(DB::raw('YEAR(date_hired)'))
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');
        //---------------------------------------------------------
        // Return the view with all the prepared data for the stats and charts, ensuring that the data is properly formatted for use in the Blade template and the JavaScript charts, and pass the selected year to maintain the state of the dropdown in the view
        return view('radiis.researchers', [
            'stats' => [
                'total_res'   => $total_res,
                'res_hired' => $filteredData->count(),
                'all_year' => $all_year,
                'max_year' => $selectedYear,
            ],
            'charts' => [
                'type_labels' => $per_type->keys(),
                'type_values' => $per_type->values(),
                'status_labels' => $per_status->keys(),
                'status_values' => $per_status->values(),
                'degree_labels' => $per_degree->keys(),
                'degree_values' => $per_degree->values(),
                'year_count' => $year_res->pluck('total'),
                'year_label' => $year_res->pluck('year'),
            ],
            'selectedYear' => $selectedYear,
        ]);
    }
}
