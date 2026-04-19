<?php

namespace App\Services;

use App\Models\FundReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * FundingDbService
 *
 * Data-access layer for the Normative Funding dashboard.
 * All database reads are isolated here so the controller stays thin.
 *
 * Responsibilities:
 *   - getYears()        — available fiscal years for the filter dropdown
 *   - readIncome()      — SUC income totals + per-account breakdowns
 *   - readAllotment()   — GAA / SUC Income allotment totals + per-function breakdown
 *   - readExpenditure() — same shape as readAllotment() but for expenditure records
 *
 * Both allotment and expenditure share an identical DB schema and query path,
 * so they are handled by the private readAE() method to avoid duplication.
 *
 * Database connection: 'normativefunding' (separate from the default app DB).
 */
class FundingDbService
{
    /**
     * getYears()
     *
     * Returns a distinct, descending-sorted list of fiscal years that have at
     * least one FundReport record. Values are cast to strings so they can be
     * compared safely against query parameters without type coercion issues.
     *
     * @return string[]  e.g. ['2024', '2023', '2022']
     */
    public function getYears(): array
    {
        return FundReport::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($y) => (string) $y)  // Cast to string for strict query-param comparison
            ->values()
            ->all();
    }

    /**
     * readIncome()
     *
     * Returns the full SUC income data structure for a given fiscal year.
     *
     * Two queries are run against fund_report_income_lines:
     *   1. Grand-total rows (is_grand_total = true)  → top-level income figures
     *   2. Detail rows      (is_grand_total = false) → per-account breakdown items
     *
     * Detail items are capped at Top 4 + "Others" per category via top4WithOthers()
     * to keep pie charts readable. This condensing also happens client-side for the
     * main combined pie, but is pre-applied here for the individual category pies.
     *
     * @return array{
     *   grand_total_income: float,
     *   tuition_misc_fee:   float,
     *   miscellaneous:      float,
     *   other_income:       float,
     *   breakdown: array{
     *     main_categories:       array,
     *     tuition_details:       array,
     *     miscellaneous_details: array,
     *     other_income_details:  array,
     *   }
     * }
     */
    public function readIncome(string $year): array
    {
        $rid = $this->reportId($year, 'suc_income');

        // No report found for this year — return a zero-valued skeleton so
        // the controller and view always receive a predictable shape.
        $empty = [
            'grand_total_income' => 0,
            'tuition_misc_fee'   => 0,
            'miscellaneous'      => 0,
            'other_income'       => 0,
            'breakdown' => [
                'main_categories'       => [],
                'tuition_details'       => [],
                'miscellaneous_details' => [],
                'other_income_details'  => [],
            ],
        ];

        if (! $rid) {
            return $empty;
        }

        // ── Query 1: grand-total rows grouped by fund_source ─────────────────
        // Produces a flat map like: ['tuition' => 12345.00, 'other_income' => 6789.00]
        $grandRows = DB::connection('normativefunding')->table('fund_report_income_lines')
            ->where('fund_report_id', $rid)
            ->where('is_grand_total', true)
            ->selectRaw('fund_source, SUM(amount) as total')
            ->groupBy('fund_source')
            ->pluck('total', 'fund_source')
            ->all();

        $tuition = (float) ($grandRows['tuition'] ?? 0);
        $misc    = (float) ($grandRows['miscellaneous'] ?? 0);
        $other   = (float) ($grandRows['other_income'] ?? 0);
        $grand   = $tuition + $misc + $other; // Computed rather than stored to stay in sync

        // ── Query 2: detail rows grouped by fund_source then account_name ────
        // Produces a nested structure: ['tuition' => [['name' => ..., 'value' => ...], ...]]
        $details = DB::connection('normativefunding')->table('fund_report_income_lines')
            ->where('fund_report_id', $rid)
            ->where('is_grand_total', false)
            ->selectRaw('fund_source, account_name, SUM(amount) as value')
            ->groupBy('fund_source', 'account_name')
            ->get()
            ->groupBy('fund_source')
            ->map(function ($group) {
                return $group->map(function ($row) {
                    return [
                        'name'  => $this->normalizeIncomeLabel($row->account_name),
                        'value' => (float) $row->value,
                    ];
                })->values()->all();
            })
            ->all();

        return [
            'grand_total_income' => $grand,
            'tuition_misc_fee'   => $tuition,
            'miscellaneous'      => $misc,
            'other_income'       => $other,
            'breakdown' => [
                // Top-level category split (used for the main income breakdown pie)
                'main_categories' => [
                    ['name' => 'Tuition & Misc. Fees', 'value' => $tuition],
                    ['name' => 'Miscellaneous', 'value' => $misc],
                    ['name' => 'Other Income', 'value' => $other],
                ],
                // Per-category detail items, each capped at Top 4 + "Others"
                'tuition_details'       => $this->top4WithOthers($details['tuition'] ?? []),
                'miscellaneous_details' => $this->top4WithOthers($details['miscellaneous'] ?? []),
                'other_income_details'  => $this->top4WithOthers($details['other_income'] ?? []),
            ],
        ];
    }

    /**
     * readAllotment()
     *
     * Public proxy for readAE() scoped to the 'allotment' report type.
     * Kept as a named method so the controller's intent remains explicit.
     */
    public function readAllotment(string $year): array
    {
        return $this->readAE($year, 'allotment');
    }

    /**
     * readExpenditure()
     *
     * Public proxy for readAE() scoped to the 'expenditure' report type.
     * Returns a structure identical to readAllotment() for symmetric JS handling.
     */
    public function readExpenditure(string $year): array
    {
        return $this->readAE($year, 'expenditure');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * reportId()
     *
     * Looks up the primary key of a FundReport record by year and type.
     * Returns null when no matching record exists, which callers use as an
     * early-exit signal to return the empty skeleton instead of querying further.
     */
    private function reportId(string $year, string $type): ?int
    {
        return FundReport::query()
            ->where('year', (int) $year)  // Cast to int to match the DB column type
            ->where('type', $type)
            ->value('id');
    }

    /**
     * emptyAE()
     *
     * Returns a zero-valued allotment/expenditure skeleton.
     * Shared between readAE() (no-report early exit) and the result initializer
     * so both code paths always return the same array shape.
     *
     * Each funding source (gaa, suc_income, combined) has the same sub-keys:
     *   ps    — Personal Services
     *   mooe  — Maintenance and Other Operating Expenses
     *   co    — Capital Outlay
     *   total — Sum of ps + mooe + co
     */
    private function emptyAE(): array
    {
        $zero = ['ps' => 0, 'mooe' => 0, 'co' => 0, 'total' => 0];

        return [
            'gaa'        => $zero,
            'suc_income' => $zero,
            'combined'   => $zero,
            'breakdown'  => [],  // Per-function rows; empty when no data exists
        ];
    }

    /**
     * readAE()
     *
     * Core data reader for both allotment and expenditure records.
     * Both report types share the fund_report_ae_lines table and column naming
     * convention (gaa_ps, suc_mooe, combined_total, etc.), so one method handles both.
     *
     * Two queries are run:
     *   1. The single is_total=true row → top-level GAA / SUC / combined totals
     *   2. All is_total=false rows      → per-function detail lines
     *
     * Detail lines are grouped by normalised institutional function label
     * (e.g. function_name='5', sub_function='1' → 'Administration') and summed,
     * producing the breakdown array consumed by the grouped bar chart in JS.
     *
     * @param  string $type  'allotment' | 'expenditure'
     */
    private function readAE(string $year, string $type): array
    {
        $rid = $this->reportId($year, $type);

        if (! $rid) {
            return $this->emptyAE(); // No report for this year/type — return safe zeros
        }

        // ── Query 1: single aggregate totals row ──────────────────────────────
        $totals = DB::connection('normativefunding')->table('fund_report_ae_lines')
            ->where('fund_report_id', $rid)
            ->where('is_total', true)
            ->first();

        $result = $this->emptyAE();

        // Map the DB row's prefixed columns into the structured result array
        if ($totals) {
            $result['gaa']        = $this->mapAERow($totals, 'gaa');
            $result['suc_income'] = $this->mapAERow($totals, 'suc');
            $result['combined']   = $this->mapAERow($totals, 'combined');
        }

        // ── Query 2: individual function/sub-function detail lines ────────────
        $lines = DB::connection('normativefunding')->table('fund_report_ae_lines')
            ->where('fund_report_id', $rid)
            ->where('is_total', false)
            ->orderBy('id') // Preserve original data entry order before grouping
            ->get();

        // Group lines by their resolved human-readable function label.
        // Multiple DB rows can map to the same label (e.g. different sub-functions
        // under the same parent), so their values are summed within each group.
        $grouped = $lines->groupBy(function ($line) {
            return $this->normalizeInstitutionalFunction(
                $line->function_name,
                $line->sub_function
            );
        });

        // Collapse each group into a single flat row of summed financial values
        $result['breakdown'] = $grouped->map(function (Collection $group, string $functionLabel) {
            return [
                'function'       => $functionLabel,
                // GAA expense class sub-totals
                'gaa_ps'         => (float) $group->sum('gaa_ps'),
                'gaa_mooe'       => (float) $group->sum('gaa_mooe'),
                'gaa_co'         => (float) $group->sum('gaa_co'),
                'gaa_total'      => (float) $group->sum('gaa_total'),
                // SUC Income expense class sub-totals
                'suc_ps'         => (float) $group->sum('suc_ps'),
                'suc_mooe'       => (float) $group->sum('suc_mooe'),
                'suc_co'         => (float) $group->sum('suc_co'),
                'suc_total'      => (float) $group->sum('suc_total'),
                // Combined (GAA + SUC) sub-totals
                'combined_ps'    => (float) $group->sum('combined_ps'),
                'combined_mooe'  => (float) $group->sum('combined_mooe'),
                'combined_co'    => (float) $group->sum('combined_co'),
                'combined_total' => (float) $group->sum('combined_total'),
            ];
        })->values()->all();

        return $result;
    }

    /**
     * mapAERow()
     *
     * Extracts PS / MOOE / CO / total values from a DB row using a column prefix.
     * Handles missing columns gracefully with null-coalescing to 0.
     *
     * Example: mapAERow($row, 'gaa') reads $row->gaa_ps, $row->gaa_mooe, etc.
     *
     * @param  object $row     stdClass DB result row
     * @param  string $prefix  Column prefix: 'gaa' | 'suc' | 'combined'
     */
    private function mapAERow(object $row, string $prefix): array
    {
        return [
            'ps'    => (float) ($row->{$prefix . '_ps'}    ?? 0),
            'mooe'  => (float) ($row->{$prefix . '_mooe'}  ?? 0),
            'co'    => (float) ($row->{$prefix . '_co'}    ?? 0),
            'total' => (float) ($row->{$prefix . '_total'} ?? 0),
        ];
    }

    /**
     * top4WithOthers()
     *
     * Condenses an arbitrary-length items array into at most 5 entries:
     *   - The 4 highest-value items (sorted descending)
     *   - A synthetic "Others" entry summing all remaining items
     *
     * This keeps pie charts legible by preventing a long tail of tiny slices.
     * Returns an empty array immediately when no items are provided.
     *
     * @param  array $items  Array of ['name' => string, 'value' => float]
     * @return array         At most 5 items in descending value order
     */
    private function top4WithOthers(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        // Sort descending by value so the most significant items always come first
        usort($items, fn($a, $b) => $b['value'] <=> $a['value']);

        $top  = array_slice($items, 0, 4); // Keep top 4
        $rest = array_slice($items, 4);    // Everything else becomes "Others"

        if (! empty($rest)) {
            $top[] = [
                'name'  => 'Others',
                'value' => array_sum(array_column($rest, 'value')), // Sum the tail
            ];
        }

        return $top;
    }

    /**
     * normalizeIncomeLabel()
     *
     * Sanitizes an income account_name from the DB.
     * Trims whitespace and substitutes a fallback label for blank/null values
     * so the chart always has a displayable slice name.
     */
    private function normalizeIncomeLabel(?string $label): string
    {
        $value = trim((string) $label);

        return $value !== '' ? $value : 'Unspecified';
    }

    /**
     * normalizeInstitutionalFunction()
     *
     * Maps a raw function_name + sub_function code pair to a human-readable
     * institutional function label for chart display and grouping.
     *
     * The DB stores functions as numeric codes (e.g. function_name='5',
     * sub_function='1'). These are concatenated into a dot-notation key
     * (e.g. '5.1') and resolved via a match expression.
     *
     * Sub-functions under function 5 (General Administration) are listed
     * before the parent '5' catch-all so they are matched first.
     * Unrecognised codes are displayed as-is rather than silently dropped.
     *
     * @param  string|null $functionName  Primary function code (e.g. '5')
     * @param  string|null $subFunction   Sub-function code    (e.g. '1')
     * @return string                     Human-readable label  (e.g. 'Administration')
     */
    private function normalizeInstitutionalFunction(?string $functionName, ?string $subFunction = null): string
    {
        $fn  = trim((string) $functionName);
        $sub = trim((string) $subFunction);

        // Build a dot-notation code; append sub-function only when present
        $code = $fn;
        if ($sub !== '') {
            $code .= '.' . $sub;
        }

        return match ($code) {
            '1'   => 'Instruction',
            '2'   => 'Research',
            '3'   => 'Extension Service',
            '4'   => 'Support to Operations',
            '5.1' => 'Administration',       // Sub-functions matched before the parent '5'
            '5.2' => 'Auxiliary Service',
            '5.3' => 'Mandatory Reserve',
            '5'   => 'General Administration', // Catch-all for bare '5' with no sub-function
            // Raw code shown for any unrecognised value; empty string falls back to 'Unspecified'
            default => $code !== '' ? $code : 'Unspecified',
        };
    }
}
