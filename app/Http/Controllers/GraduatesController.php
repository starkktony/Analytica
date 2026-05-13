<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GraduatesController extends Controller
{
    public function index(Request $request): View
    {
        $viewType     = $request->query('view_type', 'graduate_headcount');
        $studentLevel = $request->query('student_level', 'All');
        $semester     = $request->query('semester', 'All');
        $college      = $request->query('college', 'All');
        $program      = $request->query('program', 'All');

        $filterOptions = $this->getFilterOptions($college, $studentLevel, $semester);

        // Force program to All when college is All
        if ($college === 'All') {
            $program = 'All';
        }

        // If selected program is not in the allowed program list for the selected college, reset it
        if ($program !== 'All' && !in_array($program, $filterOptions['programs'], true)) {
            $program = 'All';
        }

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
        return view('graduates', array_merge($payload, [
            'active_page'      => 'graduates',
            'view_type'        => $viewType,
            'selected_view_type' => $viewType,
            'student_level'    => $studentLevel,
            'semester'         => $semester,
            'selected_college' => $college,
            'selected_program' => $program,
            'colleges'         => $filterOptions['colleges'],
            'programs'         => $filterOptions['programs'],
            'semesters'        => $filterOptions['semesters'],
        ]));
    }

    public function filters(Request $request): JsonResponse
    {
        $college      = $request->query('college', 'All');
        $studentLevel = $request->query('student_level', 'All');
        $semester     = $request->query('semester', 'All');

        return response()->json(
            $this->getFilterOptions($college, $studentLevel, $semester)
        );
    }

    public function dashboard(Request $request): JsonResponse
    {
        $viewType     = $request->query('view_type', 'graduate_headcount');
        $studentLevel = $request->query('student_level', 'All');
        $semester     = $request->query('semester', 'All');
        $college      = $request->query('college', 'All');
        $program      = $request->query('program', 'All');

        $filterOptions = $this->getFilterOptions($college, $studentLevel, $semester);

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

    private function getBaseQuery()
    {
        return DB::table('graduates')
            ->select([
                'student_id',
                'gender',
                'college',
                'program_name',
                'program_major',
                'date_graduated',
                DB::raw("
                    CASE
                        WHEN MONTH(date_graduated) = 2 THEN 'Midyear'
                        ELSE 'Annual'
                    END as derived_semester
                "),
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

    private function applyFilters($query, string $studentLevel, string $semester, string $college, string $program)
    {
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

        if ($semester !== 'All') {
            $query->whereRaw("
                CASE
                    WHEN MONTH(date_graduated) = 2 THEN 'Midyear'
                    ELSE 'Annual'
                END = ?
            ", [$semester]);
        }

        if ($college !== 'All') {
            $query->whereIn('college', $this->getCollegeAliases($college));
        }

        if ($program !== 'All') {
            $query->whereIn('program_name', $this->getProgramAliases($program));
        }

        return $query;
    }

    private function getFilterOptions(string $college = 'All', string $studentLevel = 'All', string $semester = 'All'): array
    {
        $rawColleges = DB::table('graduates')
            ->whereNotNull('college')
            ->where('college', '!=', '')
            ->pluck('college');

        $colleges = $rawColleges
            ->map(fn($c) => $this->normalizeCollegeName($c))
            ->unique()
            ->sort()
            ->values()
            ->all();

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

        $programQuery = DB::table('graduates')
            ->whereNotNull('date_graduated')
            ->whereNotNull('program_name')
            ->where('program_name', '!=', '')
            ->whereIn('college', $this->getCollegeAliases($college));

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

        if ($semester !== 'All') {
            $programQuery->whereRaw("
                CASE
                    WHEN MONTH(date_graduated) = 2 THEN 'Midyear'
                    ELSE 'Annual'
                END = ?
            ", [$semester]);
        }

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

    private function buildDashboardData(
        string $viewType,
        string $studentLevel,
        string $semester,
        string $college,
        string $program
    ): array {
        // Safety: never allow specific program while college = All
        if ($college === 'All') {
            $program = 'All';
        }

        $query       = $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, $program);
        $allFiltered = $this->normalizeRows((clone $query)->get());

        $totalGraduates = $allFiltered->count();
        $maleCount      = $allFiltered->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count();
        $femaleCount    = $allFiltered->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count();

        $undergradCount  = $allFiltered->where('derived_student_level', 'Undergraduate')->count();
        $postgradCount   = $allFiltered->where('derived_student_level', 'Postgraduate')->count();

        $undergradMale   = $allFiltered->where('derived_student_level', 'Undergraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count();
        $undergradFemale = $allFiltered->where('derived_student_level', 'Undergraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count();
        $postgradMale    = $allFiltered->where('derived_student_level', 'Postgraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count();
        $postgradFemale  = $allFiltered->where('derived_student_level', 'Postgraduate')
            ->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count();

        $dynamicTitle = $this->makeDynamicTitle($viewType, $studentLevel, $semester, $college, $program);
        $groupField   = $college === 'All' ? 'college' : 'program_name';

        $rankingSource = $this->normalizeRows(
            $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, 'All')->get()
        );

        // Ranking rows (always ungrouped by program for the ranking bar)
        $rankingData   = $this->buildGroupedCountsFromCollection($rankingSource, $groupField);
        $rankingRows   = collect($rankingData['rows']);
        $rankingLabels = $rankingData['labels'];
        $rankingValues = $rankingData['values'];

        $palette = $this->getCollegePalette($college);
        $programColors = [];

        foreach ($rankingLabels as $i => $programName) {
            $programColors[$programName] = $palette[$i % count($palette)];
        }

        $donutRows     = $rankingRows;
        $donutTotal    = max(1, $donutRows->sum('total'));
        $donutPercents = $donutRows->map(fn($r) => round(($r->total / $donutTotal) * 100, 1))->values()->all();

        // Major chart
        $majorChart = null;

        if ($program !== 'All') {
            $majorSource = $this->normalizeRows(
                $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, $program)->get()
            );

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

            if ($majorRows->isNotEmpty()) {
                $majorTotal = max(1, $majorRows->sum('total'));

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

        // Stacked sex distribution
        $sexGroupField    = ($program !== 'All') ? 'program_major' : $groupField;
        $sexProgramFilter = ($program !== 'All') ? $program : 'All';

        $sexSource = $this->normalizeRows(
            $this->applyFilters($this->getBaseQuery(), $studentLevel, $semester, $college, $sexProgramFilter)->get()
        );

        if ($program !== 'All') {
            $sexRows = $sexSource
                ->filter(fn($row) => !empty($row->program_major))
                ->groupBy('program_major')
                ->map(function ($items, $groupName) {
                    return (object) [
                        'group_name'    => $groupName,
                        'male_count'    => $items->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count(),
                        'female_count'  => $items->filter(fn($r) => in_array(strtolower($r->gender), ['female', 'f']))->count(),
                        'total_count'   => $items->count(),
                    ];
                })
                ->sortBy('group_name')
                ->values();
        } else {
            $sexRows = collect($this->buildGroupedCountsFromCollection($sexSource, $groupField)['rows'])
                ->map(function ($row) {
                    $row->total_count = $row->total;
                    return $row;
                })
                ->sortBy('group_name')
                ->values();
        }

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
        $stackLabels      = [];
        $stackMalePct     = [];
        $stackFemalePct   = [];
        $stackMaleCount   = [];
        $stackFemaleCount = [];

        foreach ($sexRows as $row) {
            $total    = max(1, (int) $row->total_count);
            $male     = (int) $row->male_count;
            $female   = (int) $row->female_count;

            $stackLabels[]      = $row->group_name;
            $stackMaleCount[]   = $male;
            $stackFemaleCount[] = $female;
            $stackMalePct[]     = round(($male / $total) * 100, 1);
            $stackFemalePct[]   = round(($female / $total) * 100, 1);
        }

        return [
            'page_title_text'    => 'Graduates Overview',
            'dynamic_title'      => $dynamicTitle,
            'selected_view_type' => $viewType,

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

            'major_chart' => $majorChart,

            'ranking_chart' => [
                'title'          => 'Ranking of Graduates Count by ' . ($college === 'All' ? 'College' : 'Program'),
                'labels'         => $rankingLabels,
                'values'         => $rankingValues,
                'highlight'      => $program !== 'All' ? $program : null,
                'y_axis_label'   => $college === 'All' ? 'Colleges' : 'Programs',
                'x_axis_label'   => 'Number of Graduates',
                'program_colors' => $programColors,
            ],

            'stacked_chart' => [
                'title'        => $this->makeStackedTitle(
                    $studentLevel,
                    $college,
                    $program,
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

        if ($program !== 'All') {
            return "Total Graduates: {$program} {$levelText} ({$semesterText})";
        }

        if ($college !== 'All') {
            return "Total Graduates: {$college} {$levelText} ({$semesterText})";
        }

        return "Total Graduates: {$levelText} ({$semesterText})";
    }

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
     * Title for the major breakdown donut shown when a program is selected
     * and that program has recorded majors.
     */
    private function makeMajorDonutTitle(string $studentLevel, string $program): string
    {
        $levelText = $studentLevel === 'All' ? 'All Level' : $studentLevel . ' Level';
        return "Percentage of {$program} {$levelText} Graduates by Major";
    }

    private function makeStackedTitle(
        string $studentLevel,
        string $college,
        string $program,
        bool $hasMajors = false
    ): string {
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

        if ($college !== 'All' && $program === 'All') {
            return "{$level} Graduates Sex Distribution of {$college}";
        }

        // Program selected with majors → show "by Major"
        if ($program !== 'All' && $hasMajors) {
            return "{$level} Graduates Sex Distribution of {$program} by Major";
        }

        return "{$level} Graduates Sex Distribution of {$program}";
    }

    /**
     * Build the pie chart data for the Demographic Profile view.
     * Respects the selected student level filter:
     *   - All          → total male / female across all levels
     *   - Undergraduate → undergraduate male / female only
     *   - Postgraduate  → postgraduate male / female only
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

        return $palettes[$college] ?? [
            '#016531',
            '#86090A',
            '#B29A00',
            '#6D430F',
            '#0A6DAF'
        ];
    }

    private function normalizeCollegeName(?string $college): ?string
    {
        if ($college === null) {
            return null;
        }

        $college = trim(preg_replace('/\s+/', ' ', $college));
        $key = strtolower($college);

        $map = [
            'college of business administration (cba)' => 'College of Business and Accountancy (CBA)',

            'college of human sciences and industry (chsi)' => 'College of Home Sciences and Industry (CHSI)',
        ];

        return $map[$key] ?? $college;
    }

    private function getCollegeAliases(string $college): array
    {
        $normalized = $this->normalizeCollegeName($college);

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

        return $aliases[$normalized] ?? [$college];
    }

    private function splitProgramNames(?string $programName): array
    {
        if ($programName === null) {
            return [];
        }

        $programName = trim(preg_replace('/\s+/', ' ', $programName));

        if ($programName === '') {
            return [];
        }

        if ($this->isCombinedAgriCertificateProgram($programName)) {
            return [
                'BS Agriculture',
                'Certificate in Agricultural Science',
            ];
        }

        return [$this->normalizeProgramName($programName)];
    }
    private function normalizeRows($rows)
    {
        return collect($rows)->map(function ($row) {
            $row->college = $this->normalizeCollegeName($row->college);
            $row->program_name = $this->normalizeProgramName($row->program_name);
            $row->program_major = $this->normalizeProgramMajor($row->program_major);

            return $row;
        });
    }

    private function buildGroupedCountsFromCollection($rows, string $groupBy): array
    {
        $expanded = collect();

        foreach ($rows as $row) {
            if ($groupBy === 'program_name') {
                foreach ($this->splitProgramNames($row->program_name) as $program) {
                    $expanded->push((object) [
                        'group_name' => $program,
                        'gender' => $row->gender,
                    ]);
                }
            } else {
                $value = $row->{$groupBy} ?? null;

                if ($value !== null && $value !== '') {
                    $expanded->push((object) [
                        'group_name' => $value,
                        'gender' => $row->gender,
                    ]);
                }
            }
        }

        $grouped = $expanded
            ->groupBy('group_name')
            ->map(function ($items, $groupName) {
                return (object) [
                    'group_name' => $groupName,
                    'total' => $items->count(),
                    'male_count' => $items->filter(fn($r) => in_array(strtolower($r->gender), ['male', 'm']))->count(),
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

    private function normalizeProgramName(?string $programName): ?string
    {
        if ($programName === null) {
            return null;
        }

        $programName = trim(preg_replace('/\s+/', ' ', $programName));

        if ($programName === '') {
            return null;
        }

        // Remove trailing suffixes like:
        // (DOT-Uni), (DOT UNI), (GS-Masters), (GS-Doctoral)
        $programName = preg_replace(
            '/\s*\((DOT-Uni|DOT UNI|GS-Masters|GS-Doctoral)\)\s*$/i',
            '',
            $programName
        );

        $programName = trim(preg_replace('/\s+/', ' ', $programName));

        if ($programName === '') {
            return null;
        }

        if ($this->isCombinedAgriCertificateProgram($programName)) {
            return 'BS Agriculture / Certificate in Agricultural Science';
        }

        $key = strtolower($programName);

        $map = [
            'bachelor of science in agriculture' => 'BS Agriculture',
            'bs agriculture' => 'BS Agriculture',

            'certificate in agricultural science' => 'Certificate in Agricultural Science',

            'bachelor of science in business administration' => 'BS Business Administration',
            'bs business administration' => 'BS Business Administration',

            'bachelor of science in management accounting' => 'BS Management Accounting',
            'bs management accounting' => 'BS Management Accounting',

            'bachelor of science in entrepreneurship' => 'BS Entrepreneurship',
            'bs entrepreneurship' => 'BS Entrepreneurship',

            'bachelor of science in accountancy' => 'BS Accountancy',
            'bs accountancy' => 'BS Accountancy',

            'master of science in education' => 'Master of Science in Education',
            'ms education' => 'Master of Science in Education',

            'doctor of philosophy in development education' => 'Doctor of Philosophy in Development Education',
            'phd development education' => 'Doctor of Philosophy in Development Education',

            'master of business administration' => 'Master of Business Administration',
            'mba' => 'Master of Business Administration',

            'master in environmental management' => 'Master in Environmental Management',

            'master of science in rural development' => 'Master of Science in Rural Development',
            'ms rural development' => 'Master of Science in Rural Development',

            'certificate in teaching' => 'Certificate in Teaching',
        ];

        return $map[$key] ?? $programName;
    }
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
    private function getProgramAliases(string $program): array
    {
        $program = $this->normalizeProgramName($program);

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

            'Systems Development' => [
                'Systems Development',
                'System Development'
            ]
        ];

        return $aliases[$program] ?? [$program];
    }

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
            'system development' => 'Systems Development',
            'systems development' => 'Systems Development',

            'network administration' => 'Network Administration',
            'networking administration' => 'Network Administration',
        ];

        return $map[$key] ?? $major;
    }
}
