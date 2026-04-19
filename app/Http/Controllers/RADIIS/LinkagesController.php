<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDLinkages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LinkagesController extends Controller
{
    public function index(Request $request)
    {
        // Get all distinct years for the dropdown, ordered in descending order
        $all_year = RDLinkages::select('effyear')
        ->distinct()
        ->orderBy('effyear', 'desc')
        ->pluck('effyear');

        // Determine the selected year, defaulting to the maximum year if not provided, and start building the query
        $permMaxYear = RDLinkages::max('effyear');
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDLinkages::query();

        // Get the grouping parameter for the stacked chart, default to 'category' if not provided, and validate it against allowed values
        $grouping = $request->input('group_by');
            if (!in_array($grouping, ['category', 'level', 'type', 'status', 'acct_unit'])) {
                $grouping = 'category';
            }
        
        // Determine the range of years to display in the stacked chart based on the selected year and the available years in the dataset
        $maxYear = $selectedYear ?? RDLinkages::max('effyear');
        // Ensure we have a valid max year and calculate the second year for percentage change calculations
        $secondYear = $maxYear - 1;

        // Logic to determine the 10-year range for the stacked chart based on the position of the selected year in the list of all years
        $targetIndex = $all_year->search($selectedYear);

        // If the selected year is not found or is too close to the end of the list, adjust the starting index to show the last 10 years; otherwise, start from the selected year
        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        // Get the 10-year range of years to display in the stacked chart, ensuring it's in ascending order for proper chart display
        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

        // Fetch the data for the stacked chart based on the selected grouping and year range
        $stackedData = RDLinkages::select('effyear', $grouping, DB::raw('count(*) as total'))
        ->whereIn('effyear', $displayYears) 
        ->groupBy('effyear', $grouping)
        ->orderBy('effyear', 'asc') 
        ->get();

        // Apply the year filter to the main query for the other charts and stats, and get the filtered data for further processing
        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('effyear', $selectedYear))
        ->get();
        
        // Group the filtered data for the various pie charts, count the number of records for each group, and sort them in descending order for better visualization
         $per_cat = $filteredData
        ->groupBy('category')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        $per_level = $filteredData
        ->groupBy('level')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        $per_type = $filteredData
        ->groupBy('type')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        $per_status = $filteredData
        ->groupBy('status')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        // Mapping of long unit names to their abbreviations for the per unit pie chart, which includes colleges and URPO units, with a fallback to 'Other' for any units not in the mapping
        $collegeMap = [
            'College of Engineering' => 'CEn',
            'College of Arts and Social Science' => 'CASS',
            'College of Education' => 'CEd',
            'College of Agriculture' => 'CAg',
            'College of Fisheries' => 'CoF',
            'College of Home Science and Industry' => 'CHSI',
            'College of Business Accountancy' => 'CBA',
            'College of Veterinary Science and Medicine' => 'CVSM',
            'College of Science' => 'COS',
            'URPO - Small Ruminant Center' => 'URPO-SRC',
            'URPO - Crops and Resources Research and Development Center' => 'URPO-CRRDC',
            'URPO - Ramon Magsaysay-Center for Agricultural Resources and Environmental Studies' => 'URPO-RMCARES',
            'URPO - Freshwater Aquaculture Center' => 'URPO-FAC',
            'URPO - Philippine-Sino Center for Agricultural Technology' => 'URPO-PhilSCAT',
            'URPO - Precision and Digital Agriculture Center' => 'URPO-PDAC',
            'URPO - Philippine Carabao Center at CLSU' => 'URPO-PCC',
        ];

        // Additional mapping for other units that may not be colleges but are still relevant for the per unit pie chart
        $per_unit = $filteredData
        ->groupBy('acct_unit')
        ->map(function ($group, $longName) use ($collegeMap) {
            $abbr = $collegeMap[$longName] ?? 'Other';
            
            return [
                'abbr' => $abbr,
                'count' => $group->count(),
                'name' => $longName,
                // You could even add a custom color here if you wanted!
            ];
        })
        ->sortByDesc('count');

        $unitresults = $per_unit->values();

        // Group the filtered data by year and count the number of records for each year to prepare data for the linkages per year line chart, ensuring the years are ordered in descending order and limited to the most recent 7 years for better visualization
        $per_year = RDLinkages::select('effyear', DB::raw('count(*) as total'))
        ->groupBy('effyear')
        ->orderBy('effyear', 'desc')
        ->take(7)
        ->get()
        ->reverse();

        // Calculate the percentage change in linkages from the previous year to the selected year for the stats section, handling cases where the previous year's value is zero to avoid division by zero errors
        $mxyear_value = RDLinkages::where('effyear', $maxYear)->count();
        $prevyear_value_test = RDLinkages::where('effyear', $secondYear)->count();
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;

        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        //---------------------------------------------------------

        // Pass all the prepared data to the view for rendering, including stats, chart data, and percentages, as well as the selected year and grouping for the stacked chart to maintain state in the UI
        return view('radiis.linkages', [
            'stats' => [
                'total_link'   => RDLinkages::where('effyear','<=',$maxYear)->count(),
                'new_link'   => $filteredData->where('effyear', $maxYear)->count(),
                'max_year' => $filteredData->max('effyear'),
                'prev_year' => $secondYear,
                'all_year' => $all_year,
            ],
            'charts' => [
                'year_labels' => $per_year->pluck('effyear')->map(fn($year) => (string)$year), 
                'year_counts' => $per_year->pluck('total'),
                'per_cat_labels' => $per_cat->keys(),
                'per_cat_values' => $per_cat->values(),
                'per_level_labels' => $per_level->keys(),
                'per_level_values' => $per_level->values(),
                'per_type_labels' => $per_type->keys(),
                'per_type_values' => $per_type->values(),
                'per_status_labels' => $per_status->keys(),
                'per_status_values' => $per_status->values(),
                'per_unit_labels' => $unitresults->pluck('abbr'),
                'per_unit_values' => $unitresults->pluck('count'),
                'per_unit_names' => $unitresults->pluck('name'),
                'stacked' => $this->formatStacked($stackedData, $grouping, $displayYears),
            ],
            'percentages' => [
                'year_percent' => number_format($year_perc, 2),
            ],
            'selectedYear' => $selectedYear,
            'selectedGroup' => $grouping,
         ]);
    }

    // Helper function to format the data for the stacked chart based on the raw results, grouping, and display years
    protected function formatStacked($rawResults, $grouping, $displayYears)
        {
            // Get the unique categories based on the grouping parameter, filter out any null or empty values, and prepare the series data for the stacked chart
            $categories = $rawResults->pluck($grouping)->unique()->filter()->values();
            // For each category, map the counts for each year in the display range, ensuring that if there's no data for a particular year-category combination, it defaults to 0
            $series = $categories->map(function ($name) use ($rawResults, $displayYears, $grouping) {
                return [
                    'name' => $name,
                    'counts' => collect($displayYears)->map(function ($year) use ($rawResults, $name, $grouping) {
                        $match = $rawResults->where('effyear', $year)
                                            ->where($grouping, $name)
                                            ->first();
                        return $match ? $match->total : 0;
                    })
                ];
            });

            // Calculate the total number of linkages for each year across all categories to prepare the total line data for the stacked chart
            $totalLine = collect($displayYears)->map(function ($year) use ($rawResults) {
                return $rawResults->where('effyear', $year)->sum('total');
            });

            // Return the formatted data for the stacked chart, including the labels (years), series (categories with their counts), and the total line data
            return [
                'labels' => $displayYears,
                'series' => $series,
                'total_line' => $totalLine
            ];
        }

}

