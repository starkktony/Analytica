<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDPublication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicationController extends Controller
{
    public function index(Request $request)
    {
        // Get all distinct years for the dropdown, ordered in descending order
        $all_year = RDPublication::select('pubyear')
        ->distinct()
        ->orderBy('pubyear', 'desc')
        ->pluck('pubyear');

        // Determine the selected year, defaulting to the maximum year if not provided, and start building the query
        $permMaxYear = RDPublication::max('pubyear');
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDPublication::query();

        // Get the grouping parameter for the stacked chart, default to 'category' if not provided, and validate it against allowed values
        $grouping = $request->input('group_by');
            if (!in_array($grouping, ['category', 'level', 'acct_unit'])) {
                $grouping = 'category';
            }

        // Determine the range of years to display in the stacked chart based on the selected year and the available years in the dataset
        $maxYear = $selectedYear ?? RDPublication::max('pubyear');
        // Ensure we have a valid max year and calculate the second year for percentage change calculations
        $secondYear = $maxYear - 1;

        // Logic to determine the 10-year range for the stacked chart based on the position of the selected year in the list of all years
        $targetIndex = $all_year->search($selectedYear);
        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        // Get the 10-year range of years to display in the stacked chart, ensuring it's in ascending order for proper chart display
        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

        // Fetch the data for the stacked chart based on the selected grouping and year range, and prepare the data for the line chart showing project counts by type per year
        $stackedData = RDPublication::select('pubyear', $grouping, DB::raw('count(*) as total'))
        ->whereIn('pubyear', $displayYears) 
        ->groupBy('pubyear', $grouping)
        ->orderBy('pubyear', 'asc') 
        ->get();

        // Apply the year filter to the main query for the other charts and stats, and get the filtered data for further processing
        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('pubyear', $selectedYear))
        ->get();
        
        // Group the filtered data for the various pie charts, count the number of records for each group, and sort them in descending order for better visualization
        $per_category = $filteredData
        ->groupBy('category')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        $per_level = $filteredData
        ->groupBy('level')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        // Map of full college names to their abbreviations for the per unit chart, which will be used to convert the long names in the dataset to shorter labels for better visualization in the charts
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
        ];

        // Group the filtered data by account unit, map the long names to their abbreviations using the collegeMap, count the number of records for each unit, and sort them in descending order for better visualization in the charts
        $per_unit = $filteredData
        ->groupBy('acct_unit')
        ->map(function ($group, $longName) use ($collegeMap) {
            $abbr = $collegeMap[$longName] ?? 'Other';
            // Return an array with the abbreviation, count, and original long name for each account unit, which will be used to prepare the data for the per unit pie chart, ensuring that any units not in the collegeMap will be labeled as 'Other'
            return [
                'abbr' => $abbr,
                'count' => $group->count(),
                'name' => $longName,
                // You could even add a custom color here if you wanted!
            ];
        })
        ->sortByDesc('count');

        $unitresults = $per_unit->values();

        // Fetch the total count of publications for each year to prepare the data for the line chart showing publication counts per year, and ensure it's ordered by year in ascending order for proper chart display
        $per_year = RDPublication::select('pubyear', DB::raw('count(*) as total'))
        ->groupBy('pubyear')
        ->orderBy('pubyear', 'desc')
        ->take(7)
        ->get()
        ->reverse();

        // Calculate the percentage change in publications from the previous year to the selected year for the stats section, handling cases where the previous year's value is zero to avoid division by zero errors
        $mxyear_value = RDPublication::where('pubyear', $maxYear)->count();
        $prevyear_value_test = RDPublication::where('pubyear', $secondYear)->count();
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;

        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        //---------------------------------------------------------

        // Return the view with all the prepared data for the stats, charts, and percentages, ensuring that the data is properly formatted for use in the Blade template and the JavaScript charts
        return view('radiis.publications', [
            'stats' => [
                'total_pub'   => RDPublication::where('pubyear','<=',$maxYear)->count(),
                'new_pub'   => $filteredData->where('pubyear', $maxYear)->count(),
                'max_year' => $filteredData->max('pubyear'),
                'prev_year' => $secondYear,
                'all_year' => $all_year,
            ],
            'charts' => [
                'year_labels' => $per_year->pluck('pubyear')->map(fn($year) => (string)$year), 
                'year_counts' => $per_year->pluck('total'),
                'per_category_labels' => $per_category->keys(),
                'per_category_values' => $per_category->values(),
                'per_level_labels' => $per_level->keys(),
                'per_level_values' => $per_level->values(),
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

    // Helper function to format the data for the stacked chart based on the raw results from the database, the selected grouping, and the range of years to display, which will be used to prepare the data structure needed for rendering the stacked chart in the view
    protected function formatStacked($rawResults, $grouping, $displayYears)
        {
            // Get the unique categories based on the selected grouping, filter out any null or empty values, and prepare the data for each category by counting the number of records for each year in the display range, ensuring that if there's no data for a particular year and category combination, it defaults to 0 for proper chart display
            $categories = $rawResults->pluck($grouping)->unique()->filter()->values();
            // For each category, map the counts for each year in the display range, ensuring that if there's no data for a particular year-category combination, it defaults to 0, to prepare the data for the stacked chart series
            $series = $categories->map(function ($name) use ($rawResults, $displayYears, $grouping) {
                return [
                    'name' => $name,
                    'counts' => collect($displayYears)->map(function ($year) use ($rawResults, $name, $grouping) {
                        $match = $rawResults->where('pubyear', $year)
                                            ->where($grouping, $name)
                                            ->first();
                        return $match ? $match->total : 0;
                    })
                ];
            });
            // Additionally, prepare the total counts for each year in the display range to be used as a line in the stacked chart, ensuring that if there's no data for a particular year, it defaults to 0
            $totalLine = collect($displayYears)->map(function ($year) use ($rawResults) {
                return $rawResults->where('pubyear', $year)->sum('total');
            });

            // Return the formatted data for the stacked chart, including the labels (years), series (categories with their counts), and the total line data, which will be used in the view to render the stacked chart with a line overlay
            return [
                'labels' => $displayYears,
                'series' => $series,
                'total_line' => $totalLine
            ];
        }

}

