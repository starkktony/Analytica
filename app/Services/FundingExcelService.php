<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class FundingExcelService
{
    public function __construct(private readonly string $filePath) {}

    public function getYearSheets(): array
    {
        // Fast: just get sheet names, don’t parse full cell data
        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($this->filePath);
        $names = $spreadsheet->getSheetNames();

        $years = array_values(array_filter($names, fn ($s) => ctype_digit($s)));
        rsort($years);

        return $years;
    }

    public function loadSheet(string $sheetName): array
    {
        $reader = IOFactory::createReaderForFile($this->filePath);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);

        $spreadsheet = $reader->load($this->filePath);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        return $sheet ? $sheet->toArray(null, true, true, false) : [];
    }

    public function cellFloat(array $row, int $col): float
    {
        $v = $row[$col] ?? null;
        return (is_numeric($v)) ? (float) $v : 0.0;
    }

    public function top4WithOthers(array $items): array
    {
        if (!$items) return [];

        usort($items, fn($a,$b) => $b['value'] <=> $a['value']);

        $top = array_slice($items, 0, 4);
        $rest = array_slice($items, 4);

        if ($rest) {
            $top[] = [
                'name' => 'Others',
                'value' => array_sum(array_column($rest, 'value'))
            ];
        }

        return $top;
    }

    public function readIncome(string $year): array
    {
        $data = $this->loadSheet($year);
        if (!$data) return [
            'grand_total_income' => 0,
            'tuition_misc_fee' => 0,
            'miscellaneous' => 0,
            'other_income' => 0,
            'breakdown' => [
                'main_categories' => [],
                'tuition_details' => [],
                'other_income_details' => [],
                'overall_distribution' => [],
            ],
        ];

        $grand = $data[91] ?? [];

        $tuitionTotal = $this->cellFloat($grand, 3);
        $miscTotal    = $this->cellFloat($grand, 4);
        $otherTotal   = $this->cellFloat($grand, 5);
        $grandTotal   = $this->cellFloat($grand, 6);

        $tuitionItems = [];
        $otherItems   = [];

        for ($i = 66; $i <= 90; $i++) {
            $row = $data[$i] ?? [];
            $name = trim((string)($row[2] ?? ''));
            if ($name === '') continue;

            $tv = $this->cellFloat($row, 3);
            if ($tv > 0) $tuitionItems[] = ['name' => $name, 'value' => $tv];

            $ov = $this->cellFloat($row, 5);
            if ($ov > 0) $otherItems[] = ['name' => $name, 'value' => $ov];
        }

        // merge + dedup
        $merged = [];
        foreach (array_merge($tuitionItems, $otherItems) as $it) {
            $merged[$it['name']] = ($merged[$it['name']] ?? 0) + $it['value'];
        }
        $overall = array_map(fn($k) => ['name' => $k, 'value' => $merged[$k]], array_keys($merged));

        return [
            'grand_total_income' => $grandTotal,
            'tuition_misc_fee' => $tuitionTotal,
            'miscellaneous' => $miscTotal,
            'other_income' => $otherTotal,
            'breakdown' => [
                'main_categories' => [
                    ['name' => 'Tuition & Misc. Fees', 'value' => $tuitionTotal],
                    ['name' => 'Miscellaneous', 'value' => $miscTotal],
                    ['name' => 'Other Income', 'value' => $otherTotal],
                ],
                'tuition_details' => $this->top4WithOthers($tuitionItems),
                'other_income_details' => $this->top4WithOthers($otherItems),
                'overall_distribution' => $this->top4WithOthers($overall),
            ],
        ];
    }

    public function readAllotment(string $year): array
    {
        $data = $this->loadSheet($year);
        if (!$data) return $this->emptyAE();

        $total = $data[21] ?? [];
        $res = $this->emptyAE();

        $res['gaa'] = [
            'ps' => $this->cellFloat($total, 3),
            'mooe' => $this->cellFloat($total, 4),
            'co' => $this->cellFloat($total, 5),
            'total' => $this->cellFloat($total, 6),
        ];

        $res['suc_income'] = [
            'ps' => $this->cellFloat($total, 8),
            'mooe' => $this->cellFloat($total, 9),
            'co' => $this->cellFloat($total, 10),
            'total' => $this->cellFloat($total, 11),
        ];

        $res['combined'] = [
            'ps' => $this->cellFloat($total, 13),
            'mooe' => $this->cellFloat($total, 14),
            'co' => $this->cellFloat($total, 15),
            'total' => $this->cellFloat($total, 16),
        ];

        // breakdown rows 11..18
        for ($i = 11; $i <= 18; $i++) {
            $row = $data[$i] ?? [];
            $col0 = $row[0] ?? null;
            $col1 = $row[1] ?? null;
            $col2 = $row[2] ?? null;

            if ($col0 !== null && $col0 !== '' && is_numeric($col0)) {
                if ((int)$col0 === 5) continue;
                $res['breakdown'][] = $this->breakdownRow((string)$col1, $row);
            } elseif ($col1 !== null && $col1 !== '' && $col2 !== null && $col2 !== '' && is_numeric($col1)) {
                $sub = (float)$col1;
                if (in_array($sub, [5.1, 5.2, 5.3], true)) {
                    $res['breakdown'][] = $this->breakdownRow((string)$col2, $row);
                }
            }
        }

        return $res;
    }

    public function readExpenditure(string $year): array
    {
        $data = $this->loadSheet($year);
        if (!$data) return $this->emptyAE();

        $total = $data[49] ?? [];
        $res = $this->emptyAE();

        $res['gaa'] = [
            'ps' => $this->cellFloat($total, 3),
            'mooe' => $this->cellFloat($total, 4),
            'co' => $this->cellFloat($total, 5),
            'total' => $this->cellFloat($total, 6),
        ];

        $res['suc_income'] = [
            'ps' => $this->cellFloat($total, 8),
            'mooe' => $this->cellFloat($total, 9),
            'co' => $this->cellFloat($total, 10),
            'total' => $this->cellFloat($total, 11),
        ];

        $res['combined'] = [
            'ps' => $this->cellFloat($total, 13),
            'mooe' => $this->cellFloat($total, 14),
            'co' => $this->cellFloat($total, 15),
            'total' => $this->cellFloat($total, 16),
        ];

        for ($i = 39; $i <= 46; $i++) {
            $row = $data[$i] ?? [];
            $col0 = $row[0] ?? null;
            $col1 = $row[1] ?? null;
            $col2 = $row[2] ?? null;

            if ($col0 !== null && $col0 !== '' && is_numeric($col0)) {
                if ((int)$col0 === 5) continue;
                $res['breakdown'][] = $this->breakdownRow((string)$col1, $row);
            } elseif ($col1 !== null && $col1 !== '' && $col2 !== null && $col2 !== '' && is_numeric($col1)) {
                $sub = (float)$col1;
                if (in_array($sub, [5.1, 5.2, 5.3], true)) {
                    $res['breakdown'][] = $this->breakdownRow((string)$col2, $row);
                }
            }
        }

        return $res;
    }

    private function breakdownRow(string $fn, array $row): array
    {
        return [
            'function' => trim($fn),
            'gaa_ps' => $this->cellFloat($row, 3),
            'gaa_mooe' => $this->cellFloat($row, 4),
            'gaa_co' => $this->cellFloat($row, 5),
            'gaa_total' => $this->cellFloat($row, 6),
            'suc_ps' => $this->cellFloat($row, 8),
            'suc_mooe' => $this->cellFloat($row, 9),
            'suc_co' => $this->cellFloat($row, 10),
            'suc_total' => $this->cellFloat($row, 11),
        ];
    }

    private function emptyAE(): array
    {
        return [
            'gaa' => ['ps' => 0, 'mooe' => 0, 'co' => 0, 'total' => 0],
            'suc_income' => ['ps' => 0, 'mooe' => 0, 'co' => 0, 'total' => 0],
            'combined' => ['ps' => 0, 'mooe' => 0, 'co' => 0, 'total' => 0],
            'breakdown' => [],
        ];
    }

    /**
 * Convert parsed Excel data into DB-ready rows for fund_report_lines.
 * Each row must match:
 *  [
 *    'campus' => ?string,
 *    'function' => ?string,
 *    'fund_source' => ?string,
 *    'account_code' => ?string,
 *    'account_name' => ?string,
 *    'amount' => float|int|string numeric
 *  ]
 */
public function toDbRows(string $year, string $type): array
{
    // 1) Get data from your existing readers
    $data = match ($type) {
        'allotment'   => $this->readAllotment($year),
        'expenditure' => $this->readExpenditure($year),
        'suc_income'  => $this->readIncome($year),
        default       => throw new \InvalidArgumentException("Unknown type: {$type}"),
    };

    // 2) Normalize whatever shape it returns into flat rows
    $rows = $this->normalizeToRows($data);

    // 3) Clean rows + ensure required keys exist
    $out = [];
    foreach ($rows as $r) {
        $amount = $this->toNumber($r['amount'] ?? 0);

        // Skip empty / zero-ish rows
        if ($amount === null) continue;

        $out[] = [
            'campus'       => $this->cleanStr($r['campus'] ?? null),
            'function'     => $this->cleanStr($r['function'] ?? null),
            'fund_source'  => $this->cleanStr($r['fund_source'] ?? ($r['source'] ?? null)),
            'account_code' => $this->cleanStr($r['account_code'] ?? ($r['code'] ?? null)),
            'account_name' => $this->cleanStr($r['account_name'] ?? ($r['name'] ?? null)),
            'amount'       => $amount,
        ];
    }

    return $out;
}

/**
 * Accepts many possible shapes and returns a flat list of associative rows.
 * This tries hard to "do the right thing" without you rewriting your parsers.
 */
private function normalizeToRows(mixed $data): array
{
    // If your reader returns ['rows' => [...]]
    if (is_array($data) && array_key_exists('rows', $data) && is_array($data['rows'])) {
        return $this->normalizeToRows($data['rows']);
    }

    // If it's already a list of rows (0..n)
    if (is_array($data) && $this->isList($data)) {
        // if each item looks like a row
        if (isset($data[0]) && is_array($data[0])) {
            return $data;
        }
        return [];
    }

    // If it's a nested associative structure, flatten common patterns
    // Example: ['Main Campus' => ['Instruction' => 123, 'Research' => 456]]
    if (is_array($data)) {
        $rows = [];

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                // If v is map: function => amount
                if (!$this->isList($v) && $this->looksLikeFunctionAmountMap($v)) {
                    foreach ($v as $func => $amt) {
                        $rows[] = [
                            'campus'   => is_string($k) ? $k : null,
                            'function' => is_string($func) ? $func : null,
                            'amount'   => $amt,
                        ];
                    }
                    continue;
                }

                // deeper nesting
                $sub = $this->normalizeToRows($v);
                foreach ($sub as $sr) {
                    // carry down a "campus" if none exists
                    if (!isset($sr['campus']) && is_string($k)) {
                        $sr['campus'] = $k;
                    }
                    $rows[] = $sr;
                }
            } else {
                // scalar value: maybe amount only
                if (is_numeric($v) || is_string($v)) {
                    $rows[] = [
                        'account_name' => is_string($k) ? $k : null,
                        'amount' => $v,
                    ];
                }
            }
        }

        return $rows;
    }

    return [];
}

private function toNumber(mixed $v): ?float
{
    if ($v === null) return null;
    if (is_int($v) || is_float($v)) return (float)$v;

    if (is_string($v)) {
        $s = trim($v);
        if ($s === '') return null;

        // remove peso sign, commas, spaces
        $s = str_replace(['₱', ',', ' '], '', $s);

        // handle parentheses as negative e.g. (123.45)
        if (preg_match('/^\((.*)\)$/', $s, $m)) {
            $s = '-' . $m[1];
        }

        // keep only valid numeric chars
        $s = preg_replace('/[^0-9.\-]/', '', $s);

        if ($s === '' || $s === '-' || $s === '.' || $s === '-.') return null;
        return (float)$s;
    }

    return null;
}

private function cleanStr(?string $s): ?string
{
    if ($s === null) return null;
    $s = trim($s);
    return $s === '' ? null : $s;
}

private function isList(array $arr): bool
{
    $i = 0;
    foreach (array_keys($arr) as $k) {
        if ($k !== $i++) return false;
    }
    return true;
}

private function looksLikeFunctionAmountMap(array $arr): bool
{
    // Heuristic: keys are strings, values are numeric-ish
    $checked = 0;
    foreach ($arr as $k => $v) {
        if (!is_string($k)) return false;
        if (!(is_numeric($v) || is_string($v))) return false;
        $checked++;
        if ($checked >= 3) break;
    }
    return $checked > 0;
}
}
