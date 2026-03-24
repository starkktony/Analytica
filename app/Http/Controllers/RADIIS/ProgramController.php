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
        $all_year = RDProgram::select('syear')
        ->distinct()
        ->orderBy('syear', 'desc')
        ->pluck('syear');

        $permMaxYear = RDProgram::max('syear');
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDProgram::query();

        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where('syear', $selectedYear))
        ->get();

        $maxYear = $selectedYear ?? RDProgram::max('syear');
        $secondYear = $maxYear - 1;
        
        //-----------------------------------------------------
        $type_res = $filteredData
        ->where('type', 'Research')
        ->count();

        $type_dev = $filteredData
        ->where('type', 'Development')
        ->count();
        
        //-----------------------------------------------------
        $targetIndex = $all_year->search($selectedYear);

        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

        $per_year = RDProgram::select('syear', DB::raw('count(*) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $count_year = $displayYears->mapWithKeys(function ($year) use ($per_year) {
            return [$year => (int)($per_year[$year]->total ?? 0)];
        });

        $res_year = RDProgram::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $res_count = $displayYears->mapWithKeys(function ($year) use ($res_year) {
            return [$year => (int)($res_year[$year]->total ?? 0)];
        });

        $dev_year = RDProgram::select('syear', DB::raw('count(*) as total'))
        ->where('type', 'Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'asc')
        ->get()
        ->keyBy('syear');

        $dev_count = $displayYears->mapWithKeys(function ($year) use ($dev_year) {
            return [$year => (int)($dev_year[$year]->total ?? 0)];
        });

        //-----------------------------------------------------Budget chart

        $per_budget = RDProgram::select('syear', DB::raw('sum(budget) as total'))
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_year = $displayYears->mapWithKeys(function ($year) use ($per_budget) {
            return [$year => (float)($per_budget[$year]->total ?? 0)];
        });

        $budget_res = RDProgram::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Research')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_research = $displayYears->mapWithKeys(function ($year) use ($budget_res) {
            return [$year => (float)($budget_res[$year]->total ?? 0)];
        });

        $budget_dev = RDProgram::select('syear', DB::raw('sum(budget) as total'))
        ->where('type', 'Development')
        ->whereIn('syear', $displayYears)
        ->groupBy('syear')
        ->orderBy('syear', 'desc')
        ->get()
        ->keyBy('syear');

        $budget_develop = $displayYears->mapWithKeys(function ($year) use ($budget_dev) {
            return [$year => (float)($budget_dev[$year]->total ?? 0)];
        });

        //-----------------------------------------------------
        $mxyear_value = RDProgram::where('syear', $maxYear)->count();
        $prevyear_value_test = RDProgram::where('syear', $secondYear)->count();
        $prevyear_value = ($prevyear_value_test > 0) ? $prevyear_value_test : 0;

        $year_perc = ($prevyear_value == 0) ? 0:((($mxyear_value-$prevyear_value)/$prevyear_value) * 100);
        //-----------------------------------------------------
        $total_prog = $filteredData->count();
        $complete_prog = $filteredData->where('status', 'Completed')->count();
        $ongoing_prog = $filteredData->where('status', 'Ongoing')->count();

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
