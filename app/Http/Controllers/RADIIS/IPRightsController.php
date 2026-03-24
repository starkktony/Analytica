<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDIPRights;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IPRightsController extends Controller
{
    public function index(Request $request)
    {
        $all_year = RDIPRights::select('filyear')
        ->distinct()
        ->orderBy('filyear', 'desc')
        ->pluck('filyear');

        $permMaxYear = RDIPRights::max('filyear');
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDIPRights::query();

        $grouping = $request->input('group_by');
            if (!in_array($grouping, ['utilization', 'type', 'acct_unit'])) {
                $grouping = 'utilization';
            }

       

        $maxYear = $selectedYear ?? RDIPRights::max('filyear');
        $secondYear = $maxYear - 1;

        $targetIndex = $all_year->search($selectedYear);

        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();
        $stackedData = RDIPRights::select('filyear', $grouping, DB::raw('count(*) as total'))
        ->whereIn('filyear', $displayYears) 
        ->groupBy('filyear', $grouping)
        ->orderBy('filyear', 'asc') 
        ->get();

        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('filyear', $selectedYear))
        ->get();
        
        //-----------------------------------------------------
        $per_util = $filteredData
        ->groupBy('utilization')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

        $per_type = $filteredData
        ->groupBy('type')
        ->map(function ($items) {
        return $items->count();})
        ->sortDesc();

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

        //-----------------------------------------------------
        $per_year = RDIPRights::select('filyear', DB::raw('count(*) as total'))
        ->groupBy('filyear')
        ->orderBy('filyear', 'desc')
        ->take(7)
        ->get()
        ->reverse();

        //-----------------------------------------------------
        $mxyear_value = RDIPRights::where('filyear', $maxYear)->count();
        $prevyear_value_test = RDIPRights::where('filyear', $secondYear)->count();
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;

        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        //---------------------------------------------------------

        return view('radiis.iprights', [
            'stats' => [
                'total_ipr'   => RDIPRights::where('filyear','<=',$maxYear)->count(),
                'new_ipr'   => $filteredData->where('filyear', $maxYear)->count(),
                'max_year' => $filteredData->max('filyear'),
                'prev_year' => $secondYear,
                'all_year' => $all_year,
            ],
            'charts' => [
                'year_labels' => $per_year->pluck('filyear')->map(fn($year) => (string)$year), 
                'year_counts' => $per_year->pluck('total'),
                'per_util_labels' => $per_util->keys(),
                'per_util_values' => $per_util->values(),
                'per_type_labels' => $per_type->keys(),
                'per_type_values' => $per_type->values(),
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

    protected function formatStacked($rawResults, $grouping, $displayYears)
        {   
            $categories = $rawResults->pluck($grouping)->unique()->filter()->values();
            $series = $categories->map(function ($name) use ($rawResults, $displayYears, $grouping) {
                return [
                    'name' => $name,
                    'counts' => collect($displayYears)->map(function ($year) use ($rawResults, $name, $grouping) {
                        $match = $rawResults->where('filyear', $year)
                                            ->where($grouping, $name)
                                            ->first();
                        return $match ? $match->total : 0;
                    })
                ];
            });
            $totalLine = collect($displayYears)->map(function ($year) use ($rawResults) {
                return $rawResults->where('filyear', $year)->sum('total');
            });

            return [
                'labels' => $displayYears,
                'series' => $series,
                'total_line' => $totalLine
            ];
        }

}

