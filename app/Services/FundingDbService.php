<?php

namespace App\Services;

use App\Models\FundReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FundingDbService
{
    public function getYears(): array
    {
        return FundReport::query()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn($y) => (string) $y)
            ->values()
            ->all();
    }

    public function readIncome(string $year): array
    {
        $rid = $this->reportId($year, 'suc_income');

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
        $grand   = $tuition + $misc + $other;

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
                'main_categories' => [
                    ['name' => 'Tuition & Misc. Fees', 'value' => $tuition],
                    ['name' => 'Miscellaneous', 'value' => $misc],
                    ['name' => 'Other Income', 'value' => $other],
                ],
                'tuition_details'       => $this->top4WithOthers($details['tuition'] ?? []),
                'miscellaneous_details' => $this->top4WithOthers($details['miscellaneous'] ?? []),
                'other_income_details'  => $this->top4WithOthers($details['other_income'] ?? []),
            ],
        ];
    }

    public function readAllotment(string $year): array
    {
        return $this->readAE($year, 'allotment');
    }

    public function readExpenditure(string $year): array
    {
        return $this->readAE($year, 'expenditure');
    }

    private function reportId(string $year, string $type): ?int
    {
        return FundReport::query()
            ->where('year', (int) $year)
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

        if (! $rid) {
            return $this->emptyAE();
        }

        $totals = DB::connection('normativefunding')->table('fund_report_ae_lines')
            ->where('fund_report_id', $rid)
            ->where('is_total', true)
            ->first();

        $result = $this->emptyAE();

        if ($totals) {
            $result['gaa']        = $this->mapAERow($totals, 'gaa');
            $result['suc_income'] = $this->mapAERow($totals, 'suc');
            $result['combined']   = $this->mapAERow($totals, 'combined');
        }

        $lines = DB::connection('normativefunding')->table('fund_report_ae_lines')
            ->where('fund_report_id', $rid)
            ->where('is_total', false)
            ->orderBy('id')
            ->get();

        $grouped = $lines->groupBy(function ($line) {
            return $this->normalizeInstitutionalFunction(
                $line->function_name,
                $line->sub_function
            );
        });

        $result['breakdown'] = $grouped->map(function (Collection $group, string $functionLabel) {
            return [
                'function'       => $functionLabel,
                'gaa_ps'         => (float) $group->sum('gaa_ps'),
                'gaa_mooe'       => (float) $group->sum('gaa_mooe'),
                'gaa_co'         => (float) $group->sum('gaa_co'),
                'gaa_total'      => (float) $group->sum('gaa_total'),
                'suc_ps'         => (float) $group->sum('suc_ps'),
                'suc_mooe'       => (float) $group->sum('suc_mooe'),
                'suc_co'         => (float) $group->sum('suc_co'),
                'suc_total'      => (float) $group->sum('suc_total'),
                'combined_ps'    => (float) $group->sum('combined_ps'),
                'combined_mooe'  => (float) $group->sum('combined_mooe'),
                'combined_co'    => (float) $group->sum('combined_co'),
                'combined_total' => (float) $group->sum('combined_total'),
            ];
        })->values()->all();

        return $result;
    }

    private function mapAERow(object $row, string $prefix): array
    {
        return [
            'ps'    => (float) ($row->{$prefix . '_ps'} ?? 0),
            'mooe'  => (float) ($row->{$prefix . '_mooe'} ?? 0),
            'co'    => (float) ($row->{$prefix . '_co'} ?? 0),
            'total' => (float) ($row->{$prefix . '_total'} ?? 0),
        ];
    }

    private function top4WithOthers(array $items): array
    {
        if (empty($items)) {
            return [];
        }
        
        usort($items, fn($a, $b) => $b['value'] <=> $a['value']);

        $top  = array_slice($items, 0, 4);
        $rest = array_slice($items, 4);

        if (! empty($rest)) {
            $top[] = [
                'name'  => 'Others',
                'value' => array_sum(array_column($rest, 'value')),
            ];
        }

        return $top;
    }

    private function normalizeIncomeLabel(?string $label): string
    {
        $value = trim((string) $label);

        return $value !== '' ? $value : 'Unspecified';
    }

    private function normalizeInstitutionalFunction(?string $functionName, ?string $subFunction = null): string
    {
        $fn = trim((string)$functionName);
        $sub = trim((string)$subFunction);

        $code = $fn;
        if ($sub !== '') {
            $code .= '.' . $sub;
        }

        return match ($code) {

            '1' => 'Instruction',

            '2' => 'Research',

            '3' => 'Extension Service',

            '4' => 'Support to Operations',

            '5.1' => 'Administration',

            '5.2' => 'Auxiliary Service',

            '5.3' => 'Mandatory Reserve',

            '5' => 'General Administration',

            default => $code !== '' ? $code : 'Unspecified',
        };
    }
}