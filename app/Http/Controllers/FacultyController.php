<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FacultyController extends Controller
{
    // -------------------------------------------------------------------------
    // DB column  →  display label mapping
    // -------------------------------------------------------------------------
    // DB columns: faculty_name, faculty_rank, college, tenured_status, gender, teaching_cat
    // The view / JS expects the old human-readable keys, so we alias them in every
    // query with AS and keep a single source-of-truth map here.
    // -------------------------------------------------------------------------
    private array $columnMap = [
        'faculty_name'   => 'Name of Faculty',
        'faculty_rank'   => 'Generic Faculty Rank',
        'college'        => 'College',
        'tenured_status' => 'Is Faculty Tenured?',
        'gender'         => 'Gender',
        'teaching_cat'   => 'Teaching Category',
    ];

    // Columns that drive the filter dropdowns (exclude name & gender)
    private array $filterDbCols = [
        'college',
        'tenured_status',
        'faculty_rank',
        'teaching_cat',
    ];

    // -------------------------------------------------------------------------
    // PAGES
    // -------------------------------------------------------------------------

    /**
     * GET /suc-faculty
     */
    public function index(Request $request): View
    {
        // ── Build base query ──────────────────────────────────────────────────
        $query = $this->baseQuery();

        // ── Filter columns & param keys ───────────────────────────────────────
        $filterColumns   = array_map(fn($c) => $this->columnMap[$c], $this->filterDbCols);
        $filterParamKeys = $this->buildFilterParamKeys($filterColumns);

        // ── Dropdown options from UNFILTERED data ─────────────────────────────
        $filterOptions = $this->buildFilterOptions();

        // ── Apply filters ─────────────────────────────────────────────────────
        $query = $this->applyFilters($query, $request);

        // ── Selected college ──────────────────────────────────────────────────
        $collegeParam    = $this->paramKey($this->columnMap['college']);
        $selectedCollege = $request->query($collegeParam);
        if (!$selectedCollege || $selectedCollege === 'All') $selectedCollege = null;

        // ── Totals (pre-pagination) ───────────────────────────────────────────
        $totalFaculty = (clone $query)->count();

        $tertiaryTotal        = (clone $query)
            ->whereRaw("LOWER(teaching_cat) LIKE '%tertiary%'")
            ->count();

        $elemSeconTechboTotal = (clone $query)
            ->whereRaw("LOWER(teaching_cat) REGEXP 'elem|secon|tech'")
            ->count();

        // ── Pagination ────────────────────────────────────────────────────────
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));

        $totalRows  = $totalFaculty;
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page       = max(1, min($page, $totalPages));

        $paginated = (clone $query)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn($r) => $this->remapRow((array) $r))
            ->all();

        return view('stzfaculty.suc-faculty', [
            'selected_college'        => $selectedCollege,
            'faculty_data'            => $paginated,
            'filter_columns'          => $filterColumns,
            'filter_options'          => $filterOptions,
            'filter_param_keys'       => $filterParamKeys,
            'active_page'             => 'stzfaculty.suc-faculty',
            'total_faculty'           => $totalFaculty,
            'tertiary_total'          => $tertiaryTotal,
            'elem_secon_techbo_total' => $elemSeconTechboTotal,
            'page'                    => $page,
            'per_page'                => $perPage,
            'total_pages'             => $totalPages,
            'total_rows'              => $totalRows,
        ]);
    }

    // -------------------------------------------------------------------------
    // API ENDPOINTS
    // -------------------------------------------------------------------------

    /**
     * GET /api/faculty-pie
     */
    public function facultyPie(Request $request): JsonResponse
    {
        $query      = $this->baseQuery();
        $query      = $this->applyFilters($query, $request);
        $filteredDf = $query->get()->map(fn($r) => $this->remapRow((array) $r))->all();

        if (empty($filteredDf)) {
            return response()->json([
                'tenure'                  => ['labels' => [], 'values' => []],
                'rank'                    => ['labels' => [], 'values' => []],
                'gender'                  => ['labels' => [], 'values' => []],
                'gender_by_college'       => ['labels' => [], 'datasets' => []],
                'gender_by_college_cards' => [],
            ]);
        }

        $tenureCol = 'Is Faculty Tenured?';
        $rankCol   = 'Generic Faculty Rank';
        $genderCol = 'Gender';
        $nameCol   = 'Name of Faculty';

        $tenure = $this->getPieDistribution($filteredDf, $tenureCol, 8);
        $rank   = $this->getPieDistribution($filteredDf, $rankCol, 20, [$this, 'normalizeFacultyRank']);
        $gender = $this->getPieDistribution(
            $filteredDf,
            $genderCol,
            6,
            [$this, 'normalizeGender'],
            $nameCol,
            [$this, 'normalizeName']
        );

        $genderByCollege      = $this->getGenderByCollege($filteredDf, $genderCol);
        $genderByCollegeCards = $this->getGenderByCollegeCards($filteredDf, $genderCol);

        return response()->json([
            'tenure'                  => $tenure,
            'rank'                    => $rank,
            'gender'                  => $gender,
            'gender_by_college'       => $genderByCollege,
            'gender_by_college_cards' => $genderByCollegeCards,
        ]);
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS — DB
    // -------------------------------------------------------------------------

    /**
     * Base query — selects all columns with their DB names.
     * We keep DB names here and remap to display labels later with remapRow().
     * 
     * CHANGED: Added connection('normativefunding') to use the new database
     */
    private function baseQuery()
    {
        return DB::connection('normativefunding')  // ← CHANGED HERE
            ->table('faculty')
            ->select(
                'faculty_name',
                'faculty_rank',
                'college',
                'tenured_status',
                'gender',
                'teaching_cat'
            );
    }

    /**
     * Re-map a single DB row (keyed by DB column names) to display-label keys.
     */
    private function remapRow(array $row): array
    {
        $out = [];
        foreach ($this->columnMap as $dbCol => $label) {
            $out[$label] = $row[$dbCol] ?? null;
        }
        return $out;
    }

    /**
     * Apply request query-string filters directly on the DB query builder.
     */
    private function applyFilters($query, Request $request)
    {
        foreach ($this->filterDbCols as $dbCol) {
            $label = $this->columnMap[$dbCol];
            $param = $this->paramKey($label);
            $value = $request->query($param);

            if ($value && $value !== 'All') {
                $query->where($dbCol, $value);
            }
        }
        return $query;
    }

    /**
     * Build dropdown options by querying distinct values for each filter column.
     * Returns array keyed by DISPLAY label (to match what the view expects).
     * 
     * CHANGED: Added connection('normativefunding') to use the new database
     */
    private function buildFilterOptions(): array
    {
        $options = [];
        foreach ($this->filterDbCols as $dbCol) {
            $label = $this->columnMap[$dbCol];

            $vals = DB::connection('normativefunding')  // ← CHANGED HERE
                ->table('faculty')
                ->select($dbCol)
                ->whereNotNull($dbCol)
                ->where($dbCol, '!=', '')
                ->whereRaw("LOWER($dbCol) != 'nan'")
                ->distinct()
                ->orderBy($dbCol)
                ->pluck($dbCol)
                ->map(fn($v) => trim((string) $v))
                ->filter(fn($v) => $v !== '')
                ->values()
                ->all();

            $options[$label] = $vals;
        }
        return $options;
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS — FILTERING UTILITIES
    // -------------------------------------------------------------------------

    private function paramKey(string $col): string
    {
        $key = preg_replace('/[^\w]+/u', '_', trim($col));
        return trim(preg_replace('/_+/', '_', $key), '_');
    }

    private function buildFilterParamKeys(array $filterColumns): array
    {
        $map = [];
        foreach ($filterColumns as $c) $map[$c] = $this->paramKey($c);
        return $map;
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS — DISTRIBUTION CALCULATIONS
    // (unchanged from original — they still operate on in-memory arrays)
    // -------------------------------------------------------------------------

    private function getPieDistribution(
        array     $df,
        string    $colName,
        int       $topN = 10,
        ?callable $normalizer = null,
        ?string   $uniqueBy = null,
        ?callable $uniqueNorm = null
    ): array {
        if (empty($df)) return ['labels' => [], 'values' => []];

        $firstRow = $df[0];
        if (!array_key_exists($colName, $firstRow)) return ['labels' => [], 'values' => []];

        $tmp = $df;

        if ($uniqueBy && array_key_exists($uniqueBy, $firstRow)) {
            $seen = [];
            $tmp  = [];
            foreach ($df as $row) {
                $raw = (string)($row[$uniqueBy] ?? '');
                $key = $uniqueNorm ? ($uniqueNorm)($raw) : trim($raw);
                if ($key === '' || isset($seen[$key])) continue;
                $seen[$key] = true;
                $tmp[]      = $row;
            }
        }

        $counts = [];
        foreach ($tmp as $row) {
            $val = trim((string)($row[$colName] ?? ''));
            if ($val === '' || strtolower($val) === 'nan') continue;
            if ($normalizer) $val = ($normalizer)($val);
            $counts[$val] = ($counts[$val] ?? 0) + 1;
        }

        if (empty($counts)) return ['labels' => [], 'values' => []];

        arsort($counts);

        if (count($counts) > $topN) {
            $top    = array_slice($counts, 0, $topN, true);
            $others = array_sum(array_slice($counts, $topN));
            if ($others > 0) $top['Others'] = $others;
            $counts = $top;
        }

        return [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    private function getGenderByCollege(array $df, string $genderCol = 'Gender'): array
    {
        $empty      = ['labels' => [], 'datasets' => []];
        $collegeCol = 'College';
        $nameCol    = 'Name of Faculty';

        if (empty($df) || !array_key_exists($collegeCol, $df[0])) return $empty;

        $seen  = [];
        $clean = [];
        foreach ($df as $row) {
            $name    = $this->normalizeName((string)($row[$nameCol]    ?? ''));
            $college = trim((string)($row[$collegeCol] ?? ''));
            $gender  = $this->normalizeGender((string)($row[$genderCol] ?? ''));

            if ($name === '' || $college === '' || strtolower($college) === 'nan') continue;

            $key = $name . '||' . $college;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $clean[]    = ['college' => $college, 'gender' => $gender];
        }

        if (empty($clean)) return $empty;

        $pivot = [];
        foreach ($clean as $r) {
            $pivot[$r['college']][$r['gender']] = ($pivot[$r['college']][$r['gender']] ?? 0) + 1;
        }

        $colleges = array_keys($pivot);
        sort($colleges);

        $genders = [];
        foreach ($pivot as $gMap) {
            foreach (array_keys($gMap) as $g) $genders[$g] = true;
        }
        $genders = array_keys($genders);

        $datasets = [];
        foreach ($genders as $g) {
            $datasets[] = [
                'label' => $g,
                'data'  => array_map(fn($c) => $pivot[$c][$g] ?? 0, $colleges),
            ];
        }

        return ['labels' => $colleges, 'datasets' => $datasets];
    }

    private function getGenderByCollegeCards(array $df, string $genderCol = 'Gender'): array
    {
        $collegeCol = 'College';
        $nameCol    = 'Name of Faculty';

        if (empty($df) || !array_key_exists($collegeCol, $df[0])) return [];

        $seen  = [];
        $pivot = [];

        foreach ($df as $row) {
            $name    = $this->normalizeName((string)($row[$nameCol]    ?? ''));
            $college = trim((string)($row[$collegeCol] ?? ''));
            $gender  = $this->normalizeGender((string)($row[$genderCol] ?? ''));

            if ($name === '' || $college === '' || strtolower($college) === 'nan') continue;

            $key = $name . '||' . $college;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $pivot[$college]['Male']   = ($pivot[$college]['Male']   ?? 0) + ($gender === 'Male' ? 1 : 0);
            $pivot[$college]['Female'] = ($pivot[$college]['Female'] ?? 0) + ($gender === 'Female' ? 1 : 0);
        }

        $cards = [];
        foreach ($pivot as $college => $counts) {
            $cards[] = [
                'college' => $college,
                'male'    => $counts['Male']   ?? 0,
                'female'  => $counts['Female'] ?? 0,
            ];
        }

        return $cards;
    }

    // -------------------------------------------------------------------------
    // PRIVATE HELPERS — NORMALISERS
    // -------------------------------------------------------------------------

    private function normalizeFacultyRank(string $value): string
    {
        $v = strtolower(trim($value));

        if (str_contains($v, 'instructor'))          return 'Instructor';
        if (str_contains($v, 'assistant professor')) return 'Assistant Professor';
        if (str_contains($v, 'associate professor')) return 'Associate Professor';
        if (str_contains($v, 'full professor'))      return 'Full Professor';
        if (str_contains($v, 'affiliate'))           return 'Affiliate Professor';
        if (str_contains($v, 'adjunct'))             return 'Adjunct Professor';
        if (str_contains($v, 'guest'))               return 'Guest Lecturer';
        if (str_contains($v, 'teacher'))             return 'Teacher / Master Teacher';

        return 'Others';
    }

    private function normalizeGender(string $value): string
    {
        $v = strtolower(trim($value));

        if (in_array($v, ['m', 'male', 'man'], true))     return 'Male';
        if (in_array($v, ['f', 'female', 'woman'], true)) return 'Female';
        if (in_array($v, ['', 'nan', 'none'], true))      return 'Unknown';

        return ucwords(strtolower(trim($value)));
    }

    private function normalizeName(string $value): string
    {
        $s = trim($value);
        $s = preg_replace('/\s+/', ' ', $s);
        return strtolower($s);
    }
}