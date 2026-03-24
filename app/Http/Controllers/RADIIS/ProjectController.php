<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $all_year = RDProject::select('syear')
        ->distinct()
        ->orderBy('syear', 'desc')
        ->pluck('syear');

        $permMaxYear = RDProject::max('syear');
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDProject::query();

        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('syear', $selectedYear))
        ->get();

        $maxYear = $selectedYear ?? RDProject::max('syear');
        $secondYear = $maxYear - 1;
        //-----------------------------------------------------
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
        //-----------------------------------------------------
        $targetIndex = $all_year->search($selectedYear);

        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();


        $per_year = RDProject::select('syear', DB::raw('count(*) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $count_year = $displayYears->mapWithKeys(function ($year) use ($per_year) {
            return [$year => (int)($per_year[$year]->total ?? 0)];
        });

        $res_year = RDProject::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $res_count = $displayYears->mapWithKeys(function ($year) use ($res_year) {
            return [$year => (int)($res_year[$year]->total ?? 0)];
        });

        $dev_year = RDProject::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $dev_count = $displayYears->mapWithKeys(function ($year) use ($dev_year) {
            return [$year => (int)($dev_year[$year]->total ?? 0)];
        });

        $resdev_year = RDProject::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Research and Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $resdev_count = $displayYears->mapWithKeys(function ($year) use ($resdev_year) {
            return [$year => (int)($resdev_year[$year]->total ?? 0)];
        });

        $bw_year = RDProject::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Book Writing')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $bw_count = $displayYears->mapWithKeys(function ($year) use ($bw_year) {
            return [$year => (int)($bw_year[$year]->total ?? 0)];
        });

        //-----------------------------------------------------
        $per_budget = RDProject::select('syear', DB::raw('sum(budget) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_year = $displayYears->mapWithKeys(function ($year) use ($per_budget) {
            return [$year => (float)($per_budget[$year]->total ?? 0)];
        });

        $budget_res = RDProject::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_research = $displayYears->mapWithKeys(function ($year) use ($budget_res) {
            return [$year => (float)($budget_res[$year]->total ?? 0)];
        });

        $budget_dev = RDProject::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_develop = $displayYears->mapWithKeys(function ($year) use ($budget_dev) {
            return [$year => (float)($budget_dev[$year]->total ?? 0)];
        });

        $budget_resdev = RDProject::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Research and Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_resdevelop = $displayYears->mapWithKeys(function ($year) use ($budget_resdev) {
            return [$year => (float)($budget_resdev[$year]->total ?? 0)];
        });

        $budget_book = RDProject::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Book Writing')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_bw = $displayYears->mapWithKeys(function ($year) use ($budget_book) {
            return [$year => (float)($budget_book[$year]->total ?? 0)];
        });
        //-----------------------------------------------------
        $mxyear_value = RDProject::where('syear', $maxYear)->count();
        $prevyear_value_test = RDProject::where('syear', $secondYear)->count();
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;

        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        //-----------------------------------------------------
        $total_prog = $filteredData->count();
        $complete_prog = $filteredData->where('status', 'Completed')->count();
        $ongoing_prog = $filteredData->where('status', 'Ongoing')->count();

        $comp_perc = ($total_prog > 0) ? ($complete_prog / $total_prog) * 100 : 0;
        $ong_perc  = ($total_prog > 0) ? ($ongoing_prog / $total_prog) * 100 : 0;
        //---------------------------------------------------------

        return view('radiis.projects', [
            'stats' => [
                'total_projects'   => RDProject::where('syear','<=',$maxYear)->count(),
                'completed_projects' => $filteredData->where('status', 'Completed')->count(),
                'ongoing_projects'   => $filteredData->where('status', 'Ongoing')->count(),
                'new_projects'   => $filteredData->where('syear', $maxYear)->count(),
                'total_budget' => RDProject::where('syear','<=', $maxYear)->sum('budget'),
                'new_budget' => $filteredData->where('syear', $maxYear)->sum('budget'),
                'max_year' => $filteredData->max('syear'),
                'prev_year' => $secondYear,
                'all_year' => $all_year,
            ],
            'charts' => [
                'type_res' => $type_res,
                'type_dev' => $type_dev,
                'type_resdev' => $type_resdev,
                'type_bw' => $type_bw,
                'year_labels' => $count_year->keys(), 
                'year_counts' => $count_year->values(),
                'res_counts' => $res_count->values(),
                'dev_counts' => $dev_count->values(),
                'resdev_counts' => $resdev_count->values(),
                'bw_counts' => $bw_count->values(),
                'budget_labels' => $budget_year->keys(), 
                'budget_totals' => $budget_year->values(),
                'res_sums' => $budget_research->values(),
                'dev_sums' => $budget_develop->values(),
                'resdev_sums' => $budget_resdevelop->values(),
                'bw_sums' => $budget_bw->values(),
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
