<?php

namespace App\Services;

use App\Models\FundReport;
use Illuminate\Support\Facades\DB;

class FundingDbService
{
    // ─── Years ────────────────────────────────────────────────────────────────

    public function getYears(): array
    {
        return FundReport::on('normativefunding')
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($y) => (string)$y)
            ->values()
            ->all();
    }

    // ─── Income (Form H) ──────────────────────────────────────────────────────

    public function readIncome(string $year): array
    {
        $rid = $this->reportId($year, 'suc_income');

        $empty = [
            'grand_total_income' => 0,
            'tuition_misc_fee'   => 0,
            'miscellaneous'      => 0,
            'other_income'       => 0,
            'breakdown' => [
                'main_categories'      => [],
                'tuition_details'      => [],
                'miscellaneous_details'=> [],
                'other_income_details' => [],
            ],
        ];

        if (!$rid) return $empty;

        // ── Totals from GRAND TOTAL rows ──────────────────────────────────────
        $grandRows = DB::connection('normativefunding')
            ->table('fund_report_income_lines')
            ->where('fund_report_id', $rid)
            ->where('is_grand_total', true)
            ->selectRaw('fund_source, SUM(amount) as total')
            ->groupBy('fund_source')
            ->pluck('total', 'fund_source')
            ->all();

        $tuition = (float)($grandRows['tuition']       ?? 0);
        $misc    = (float)($grandRows['miscellaneous']  ?? 0);
        $other   = (float)($grandRows['other_income']   ?? 0);
        $grand   = $tuition + $misc + $other;

        // ── Detail lines per fund_source ──────────────────────────────────────
        $details = DB::connection('normativefunding')
            ->table('fund_report_income_lines')
            ->where('fund_report_id', $rid)
            ->where('is_grand_total', false)
            ->selectRaw('fund_source, account_name, SUM(amount) as value')
            ->groupBy('fund_source', 'account_name')
            ->get()
            ->groupBy('fund_source')
            ->map(fn($g) => $g->map(fn($r) => ['name' => $r->account_name, 'value' => (float)$r->value])->values()->all())
            ->all();

        return [
            'grand_total_income' => $grand,
            'tuition_misc_fee'   => $tuition,
            'miscellaneous'      => $misc,
            'other_income'       => $other,
            'breakdown' => [
                'main_categories' => [
                    ['name' => 'Tuition & Misc. Fees', 'value' => $tuition],
                    ['name' => 'Miscellaneous',         'value' => $misc],
                    ['name' => 'Other Income',          'value' => $other],
                ],
                'tuition_details'       => $this->top4WithOthers($details['tuition']       ?? []),
                'miscellaneous_details' => $this->top4WithOthers($details['miscellaneous']  ?? []),
                'other_income_details'  => $this->top4WithOthers($details['other_income']   ?? []),
            ],
        ];
    }

    // ─── Allotment (Form G-1) ─────────────────────────────────────────────────

    public function readAllotment(string $year): array
    {
        return $this->readAE($year, 'allotment');
    }

    // ─── Expenditure (Form G-2) ───────────────────────────────────────────────

    public function readExpenditure(string $year): array
    {
        return $this->readAE($year, 'expenditure');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function reportId(string $year, string $type): ?int
    {
        return FundReport::on('normativefunding')
            ->where('year', (int)$year)
            ->where('type', $type)
            ->value('id');
    }

    private function emptyAE(): array
    {
        $zero = ['ps' => 0, 'mooe' => 0, 'co' => 0, 'total' => 0];
        return [
            'gaa'        => $zero,
            'suc_income' => $zero,
            'combined'   => $zero,
            'breakdown'  => [],
        ];
    }

    private function readAE(string $year, string $type): array
    {
        $rid = $this->reportId($year, $type);
        if (!$rid) return $this->emptyAE();

        // ── Grand totals (is_total = true row) ───────────────────────────────
        $totals = DB::connection('normativefunding')
            ->table('fund_report_ae_lines')
            ->where('fund_report_id', $rid)
            ->where('is_total', true)
            ->first();

        $res = $this->emptyAE();

        if ($totals) {
            $res['gaa']        = $this->mapAERow($totals, 'gaa');
            $res['suc_income'] = $this->mapAERow($totals, 'suc');
            $res['combined']   = $this->mapAERow($totals, 'combined');
        }

        // ── Breakdown (all non-total rows) ────────────────────────────────────
        $lines = DB::connection('normativefunding')
            ->table('fund_report_ae_lines')
            ->where('fund_report_id', $rid)
            ->where('is_total', false)
            ->orderBy('id')
            ->get();

        $res['breakdown'] = $lines->map(fn($l) => [
            'function'      => $l->function_name . ($l->sub_function ? ' – ' . $l->sub_function : ''),
            'gaa_ps'        => (float)$l->gaa_ps,
            'gaa_mooe'      => (float)$l->gaa_mooe,
            'gaa_co'        => (float)$l->gaa_co,
            'gaa_total'     => (float)$l->gaa_total,
            'suc_ps'        => (float)$l->suc_ps,
            'suc_mooe'      => (float)$l->suc_mooe,
            'suc_co'        => (float)$l->suc_co,
            'suc_total'     => (float)$l->suc_total,
            'combined_ps'   => (float)$l->combined_ps,
            'combined_mooe' => (float)$l->combined_mooe,
            'combined_co'   => (float)$l->combined_co,
            'combined_total'=> (float)$l->combined_total,
        ])->values()->all();

        return $res;
    }

    /**
     * Map a DB row's columns into the ['ps','mooe','co','total'] shape
     * using the given prefix (gaa / suc / combined).
     */
    private function mapAERow(object $row, string $prefix): array
    {
        // combined_total column is named combined_total; suc prefix maps to suc_*
        return [
            'ps'    => (float)($row->{$prefix . '_ps'}    ?? 0),
            'mooe'  => (float)($row->{$prefix . '_mooe'}  ?? 0),
            'co'    => (float)($row->{$prefix . '_co'}    ?? 0),
            'total' => (float)($row->{$prefix . '_total'} ?? 0),
        ];
    }

    /** Keep top 4 items by value, merge the rest into an "Others" bucket. */
    private function top4WithOthers(array $items): array
    {
        if (!$items) return [];

        usort($items, fn($a, $b) => $b['value'] <=> $a['value']);

        $top  = array_slice($items, 0, 4);
        $rest = array_slice($items, 4);

        if ($rest) {
            $top[] = ['name' => 'Others', 'value' => array_sum(array_column($rest, 'value'))];
        }

        return $top;
    }
}