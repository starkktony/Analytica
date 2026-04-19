<?php

namespace App\Http\Controllers;

use App\Services\FundingDbService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

/**
 * FundingController
 *
 * Handles all routes under the Normative Funding section of the dashboard.
 * Responsibilities:
 *   - Rendering the main dashboard view with pre-formatted PHP data (index)
 *   - Serving raw JSON endpoints consumed by Plotly charts via fetch() (get*Data)
 *
 * All heavy database reads are delegated to FundingDbService and cached for
 * 1 hour (3600 s) to avoid repeated queries on every page load or chart request.
 */
class FundingController extends Controller
{
    /**
     * FundingDbService is injected via constructor promotion (readonly).
     * This is the sole data-access layer; the controller never queries directly.
     */
    
    /**
     * index()
     *
     * Renders the Normative Funding dashboard view.
     *
     * Query parameters:
     *   ?year=<string>  — Fiscal year to display. Falls back to the most recent
     *                     available year if omitted or invalid.
     *   ?type=<string>  — Section to show: 'all' | 'allotment_expenditure' | 'suc_income'.
     *                     Defaults to 'all'. Invalid values are silently reset to 'all'.
     *
     * The view receives peso-formatted strings for stat cards (so Blade never
     * needs to touch raw floats) and raw arrays for everything the JS charts
     * fetch separately via their own JSON endpoints.
     */
    public function index(Request $request): View
    {
        // Cache the available years list — changes infrequently, safe to hold for 1 hour
        $years = Cache::remember('funding:years', 3600, fn() => $this->svc->getYears());

        // Validate the requested year; fall back to the latest year (first in list)
        $year = $request->query('year');
        if (!$year || !in_array($year, $years, true)) {
            $year = $years[0] ?? 'None';
        }

        // Validate the view type; silently coerce unknown values to 'all'
        $type = $request->query('type', 'all');
        $allowed = ['all', 'allotment_expenditure', 'suc_income'];
        if (!in_array($type, $allowed, true)) {
            $type = 'all';
        }

        // Fetch (or retrieve from cache) the three financial data sets for this year.
        // Each key is year-scoped so switching years doesn't serve stale data.
        $income = Cache::remember("funding:income:$year", 3600, fn() => $this->svc->readIncome($year));
        $allot  = Cache::remember("funding:allot:$year",  3600, fn() => $this->svc->readAllotment($year));
        $exp    = Cache::remember("funding:exp:$year",    3600, fn() => $this->svc->readExpenditure($year));

        // Convenience formatter — converts a raw float to a Philippine Peso string
        // (e.g. 1234567.89 → "₱1,234,567.89"). Used only for stat card values in Blade;
        // the JS charts receive unformatted numbers via the JSON endpoints.
        $peso = fn(float $v) => '₱' . number_format($v, 2);

        return view('snormativefunding.dashboard', [

            // ── Stat card values (pre-formatted peso strings) ──────────────────
            'income' => [
                'grand_total_income' => $peso((float)$income['grand_total_income']),
                'tuition_misc_fee'   => $peso((float)$income['tuition_misc_fee']),
                'miscellaneous'      => $peso((float)$income['miscellaneous']),
                'other_income'       => $peso((float)$income['other_income']),
            ],

            // Allotment totals split by funding source (GAA / SUC Income / Combined)
            'allotment' => [
                'gaa_total'      => $peso((float)$allot['gaa']['total']),
                'suc_total'      => $peso((float)$allot['suc_income']['total']),
                'combined_total' => $peso((float)$allot['combined']['total']),
            ],

            // Expenditure totals — mirrors allotment structure for easy comparison
            'expenditure' => [
                'gaa_total'      => $peso((float)$exp['gaa']['total']),
                'suc_total'      => $peso((float)$exp['suc_income']['total']),
                'combined_total' => $peso((float)$exp['combined']['total']),
            ],

            // ── View-level metadata ────────────────────────────────────────────
            'year'        => $year,          // Active fiscal year (drives filter dropdown)
            'filter_type' => $type,          // Active section ('all' | 'suc_income' | 'allotment_expenditure')
            'suc_years'   => $years,         // Full year list for the year dropdown
            'active_page' => 'normative_breakdown', // Sidebar active-state key
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // JSON DATA ENDPOINTS
    // These are called by the Blade view's JavaScript (via fetch()) to populate
    // the Plotly charts asynchronously after the initial page load.
    // Each endpoint validates + resolves the year, then returns the full raw
    // data array so the client can pick whatever fields it needs for each chart.
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * getIncomeData()
     *
     * Returns the full income data structure for a given year.
     * Consumed by: mainPieChart, tuitionPieChart, otherIncomePieChart, sucIncomeLineChart
     *
     * Response shape (mirrors FundingDbService::readIncome):
     *   { grand_total_income, tuition_misc_fee, miscellaneous, other_income,
     *     breakdown: { tuition_details: [...], other_income_details: [...] }, year }
     */
    public function getIncomeData(Request $request): JsonResponse
    {
        $year = $this->resolveYear($request);
        $data = Cache::remember("funding:income:$year", 3600, fn() => $this->svc->readIncome($year));
        $data['year'] = $year; // Append year so the client can verify what was returned
        return response()->json($data);
    }

    /**
     * getAllotmentData()
     *
     * Returns the full allotment data structure for a given year.
     * Consumed by: allotmentPieChart, allotmentCategoryChart,
     *              allotmentGAAChart, allotmentSUCChart, budgetUtilizationFunctionChart
     *
     * Response shape (mirrors FundingDbService::readAllotment):
     *   { gaa: { total, ps, mooe, co }, suc_income: { total, ps, mooe, co },
     *     combined: { total, ... }, breakdown: [...per-function rows...], year }
     */
    public function getAllotmentData(Request $request): JsonResponse
    {
        $year = $this->resolveYear($request);
        $data = Cache::remember("funding:allot:$year", 3600, fn() => $this->svc->readAllotment($year));
        $data['year'] = $year;
        return response()->json($data);
    }

    /**
     * getExpenditureData()
     *
     * Returns the full expenditure data structure for a given year.
     * Consumed by: expenditurePieChart, expenditureCategoryChart,
     *              expenditureGAAChart, expenditureSUCChart, budgetUtilizationFunctionChart
     *
     * Response shape mirrors getAllotmentData() for easy symmetric handling in JS.
     */
    public function getExpenditureData(Request $request): JsonResponse
    {
        $year = $this->resolveYear($request);
        $data = Cache::remember("funding:exp:$year", 3600, fn() => $this->svc->readExpenditure($year));
        $data['year'] = $year;
        return response()->json($data);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * resolveYear()
     *
     * Extracts and validates the ?year query parameter against the cached list
     * of available years. Falls back to the most recent year if the parameter
     * is missing, empty, or not in the valid set.
     *
     * Extracted into a private method because index() and all three JSON
     * endpoints share identical validation logic.
     */
    private function resolveYear(Request $request): string
    {
        $years = Cache::remember('funding:years', 3600, fn() => $this->svc->getYears());

        $year = $request->query('year');
        if (!$year || !in_array($year, $years, true)) {
            $year = $years[0] ?? 'None'; // 'None' sentinel when no data exists at all
        }
        return $year;
    }
}
