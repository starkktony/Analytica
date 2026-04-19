<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDAward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AwardController extends Controller
{
    public function index(Request $request)
    {
        // Get all distinct years for the dropdown
        $all_year = RDAward::select('awdyear')
        ->distinct()
        ->orderBy('awdyear', 'desc')
        ->pluck('awdyear');

        $permMaxYear = RDAward::max('awdyear');// Determine the selected year, defaulting to the maximum year if not provided
        $selectedYear = $request->input('year') ?? $permMaxYear;// Get the selected year from the request, default to max year if not provided
        $query = RDAward::query();// Start building the query

        // Get the grouping parameter for the stacked chart, default to 'category'
        $grouping = $request->input('group_by');
            if (!in_array($grouping, ['category', 'level', 'mode'])) {
                $grouping = 'category';
            }

        $maxYear = $selectedYear ?? RDAward::max('awdyear');// Determine the range of years to display in the stacked chart
        $secondYear = $maxYear - 1;// Ensure we have a valid max year and calculate the second year for percentage change

        // Logic to determine the 10-year range for the stacked chart
        $targetIndex = $all_year->search($selectedYear);

        // If the selected year is not found or is too close to the end of the list, adjust the starting index to show the last 10 years
        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        // Get the 10-year range of years to display in the stacked chart, ensuring it's in ascending order for the chart
        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

        // Fetch the data for the stacked chart based on the selected grouping and year range
        $stackedData = RDAward::select('awdyear', $grouping, DB::raw('count(*) as total'))
        ->whereIn('awdyear', $displayYears) 
        ->groupBy('awdyear', $grouping)
        ->orderBy('awdyear', 'asc') 
        ->get();

        // Apply the year filter to the main query for the other charts and stats
        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('awdyear', $selectedYear))
        ->get();
        
        // Group the filtered data for the various pie charts and sort them in descending order
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

        $per_mode = $filteredData
        ->groupBy('mode')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        // Mapping of long unit names to their abbreviations for the per unit pie chart
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
            'Office of the President' => 'OP',
            'Office of the Vice President for Academic Affairs' => 'OVPAA',
            'Office of the Vice President for Administration' => 'OVPAd',
            'Office of the Vice President for Research and Extension' => 'OVPRET',
        ];

        // Group the filtered data by account unit, count the number of awards for each unit, and map the long names to their abbreviations for the per unit pie chart
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

        // Group the filtered data by year and count the number of awards for each year to prepare data for the awards per year line chart
        $per_year = RDAward::select('awdyear', DB::raw('count(*) as total'))
        ->groupBy('awdyear')
        ->orderBy('awdyear', 'desc')
        ->take(7)
        ->get()
        ->reverse();

        // Calculate the percentage change in awards from the previous year to the selected year for the stats section
        $mxyear_value = RDAward::where('awdyear', $maxYear)->count();
        $prevyear_value_test = RDAward::where('awdyear', $secondYear)->count();
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;

        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        //---------------------------------------------------------

        // Pass all the prepared data to the view for rendering
        return view('radiis.awards', [
            'stats' => [
                'total_awd'   => RDAward::where('awdyear','<=',$maxYear)->count(),
                'new_awd'   => $filteredData->where('awdyear', $maxYear)->count(),
                'max_year' => $filteredData->max('awdyear'),
                'prev_year' => $secondYear,
                'all_year' => $all_year,
            ],
            'charts' => [
                'year_labels' => $per_year->pluck('awdyear')->map(fn($year) => (string)$year), 
                'year_counts' => $per_year->pluck('total'),
                'per_category_labels' => $per_category->keys(),
                'per_category_values' => $per_category->values(),
                'per_level_labels' => $per_level->keys(),
                'per_level_values' => $per_level->values(),
                'per_mode_labels' => $per_mode->keys(),
                'per_mode_values' => $per_mode->values(),
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
                        $match = $rawResults->where('awdyear', $year)
                                            ->where($grouping, $name)
                                            ->first();
                        return $match ? $match->total : 0;
                    })
                ];
            });

            // Calculate the total number of awards for each year across all categories to prepare the total line data for the stacked chart
            $totalLine = collect($displayYears)->map(function ($year) use ($rawResults) {
                return $rawResults->where('awdyear', $year)->sum('total');
            });

            // Return the formatted data for the stacked chart, including the labels (years), series (categories with their counts), and the total line data
            return [
                'labels' => $displayYears,
                'series' => $series,
                'total_line' => $totalLine
            ];
        }

}

