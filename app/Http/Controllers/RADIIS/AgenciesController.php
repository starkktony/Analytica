<?php

namespace App\Http\Controllers\Radiis;

use App\Http\Controllers\Controller;
use App\Models\Radiis\RDAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgenciesController extends Controller
{

    public function index()
    {
        $query = RDAgency::query();// Start with a base query

        // Apply year filter if provided
        $queryData = (clone $query)
        ->get();

        // Get top 3 agencies by total funded amount
        $top_three = RDAgency::select('abbr', 'total_funded')
        ->orderBy('total_funded', 'desc')
        ->get();

        //Data for the per category pie chart
        $per_category = $queryData
        ->groupBy('category')
        ->map(function ($items) {
        return $items->count(); 
        });

        //Data for the per type pie chart
        $per_type = $queryData
        ->groupBy('type')
        ->map(function ($items) {
        return $items->count(); 
        });

        //Data for the per sector pie chart
        $per_sector = $queryData
        ->groupBy('sector')
        ->map(function ($items) {
        return $items->count(); 
        });

        return view('radiis.fundagency', [
            'total_agency' => RDAgency::count(),
            'total_fund' => RDAgency::sum('total_funded'),
            'top_names' => $top_three->pluck('abbr'),
            'top_totals' => $top_three->pluck('total_funded'),
            'year' => date('Y'),
            'charts' => [
                'per_category_labels' => $per_category->keys(),
                'per_category_values' => $per_category->values(),
                'per_type_labels' => $per_type->keys(),
                'per_type_values' => $per_type->values(),
                'per_sect_labels' => $per_sector->keys(),
                'per_sect_values' => $per_sector->values(),
            ],
         ]);
    }
}
