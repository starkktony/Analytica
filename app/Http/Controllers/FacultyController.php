<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FacultyController extends Controller
{
    // =========================================================================
    // CONFIGURATION
    // =========================================================================

    /**
     * Maps raw database column names to human-readable display labels.
     *
     * This is the single source of truth for column naming throughout the
     * controller. Any renaming should be done here and nowhere else.
     *
     * DB column       → Display label
     * ------------------------------
     * faculty_name    → Name of Faculty
     * faculty_rank    → Generic Faculty Rank
     * college         → College
     * tenured_status  → Is Faculty Tenured?
     * gender          → Gender
     * teaching_cat    → Teaching Category
     */
    private array $columnMap = [
        'faculty_name'   => 'Name of Faculty',
        'faculty_rank'   => 'Generic Faculty Rank',
        'college'        => 'College',
        'tenured_status' => 'Is Faculty Tenured?',
        'gender'         => 'Gender',
        'teaching_cat'   => 'Teaching Category',
    ];

    /**
     * DB columns used to populate the filter dropdowns in the view.
     * Name and gender are intentionally excluded from filtering.
     */
    private array $filterDbCols = [
        'college',
        'tenured_status',
        'faculty_rank',
        'teaching_cat',
    ];

    // =========================================================================
    // PAGES
    // =========================================================================

    /**
     * Renders the main SUC Faculty listing page.
     *
     * Handles filter application, pagination, and summary totals before
     * passing all necessary data to the Blade view.
     *
     * Route: GET /suc-faculty
     */
    public function index(Request $request): View
    {
        // Start with the base DB query (all columns, correct connection)
        $query = $this->baseQuery();

        // Convert DB column names to display labels for use in the view
        $filterColumns   = array_map(fn($c) => $this->columnMap[$c], $this->filterDbCols);

        // Build URL parameter keys from the display labels (e.g. "Is Faculty Tenured?" → "Is_Faculty_Tenured")
        $filterParamKeys = $this->buildFilterParamKeys($filterColumns);

        // Fetch dropdown options from the full unfiltered dataset so all
        // choices are always visible regardless of the current active filters
        $filterOptions = $this->buildFilterOptions();

        // Narrow the query down based on whatever filters the user has applied
        $query = $this->applyFilters($query, $request);

        // Resolve the selected college from the query string (null means "All")
        $collegeParam    = $this->paramKey($this->columnMap['college']);
        $selectedCollege = $request->query($collegeParam);
        if (!$selectedCollege || $selectedCollege === 'All') $selectedCollege = null;

        // ── Summary counts (computed before pagination so they reflect the full filtered set) ──

        // Total number of faculty matching current filters
        $totalFaculty = (clone $query)->count();

        // Faculty whose teaching category contains "tertiary"
        $tertiaryTotal = (clone $query)
            ->whereRaw("LOWER(teaching_cat) LIKE '%tertiary%'")
            ->count();

        // Faculty in elementary, secondary, or technical/vocational categories
        $elemSeconTechboTotal = (clone $query)
            ->whereRaw("LOWER(teaching_cat) REGEXP 'elem|secon|tech'")
            ->count();

        // ── Pagination ────────────────────────────────────────────────────────

        // Sanitise page and per_page inputs to safe integer ranges
        $page    = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));

        $totalRows  = $totalFaculty;
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page       = max(1, min($page, $totalPages)); // clamp page within valid range

        // Fetch only the rows for the current page, then remap DB keys → display labels
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

    // =========================================================================
    // API ENDPOINTS
    // =========================================================================

    /**
     * Returns chart-ready distribution data for the faculty pie/bar charts.
     *
     * Applies the same filters as the index page, then computes breakdowns by
     * tenure, rank, gender, and gender-per-college. Returns an empty structure
     * when no faculty match the current filters.
     *
     * Route: GET /api/faculty-pie
     */
    public function facultyPie(Request $request): JsonResponse
    {
        // Apply filters and pull all matching rows into memory for in-app aggregation
        $query      = $this->baseQuery();
        $query      = $this->applyFilters($query, $request);
        $filteredDf = $query->get()->map(fn($r) => $this->remapRow((array) $r))->all();

        // Return empty chart data shapes when there are no results
        if (empty($filteredDf)) {
            return response()->json([
                'tenure'                  => ['labels' => [], 'values' => []],
                'rank'                    => ['labels' => [], 'values' => []],
                'gender'                  => ['labels' => [], 'values' => []],
                'gender_by_college'       => ['labels' => [], 'datasets' => []],
                'gender_by_college_cards' => [],
            ]);
        }

        // Convenience aliases for the display-label column names
        $tenureCol = 'Is Faculty Tenured?';
        $rankCol   = 'Generic Faculty Rank';
        $genderCol = 'Gender';
        $nameCol   = 'Name of Faculty';

        // Tenure distribution — top 8 categories, no normalisation needed
        $tenure = $this->getPieDistribution($filteredDf, $tenureCol, 8);

        // Rank distribution — top 20, collapsed to standard rank labels via normaliser
        $rank = $this->getPieDistribution($filteredDf, $rankCol, 20, [$this, 'normalizeFacultyRank']);

        // Gender distribution — deduplicated by faculty name so each person counts once
        $gender = $this->getPieDistribution(
            $filteredDf,
            $genderCol,
            6,
            [$this, 'normalizeGender'],
            $nameCol,          // deduplicate on the name column …
            [$this, 'normalizeName'] // … after normalising the name value
        );

        // Grouped bar chart data: colleges on the x-axis, one bar series per gender
        $genderByCollege = $this->getGenderByCollege($filteredDf, $genderCol);

        // Summary card data: male/female count per college (simpler than the chart shape)
        $genderByCollegeCards = $this->getGenderByCollegeCards($filteredDf, $genderCol);

        return response()->json([
            'tenure'                  => $tenure,
            'rank'                    => $rank,
            'gender'                  => $gender,
            'gender_by_college'       => $genderByCollege,
            'gender_by_college_cards' => $genderByCollegeCards,
        ]);
    }

    // =========================================================================
    // PRIVATE HELPERS — DB
    // =========================================================================

    /**
     * Returns a query builder scoped to the faculty table on the
     * normativefunding database connection.
     *
     * Only the six columns defined in $columnMap are selected so that
     * remapRow() can safely assume their presence.
     */
    private function baseQuery()
    {
        return DB::connection('normativefunding')
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
     * Converts a raw DB row (keyed by column names) into a row keyed by
     * the human-readable display labels defined in $columnMap.
     *
     * Missing columns fall back to null rather than throwing an error.
     *
     * @param  array $row  Associative array with DB column keys.
     * @return array       Associative array with display label keys.
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
     * Applies active query-string filters to the given query builder.
     *
     * Iterates over every filterable DB column, looks up its corresponding
     * URL parameter, and adds a WHERE clause when a non-"All" value is present.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @param  Request $request
     * @return \Illuminate\Database\Query\Builder
     */
    private function applyFilters($query, Request $request)
    {
        foreach ($this->filterDbCols as $dbCol) {
            $label = $this->columnMap[$dbCol];   // e.g. "Is Faculty Tenured?"
            $param = $this->paramKey($label);     // e.g. "Is_Faculty_Tenured"
            $value = $request->query($param);

            // Skip the filter entirely when the value is absent or "All"
            if ($value && $value !== 'All') {
                $query->where($dbCol, $value);
            }
        }
        return $query;
    }

    /**
     * Queries distinct, non-empty values for each filterable column and
     * returns them keyed by display label for use in dropdown menus.
     *
     * Strips blank strings and literal "nan" values that may appear in the data.
     *
     * @return array<string, string[]>  e.g. ['College' => ['CAS', 'COED', ...], ...]
     */
    private function buildFilterOptions(): array
    {
        $options = [];
        foreach ($this->filterDbCols as $dbCol) {
            $label = $this->columnMap[$dbCol];

            $vals = DB::connection('normativefunding')
                ->table('faculty')
                ->select($dbCol)
                ->whereNotNull($dbCol)
                ->where($dbCol, '!=', '')
                ->whereRaw("LOWER($dbCol) != 'nan'") // exclude literal "nan" placeholders
                ->distinct()
                ->orderBy($dbCol)
                ->pluck($dbCol)
                ->map(fn($v) => trim((string) $v))
                ->filter(fn($v) => $v !== '')        // drop any remaining blanks after trim
                ->values()
                ->all();

            $options[$label] = $vals;
        }
        return $options;
    }

    // =========================================================================
    // PRIVATE HELPERS — FILTERING UTILITIES
    // =========================================================================

    /**
     * Converts a display label into a URL-safe parameter key.
     *
     * Replaces all non-word characters with underscores and trims leading/
     * trailing underscores.
     *
     * Example: "Is Faculty Tenured?" → "Is_Faculty_Tenured"
     *
     * @param  string $col  Display label.
     * @return string       URL parameter key.
     */
    private function paramKey(string $col): string
    {
        $key = preg_replace('/[^\w]+/u', '_', trim($col));
        return trim(preg_replace('/_+/', '_', $key), '_');
    }

    /**
     * Builds a map of display label → URL parameter key for every filter column.
     *
     * Used by the view to construct filter form inputs without duplicating
     * the paramKey logic in Blade templates.
     *
     * @param  string[] $filterColumns  Display labels for the filterable columns.
     * @return array<string, string>    e.g. ['College' => 'College', 'Is Faculty Tenured?' => 'Is_Faculty_Tenured']
     */
    private function buildFilterParamKeys(array $filterColumns): array
    {
        $map = [];
        foreach ($filterColumns as $c) $map[$c] = $this->paramKey($c);
        return $map;
    }

    // =========================================================================
    // PRIVATE HELPERS — DISTRIBUTION CALCULATIONS
    // =========================================================================

    /**
     * Computes a value-count distribution for a single column, suitable for
     * rendering as a pie or donut chart.
     *
     * Optionally deduplicates rows by a secondary column before counting
     * (e.g. count each faculty member only once regardless of how many rows
     * they appear in).
     *
     * When the number of distinct values exceeds $topN, the remainder are
     * collapsed into a single "Others" bucket.
     *
     * @param  array          $df          In-memory rows (display-label keys).
     * @param  string         $colName     The column to count values for.
     * @param  int            $topN        Maximum distinct labels before "Others" bucketing.
     * @param  callable|null  $normalizer  Optional value transformer (e.g. rank normaliser).
     * @param  string|null    $uniqueBy    Column used for deduplication (e.g. faculty name).
     * @param  callable|null  $uniqueNorm  Normaliser applied to the $uniqueBy value before
     *                                     deduplication (e.g. lowercase + trim).
     * @return array{labels: string[], values: int[]}
     */
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

        // Deduplicate rows by a secondary column when requested.
        // Useful for counting unique individuals rather than raw record occurrences.
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

        // Count occurrences of each (optionally normalised) value
        $counts = [];
        foreach ($tmp as $row) {
            $val = trim((string)($row[$colName] ?? ''));

            // Skip blank or sentinel values
            if ($val === '' || strtolower($val) === 'nan') continue;

            if ($normalizer) $val = ($normalizer)($val);

            $counts[$val] = ($counts[$val] ?? 0) + 1;
        }

        if (empty($counts)) return ['labels' => [], 'values' => []];

        // Sort descending so the largest slices appear first
        arsort($counts);

        // Collapse any values beyond the top-N into a single "Others" entry
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

    /**
     * Builds a grouped bar chart payload showing male/female headcount
     * broken down by college.
     *
     * Deduplicates faculty by name+college so transfers or multi-row records
     * don't inflate the counts.
     *
     * @param  array  $df         In-memory rows (display-label keys).
     * @param  string $genderCol  Display label of the gender column.
     * @return array{labels: string[], datasets: array<array{label: string, data: int[]}>}
     */
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

            // Skip rows with no usable name or college
            if ($name === '' || $college === '' || strtolower($college) === 'nan') continue;

            // Deduplicate: each name+college combination is counted only once
            $key = $name . '||' . $college;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $clean[] = ['college' => $college, 'gender' => $gender];
        }

        if (empty($clean)) return $empty;

        // Build a pivot table: college → gender → count
        $pivot = [];
        foreach ($clean as $r) {
            $pivot[$r['college']][$r['gender']] = ($pivot[$r['college']][$r['gender']] ?? 0) + 1;
        }

        // Sort colleges alphabetically for a consistent x-axis order
        $colleges = array_keys($pivot);
        sort($colleges);

        // Collect all gender labels that appear in the data
        $genders = [];
        foreach ($pivot as $gMap) {
            foreach (array_keys($gMap) as $g) $genders[$g] = true;
        }
        $genders = array_keys($genders);

        // One dataset (bar series) per gender, with a value for every college
        $datasets = [];
        foreach ($genders as $g) {
            $datasets[] = [
                'label' => $g,
                'data'  => array_map(fn($c) => $pivot[$c][$g] ?? 0, $colleges),
            ];
        }

        return ['labels' => $colleges, 'datasets' => $datasets];
    }

    /**
     * Builds the per-college gender summary used for the summary cards UI.
     *
     * Unlike getGenderByCollege(), this only tracks Male/Female totals and
     * returns a flat array of card objects rather than a chart dataset shape.
     *
     * @param  array  $df         In-memory rows (display-label keys).
     * @param  string $genderCol  Display label of the gender column.
     * @return array<array{college: string, male: int, female: int}>
     */
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

            // Same name+college deduplication as getGenderByCollege()
            $key = $name . '||' . $college;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            // Increment only the Male or Female bucket; other genders are ignored here
            $pivot[$college]['Male']   = ($pivot[$college]['Male']   ?? 0) + ($gender === 'Male'   ? 1 : 0);
            $pivot[$college]['Female'] = ($pivot[$college]['Female'] ?? 0) + ($gender === 'Female' ? 1 : 0);
        }

        // Flatten the pivot into a simple list of card objects
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

    // =========================================================================
    // PRIVATE HELPERS — NORMALISERS
    // =========================================================================

    /**
     * Collapses the wide variety of raw faculty rank strings found in the data
     * into a small, consistent set of canonical labels.
     *
     * Matching is case-insensitive and substring-based so minor variations
     * (e.g. "Asst. Professor", "assistant prof.") all resolve to the same label.
     * Anything that doesn't match a known pattern is grouped under "Others".
     *
     * @param  string $value  Raw rank value from the database.
     * @return string         Canonical rank label.
     */
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

    /**
     * Normalises raw gender strings to one of three canonical values:
     * "Male", "Female", or "Unknown".
     *
     * Handles common single-character codes ("m", "f") as well as full words.
     * Any unrecognised value is title-cased and returned as-is rather than
     * being silently dropped.
     *
     * @param  string $value  Raw gender value from the database.
     * @return string         Canonical gender label.
     */
    private function normalizeGender(string $value): string
    {
        $v = strtolower(trim($value));

        if (in_array($v, ['m', 'male', 'man'], true))     return 'Male';
        if (in_array($v, ['f', 'female', 'woman'], true)) return 'Female';
        if (in_array($v, ['', 'nan', 'none'], true))      return 'Unknown';

        // Return unrecognised values in a consistent case rather than dropping them
        return ucwords(strtolower(trim($value)));
    }

    /**
     * Normalises a faculty name for deduplication purposes.
     *
     * Trims surrounding whitespace, collapses internal runs of whitespace to a
     * single space, and lowercases the whole string so that capitalisation
     * differences don't create spurious duplicates.
     *
     * @param  string $value  Raw name value from the database.
     * @return string         Normalised name (lowercase, single-spaced).
     */
    private function normalizeName(string $value): string
    {
        $s = trim($value);
        $s = preg_replace('/\s+/', ' ', $s); // collapse multiple spaces into one
        return strtolower($s);
    }
}
