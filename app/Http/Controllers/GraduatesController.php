<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * GraduatesController
 *
 * Handles the Graduates dashboard — a reporting view that displays
 * graduate headcount and demographic profile data from the
 * `normativefunding` database connection.
 *
 * Key responsibilities:
 *  - Serve the main Blade view with pre-built chart/table payloads.
 *  - Expose JSON endpoints for dynamic filter options and dashboard data
 *    (used by front-end AJAX calls when the user changes a filter).
 *  - Normalize inconsistent college/program names stored in the database
 *    so that the UI always shows clean labels.
 */
class GraduatesController extends Controller
{
    // -------------------------------------------------------------------------
    // Public route handlers
    // -------------------------------------------------------------------------

    /**
     * GET /graduates
     *
     * Renders the full graduates dashboard Blade view.
     * Reads filter values from the query string and falls back to sensible
     * defaults so the page is always usable without any parameters.
     *
     * Query parameters:
     *  - view_type     : 'graduate_headcount' | 'demographic_profile'
     *  - student_level : 'All' | 'Undergraduate' | 'Postgraduate'
     *  - semester      : 'All' | 'Annual' | 'Midyear'
     *  - college       : 'All' | < college name>
     *  - program       : 'All' | < program name>
     *
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request): View
    {
        $viewType     = $request->query('view_type', 'graduate_headcount');
        $studentLevel = $request->query('student_level', 'All');
        $semester     = $request->query('semester', 'All');
        $college      = $request->query('college', 'All');
        $program      = $request->query('program', 'All');

        // Fetch available filter options — programs depend on the selected college.
        $filterOptions = $this->getFilterOptions($college, $studentLevel, $semester);

        // A program filter only makes sense within a college context.
        // If no college is selected, always show all programs.
        if ($college === 'All') {
            $program = 'All';
        }

        // Guard against a stale program selection (e.g. the user switched college
        // but a program from the previous college is still in the URL).
        if ($program !== 'All' && !in_array($program, $filterOptions['programs'], true)) {
            $program = 'All';
        }

        // Build the full chart/value-box payload for the selected filters.
        $payload = $this->buildDashboardData($viewType, $studentLevel, $semester, $college, $program);

        // dd(
        //     DB::table('graduates')
        //         ->where('college', 'like', '%Agriculture%')
        //         ->where('program_name', 'like', '%Agriculture%')
        //         ->pluck('program_name')
        //         ->unique()
        //         ->values()
        //         ->all()
        // );

        return view('student.graduates', array_merge($payload, [
            'active_page'        => 'graduates',
            'view_type'          => $viewType,
            'selected_view_type' => $viewType,
            'student_level'      => $studentLevel,
            'semester'           => $semester,
            'selected_college'   => $college,
            'selected_program'   => $program,
            'colleges'           => $filterOptions['colleges'],
            'programs'           => $filterOptions['programs'],
            'semesters'          => $filterOptions['semesters'],
        ]));
    }

    /**
     * GET /graduates/filters  (JSON)
     *
     * Returns the available filter options as JSON.
     * Called by the front end whenever the user changes the college
     * dropdown so that the program list can be refreshed without a
     * full page reload.
     *
     * @param  Request       $request
     * @return JsonResponse
     */
    public function filters(Request $request): JsonResponse
    {
        $college      = $request->query('college', 'All');
        $studentLevel = $request->query('student_level', 'All');
        $semester     = $request->query('semester', 'All');

        return response()->json(
            $this->getFilterOptions($college, $studentLevel, $semester)
        );
    }

    /**
     * GET /graduates/dashboard  (JSON)
     *
     * Returns the full dashboard payload as JSON.
     * Used by the front end for partial refreshes when filters change —
     * mirrors the logic in index() without rendering a Blade view.
     *
     * @param  Request       $request
     * @return JsonResponse
     */
    public function dashboard(Request $request): JsonResponse
    {
        $viewType     = $request->query('view_type', 'graduate_headcount');
        $studentLevel = $request->query('student_level', 'All');
        $semester     = $request->query('semester', 'All');
        $college      = $request->query('college', 'All');
        $program      = $request->query('program', 'All');

        $filterOptions = $this->getFilterOptions($college, $studentLevel, $semester);

        // Same guard logic as in index() — keep program consistent with college.
        if ($college === 'All') {
            $program = 'All';
        }

        if ($program !== 'All' && !in_array($program, $filterOptions['programs'], true)) {
            $program = 'All';
        }

        return response()->json(
            $this->buildDashboardData($viewType, $studentLevel, $semester, $college, $program)
        );
    }

    // -------------------------------------------------------------------------
    // Database query helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the base Eloquent/DB query builder for the `graduates` table.
     *
     * Selects only the columns needed for the dashboard and appends two
     * derived columns computed at query time:
     *
     *  - derived_semester      : 'Midyear' when graduation month is February,
     *                            'Annual' for every other month.
     *  - derived_student_level : 'Postgraduate' when the program name contains
     *                            typical graduate-level keywords (master, doctoral,
     *                            phd, graduate); 'Undergraduate' otherwise.
     *
     * Only rows with a non-null `date_graduated` are included because records
     * without a graduation date represent students who have not yet graduated.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBaseQuery()
    {
        return DB::connection('normativefunding')->table('graduates')
            ->select([
                'student_id',
                'gender',
                'college',
                'program_name',
                'program_major',
                'date_graduated',

                // Semester is inferred from the graduation month.
                // February commencements are classified as Midyear; all others as Annual.
                DB::raw("
                    CASE
                        WHEN MONTH(date_graduated) = 2 THEN 'Midyear'
                        ELSE 'Annual'
                    END as derived_semester
                "),

                // Student level is inferred from keywords in the program name
                // because the database does not store a separate level column.
                DB::raw("
                    CASE
                        WHEN LOWER(program_name) LIKE '%master%'
                          OR LOWER(program_name) LIKE '%doctoral%'
                          OR LOWER(program_name) LIKE '%phd%'
                          OR LOWER(program_name) LIKE '%graduate%'
                        THEN 'Postgraduate'
                        ELSE 'Undergraduate'
                    END as derived_student_level
                "),
            ])
            ->whereNotNull('date_graduated');
    }

    /**
     * Applies the active dashboard filter selections to a query builder.
     *
     * Each filter is only applied when it is not set to 'All', allowing
     * any combination of filters to be used independently.
     *
     * The student-level and semester filters replicate the same CASE
     * expressions used in getBaseQuery() so that MySQL can evaluate the
     * condition correctly even though the derived columns are not directly
     * filterable via WHERE in a flat SELECT.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $studentLevel  'All' | 'Undergraduate' | 'Postgraduate'
     * @param  string  $semester      'All' | 'Annual' | 'Midyear'
     * @param  string  $college       'All' |  college name
     * @param  string  $program       'All' |  program name
     * @return \Illuminate\Database\Query\Builder
     */
    private function applyFilters($query, string $studentLevel, string $semester, string $college, string $program)
    {
        // Filter by student level using the same keyword-based CASE logic.
        if ($studentLevel !== 'All') {
            $query->whereRaw("
                CASE
                    WHEN LOWER(program_name) LIKE '%master%'
                    OR LOWER(program_name) LIKE '%doctoral%'
                    OR LOWER(program_name) LIKE '%phd%'
                    OR LOWER(program_name) LIKE '%graduate%'
                    THEN 'Postgraduate'
                    ELSE 'Undergraduate'
                END = ?
            ", [$studentLevel]);
        }

        // Filter by semester using the month-based CASE logic.
        if ($semester !== 'All') {
            $query->whereRaw("
                CASE
                    WHEN MONTH(date_graduated) = 2 THEN 'Midyear'
                    ELSE 'Annual'
                END = ?
            ", [$semester]);
        }

        // Use whereIn with all known raw aliases for the selected college
        // to handle inconsistent naming in the database (e.g. 'CBA' vs 'College of Business Administration').
        if ($college !== 'All') {
            $query->whereIn('college', $this->getCollegeAliases($college));
        }

        // Same alias expansion for program names.
        if ($program !== 'All') {
            $query->whereIn('program_name', $this->getProgramAliases($program));
        }

        return $query;
    }

    // -------------------------------------------------------------------------
    // Filter option builder
    // -------------------------------------------------------------------------

    /**
     * Builds the arrays of selectable values for each filter dropdown.
     *
     * - Colleges : always the full list, normalized and sorted.
     * - Programs  : only populated when a specific college is selected.
     *               Programs are also filtered by the active student-level
     *               and semester selections so that only relevant options appear.
     * - Semesters / student levels / view types are static enumerations.
     *
     * @param  string  $college       Currently selected college ('All' or a  name).
     * @param  string  $studentLevel  Currently selected student level.
     * @param  string  $semester      Currently selected semester.
     * @return array{
     *     colleges: string[],
     *     programs: string[],
     *     semesters: string[],
     *     student_levels: string[],
     *     view_types: array<array{value: string, label: string}>
     * }
     */
    private function getFilterOptions(string $college = 'All', string $studentLevel = 'All', string $semester = 'All'): array
    {
        // Pull every distinct college value from the DB and normalize it.
        $rawColleges = DB::connection('normativefunding')->table('graduates')
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->pluck('college');

        $colleges = $rawColleges
            ->map(fn($c) => $this->normalizeCollegeName($c))
            ->unique()
            ->sort()
            ->values()
            ->all();

        // When no college is selected, the program list is meaningless —
        // return an empty array so the front-end hides/disables that dropdown.
        if ($college === 'All') {
            return [
                'colleges'       => $colleges,
                'programs'       => [],
                'semesters'      => ['Annual', 'Midyear'],
                'student_levels' => ['Undergraduate', 'Postgraduate'],
                'view_types'     => [
                    ['value' => 'graduate_headcount', 'label' => 'Graduate Headcount'],
                    ['value' => 'demographic_profile', 'label' => 'Demographic Profile'],
                ],
            ];
        }

        // Build the program list scoped to the selected college and any
        // additional active filters so irrelevant programs are hidden.
        $programQuery = DB::connection('normativefunding')->table('graduates')
            ->whereNotNull('date_graduated')
            ->whereNotNull('program_name')
            ->where('program_name', '!=', '')
            ->whereIn('college', $this->getCollegeAliases($college));

        // Apply optional student-level scoping to the program list.
        if ($studentLevel !== 'All') {
            $programQuery->whereRaw("
                CASE
                    WHEN LOWER(program_name) LIKE '%master%'
                    OR LOWER(program_name) LIKE '%doctoral%'
                    OR LOWER(program_name) LIKE '%phd%'
                    OR LOWER(program_name) LIKE '%graduate%'
                    THEN 'Postgraduate'
                    ELSE 'Undergraduate'
                END = ?
            ", [$studentLevel]);
        }

        // Apply optional semester scoping to the program list.
        if ($semester !== 'All') {
            $programQuery->whereRaw("
                CASE
                    WHEN MONTH(date_graduated) = 2 THEN 'Midyear'
                    ELSE 'Annual'
                END = ?
            ", [$semester]);
        }

        // Some DB rows contain combined program strings (e.g. "BS Agriculture /
        // Certificate in Agricultural Science"). splitProgramNames() splits
        // those into individual  names so the dropdown shows each
        // program as a separate option.
        $programs = $programQuery
            ->pluck('program_name')
            ->flatMap(fn($p) => $this->splitProgramNames($p))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return [
            'colleges'       => $colleges,
            'programs'       => $programs,
            'semesters'      => ['Annual', 'Midyear'],
            'student_levels' => ['Undergraduate', 'Postgraduate'],
            'view_types'     => [
                ['value' => 'graduate_headcount', 'label' => 'Graduate Headcount'],
                ['value' => 'demographic_profile', 'label' => 'Demographic Profile'],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Dashboard payload builder
    // -------------------------------------------------------------------------

    /**
     * Assembles the complete data payload consumed by the Blade view and the
     * JSON dashboard endpoint.
     *
     * The payload includes:
     *  - Summary value boxes (totals and level breakdowns).
     *  - Pie chart  : sex distribution of all filtered graduates.
     *  - Donut chart: share of graduates per college or per program.
     *  - Major chart: share of graduates per major within a selected program
     *                 (only when a program with recorded majors is selected).
     *  - Ranking chart: horizontal bar chart ranking colleges/programs by count.
     *  - Stacked chart: sex distribution broken down by college / program / major.
     *
     * @param  string  $viewType      'graduate_headcount' | 'demographic_profile'
     * @param  string  $studentLevel  Active student-level filter.
     * @param  string  $semester      Active semester filter.
     * @param  string  $college       Active college filter.
     * @param  string  $program       Active program filter.
     * @return array
     */
    private function buildDashboardData(
        string $viewType,
        string $studentLevel,
        string $semester,
        string $college,
        string $program
    ): array {
        // Safety guard: a program filter is only valid within a college context.
        if ($college === 'All') {
            $program = 'All';
        }

        // Fetch and normalize all rows that match the current filters.
        $query       = $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, $program);
        $allFiltered = $this->normalizeRows((clone $query)->get());

        // --- Summary counts ---------------------------------------------------

        $totalGraduates = $allFiltered->count();

        // Gender counts across all filtered graduates.
        $maleCount   = $allFiltered->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count();
        $femaleCount = $allFiltered->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count();

        // Level counts for the value boxes.
        $undergradCount = $allFiltered->where('derived_student_level', 'Undergraduate')->count();
        $postgradCount  = $allFiltered->where('derived_student_level', 'Postgraduate')->count();

        // Gender × level breakdown for the demographic-profile value boxes and pie chart.
        $undergradMale   = $allFiltered->where('derived_student_level', 'Undergraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count();
        $undergradFemale = $allFiltered->where('derived_student_level', 'Undergraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count();
        $postgradMale    = $allFiltered->where('derived_student_level', 'Postgraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count();
        $postgradFemale  = $allFiltered->where('derived_student_level', 'Postgraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count();

        // --- Chart titles & grouping ------------------------------------------

        $dynamicTitle = $this->makeDynamicTitle($viewType, $studentLevel, $semester, $college, $program);

        // When no college is selected, group by college; otherwise group by program.
        $groupField = $college === 'All' ? 'college' : 'program_name';

        // --- Ranking & donut chart data ---------------------------------------

        // The ranking bar chart always shows all programs/colleges within the
        // active college/level/semester scope — it is NOT filtered by program —
        // so users can see where the selected program sits relative to its peers.
        $rankingSource = $this->normalizeRows(
            $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, 'All')->get()
        );

        $rankingData   = $this->buildGroupedCountsFromCollection($rankingSource, $groupField);
        $rankingRows   = collect($rankingData['rows']);
        $rankingLabels = $rankingData['labels'];
        $rankingValues = $rankingData['values'];

        // Assign a consistent color to each college/program label using the
        // college-specific palette so colors are stable as filters change.
        $palette       = $this->getCollegePalette($college);
        $programColors = [];

        foreach ($rankingLabels as $i => $programName) {
            $programColors[$programName] = $palette[$i % count($palette)];
        }

        // Donut chart shares the same data as the ranking chart.
        $donutRows     = $rankingRows;
        $donutTotal    = max(1, $donutRows->sum('total'));  // guard against division by zero
        $donutPercents = $donutRows->map(fn($r) => round(($r->total / $donutTotal) * 100, 1))->values()->all();

        // --- Major breakdown donut (program-level detail) --------------------

        // Only built when a specific program is selected and that program has
        // rows with a non-empty `program_major` value.
        $majorChart = null;

        if ($program !== 'All') {
            // Fetch rows scoped to the selected program.
            $majorSource = $this->normalizeRows(
                $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, $program)->get()
            );

            // Group by major, drop rows without a recorded major.
            $majorRows = $majorSource
                ->filter(fn($row) => !empty($row->program_major))
                ->groupBy('program_major')
                ->map(function ($items, $majorName) {
                    return (object) [
                        'major' => $majorName,
                        'total' => $items->count(),
                    ];
                })
                ->sortByDesc('total')
                ->values();

            // Only add the chart to the payload if there is at least one major.
            if ($majorRows->isNotEmpty()) {
                $majorTotal  = max(1, $majorRows->sum('total'));
                $majorLabels = $majorRows->pluck('major')->all();
                $majorColors = [];

                foreach ($majorLabels as $i => $majorName) {
                    $majorColors[$majorName] = $palette[$i % count($palette)];
                }

                $majorChart = [
                    'title'          => $this->makeMajorDonutTitle($studentLevel, $program),
                    'labels'         => $majorLabels,
                    'values'         => $majorRows->pluck('total')->map(fn($v) => (int) $v)->all(),
                    'percents'       => $majorRows->map(fn($r) => round(($r->total / $majorTotal) * 100, 1))->values()->all(),
                    'program_colors' => $majorColors,
                ];
            }
        }

        // --- Stacked sex-distribution chart ----------------------------------

        // When a program is selected, break down by major; otherwise break
        // down by the same groupField used for the ranking chart.
        $sexGroupField    = ($program !== 'All') ? 'program_major' : $groupField;
        $sexProgramFilter = ($program !== 'All') ? $program : 'All';

        $sexSource = $this->normalizeRows(
            $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, $sexProgramFilter)->get()
        );

        if ($program !== 'All') {
            // Break the selected program's graduates down by major and compute
            // male/female counts per major.
            $sexRows = $sexSource
                ->filter(fn($row) => !empty($row->program_major))
                ->groupBy('program_major')
                ->map(function ($items, $groupName) {
                    return (object) [
                        'group_name'   => $groupName,
                        'male_count'   => $items->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count(),
                        'female_count' => $items->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count(),
                        'total_count'  => $items->count(),
                    ];
                })
                ->sortBy('group_name')
                ->values();
        } else {
            // No program selected — reuse the grouped counts already computed
            // for the ranking chart and add a `total_count` alias for uniform access.
            $sexRows = collect($this->buildGroupedCountsFromCollection($sexSource, $groupField)['rows'])
                ->map(function ($row) {
                    $row->total_count = $row->total;
                    return $row;
                })
                ->sortBy('group_name')
                ->values();
        }

        // Fallback: if a program was selected but it has no recorded majors,
        // show the college-level sex distribution instead so the chart is not empty.
        if ($program !== 'All' && $sexRows->isEmpty()) {
            $fallbackSource = $this->normalizeRows(
                $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, 'All')->get()
            );

            $sexRows = collect($this->buildGroupedCountsFromCollection($fallbackSource, $groupField)['rows'])
                ->map(function ($row) {
                    $row->total_count = $row->total;
                    return $row;
                })
                ->sortBy('group_name')
                ->values();
        }

        // Convert collection rows into parallel arrays for the charting library.
        $stackLabels      = [];
        $stackMalePct     = [];
        $stackFemalePct   = [];
        $stackMaleCount   = [];
        $stackFemaleCount = [];

        foreach ($sexRows as $row) {
            $total  = max(1, (int) $row->total_count); // guard against division by zero
            $male   = (int) $row->male_count;
            $female = (int) $row->female_count;

            $stackLabels[]      = $row->group_name;
            $stackMaleCount[]   = $male;
            $stackFemaleCount[] = $female;
            $stackMalePct[]     = round(($male / $total) * 100, 1);
            $stackFemalePct[]   = round(($female / $total) * 100, 1);
        }

        // --- Assemble and return the full payload ----------------------------

        return [
            'page_title_text'    => 'Graduates Overview',
            'dynamic_title'      => $dynamicTitle,
            'selected_view_type' => $viewType,

            // Value boxes differ between view types:
            //   - graduate_headcount  → simple integer totals
            //   - demographic_profile → male/female pair per level
            'value_boxes' => $viewType === 'graduate_headcount'
                ? [
                    ['title' => 'Total University Graduates',    'value' => $totalGraduates],
                    ['title' => 'Undergraduate Level Graduates', 'value' => $undergradCount],
                    ['title' => 'Postgraduate Level Graduates',  'value' => $postgradCount],
                ]
                : [
                    ['title' => 'Total University Graduates',    'value' => $totalGraduates],
                    ['title' => 'Undergraduate Level Graduates', 'value' => ['male' => $undergradMale, 'female' => $undergradFemale]],
                    ['title' => 'Postgraduate Level Graduates',  'value' => ['male' => $postgradMale, 'female' => $postgradFemale]],
                ],

            'pie_chart' => $this->makePieChart(
                $studentLevel,
                $maleCount,
                $femaleCount,
                $undergradMale,
                $undergradFemale,
                $postgradMale,
                $postgradFemale
            ),

            'donut_chart' => [
                'title'          => $this->makeDonutTitle($studentLevel, $college, $program),
                'labels'         => $donutRows->pluck('group_name')->all(),
                'values'         => $donutRows->pluck('total')->map(fn($v) => (int) $v)->all(),
                'percents'       => $donutPercents,
                'program_colors' => $programColors,
            ],

            // null when no program is selected or when the selected program
            // has no recorded majors — the view should handle this gracefully.
            'major_chart' => $majorChart,

            'ranking_chart' => [
                'title'          => 'Ranking of Graduates Count by ' . ($college === 'All' ? 'College' : 'Program'),
                'labels'         => $rankingLabels,
                'values'         => $rankingValues,
                'highlight'      => $program !== 'All' ? $program : null,  // highlights the active bar
                'y_axis_label'   => $college === 'All' ? 'Colleges' : 'Programs',
                'x_axis_label'   => 'Number of Graduates',
                'program_colors' => $programColors,
            ],

            'stacked_chart' => [
                'title'        => $this->makeStackedTitle(
                    $studentLevel,
                    $college,
                    $program,
                    // Pass true when we are actually showing major-level breakdown.
                    !empty($stackLabels) && $program !== 'All' && $sexGroupField === 'program_major'
                ),
                'labels'       => $stackLabels,
                'male_pct'     => $stackMalePct,
                'female_pct'   => $stackFemalePct,
                'male_count'   => $stackMaleCount,
                'female_count' => $stackFemaleCount,
                'y_axis_label' => $college === 'All'
                    ? 'College'
                    : ($program !== 'All' && !empty($stackLabels) ? 'Major' : 'Program'),
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Dynamic title helpers
    // -------------------------------------------------------------------------

    /**
     * Generates the descriptive headline shown above the main chart area.
     *
     * Only the 'graduate_headcount' view type produces a context-specific
     * title; the demographic profile view always uses a generic heading.
     *
     * @param  string  $viewType
     * @param  string  $studentLevel
     * @param  string  $semester
     * @param  string  $college
     * @param  string  $program
     * @return string
     */
    private function makeDynamicTitle(
        string $viewType,
        string $studentLevel,
        string $semester,
        string $college,
        string $program
    ): string {
        if ($viewType !== 'graduate_headcount') {
            return 'Graduates Overview';
        }

        $levelText    = $studentLevel === 'All' ? 'All Levels' : $studentLevel . ' Level';
        $semesterText = $semester === 'All' ? 'All Periods' : $semester;

        // Progressively more specific based on what the user has filtered.
        if ($program !== 'All') {
            return "Total Graduates: {$program} {$levelText} ({$semesterText})";
        }

        if ($college !== 'All') {
            return "Total Graduates: {$college} {$levelText} ({$semesterText})";
        }

        return "Total Graduates: {$levelText} ({$semesterText})";
    }

    /**
     * Generates the title for the main donut chart.
     *
     * @param  string  $studentLevel
     * @param  string  $college
     * @param  string  $program
     * @return string
     */
    private function makeDonutTitle(string $studentLevel, string $college, string $program): string
    {
        $levelText = $studentLevel === 'All' ? 'All Level' : $studentLevel . ' Level';

        if ($program !== 'All') {
            return "Percentage of {$program} {$levelText} Graduates";
        }

        if ($college !== 'All') {
            return "Percentage of {$college} {$levelText} Graduates";
        }

        return "Percentage of University Graduates by College";
    }

    /**
     * Generates the title for the major-breakdown donut chart shown when a
     * program with recorded majors is selected.
     *
     * @param  string  $studentLevel
     * @param  string  $program
     * @return string
     */
    private function makeMajorDonutTitle(string $studentLevel, string $program): string
    {
        $levelText = $studentLevel === 'All' ? 'All Level' : $studentLevel . ' Level';
        return "Percentage of {$program} {$levelText} Graduates by Major";
    }

    /**
     * Generates the title for the stacked sex-distribution chart.
     *
     * The title adapts based on which filters are active and whether the
     * chart is showing a major-level breakdown.
     *
     * @param  string  $studentLevel
     * @param  string  $college
     * @param  string  $program
     * @param  bool    $hasMajors     True when the chart is grouped by major.
     * @return string
     */
    private function makeStackedTitle(
        string $studentLevel,
        string $college,
        string $program,
        bool $hasMajors = false
    ): string {
        // University-wide views (no college filter applied).
        if ($college === 'All' && $studentLevel === 'All') {
            return 'Total University Graduates Sex Distribution';
        }

        if ($college === 'All' && $studentLevel === 'Undergraduate') {
            return 'Total Undergraduate Level Graduates Sex Distribution';
        }

        if ($college === 'All' && $studentLevel === 'Postgraduate') {
            return 'Total Postgraduate Level Graduates Sex Distribution';
        }

        $level = $studentLevel === 'All' ? 'All Level' : $studentLevel . ' Level';

        // College selected, no program drill-down.
        if ($college !== 'All' && $program === 'All') {
            return "{$level} Graduates Sex Distribution of {$college}";
        }

        // Program selected and the chart is broken down further by major.
        if ($program !== 'All' && $hasMajors) {
            return "{$level} Graduates Sex Distribution of {$program} by Major";
        }

        return "{$level} Graduates Sex Distribution of {$program}";
    }

    // -------------------------------------------------------------------------
    // Pie chart builder
    // -------------------------------------------------------------------------

    /**
     * Builds the pie chart data array for the Demographic Profile view.
     *
     * The chart always shows male vs. female proportions, but the data set
     * and title change depending on the active student-level filter:
     *   - 'All'          → combined male/female across both levels
     *   - 'Undergraduate'→ undergraduate male/female only
     *   - 'Postgraduate' → postgraduate male/female only
     *
     * @param  string  $studentLevel
     * @param  int     $maleCount        Total males (all levels).
     * @param  int     $femaleCount      Total females (all levels).
     * @param  int     $undergradMale
     * @param  int     $undergradFemale
     * @param  int     $postgradMale
     * @param  int     $postgradFemale
     * @return array{title: string, labels: string[], values: int[]}
     */
    private function makePieChart(
        string $studentLevel,
        int $maleCount,
        int $femaleCount,
        int $undergradMale,
        int $undergradFemale,
        int $postgradMale,
        int $postgradFemale
    ): array {
        switch ($studentLevel) {
            case 'Undergraduate':
                return [
                    'title'  => 'Percentage of Undergraduate Level Graduates by Sex',
                    'labels' => ['Male', 'Female'],
                    'values' => [$undergradMale, $undergradFemale],
                ];
            case 'Postgraduate':
                return [
                    'title'  => 'Percentage of Postgraduate Level Graduates by Sex',
                    'labels' => ['Male', 'Female'],
                    'values' => [$postgradMale, $postgradFemale],
                ];
            default: // 'All'
                return [
                    'title'  => 'Percentage of All Graduates by Sex',
                    'labels' => ['Male', 'Female'],
                    'values' => [$maleCount, $femaleCount],
                ];
        }
    }

    // -------------------------------------------------------------------------
    // Color palettes
    // -------------------------------------------------------------------------

    /**
     * Returns the ordered color palette for charts scoped to a given college.
     *
     * Each college has a distinct palette derived from its institutional
     * branding colors. Colors are applied cyclically to chart segments so
     * that related shades are used when a college has many programs.
     *
     * Falls back to a small multi-college palette when no college is selected
     * or when the college is unrecognized.
     *
     * @param  string  $college   college name, or 'All'.
     * @return string[]          Ordered array of hex color strings.
     */
    private function getCollegePalette(string $college): array
    {
        $palettes = [
            'College of Agriculture (CAG)' => [
                '#016531',
                '#0B7A3A',
                '#169042',
                '#2AA857',
                '#4CC276',
                '#7DDBA3',
                '#A8E8C3',
                '#CFF4E0',
            ],

            'College of Arts and Social Sciences (CASS)' => [
                '#6D430F',
                '#9E6E28',
                '#CF9D43',
                '#FFD05F',
                '#9E6E28',
                '#CF9D43'
            ],

            'College of Business and Accountancy (CBA)' => [
                '#084E7C',
                '#0A6DAF',
                '#0097D1',
                '#00B3E6',
                '#00C2EC',
                '#33D6F3',
                '#66E6FA'
            ],

            'College of Education (CED)' => [
                '#B29A00',
                '#CBB223',
                '#E5CB3A',
                '#FFE450',
                '#FFEB73',
                '#FFF199',
            ],

            'College of Engineering (CEN)' => [
                '#86090A',
                '#B04B33',
                '#D87F62',
                '#FFB495',
                '#74C8F7'
            ],

            'College of Fisheries (COF)' => [
                '#B82C2C',
                '#1C82C7',
                '#2E97DF',
                '#4DB1F0',
                '#74C8F7'
            ],

            'College of Home Sciences and Industry (CHSI)' => [
                '#A70062',
                '#C74993',
                '#E479C5',
                '#FFA8F7',
                '#FFC4FB',
            ],

            'College of Science (COS)' => [
                '#008080',
                '#00A9A9',
                '#00D3D3',
                '#39EDFF',
                '#00FFFF',
                '#66FFFF',
                '#99FFFF'
            ],

            'College of Veterinary Science and Medicine (CVSM)' => [
                '#797979',
                '#1C82C7',
                '#2E97DF',
                '#4DB1F0',
                '#74C8F7'
            ],

            // Graduate school colleges share the same green palette.
            'Graduate School - Masters' => [
                '#016531',
                '#0B7A3A',
                '#169042',
                '#2AA857',
                '#4CC276',
                '#7DDBA3',
                '#A8E8C3',
                '#CFF4E0',
            ],

            'DOT-UNI' => [
                '#016531',
                '#0B7A3A',
                '#169042',
                '#2AA857',
                '#4CC276',
                '#7DDBA3',
                '#A8E8C3',
                '#CFF4E0',
            ],

            'Graduate School - Doctoral' => [
                '#016531',
                '#0B7A3A',
                '#169042',
                '#2AA857',
                '#4CC276',
                '#7DDBA3',
                '#A8E8C3',
                '#CFF4E0',
            ],
        ];

        // Multi-college fallback palette (used when $college === 'All' or is unrecognised).
        return $palettes[$college] ?? [
            '#016531',
            '#86090A',
            '#B29A00',
            '#6D430F',
            '#0A6DAF'
        ];
    }

    // -------------------------------------------------------------------------
    // Name normalization helpers
    // -------------------------------------------------------------------------

    /**
     * Normalizes a raw college name from the database to its  form.
     *
     * Handles known historical/typo variants stored in the DB so that the UI
     * always displays consistent, human-readable college names.
     *
     * @param  string|null  $college  Raw value from the `graduates.college` column.
     * @return string|null            college name, or null if input is null.
     */
    private function normalizeCollegeName(?string $college): ?string
    {
        if ($college === null) {
            return null;
        }

        // Collapse multiple spaces and trim whitespace before comparing.
        $college = trim(preg_replace('/\s+/', ' ', $college));
        $key     = strtolower($college);

        // Map of known legacy/misspelled names →  names.
        $map = [
            'college of business administration (cba)'   => 'College of Business and Accountancy (CBA)',
            'college of human sciences and industry (chsi)' => 'College of Home Sciences and Industry (CHSI)',
        ];

        return $map[$key] ?? $college;
    }

    /**
     * Returns every raw `college` column value that maps to the given
     *  college name.
     *
     * Used with `whereIn()` so that filtering by a  name catches
     * all spelling variants stored in the database.
     *
     * @param  string  $college  college name.
     * @return string[]          Raw database values to match against.
     */
    private function getCollegeAliases(string $college): array
    {
        $normalized = $this->normalizeCollegeName($college);

        // Each key is a  name; each value is the list of raw DB
        // strings that should be treated as equivalent.
        $aliases = [
            'College of Business and Accountancy (CBA)' => [
                'College of BUsiness And Accountancy',
                'College of Business And Accountancy',
                'College of Business Administration',
                'College of Business Admininstration',
                'College of Business Administration (CBA)',
            ],
            'College of Home Sciences and Industry (CHSI)' => [
                'College of Home Science and Industry',
                'College of Human Science and Industry',
                'College of Human Sciences and Industry',
                'College of Human Sciences and Industry (CHSI)',
            ],
        ];

        // If no aliases are defined, the  name itself is the only match.
        return $aliases[$normalized] ?? [$college];
    }

    /**
     * Splits a program name string into one or more  program names.
     *
     * Some DB rows store combined program names such as
     * "BS Agriculture / Certificate in Agricultural Science". This method
     * detects that pattern and returns both programs separately so they can
     * each appear as their own item in dropdowns and charts.
     *
     * @param  string|null  $programName
     * @return string[]  One or two  program name strings.
     */
    private function splitProgramNames(?string $programName): array
    {
        if ($programName === null) {
            return [];
        }

        $programName = trim(preg_replace('/\s+/', ' ', $programName));

        if ($programName === '') {
            return [];
        }

        // Detect the combined Agriculture + Certificate pattern and split it.
        if ($this->isCombinedAgriCertificateProgram($programName)) {
            return [
                'BS Agriculture',
                'Certificate in Agricultural Science',
            ];
        }

        // All other programs normalize to a single  name.
        return [$this->normalizeProgramName($programName)];
    }

    /**
     * Applies college/program/major normalization to every row in a result set.
     *
     * Wraps a raw DB result (array of stdClass objects) in a Collection and
     * rewrites the name fields on each row using the normalization helpers,
     * ensuring chart labels and filter comparisons always use  values.
     *
     * @param  iterable  $rows  Raw rows from a DB query.
     * @return \Illuminate\Support\Collection
     */
    private function normalizeRows($rows)
    {
        return collect($rows)->map(function ($row) {
            $row->college      = $this->normalizeCollegeName($row->college);
            $row->program_name = $this->normalizeProgramName($row->program_name);
            $row->program_major = $this->normalizeProgramMajor($row->program_major);

            return $row;
        });
    }

    /**
     * Groups a normalized row Collection by a given field and computes
     * total, male, and female counts per group.
     *
     * When grouping by `program_name`, each row is first passed through
     * splitProgramNames() so that combined-program rows are counted under
     * both programs they represent.
     *
     * Results are sorted descending by total count (highest first), which
     * is the natural order for the ranking bar chart.
     *
     * @param  \Illuminate\Support\Collection  $rows     Normalized row collection.
     * @param  string                          $groupBy  Field name to group on.
     * @return array{
     *     labels: string[],
     *     values: int[],
     *     rows: \Illuminate\Support\Collection
     * }
     */
    private function buildGroupedCountsFromCollection($rows, string $groupBy): array
    {
        // Expand combined-program rows into individual records before grouping.
        $expanded = collect();

        foreach ($rows as $row) {
            if ($groupBy === 'program_name') {
                // splitProgramNames may produce 1 or 2  names per row.
                foreach ($this->splitProgramNames($row->program_name) as $program) {
                    $expanded->push((object) [
                        'group_name' => $program,
                        'gender'     => $row->gender,
                    ]);
                }
            } else {
                $value = $row->{$groupBy} ?? null;

                // Skip rows with null or empty group values to avoid a blank segment.
                if ($value !== null && $value !== '') {
                    $expanded->push((object) [
                        'group_name' => $value,
                        'gender'     => $row->gender,
                    ]);
                }
            }
        }

        // Aggregate counts per group.
        $grouped = $expanded
            ->groupBy('group_name')
            ->map(function ($items, $groupName) {
                return (object) [
                    'group_name'   => $groupName,
                    'total'        => $items->count(),
                    'male_count'   => $items->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count(),
                    'female_count' => $items->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return [
            'labels' => $grouped->pluck('group_name')->all(),
            'values' => $grouped->pluck('total')->map(fn($v) => (int) $v)->all(),
            'rows'   => $grouped,
        ];
    }

    /**
     * Normalizes a raw program name.
     *
     * Performs two transformation steps:
     *  1. Strips known trailing suffixes that encode the delivery channel
     *     (e.g. "(DOT-Uni)", "(GS-Masters)") — these are UI-irrelevant metadata.
     *  2. Maps known aliases and abbreviations to a single  label
     *     (e.g. "BS Agriculture" → "BS Agriculture",
     *            "Bachelor of Science in Agriculture" → "BS Agriculture").
     *
     * Combined programs (agriculture + certificate) are collapsed to a single
     * slash-delimited  string; use splitProgramNames() to re-expand them.
     *
     * @param  string|null  $programName  Raw value from the `graduates.program_name` column.
     * @return string|null                program name, or null for empty/null input.
     */
    private function normalizeProgramName(?string $programName): ?string
    {
        if ($programName === null) {
            return null;
        }

        $programName = trim(preg_replace('/\s+/', ' ', $programName));

        if ($programName === '') {
            return null;
        }

        // Strip delivery-channel suffixes appended to some program names.
        $programName = preg_replace(
            '/\s*\((DOT-Uni|DOT UNI|GS-Masters|GS-Doctoral)\)\s*$/i',
            '',
            $programName
        );

        $programName = trim(preg_replace('/\s+/', ' ', $programName));

        if ($programName === '') {
            return null;
        }

        // Combined agri+certificate programs get their own  representation.
        if ($this->isCombinedAgriCertificateProgram($programName)) {
            return 'BS Agriculture / Certificate in Agricultural Science';
        }

        $key = strtolower($programName);

        // Alias map: lowercase raw value →  display name.
        $map = [
            'bachelor of science in agriculture'          => 'BS Agriculture',
            'bs agriculture'                              => 'BS Agriculture',

            'certificate in agricultural science'         => 'Certificate in Agricultural Science',

            'bachelor of science in business administration' => 'BS Business Administration',
            'bs business administration'                  => 'BS Business Administration',

            'bachelor of science in management accounting' => 'BS Management Accounting',
            'bs management accounting'                    => 'BS Management Accounting',

            'bachelor of science in entrepreneurship'     => 'BS Entrepreneurship',
            'bs entrepreneurship'                         => 'BS Entrepreneurship',

            'bachelor of science in accountancy'          => 'BS Accountancy',
            'bs accountancy'                              => 'BS Accountancy',

            'master of science in education'              => 'Master of Science in Education',
            'ms education'                                => 'Master of Science in Education',

            'doctor of philosophy in development education' => 'Doctor of Philosophy in Development Education',
            'phd development education'                   => 'Doctor of Philosophy in Development Education',

            'master of business administration'           => 'Master of Business Administration',
            'mba'                                         => 'Master of Business Administration',

            'master in environmental management'          => 'Master in Environmental Management',

            'master of science in rural development'      => 'Master of Science in Rural Development',
            'ms rural development'                        => 'Master of Science in Rural Development',

            'certificate in teaching'                     => 'Certificate in Teaching',
        ];

        return $map[$key] ?? $programName;
    }

    /**
     * Returns true when a program name string represents the combined
     * "BS Agriculture / Certificate in Agricultural Science" record.
     *
     * Some historical DB entries bundled both programs into a single row.
     * This predicate is used by normalizeProgramName() and splitProgramNames()
     * to detect and handle that special case consistently.
     *
     * @param  string|null  $programName
     * @return bool
     */
    private function isCombinedAgriCertificateProgram(?string $programName): bool
    {
        if ($programName === null) {
            return false;
        }

        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $programName)));

        $hasAgriculture =
            str_contains($normalized, 'bachelor of science in agriculture') ||
            str_contains($normalized, 'bs agriculture');

        $hasCertificate = str_contains($normalized, 'certificate in agricultural science');

        return $hasAgriculture && $hasCertificate;
    }

    /**
     * Returns every raw `program_name` column value that maps to the given
     *  program name.
     *
     * Used with `whereIn()` to capture all spelling/abbreviation variants
     * of a program stored across different academic years or data imports.
     *
     * @param  string  $program  program name.
     * @return string[]          Raw database values to match against.
     */
    private function getProgramAliases(string $program): array
    {
        $program = $this->normalizeProgramName($program);

        // Combined agri+certificate programs overlap intentionally — both
        //  names reference the same raw combined DB rows.
        $aliases = [
            'BS Agriculture' => [
                'BS Agriculture',
                'Bachelor of Science in Agriculture',
                'BS Agriculture / Certificate in Agricultural Science',
                'BS Agriculture/Certificate in Agricultural Science',
                'Bachelor of Science in Agriculture / Certificate in Agricultural Science',
            ],

            'Certificate in Agricultural Science' => [
                'Certificate in Agricultural Science',
                'BS Agriculture / Certificate in Agricultural Science',
                'BS Agriculture/Certificate in Agricultural Science',
                'Bachelor of Science in Agriculture / Certificate in Agricultural Science',
            ],

            'BS Business Administration' => [
                'BS Business Administration',
                'Bachelor of Science in Business Administration',
            ],

            'BS Management Accounting' => [
                'BS Management Accounting',
                'Bachelor of Science in Management Accounting',
            ],

            'BS Entrepreneurship' => [
                'BS Entrepreneurship',
                'Bachelor of Science in Entrepreneurship',
            ],

            'BS Accountancy' => [
                'BS Accountancy',
                'Bachelor of Science in Accountancy',
            ],

            // DOT-Uni / DOT UNI variants are stripped by normalizeProgramName()
            // but are retained here as a safety net for any direct DB value
            // comparisons that bypass the normalization layer.
            'Master of Science in Education' => [
                'Master of Science in Education',
                'Master of Science in Education (DOT-Uni)',
                'Master of Science in Education (DOT UNI)',
                'MS Education',
            ],

            'Doctor of Philosophy in Development Education' => [
                'Doctor of Philosophy in Development Education',
                'Doctor of Philosophy in Development Education (DOT-Uni)',
                'Doctor of Philosophy in Development Education (DOT UNI)',
                'PhD Development Education',
            ],

            'Master of Business Administration' => [
                'Master of Business Administration',
                'Master of Business Administration (DOT-Uni)',
                'Master of Business Administration (DOT UNI)',
                'MBA',
            ],

            'Master in Environmental Management' => [
                'Master in Environmental Management',
                'Master in Environmental Management (DOT-Uni)',
                'Master in Environmental Management (DOT UNI)',
            ],

            'Master of Science in Rural Development' => [
                'Master of Science in Rural Development',
                'Master of Science in Rural Development (DOT-Uni)',
                'Master of Science in Rural Development (DOT UNI)',
                'MS Rural Development',
            ],

            'Certificate in Teaching' => [
                'Certificate in Teaching',
                'Certificate in Teaching (DOT-Uni)',
                'Certificate in Teaching (DOT UNI)',
            ],

            // Minor spelling variation found in the DB.
            'Systems Development' => [
                'Systems Development',
                'System Development',
            ],
        ];

        return $aliases[$program] ?? [$program];
    }

    /**
     * Normalizes a raw program major.
     *
     * Corrects known typos and abbreviation inconsistencies stored in the
     * `graduates.program_major` column.
     *
     * @param  string|null  $major  Raw value from the DB column.
     * @return string|null          major name, or null for empty/null input.
     */
    private function normalizeProgramMajor(?string $major): ?string
    {
        if ($major === null) {
            return null;
        }

        $major = trim(preg_replace('/\s+/', ' ', $major));

        if ($major === '') {
            return null;
        }

        $key = strtolower($major);

        $map = [
            'system development'      => 'Systems Development',  // missing plural 's'
            'systems development'     => 'Systems Development',

            'network administration'  => 'Network Administration',
            'networking administration' => 'Network Administration', // legacy variant
        ];

        return $map[$key] ?? $major;
    }
}
