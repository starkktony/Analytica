<?php

namespace App\Services;

use Illuminate\Http\Request;

class FacultyService
{
    private string $facultyFile = 'faculty.xlsx';
    private ?array $facultyCache = null;

    private array $allowedFilters = [
        'Generic Faculty Rank',
        'College',
        'Is Faculty Tenured?',
        'Teaching Category',
    ];

    /*
    |--------------------------------------------------------------------------
    | Public Methods
    |--------------------------------------------------------------------------
    */

    public function getFacultyData(): array
    {
        if ($this->facultyCache !== null) {
            return $this->facultyCache;
        }

        return $this->facultyCache = $this->readFaculty($this->facultyFile);
    }

    public function getFilterColumns(array $df): array
    {
        if (empty($df)) return [];

        return array_values(array_filter(
            $this->allowedFilters,
            fn($col) => array_key_exists($col, $df[0])
        ));
    }

    public function applyFilters(array $df, Request $request, array $columns): array
    {
        foreach ($columns as $column) {
            $value = $request->query($column);

            if ($value && $value !== 'All') {
                $df = array_values(array_filter(
                    $df,
                    fn($row) =>
                        isset($row[$column]) &&
                        (string)$row[$column] === $value
                ));
            }
        }

        return $df;
    }

    public function buildFilterOptions(array $df, array $columns): array
    {
        $options = [];

        foreach ($columns as $col) {
            $vals = array_unique(array_column($df, $col));
            $vals = array_filter($vals, fn($v) => $v !== null && $v !== '');
            sort($vals);
            $options[$col] = array_values($vals);
        }

        return $options;
    }

    public function calculateTotals(array $df): array
    {
        $total = count($df);
        $tertiary = 0;
        $elem = 0;

        foreach ($df as $row) {
            $cat = strtolower((string)($row['Teaching Category'] ?? ''));

            if (str_contains($cat, 'tertiary')) {
                $tertiary++;
            }

            if (preg_match('/elem|secon|tech/i', $cat)) {
                $elem++;
            }
        }

        return [$total, $tertiary, $elem];
    }

    public function paginate(array $df, Request $request): array
    {
        $page    = max(1, $request->integer('page', 1));
        $perPage = max(1, min(100, $request->integer('per_page', 10)));

        $totalRows  = count($df);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page       = min($page, $totalPages);

        $start = ($page - 1) * $perPage;

        return [
            array_slice($df, $start, $perPage),
            $page,
            $perPage,
            $totalPages,
            $totalRows
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Private Excel Reader
    |--------------------------------------------------------------------------
    */

    private function readFaculty(string $file): array
    {
        $path = storage_path("app/{$file}");

        if (!file_exists($path)) {
            return [];
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows)) {
            return [];
        }

        // Trim headers (IMPORTANT)
        $headers = array_map(
            fn($h) => trim((string) $h),
            $rows[0]
        );

        $data = [];

        foreach (array_slice($rows, 1) as $row) {
            if (array_filter($row)) {
                $data[] = array_combine($headers, $row);
            }
        }

        return $data;
    }
}
