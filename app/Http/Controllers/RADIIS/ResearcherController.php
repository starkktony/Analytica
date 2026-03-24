<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDResearcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResearcherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $all_year = RDResearcher::selectRaw('YEAR(date_hired) as year')
                ->distinct()->orderBy('year', 'desc')->pluck('year');

        $year_col = $all_year->toArray();

        $permMaxYear = max($year_col);
        $selectedYear = $request->input('year') ?? $permMaxYear;
        $query = RDResearcher::query();

        $filteredData = (clone $query)
        ->when($selectedYear, fn($q) => $q->where(DB::raw('YEAR(date_hired)'), $selectedYear))
        ->get();

        $total_res = RDResearcher::count();

        //----------------------------------------------------- CHARTS

        $targetIndex = $all_year->search($selectedYear);

        if ($targetIndex === false || $targetIndex > ($all_year->count() - 10)) {
            $startIndex = max(0, $all_year->count() - 10);
        } else {
            $startIndex = $targetIndex;
        }

        $displayYears = $all_year->slice($startIndex, 10)->reverse()->values();

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

        $year_res = RDResearcher::select(DB::raw('YEAR(date_hired) as year'), DB::raw('count(*) as total'))
        ->whereIn(DB::raw('YEAR(date_hired)'), $displayYears)
        ->groupBy(DB::raw('YEAR(date_hired)'))
        ->orderBy('year', 'asc')
        ->get()
        ->keyBy('year');
        //---------------------------------------------------------

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
