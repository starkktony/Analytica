<?php

namespace App\Http\Controllers;

use App\Services\FundingDbService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class FundingController extends Controller
{
    public function __construct(private readonly FundingDbService $svc) {}

    public function index(Request $request): View
    {
        $years = Cache::remember('funding:years', 3600, fn() => $this->svc->getYears());

        $year = $request->query('year');
        if (!$year || !in_array($year, $years, true)) {
            $year = $years[0] ?? 'None';
        }

        $type = $request->query('type', 'all');
        $allowed = ['all', 'allotment', 'expenditure', 'suc_income'];
        if (!in_array($type, $allowed, true)) {
            $type = 'all';
        }

        // cache keys stay the same
        $income = Cache::remember("funding:income:$year", 3600, fn() => $this->svc->readIncome($year));
        $allot  = Cache::remember("funding:allot:$year",  3600, fn() => $this->svc->readAllotment($year));
        $exp    = Cache::remember("funding:exp:$year",    3600, fn() => $this->svc->readExpenditure($year));

        $peso = fn(float $v) => '₱' . number_format($v, 2);

        return view('snormativefunding.dashboard', [  // ← CHANGED HERE
            'income' => [
                'grand_total_income' => $peso((float)$income['grand_total_income']),
                'tuition_misc_fee'   => $peso((float)$income['tuition_misc_fee']),
                'miscellaneous'      => $peso((float)$income['miscellaneous']),
                'other_income'       => $peso((float)$income['other_income']),
            ],
            'allotment' => [
                'gaa_total'      => $peso((float)$allot['gaa']['total']),
                'suc_total'      => $peso((float)$allot['suc_income']['total']),
                'combined_total' => $peso((float)$allot['combined']['total']),
            ],
            'expenditure' => [
                'gaa_total'      => $peso((float)$exp['gaa']['total']),
                'suc_total'      => $peso((float)$exp['suc_income']['total']),
                'combined_total' => $peso((float)$exp['combined']['total']),
            ],
            'year'        => $year,
            'filter_type' => $type,
            'suc_years'   => $years,
            'active_page' => 'normative_breakdown',
        ]);
    }

    public function getIncomeData(Request $request): JsonResponse
    {
        $year = $this->resolveYear($request);
        $data = Cache::remember("funding:income:$year", 3600, fn() => $this->svc->readIncome($year));
        $data['year'] = $year;
        return response()->json($data);
    }

    public function getAllotmentData(Request $request): JsonResponse
    {
        $year = $this->resolveYear($request);
        $data = Cache::remember("funding:allot:$year", 3600, fn() => $this->svc->readAllotment($year));
        $data['year'] = $year;
        return response()->json($data);
    }

    public function getExpenditureData(Request $request): JsonResponse
    {
        $year = $this->resolveYear($request);
        $data = Cache::remember("funding:exp:$year", 3600, fn() => $this->svc->readExpenditure($year));
        $data['year'] = $year;
        return response()->json($data);
    }

    private function resolveYear(Request $request): string
    {
        $years = Cache::remember('funding:years', 3600, fn() => $this->svc->getYears());

        $year = $request->query('year');
        if (!$year || !in_array($year, $years, true)) {
            $year = $years[0] ?? 'None';
        }
        return $year;
    }
}