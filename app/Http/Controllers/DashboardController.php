<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Ewms\FacultyProfile;
use App\Models\Ewms\Department;
use App\Models\Ewms\FacultyStatus;
use App\Models\Ewms\CollegeUnit;
use App\Models\Ewms\Semester;
use Illuminate\Http\Request;
use App\Models\Ewms\Summary;
use App\Models\Ewms\ClassSchedule;
use App\Models\Ewms\AssignmentInStudentRS;
use App\Models\Ewms\AssignmentInStudentRSRD;
use App\Models\Ewms\FacultyDesignations;
use App\Models\Ewms\Publication;
use App\Models\Ewms\RecognizedDesignations;

class DashboardController extends Controller
{
    // =========================================================================
    // FACULTY OVERVIEW PAGE
    // =========================================================================
public function facultyOverview(Request $request)
    {
        $activeSemester = Semester::where('status', 1)->orderBy('sem_id', 'desc')->first()
                       ?? Semester::orderBy('sem_id', 'desc')->first();

        $filters = [
            'semester'   => $request->get('semester',   $activeSemester->sem_id ?? null),
            'college'    => $request->get('college',    'all'),
            'department' => $request->get('department', 'all'),
        ];

        // 1. Active faculty
        $activeFaculty = DB::connection('ewms')
            ->table('table_faculty_profile as fp')
            ->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            })
            ->when($filters['college']    !== 'all', fn($q) => $q->where('fp.college',    $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->select('fp.id', 'fp.employeeID', 'fp.college', 'fp.department')
            ->get();

        $activeFacultyIds = $activeFaculty->pluck('id')->toArray();

        // 2. On-leave faculty
        $onLeaveFaculty = DB::connection('ewms')
            ->table('table_faculty_profile as fp')
            ->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'No');
            })
            ->when($filters['college']    !== 'all', fn($q) => $q->where('fp.college',    $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->select('fp.id', 'fp.employeeID')
            ->get();

        $onLeaveFacultyIds = $onLeaveFaculty->pluck('id')->toArray();

        $activeCount   = count($activeFacultyIds);
        $onLeaveCount  = count($onLeaveFacultyIds);
        $totalFaculty  = $activeCount + $onLeaveCount;
        $allFacultyIds = array_merge($activeFacultyIds, $onLeaveFacultyIds);

        // 3. PhD / Masters
        $phdHolders = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->whereIn('fad.f_id', $allFacultyIds)
            ->whereNotNull('fad.phd_degree_title')
            ->where('fad.phd_degree_title', '!=', '')
            ->where('fad.phd_degree_title', '!=', 'N/A')
            ->distinct()
            ->count('fad.f_id');

        $mastersHolders = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->whereIn('fad.f_id', $allFacultyIds)
            ->whereNotNull('fad.ms_degree_title')
            ->where('fad.ms_degree_title', '!=', '')
            ->where('fad.ms_degree_title', '!=', 'N/A')
            ->where(fn($q) => $q->whereNull('fad.phd_degree_title')
                                ->orWhere('fad.phd_degree_title', '')
                                ->orWhere('fad.phd_degree_title', 'N/A'))
            ->distinct()
            ->count('fad.f_id');

        // 4. Employment categories
        $categoryNames = [1 => 'Regular', 2 => 'Contractual', 3 => 'Part-Time', 4 => 'Temporary'];
        $categories = DB::connection('ewms')
            ->table('table_faculty_status as fs')
            ->join('table_faculty_profile as fp', 'fs.f_id', '=', 'fp.id')
            ->where('fs.sem_id',    $filters['semester'])
            ->where('fs.is_active', 'Yes')
            ->whereIn('fp.id', $activeFacultyIds)
            ->selectRaw('fs.category_of_faculty, COUNT(DISTINCT fp.id) as count')
            ->groupBy('fs.category_of_faculty')
            ->get()
            ->map(fn($item) => [
                'category' => $categoryNames[$item->category_of_faculty] ?? 'Other',
                'count'    => $item->count,
            ]);

        // 5. Faculty count ranking
        if ($filters['college'] !== 'all') {
            $rankingData = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function ($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id',    $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->whereIn('fp.id', $activeFacultyIds)
                ->where('fp.college', $filters['college'])
                ->select('d.department_acro', 'd.department_id', DB::raw('COUNT(DISTINCT fp.id) as total_faculty'))
                ->groupBy('d.department_id', 'd.department_acro')
                ->orderByDesc('total_faculty')
                ->get();

            if ($filters['department'] !== 'all') {
                $deptInfo = DB::connection('ewms')
                    ->table('table_department')
                    ->where('department_id', $filters['department'])
                    ->first();
                $filters['department_acro'] = $deptInfo->department_acro ?? '';
            }
        } else {
            $allowedColleges = ['CED', 'COS', 'CASS', 'CEN', 'CAG', 'CHSI', 'CVSM', 'CBA', 'CF'];

            $rankingData = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function ($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id',    $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
                ->whereIn('fp.id', $activeFacultyIds)
                ->whereIn('cu.college_acro', $allowedColleges)
                ->select('cu.college_acro as college', DB::raw('COUNT(DISTINCT fp.id) as total_faculty'))
                ->groupBy('cu.college_acro')
                ->having('total_faculty', '>', 0)
                ->orderByDesc('total_faculty')
                ->get();
        }

        // 7. Qualification by department (stacked bar)
        $phdByDepartment = $this->buildQualificationByDepartment($activeFacultyIds, $filters);

        // 8. Flat arrays for Blade JS
        $rankingLabels        = collect($rankingData)->pluck($filters['college'] !== 'all' ? 'department_acro' : 'college')->toArray();
        $rankingCounts        = collect($rankingData)->pluck('total_faculty')->toArray();
        $selectedDeptAcro     = $filters['department_acro'] ?? '';
        $qualLabels           = $phdByDepartment->pluck('department_acro')->toArray();
        $phdPercentages       = $phdByDepartment->pluck('phd_percentage')->toArray();
        $mastersPercentages   = $phdByDepartment->pluck('masters_percentage')->toArray();
        $bachelorsPercentages = $phdByDepartment->pluck('bachelors_percentage')->toArray();
        $phdCounts            = $phdByDepartment->pluck('phd_count')->toArray();
        $mastersCounts        = $phdByDepartment->pluck('masters_count')->toArray();
        $bachelorsCounts      = $phdByDepartment->pluck('bachelors_count')->toArray();

        // 9. Dropdown data
        $semesters   = Semester::orderBy('sem_id', 'desc')->get();
        $colleges    = CollegeUnit::orderBy('college_acro')->get();
        $departments = $filters['college'] !== 'all'
            ? Department::where('college_id', $filters['college'])->orderBy('department')->get()
            : Department::orderBy('department')->get();

        $collegeStats    = collect();
        $rankingData     = $rankingData     ?? collect();
        $phdByDepartment = $phdByDepartment ?? collect();

        return view('stzfaculty.overview', compact(
            'totalFaculty', 'activeCount', 'onLeaveCount', 'phdHolders', 'mastersHolders',
            'categories', 'collegeStats', 'rankingData', 'phdByDepartment', 'filters',
            'semesters', 'colleges', 'departments',
            'rankingLabels', 'rankingCounts', 'selectedDeptAcro',
            'qualLabels', 'phdPercentages', 'mastersPercentages', 'bachelorsPercentages',
            'phdCounts', 'mastersCounts', 'bachelorsCounts',
        ));
    }


    // =========================================================================
    // FACULTY OVERVIEW — AJAX ENDPOINT
    // =========================================================================
    public function facultyOverviewAjax(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Not an AJAX request'], 400);
        }

        $activeSemester = Semester::where('status', 1)->orderBy('sem_id', 'desc')->first()
                       ?? Semester::orderBy('sem_id', 'desc')->first();

        $filters = [
            'semester'   => $request->get('semester',   $activeSemester->sem_id ?? null),
            'college'    => $request->get('college',    'all'),
            'department' => $request->get('department', 'all'),
        ];

        // 1. Active faculty
        $activeFaculty = DB::connection('ewms')
            ->table('table_faculty_profile as fp')
            ->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            })
            ->when($filters['college']    !== 'all', fn($q) => $q->where('fp.college',    $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->select('fp.id', 'fp.employeeID', 'fp.college', 'fp.department')
            ->get();

        $activeFacultyIds = $activeFaculty->pluck('id')->toArray();

        // 2. On-leave faculty
        $onLeaveFaculty = DB::connection('ewms')
            ->table('table_faculty_profile as fp')
            ->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'No');
            })
            ->when($filters['college']    !== 'all', fn($q) => $q->where('fp.college',    $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->select('fp.id', 'fp.employeeID')
            ->get();

        $onLeaveFacultyIds = $onLeaveFaculty->pluck('id')->toArray();

        $activeCount   = count($activeFacultyIds);
        $onLeaveCount  = count($onLeaveFacultyIds);
        $totalFaculty  = $activeCount + $onLeaveCount;
        $allFacultyIds = array_merge($activeFacultyIds, $onLeaveFacultyIds);

        // 3. PhD / Masters
        $phdHolders = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->whereIn('fad.f_id', $allFacultyIds)
            ->whereNotNull('fad.phd_degree_title')
            ->where('fad.phd_degree_title', '!=', '')
            ->where('fad.phd_degree_title', '!=', 'N/A')
            ->distinct()
            ->count('fad.f_id');

        $mastersHolders = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->whereIn('fad.f_id', $allFacultyIds)
            ->whereNotNull('fad.ms_degree_title')
            ->where('fad.ms_degree_title', '!=', '')
            ->where('fad.ms_degree_title', '!=', 'N/A')
            ->where(fn($q) => $q->whereNull('fad.phd_degree_title')
                                ->orWhere('fad.phd_degree_title', '')
                                ->orWhere('fad.phd_degree_title', 'N/A'))
            ->distinct()
            ->count('fad.f_id');

        // 4. Employment categories
        $categoryNames = [1 => 'Regular', 2 => 'Contractual', 3 => 'Part-Time', 4 => 'Temporary'];
        $categories = DB::connection('ewms')
            ->table('table_faculty_status as fs')
            ->join('table_faculty_profile as fp', 'fs.f_id', '=', 'fp.id')
            ->where('fs.sem_id',    $filters['semester'])
            ->where('fs.is_active', 'Yes')
            ->whereIn('fp.id', $activeFacultyIds)
            ->selectRaw('fs.category_of_faculty, COUNT(DISTINCT fp.id) as count')
            ->groupBy('fs.category_of_faculty')
            ->get()
            ->map(fn($item) => [
                'category' => $categoryNames[$item->category_of_faculty] ?? 'Other',
                'count'    => $item->count,
            ]);

        // 6. Faculty count ranking
        $selectedDeptAcro = '';
        if ($filters['college'] !== 'all') {
            $rankRows = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function ($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id',    $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->whereIn('fp.id', $activeFacultyIds)
                ->where('fp.college', $filters['college'])
                ->select('d.department_acro', DB::raw('COUNT(DISTINCT fp.id) as total_faculty'))
                ->groupBy('d.department_id', 'd.department_acro')
                ->orderByDesc('total_faculty')
                ->get();

            if ($filters['department'] !== 'all') {
                $deptInfo         = DB::connection('ewms')->table('table_department')->where('department_id', $filters['department'])->first();
                $selectedDeptAcro = $deptInfo->department_acro ?? '';
            }
        } else {
            $allowedColleges = ['CED', 'COS', 'CASS', 'CEN', 'CAG', 'CHSI', 'CVSM', 'CBA', 'CF'];

            $rankRows = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function ($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id',    $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
                ->whereIn('fp.id', $activeFacultyIds)
                ->whereIn('cu.college_acro', $allowedColleges)
                ->select('cu.college_acro as department_acro', DB::raw('COUNT(DISTINCT fp.id) as total_faculty'))
                ->groupBy('cu.college_acro')
                ->having('total_faculty', '>', 0)
                ->orderByDesc('total_faculty')
                ->get();
        }

        $rankingLabels = $rankRows->pluck('department_acro')->toArray();
        $rankingCounts = $rankRows->pluck('total_faculty')->toArray();

        // 7. Qualification by department (stacked bar)
        $phdByDepartment = $this->buildQualificationByDepartment($activeFacultyIds, $filters);
        $qualLabels      = $phdByDepartment->pluck('department_acro')->toArray();
        $phdPct          = $phdByDepartment->pluck('phd_percentage')->toArray();
        $mastersPct      = $phdByDepartment->pluck('masters_percentage')->toArray();
        $bachelorsPct    = $phdByDepartment->pluck('bachelors_percentage')->toArray();
        $phdCounts       = $phdByDepartment->pluck('phd_count')->toArray();
        $mastersCounts   = $phdByDepartment->pluck('masters_count')->toArray();
        $bachelorsCounts = $phdByDepartment->pluck('bachelors_count')->toArray();

        // 8. Semester / college / dept text for titles
        $selectedSemester = Semester::find($filters['semester']);
        $semesterText     = $selectedSemester ? $selectedSemester->semester . ' ' . $selectedSemester->sy : '';
        $collegeAcro      = '';
        $deptAcro         = '';
        if ($filters['college'] !== 'all') {
            $col         = CollegeUnit::find($filters['college']);
            $collegeAcro = $col->college_acro ?? '';
        }
        if ($filters['department'] !== 'all') {
            $dep      = Department::find($filters['department']);
            $deptAcro = $dep->department_acro ?? '';
        }

        // 9. Dept dropdown for AJAX rebuild
        $departments = $filters['college'] !== 'all'
            ? Department::where('college_id', $filters['college'])->orderBy('department')->get(['department_id', 'department_acro'])
            : collect();

        return response()->json([
            'totalFaculty'   => $totalFaculty,
            'activeCount'    => $activeCount,
            'onLeaveCount'   => $onLeaveCount,
            'phdHolders'     => $phdHolders,
            'mastersHolders' => $mastersHolders,
            'categoryLabels' => $categories->pluck('category')->values(),
            'categoryData'   => $categories->pluck('count')->values(),
            'rankingLabels'  => $rankingLabels,
            'rankingCounts'  => $rankingCounts,
            'selectedDept'   => $selectedDeptAcro,
            'qualLabels'     => $qualLabels,
            'phdPct'         => $phdPct,
            'mastersPct'     => $mastersPct,
            'bachelorsPct'   => $bachelorsPct,
            'phdCounts'      => $phdCounts,
            'mastersCounts'  => $mastersCounts,
            'bachelorsCounts'=> $bachelorsCounts,
            'semesterText'   => $semesterText,
            'collegeAcro'    => $collegeAcro,
            'deptAcro'       => $deptAcro,
            'departments'    => $departments,
        ]);
    }
    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================
    private function buildQualificationByDepartment(array $facultyIds, array $filters): \Illuminate\Support\Collection
    {
        $facultyByDept = DB::connection('ewms')
            ->table('table_faculty_profile as fp')
            ->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            })
            ->join('table_department as d', 'fp.department', '=', 'd.department_id')
            ->whereIn('fp.id', $facultyIds)
            ->when($filters['college'] !== 'all', fn($q) => $q->where('fp.college', $filters['college']))
            ->select('d.department_id', 'd.department_acro', 'd.department', DB::raw('COUNT(DISTINCT fp.id) as total_faculty'))
            ->groupBy('d.department_id', 'd.department_acro', 'd.department')
            ->get();

        $result = collect();

        foreach ($facultyByDept as $dept) {
            $deptIds = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function ($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id',    $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->whereIn('fp.id', $facultyIds)
                ->where('fp.department', $dept->department_id)
                ->pluck('fp.id')
                ->toArray();

            if (empty($deptIds)) continue;

            $phd = DB::connection('ewms')
                ->table('table_faculty_academic_degree as fad')
                ->whereIn('fad.f_id', $deptIds)
                ->whereNotNull('fad.phd_degree_title')
                ->where('fad.phd_degree_title', '!=', '')
                ->where('fad.phd_degree_title', '!=', 'N/A')
                ->distinct()
                ->count('fad.f_id');

            $masters = DB::connection('ewms')
                ->table('table_faculty_academic_degree as fad')
                ->whereIn('fad.f_id', $deptIds)
                ->whereNotNull('fad.ms_degree_title')
                ->where('fad.ms_degree_title', '!=', '')
                ->where('fad.ms_degree_title', '!=', 'N/A')
                ->where(fn($q) => $q->whereNull('fad.phd_degree_title')
                                    ->orWhere('fad.phd_degree_title', '')
                                    ->orWhere('fad.phd_degree_title', 'N/A'))
                ->distinct()
                ->count('fad.f_id');

            $withDegree = DB::connection('ewms')
                ->table('table_faculty_academic_degree')
                ->whereIn('f_id', $deptIds)
                ->distinct()
                ->pluck('f_id')
                ->toArray();

            $bachelors = 0;
            foreach ($deptIds as $fid) {
                if (!in_array($fid, $withDegree)) continue;
                $deg        = DB::connection('ewms')->table('table_faculty_academic_degree')->where('f_id', $fid)->first();
                $hasPhD     = !empty($deg->phd_degree_title) && !in_array($deg->phd_degree_title, ['N/A', '']);
                $hasMasters = !$hasPhD && !empty($deg->ms_degree_title) && !in_array($deg->ms_degree_title, ['N/A', '']);
                if (!$hasPhD && !$hasMasters && !empty($deg->degree_title) && !in_array($deg->degree_title, ['N/A', ''])) {
                    $bachelors++;
                }
            }

            $noDeg   = count($deptIds) - count($withDegree);
            $total   = $dept->total_faculty;
            $bachAll = $bachelors + $noDeg;

            $result->push((object) [
                'department_id'        => $dept->department_id,
                'department_acro'      => $dept->department_acro,
                'department'           => $dept->department,
                'total_faculty'        => $total,
                'phd_count'            => $phd,
                'masters_count'        => $masters,
                'bachelors_count'      => $bachAll,
                'phd_percentage'       => $total > 0 ? round($phd     / $total * 100, 1) : 0,
                'masters_percentage'   => $total > 0 ? round($masters / $total * 100, 1) : 0,
                'bachelors_percentage' => $total > 0 ? round($bachAll / $total * 100, 1) : 0,
            ]);
        }

        return $result->sortByDesc('total_faculty')->values();
    }


    // =========================================================================
    // FACULTY QUALIFICATIONS PAGE
    // =========================================================================
    public function facultyQualifications(Request $request)
    {
        $activeSemester = Semester::where('status', 1)->orderBy('sem_id', 'desc')->first()
                       ?? Semester::orderBy('sem_id', 'desc')->first();

        $filters = [
            'semester'   => $request->get('semester',  $activeSemester->sem_id ?? null),
            'college'    => $request->get('college',   'all'),
            'department' => $request->get('department','all'),
            'rank'       => $request->get('rank',      'all'),
        ];

        $base = fn() => DB::connection('ewms')
            ->table('table_faculty_academic_degree')
            ->join('table_faculty_profile as fp', 'table_faculty_academic_degree.f_id', '=', 'fp.id')
            ->when($filters['semester'], fn($q) => $q->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            }))
            ->when($filters['college']    !== 'all', fn($q) => $q->where('fp.college',              $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department',           $filters['department']))
            ->when($filters['rank']       !== 'all', fn($q) => $q->where('fp.generic_faculty_rank', $filters['rank']));

        $facultyWithDegrees        = $base()->distinct('table_faculty_academic_degree.f_id')->count('table_faculty_academic_degree.f_id');
        $highestDegrees            = $base()->selectRaw("COUNT(CASE WHEN phd_degree_title IS NOT NULL AND phd_degree_title != '' THEN 1 END) as phd_count, COUNT(CASE WHEN ms_degree_title IS NOT NULL AND ms_degree_title != '' AND (phd_degree_title IS NULL OR phd_degree_title = '') THEN 1 END) as masters_count, COUNT(*) as total_faculty")->first();
        $thesisExperience          = $base()->selectRaw("COUNT(CASE WHEN wrote_thesis='Yes' OR ms_wrote_thesis='Yes' OR phd_wrote_thesis='Yes' THEN 1 END) as with_thesis, COUNT(*) as total_faculty")->first();
        $internationalEducation    = $base()->selectRaw("COUNT(CASE WHEN where_obtained LIKE '%abroad%' OR ms_where_obtained LIKE '%abroad%' OR phd_where_obtained LIKE '%abroad%' THEN 1 END) as international_count, COUNT(*) as total_faculty")->first();
        $highestDegreeDistribution = $base()->selectRaw("COUNT(CASE WHEN phd_degree_title IS NOT NULL AND phd_degree_title != '' THEN 1 END) as doctorate, COUNT(CASE WHEN ms_degree_title IS NOT NULL AND ms_degree_title != '' AND (phd_degree_title IS NULL OR phd_degree_title = '') THEN 1 END) as masters, COUNT(CASE WHEN (phd_degree_title IS NULL OR phd_degree_title = '') AND (ms_degree_title IS NULL OR ms_degree_title = '') AND degree_title IS NOT NULL THEN 1 END) as bachelors, COUNT(CASE WHEN degree_title IS NULL OR degree_title = '' THEN 1 END) as no_degree")->first();

        $degreeByDepartment = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
            ->join('table_department as d', 'fp.department', '=', 'd.department_id')
            ->when($filters['semester'], fn($q) => $q->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            }))
            ->when($filters['college'] !== 'all', fn($q) => $q->where('fp.college', $filters['college']))
            ->selectRaw("d.department_acro, COUNT(DISTINCT fp.id) as total_faculty, COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END) as phd_count, ROUND(COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END)*100.0/COUNT(DISTINCT fp.id),1) as phd_percentage")
            ->groupBy('d.department_id', 'd.department_acro')
            ->orderBy('phd_percentage', 'desc')
            ->limit(10)
            ->get();

        $qualificationByRank = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
            ->when($filters['semester'], fn($q) => $q->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            }))
            ->when($filters['college']    !== 'all', fn($q) => $q->where('fp.college',    $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->selectRaw("fp.generic_faculty_rank as faculty_rank, COUNT(DISTINCT fp.id) as total_faculty, COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END) as phd_count, ROUND(COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END)*100.0/COUNT(DISTINCT fp.id),1) as phd_percentage")
            ->whereNotNull('fp.generic_faculty_rank')
            ->where('fp.generic_faculty_rank', '!=', '')
            ->groupBy('fp.generic_faculty_rank')
            ->orderBy('phd_percentage', 'desc')
            ->get();

        $thesisByDepartment = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
            ->join('table_department as d', 'fp.department', '=', 'd.department_id')
            ->when($filters['semester'], fn($q) => $q->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            }))
            ->when($filters['college'] !== 'all', fn($q) => $q->where('fp.college', $filters['college']))
            ->selectRaw("d.department_acro, COUNT(DISTINCT fp.id) as total_faculty, COUNT(CASE WHEN fad.wrote_thesis='Yes' OR fad.ms_wrote_thesis='Yes' OR fad.phd_wrote_thesis='Yes' THEN 1 END) as with_thesis_count, ROUND(COUNT(CASE WHEN fad.wrote_thesis='Yes' OR fad.ms_wrote_thesis='Yes' OR fad.phd_wrote_thesis='Yes' THEN 1 END)*100.0/COUNT(DISTINCT fp.id),1) as thesis_percentage")
            ->groupBy('d.department_id', 'd.department_acro')
            ->orderBy('thesis_percentage', 'desc')
            ->get();

        $facultyQualifications = DB::connection('ewms')
            ->table('table_faculty_academic_degree as fad')
            ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
            ->leftJoin('table_department as d', 'fp.department', '=', 'd.department_id')
            ->leftJoin('table_college_unit as c', 'fp.college', '=', 'c.c_u_id')
            ->when($filters['semester'], fn($q) => $q->join('table_faculty_status as fs', function ($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id',    $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            }))
            ->when($filters['college']    !== 'all', fn($q) => $q->where('fp.college',              $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department',           $filters['department']))
            ->when($filters['rank']       !== 'all', fn($q) => $q->where('fp.generic_faculty_rank', $filters['rank']))
            ->selectRaw("fp.id, CONCAT(fp.fname,' ',fp.lname) as faculty_name, c.college_acro, d.department_acro, fp.generic_faculty_rank, fad.degree_title as bachelors_degree, fad.ms_degree_title as masters_degree, fad.phd_degree_title as doctoral_degree, CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 'Doctorate' WHEN fad.ms_degree_title IS NOT NULL AND fad.ms_degree_title != '' THEN 'Masters' WHEN fad.degree_title IS NOT NULL AND fad.degree_title != '' THEN 'Bachelors' ELSE 'No Degree' END as highest_degree, CASE WHEN fad.wrote_thesis='Yes' OR fad.ms_wrote_thesis='Yes' OR fad.phd_wrote_thesis='Yes' THEN 'Yes' ELSE 'No' END as thesis_experience, CASE WHEN fad.where_obtained LIKE '%abroad%' OR fad.ms_where_obtained LIKE '%abroad%' OR fad.phd_where_obtained LIKE '%abroad%' THEN 'Yes' ELSE 'No' END as international_education")
            ->orderBy('d.department_acro')
            ->orderBy('fp.lname')
            ->limit(50)
            ->get();

        $semesters   = Semester::orderBy('sem_id', 'desc')->get();
        $colleges    = CollegeUnit::orderBy('college_unit')->get();
        $departments = $filters['college'] !== 'all'
            ? Department::where('college_id', $filters['college'])->orderBy('department')->get()
            : Department::orderBy('department')->get();
        $facultyRanks = FacultyProfile::whereNotNull('generic_faculty_rank')
            ->where('generic_faculty_rank', '!=', '')
            ->select('generic_faculty_rank')
            ->distinct()
            ->orderBy('generic_faculty_rank')
            ->get();

        $highestDegrees            = $highestDegrees            ?? (object) ['phd_count' => 0, 'masters_count' => 0, 'total_faculty' => 0];
        $thesisExperience          = $thesisExperience          ?? (object) ['with_thesis' => 0, 'total_faculty' => 0];
        $internationalEducation    = $internationalEducation    ?? (object) ['international_count' => 0, 'total_faculty' => 0];
        $highestDegreeDistribution = $highestDegreeDistribution ?? (object) ['doctorate' => 0, 'masters' => 0, 'bachelors' => 0, 'no_degree' => 0];

        return view('stzfaculty.qualifications', compact(
            'facultyWithDegrees', 'highestDegrees', 'thesisExperience', 'internationalEducation',
            'degreeByDepartment', 'highestDegreeDistribution', 'qualificationByRank',
            'thesisByDepartment', 'facultyQualifications', 'filters',
            'semesters', 'colleges', 'departments', 'facultyRanks'
        ));
    }


    // =========================================================================
    // TEACHING LOAD PAGE
    // =========================================================================
    public function teachingLoad(Request $request)
    {
        $academicCollegeAcros = ['CED','CASS','CAG','CEN','COS','CVSM','CHSI','CBA','CF'];

        $activeSemester = Semester::where('status', 1)->orderBy('sem_id', 'desc')->first()
                       ?? Semester::orderBy('sem_id', 'desc')->first();

        $filters = [
            'semester'   => $request->get('semester',   $activeSemester->sem_id ?? null),
            'college'    => $request->get('college',    'all'),
            'department' => $request->get('department', 'all'),
        ];

        $drillDown = $filters['college'] !== 'all';
        $semId     = $filters['semester'];

        $overallStats = Summary::query()
            ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
            ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
            ->when($semId,      fn($q) => $q->where('table_summary.sem_id', $semId))
            ->when(!$drillDown, fn($q) => $q->whereIn('cu.college_acro', $academicCollegeAcros))
            ->when($drillDown,  fn($q) => $q->where('fp.college', $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->selectRaw('AVG(table_summary.actual_atl) as avg_atl, COUNT(DISTINCT table_summary.f_id) as total_faculty')
            ->first();

        $avgAtl       = $overallStats->avg_atl      ?? 0;
        $totalFaculty = $overallStats->total_faculty ?? 0;

        $subjectQuery = ClassSchedule::query()
            ->when($semId, fn($q) => $q->where('sem_id', $semId))
            ->when(!$drillDown, fn($q) => $q->whereHas('department.college', fn($sq) => $sq->whereIn('college_acro', $academicCollegeAcros)))
            ->when($drillDown,  fn($q) => $q->whereHas('department', fn($sq) => $sq->where('college_id', $filters['college'])))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('department_id', $filters['department']));

        $totalSubjects = (clone $subjectQuery)->distinct('subject_title')->count('subject_title');
        $totalStudents = (clone $subjectQuery)->sum('no_of_student');

        if (!$drillDown) {
            $csSub = DB::raw("(SELECT d.college_id, COUNT(DISTINCT cs.subject_title) AS total_subjects, SUM(cs.no_of_student) AS total_students FROM table_class_schedule cs JOIN table_department d ON cs.department_id=d.department_id WHERE cs.sem_id={$semId} GROUP BY d.college_id) AS cs");
            $chartStats = Summary::query()
                ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
                ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
                ->leftJoin($csSub, 'cu.c_u_id', '=', 'cs.college_id')
                ->when($semId, fn($q) => $q->where('table_summary.sem_id', $semId))
                ->whereIn('cu.college_acro', $academicCollegeAcros)
                ->selectRaw("cu.c_u_id AS group_id,cu.college_acro AS group_label,COUNT(DISTINCT fp.id) AS faculty_count,AVG(table_summary.actual_atl) AS avg_atl,COALESCE(MAX(cs.total_subjects),0) AS total_subjects,COALESCE(MAX(cs.total_students),0) AS total_students,SUM(CASE WHEN table_summary.actual_atl<10 THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN table_summary.actual_atl>=10 AND table_summary.actual_atl<15 THEN 1 ELSE 0 END) AS moderate,SUM(CASE WHEN table_summary.actual_atl>=15 AND table_summary.actual_atl<20 THEN 1 ELSE 0 END) AS high,SUM(CASE WHEN table_summary.actual_atl>=20 THEN 1 ELSE 0 END) AS very_high")
                ->groupBy('cu.c_u_id', 'cu.college_acro')
                ->having('faculty_count', '>', 0)
                ->orderBy('avg_atl', 'desc')
                ->get();
            $chartGroupLabel = 'College';
        } else {
            $csSub = DB::raw("(SELECT department_id,COUNT(DISTINCT subject_title) AS total_subjects,SUM(no_of_student) AS total_students FROM table_class_schedule WHERE sem_id={$semId} GROUP BY department_id) AS cs");
            $chartStats = Summary::query()
                ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->leftJoin($csSub, 'fp.department', '=', 'cs.department_id')
                ->when($semId, fn($q) => $q->where('table_summary.sem_id', $semId))
                ->where('fp.college', $filters['college'])
                ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
                ->selectRaw("d.department_id AS group_id,d.department_acro AS group_label,COUNT(DISTINCT fp.id) AS faculty_count,AVG(table_summary.actual_atl) AS avg_atl,COALESCE(MAX(cs.total_subjects),0) AS total_subjects,COALESCE(MAX(cs.total_students),0) AS total_students,SUM(CASE WHEN table_summary.actual_atl<10 THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN table_summary.actual_atl>=10 AND table_summary.actual_atl<15 THEN 1 ELSE 0 END) AS moderate,SUM(CASE WHEN table_summary.actual_atl>=15 AND table_summary.actual_atl<20 THEN 1 ELSE 0 END) AS high,SUM(CASE WHEN table_summary.actual_atl>=20 THEN 1 ELSE 0 END) AS very_high")
                ->groupBy('d.department_id', 'd.department_acro')
                ->having('faculty_count', '>', 0)
                ->orderBy('avg_atl', 'desc')
                ->get();
            $chartGroupLabel = 'Department';
        }

        $workloadDistribution = Summary::query()
            ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
            ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
            ->when($semId,      fn($q) => $q->where('table_summary.sem_id', $semId))
            ->when(!$drillDown, fn($q) => $q->whereIn('cu.college_acro', $academicCollegeAcros))
            ->when($drillDown,  fn($q) => $q->where('fp.college', $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->selectRaw("SUM(CASE WHEN table_summary.actual_atl<10 THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN table_summary.actual_atl>=10 AND table_summary.actual_atl<15 THEN 1 ELSE 0 END) AS moderate,SUM(CASE WHEN table_summary.actual_atl>=15 AND table_summary.actual_atl<20 THEN 1 ELSE 0 END) AS high,SUM(CASE WHEN table_summary.actual_atl>=20 THEN 1 ELSE 0 END) AS very_high")
            ->first();

        $semesters       = Semester::orderBy('sem_id', 'desc')->get();
        $colleges        = CollegeUnit::orderBy('college_acro')->get();
        $departments     = $drillDown ? Department::where('college_id', $filters['college'])->orderBy('department')->get() : collect();
        $selectedCollege = $drillDown ? CollegeUnit::find($filters['college']) : null;

        return view('stzfaculty.teaching-load', compact(
            'avgAtl', 'totalFaculty', 'totalSubjects', 'totalStudents',
            'chartStats', 'chartGroupLabel', 'workloadDistribution',
            'filters', 'semesters', 'colleges', 'departments', 'selectedCollege', 'drillDown'
        ));
    }


    // =========================================================================
    // TEACHING LOAD — AJAX ENDPOINT
    // =========================================================================
    public function teachingLoadAjax(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Not an AJAX request'], 400);
        }

        $academicCollegeAcros = ['CED','CASS','CAG','CEN','COS','CVSM','CHSI','CBA','CF'];

        $activeSemester = Semester::where('status', 1)->orderBy('sem_id', 'desc')->first()
                       ?? Semester::orderBy('sem_id', 'desc')->first();

        $filters = [
            'semester'   => $request->get('semester',   $activeSemester->sem_id ?? null),
            'college'    => $request->get('college',    'all'),
            'department' => $request->get('department', 'all'),
        ];

        $drillDown = $filters['college'] !== 'all';
        $semId     = $filters['semester'];

        $overallStats = Summary::query()
            ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
            ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
            ->when($semId,      fn($q) => $q->where('table_summary.sem_id', $semId))
            ->when(!$drillDown, fn($q) => $q->whereIn('cu.college_acro', $academicCollegeAcros))
            ->when($drillDown,  fn($q) => $q->where('fp.college', $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->selectRaw('AVG(table_summary.actual_atl) as avg_atl, COUNT(DISTINCT table_summary.f_id) as total_faculty')
            ->first();

        $avgAtl       = $overallStats->avg_atl      ?? 0;
        $totalFaculty = $overallStats->total_faculty ?? 0;

        $subjectQuery = ClassSchedule::query()
            ->when($semId, fn($q) => $q->where('sem_id', $semId))
            ->when(!$drillDown, fn($q) => $q->whereHas('department.college', fn($sq) => $sq->whereIn('college_acro', $academicCollegeAcros)))
            ->when($drillDown,  fn($q) => $q->whereHas('department', fn($sq) => $sq->where('college_id', $filters['college'])))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('department_id', $filters['department']));

        $totalSubjects = (clone $subjectQuery)->distinct('subject_title')->count('subject_title');

        if (!$drillDown) {
            $csSub = DB::raw("(SELECT d.college_id,COUNT(DISTINCT cs.subject_title) AS total_subjects,SUM(cs.no_of_student) AS total_students FROM table_class_schedule cs JOIN table_department d ON cs.department_id=d.department_id WHERE cs.sem_id={$semId} GROUP BY d.college_id) AS cs");
            $chartStats = Summary::query()
                ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
                ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
                ->leftJoin($csSub, 'cu.c_u_id', '=', 'cs.college_id')
                ->when($semId, fn($q) => $q->where('table_summary.sem_id', $semId))
                ->whereIn('cu.college_acro', $academicCollegeAcros)
                ->selectRaw("cu.c_u_id AS group_id,cu.college_acro AS group_label,COUNT(DISTINCT fp.id) AS faculty_count,AVG(table_summary.actual_atl) AS avg_atl,COALESCE(MAX(cs.total_subjects),0) AS total_subjects,COALESCE(MAX(cs.total_students),0) AS total_students,SUM(CASE WHEN table_summary.actual_atl<10 THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN table_summary.actual_atl>=10 AND table_summary.actual_atl<15 THEN 1 ELSE 0 END) AS moderate,SUM(CASE WHEN table_summary.actual_atl>=15 AND table_summary.actual_atl<20 THEN 1 ELSE 0 END) AS high,SUM(CASE WHEN table_summary.actual_atl>=20 THEN 1 ELSE 0 END) AS very_high")
                ->groupBy('cu.c_u_id', 'cu.college_acro')
                ->having('faculty_count', '>', 0)
                ->orderBy('avg_atl', 'desc')
                ->get();
            $chartGroupLabel = 'College';
        } else {
            $csSub = DB::raw("(SELECT department_id,COUNT(DISTINCT subject_title) AS total_subjects,SUM(no_of_student) AS total_students FROM table_class_schedule WHERE sem_id={$semId} GROUP BY department_id) AS cs");
            $chartStats = Summary::query()
                ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->leftJoin($csSub, 'fp.department', '=', 'cs.department_id')
                ->when($semId, fn($q) => $q->where('table_summary.sem_id', $semId))
                ->where('fp.college', $filters['college'])
                ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
                ->selectRaw("d.department_id AS group_id,d.department_acro AS group_label,COUNT(DISTINCT fp.id) AS faculty_count,AVG(table_summary.actual_atl) AS avg_atl,COALESCE(MAX(cs.total_subjects),0) AS total_subjects,COALESCE(MAX(cs.total_students),0) AS total_students,SUM(CASE WHEN table_summary.actual_atl<10 THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN table_summary.actual_atl>=10 AND table_summary.actual_atl<15 THEN 1 ELSE 0 END) AS moderate,SUM(CASE WHEN table_summary.actual_atl>=15 AND table_summary.actual_atl<20 THEN 1 ELSE 0 END) AS high,SUM(CASE WHEN table_summary.actual_atl>=20 THEN 1 ELSE 0 END) AS very_high")
                ->groupBy('d.department_id', 'd.department_acro')
                ->having('faculty_count', '>', 0)
                ->orderBy('avg_atl', 'desc')
                ->get();
            $chartGroupLabel = 'Department';
        }

        $workloadDist = Summary::query()
            ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
            ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
            ->when($semId,      fn($q) => $q->where('table_summary.sem_id', $semId))
            ->when(!$drillDown, fn($q) => $q->whereIn('cu.college_acro', $academicCollegeAcros))
            ->when($drillDown,  fn($q) => $q->where('fp.college', $filters['college']))
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->selectRaw("SUM(CASE WHEN table_summary.actual_atl<10 THEN 1 ELSE 0 END) AS low,SUM(CASE WHEN table_summary.actual_atl>=10 AND table_summary.actual_atl<15 THEN 1 ELSE 0 END) AS moderate,SUM(CASE WHEN table_summary.actual_atl>=15 AND table_summary.actual_atl<20 THEN 1 ELSE 0 END) AS high,SUM(CASE WHEN table_summary.actual_atl>=20 THEN 1 ELSE 0 END) AS very_high")
            ->first();

        $semesterText = '';
        if ($semId) {
            $sem          = Semester::find($semId);
            $semesterText = $sem ? $sem->semester . ' ' . $sem->sy : '';
        }

        return response()->json([
            'chartStats'      => $chartStats,
            'workloadDist'    => $workloadDist,
            'avgAtl'          => $avgAtl,
            'totalFaculty'    => $totalFaculty,
            'totalSubjects'   => $totalSubjects,
            'chartGroupLabel' => $chartGroupLabel,
            'semesterText'    => $semesterText,
        ]);
    }


    // =========================================================================
    // DEPARTMENTS BY COLLEGE
    // =========================================================================
    public function departmentsByCollege($collegeId)
    {
        return response()->json(
            Department::where('college_id', $collegeId)
                ->orderBy('department')
                ->get(['department_id', 'department_acro'])
        );
    }


   // =========================================================================
    // RESEARCH PERFORMANCE PAGE
    // Filter: semester + college_unit (shows all departments under that unit)
    // Removed: role_type, activity_type, pub_type filters
    // =========================================================================
    public function researchPerformance(Request $request)
    {
        $activeSemester = Semester::where('status', 1)->orderBy('sem_id', 'desc')->first()
                       ?? Semester::orderBy('sem_id', 'desc')->first();

        $filters = [
            'semester' => $request->get('semester', $activeSemester->sem_id ?? 'all'),
            'college'  => $request->get('college',  'all'),   // college_unit c_u_id
        ];

        // Normalise
        foreach ($filters as $k => $v) {
            if ($v === '' || $v === null) $filters[$k] = 'all';
        }

        // Departments that belong to the selected college unit (for scoping)
        // When 'all', no extra filter; when a unit is chosen we join through
        // table_college_unit so every query automatically scopes to that unit's
        // departments.

        // ── Research load by department ──────────────────────────────────────
        $researchLoad = AssignmentInStudentRS::query()
            ->join('table_faculty_profile as fp',
                'table_assignment_in_student_rs.f_id', '=', 'fp.id')
            ->join('table_department as d',
                'fp.department', '=', 'd.department_id')
            ->join('table_college_unit as cu',
                'd.college_id', '=', 'cu.c_u_id')
            ->selectRaw('
                d.department_id,
                d.department,
                d.department_acro,
                cu.c_u_id           AS college_id,
                cu.college_acro,
                COUNT(DISTINCT fp.id)                                   AS faculty_with_research,
                ROUND(SUM(table_assignment_in_student_rs.etl), 2)       AS total_etl,
                ROUND(AVG(table_assignment_in_student_rs.etl), 2)       AS avg_etl,
                COUNT(DISTINCT table_assignment_in_student_rs.id)       AS research_count
            ')
            ->when(
                $filters['semester'] !== 'all',
                fn($q) => $q->where('table_assignment_in_student_rs.sem_id', $filters['semester'])
            )
            ->when(
                $filters['college'] !== 'all',
                fn($q) => $q->where('cu.c_u_id', $filters['college'])
            )
            ->groupBy(
                'd.department_id', 'd.department', 'd.department_acro',
                'cu.c_u_id', 'cu.college_acro'
            )
            ->orderBy('total_etl', 'desc')
            ->get();

        // ── Publications by department ────────────────────────────────────────
        // table_publication has no sem_id — semester filter not applied here
        $publications = Publication::query()
            ->join('table_faculty_profile as fp',
                'table_publication.f_id', '=', 'fp.id')
            ->join('table_department as d',
                'fp.department', '=', 'd.department_id')
            ->join('table_college_unit as cu',
                'd.college_id', '=', 'cu.c_u_id')
            ->selectRaw('
                d.department_id,
                d.department,
                d.department_acro,
                cu.c_u_id           AS college_id,
                cu.college_acro,
                COUNT(table_publication.id)  AS publication_count,
                COUNT(DISTINCT fp.id)        AS faculty_with_publications
            ')
            ->when(
                $filters['college'] !== 'all',
                fn($q) => $q->where('cu.c_u_id', $filters['college'])
            )
            ->whereNotNull('table_publication.type')
            ->where('table_publication.type', '!=', '')
            ->groupBy(
                'd.department_id', 'd.department', 'd.department_acro',
                'cu.c_u_id', 'cu.college_acro'
            )
            ->orderBy('publication_count', 'desc')
            ->get();

        // ── Per-type publication counts for pie chart ─────────────────────────
        $publicationTypeBreakdown = Publication::query()
            ->join('table_faculty_profile as fp',
                'table_publication.f_id', '=', 'fp.id')
            ->join('table_department as d',
                'fp.department', '=', 'd.department_id')
            ->join('table_college_unit as cu',
                'd.college_id', '=', 'cu.c_u_id')
            ->selectRaw('
                table_publication.type AS pub_type,
                COUNT(table_publication.id) AS type_count
            ')
            ->when(
                $filters['college'] !== 'all',
                fn($q) => $q->where('cu.c_u_id', $filters['college'])
            )
            ->whereNotNull('table_publication.type')
            ->where('table_publication.type', '!=', '')
            ->groupBy('table_publication.type')
            ->orderBy('type_count', 'desc')
            ->get();

        // ── Helpers ───────────────────────────────────────────────────────────
        $activeSemObj = ($filters['semester'] !== 'all')
            ? Semester::find($filters['semester'])
            : null;

        $selectedCollegeObj = ($filters['college'] !== 'all')
            ? CollegeUnit::find($filters['college'])
            : null;

        $semesters = Semester::orderBy('sem_id', 'desc')->get();

        // Only the 9 academic colleges (c_u_id 1–9: CAG, CASS, CBA, CED, CEN, CF, CHSI, COS, CVSM)
        $academicCollegeAcros = ['CAG','CASS','CBA','CED','CEN','CF','CHSI','COS','CVSM'];
        $collegeUnits = CollegeUnit::whereIn('college_acro', $academicCollegeAcros)
            ->orderBy('college_acro')
            ->get();

        return view('stzfaculty.research-performance', compact(
            'researchLoad',
            'publications',
            'publicationTypeBreakdown',
            'filters',
            'semesters',
            'collegeUnits',
            'activeSemObj',
            'selectedCollegeObj',
        ));
    }


    // =========================================================================
    // RESEARCH PERFORMANCE — AJAX ENDPOINT
    // =========================================================================
    public function researchPerformanceAjax(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Not an AJAX request'], 400);
        }

        $activeSemester = Semester::where('status', 1)->orderBy('sem_id', 'desc')->first()
                       ?? Semester::orderBy('sem_id', 'desc')->first();

        $filters = [
            'semester' => $request->get('semester', $activeSemester->sem_id ?? 'all'),
            'college'  => $request->get('college',  'all'),
        ];

        foreach ($filters as $k => $v) {
            if ($v === '' || $v === null) $filters[$k] = 'all';
        }

        // ── Research load ─────────────────────────────────────────────────────
        $researchLoad = AssignmentInStudentRS::query()
            ->join('table_faculty_profile as fp',
                'table_assignment_in_student_rs.f_id', '=', 'fp.id')
            ->join('table_department as d',
                'fp.department', '=', 'd.department_id')
            ->join('table_college_unit as cu',
                'd.college_id', '=', 'cu.c_u_id')
            ->selectRaw('
                d.department_id,
                d.department,
                d.department_acro,
                cu.c_u_id           AS college_id,
                cu.college_acro,
                COUNT(DISTINCT fp.id)                                   AS faculty_with_research,
                ROUND(SUM(table_assignment_in_student_rs.etl), 2)       AS total_etl,
                ROUND(AVG(table_assignment_in_student_rs.etl), 2)       AS avg_etl,
                COUNT(DISTINCT table_assignment_in_student_rs.id)       AS research_count
            ')
            ->when(
                $filters['semester'] !== 'all',
                fn($q) => $q->where('table_assignment_in_student_rs.sem_id', $filters['semester'])
            )
            ->when(
                $filters['college'] !== 'all',
                fn($q) => $q->where('cu.c_u_id', $filters['college'])
            )
            ->groupBy(
                'd.department_id', 'd.department', 'd.department_acro',
                'cu.c_u_id', 'cu.college_acro'
            )
            ->orderBy('total_etl', 'desc')
            ->get();

        // ── Publications ──────────────────────────────────────────────────────
        $publications = Publication::query()
            ->join('table_faculty_profile as fp',
                'table_publication.f_id', '=', 'fp.id')
            ->join('table_department as d',
                'fp.department', '=', 'd.department_id')
            ->join('table_college_unit as cu',
                'd.college_id', '=', 'cu.c_u_id')
            ->selectRaw('
                d.department_id,
                d.department,
                d.department_acro,
                cu.c_u_id           AS college_id,
                cu.college_acro,
                COUNT(table_publication.id)  AS publication_count,
                COUNT(DISTINCT fp.id)        AS faculty_with_publications
            ')
            ->when(
                $filters['college'] !== 'all',
                fn($q) => $q->where('cu.c_u_id', $filters['college'])
            )
            ->whereNotNull('table_publication.type')
            ->where('table_publication.type', '!=', '')
            ->groupBy(
                'd.department_id', 'd.department', 'd.department_acro',
                'cu.c_u_id', 'cu.college_acro'
            )
            ->orderBy('publication_count', 'desc')
            ->get();

        // ── Per-type pie breakdown ────────────────────────────────────────────
        $publicationTypeBreakdown = Publication::query()
            ->join('table_faculty_profile as fp',
                'table_publication.f_id', '=', 'fp.id')
            ->join('table_department as d',
                'fp.department', '=', 'd.department_id')
            ->join('table_college_unit as cu',
                'd.college_id', '=', 'cu.c_u_id')
            ->selectRaw('
                table_publication.type AS pub_type,
                COUNT(table_publication.id) AS type_count
            ')
            ->when(
                $filters['college'] !== 'all',
                fn($q) => $q->where('cu.c_u_id', $filters['college'])
            )
            ->whereNotNull('table_publication.type')
            ->where('table_publication.type', '!=', '')
            ->groupBy('table_publication.type')
            ->orderBy('type_count', 'desc')
            ->get();

        // ── Totals ────────────────────────────────────────────────────────────
        $totals = [
            'researchCount' => (int)   $researchLoad->sum('research_count'),
            'pubCount'      => (int)   $publications->sum('publication_count'),
            'etlHours'      => (float) round($researchLoad->sum('total_etl'), 0),
        ];

        // ── Labels ────────────────────────────────────────────────────────────
        $semesterText = '';
        if ($filters['semester'] !== 'all') {
            $sem          = Semester::find($filters['semester']);
            $semesterText = $sem ? $sem->semester . ' ' . $sem->sy : '';
        }

        $collegeAcro = '';
        if ($filters['college'] !== 'all') {
            $cu          = CollegeUnit::find($filters['college']);
            $collegeAcro = $cu->college_acro ?? '';
        }

        return response()->json([
            'researchLoad'             => $researchLoad,
            'publications'             => $publications,
            'publicationTypeBreakdown' => $publicationTypeBreakdown,
            'totals'                   => $totals,
            'semesterText'             => $semesterText,
            'collegeAcro'              => $collegeAcro,
        ]);
    }

    // =========================================================================
    // FACULTY APPROVAL PAGE
    // =========================================================================
    public function facultyApproval(Request $request)
    {
        $availableSemesters = Semester::orderBy('start_date', 'desc')->get();

        $filters = [
            'main_semester'      => $request->get('main_semester',      null),
            'main_signatory'     => $request->get('main_signatory',     null),
            'timeline_signatory' => $request->get('timeline_signatory', null),
        ];

        $mainQuery = DB::connection('ewms')->table('table_signatory');
        if (!empty($filters['main_semester'])) {
            $semester = Semester::find($filters['main_semester']);
            if ($semester && !empty($semester->start_date) && !empty($semester->end_date)) {
                $mainQuery->whereBetween('date_submitted', [$semester->start_date, $semester->end_date]);
            }
        }

        $mainSignatories = $mainQuery->get();
        $totalDocuments  = $mainSignatories->count();
        $fullyApproved   = $mainSignatories->filter(fn($i) => $this->isFullyApproved($i))->count();
        $declined        = $mainSignatories->filter(fn($i) => $this->hasDeclinedApproval($i))->count();
        $pendingApproval = $mainSignatories->filter(fn($i) => $this->isSubmitted($i))->count();

        $overallApproved = $overallPending = $overallDeclined = 0;
        foreach ($mainSignatories as $signatory) {
            $counts          = $this->getApprovalCounts($signatory);
            $overallApproved += $counts['approved'];
            $overallPending  += $counts['pending'];
            $overallDeclined += $counts['declined'];
        }

        $dhStats       = $this->calculateSingleSignatoryStats($mainSignatories, 'dh_approval');
        $deanStats     = $this->calculateSingleSignatoryStats($mainSignatories, 'dean_approval');
        $directorStats = $this->calculateSingleSignatoryStats($mainSignatories, 'director_supervisor');
        $dsStats       = $this->calculateSingleSignatoryStats($mainSignatories, 'ds_approval');
        $dotUniStats   = $this->calculateSingleSignatoryStats($mainSignatories, 'dot_uni_approval');
        $nstpStats     = $this->calculateSingleSignatoryStats($mainSignatories, 'nstp_approval');
        $eteeapStats   = $this->calculateSingleSignatoryStats($mainSignatories, 'eteeap_approval');
        $vpaaStats     = $this->calculateSingleSignatoryStats($mainSignatories, 'vpaa_approval');

        $signatoryRows = [
            ['label' => 'Department Head',     'filter' => 'dh',      'stats' => $dhStats],
            ['label' => 'Dean',                'filter' => 'dean',    'stats' => $deanStats],
            ['label' => 'Director/Supervisor', 'filter' => 'director','stats' => $directorStats],
            ['label' => 'DS',                  'filter' => 'ds',      'stats' => $dsStats],
            ['label' => 'DOT UNI',             'filter' => 'dot_uni', 'stats' => $dotUniStats],
            ['label' => 'NSTP',                'filter' => 'nstp',    'stats' => $nstpStats],
            ['label' => 'ETEEAP',              'filter' => 'eteeap',  'stats' => $eteeapStats],
            ['label' => 'VPAA',                'filter' => 'vpaa',    'stats' => $vpaaStats],
        ];

        $allTimelineDocs = DB::connection('ewms')->table('table_signatory')->get();
        $timelineYears   = DB::connection('ewms')->table('table_signatory')
            ->select(DB::raw('YEAR(date_submitted) as year'))
            ->whereNotNull('date_submitted')
            ->distinct()
            ->orderBy('year', 'asc')
            ->pluck('year')
            ->toArray();
        if (empty($timelineYears)) {
            $timelineYears = range(date('Y') - 4, date('Y'));
        }

        $yearlyDocumentCounts = $yearlyApprovedCounts = $yearlyDeclinedCounts = $yearlyPendingCounts = $yearlyApprovalRates = [];
        foreach ($timelineYears as $year) {
            $yearlyDocs = $allTimelineDocs->filter(fn($item) =>
                ($item->date_submitted ?? null) && date('Y', strtotime($item->date_submitted)) == $year
            );
            $totalCount                   = $yearlyDocs->count();
            $yearlyDocumentCounts[$year]  = $totalCount;

            if ($filters['timeline_signatory']) {
                $field     = $this->getTimelineField($filters['timeline_signatory']);
                $approved  = $yearlyDocs->filter(fn($i) => $this->checkIsApproved($i->$field ?? null))->count();
                $declined2 = $yearlyDocs->filter(fn($i) => $this->checkIsDeclined($i->$field ?? null))->count();
                $pending   = max(0, $totalCount - $approved - $declined2);
            } else {
                $approved  = $yearlyDocs->filter(fn($i) => $this->isFullyApproved($i))->count();
                $declined2 = $yearlyDocs->filter(fn($i) => $this->hasDeclinedApproval($i))->count();
                $pending   = $yearlyDocs->filter(fn($i) => $this->isSubmitted($i))->count();
            }

            $yearlyApprovedCounts[$year] = $approved;
            $yearlyDeclinedCounts[$year] = $declined2;
            $yearlyPendingCounts[$year]  = $pending;
            $yearlyApprovalRates[$year]  = $totalCount > 0 ? round(($approved / $totalCount) * 100, 1) : 0;
        }

        if ($request->ajax()) {
            return response()->json([
                'overallStats'   => compact('totalDocuments', 'fullyApproved', 'pendingApproval', 'declined', 'overallApproved', 'overallPending', 'overallDeclined'),
                'signatoryStats' => ['dh' => $dhStats, 'dean' => $deanStats, 'director' => $directorStats, 'ds' => $dsStats, 'dot_uni' => $dotUniStats, 'nstp' => $nstpStats, 'eteeap' => $eteeapStats, 'vpaa' => $vpaaStats],
                'timeline'       => ['years' => array_values($timelineYears), 'documentCounts' => array_values($yearlyDocumentCounts), 'approvedCounts' => array_values($yearlyApprovedCounts), 'declinedCounts' => array_values($yearlyDeclinedCounts), 'pendingCounts' => array_values($yearlyPendingCounts)],
            ]);
        }

        return view('stzfaculty.approval', compact(
            'availableSemesters', 'timelineYears', 'totalDocuments', 'fullyApproved',
            'pendingApproval', 'declined', 'overallApproved', 'overallPending', 'overallDeclined',
            'signatoryRows', 'dhStats', 'deanStats', 'directorStats', 'dsStats',
            'dotUniStats', 'nstpStats', 'eteeapStats', 'vpaaStats',
            'yearlyDocumentCounts', 'yearlyApprovedCounts', 'yearlyDeclinedCounts',
            'yearlyPendingCounts', 'yearlyApprovalRates', 'filters'
        ));
    }


    // =========================================================================
    // APPROVAL HELPERS
    // =========================================================================
    private function getFields(): array
    {
        return [
            'dh_approval', 'dean_approval', 'director_supervisor', 'ds_approval',
            'dot_uni_approval', 'nstp_approval', 'eteeap_approval', 'vpaa_approval',
        ];
    }

    private function getTimelineField($filter)
    {
        return [
            'dh'       => 'dh_approval',
            'dean'     => 'dean_approval',
            'director' => 'director_supervisor',
            'ds'       => 'ds_approval',
            'dot_uni'  => 'dot_uni_approval',
            'nstp'     => 'nstp_approval',
            'eteeap'   => 'eteeap_approval',
            'vpaa'     => 'vpaa_approval',
        ][$filter] ?? null;
    }

    private function checkIsApproved($status): bool
    {
        if ($status === null || trim((string) $status) === '') return false;
        return in_array(strtolower(trim($status)), ['approved','approve','yes','1','true','accept','accepted']);
    }

    private function checkIsDeclined($status): bool
    {
        if ($status === null || trim((string) $status) === '') return false;
        return in_array(strtolower(trim($status)), ['declined','rejected','reject','deny','denied','no','disapproved','disapprove']);
    }

    private function checkIsPending($status): bool
    {
        if ($status === null || trim((string) $status) === '') return true;
        return in_array(strtolower(trim($status)), ['pending','waiting','in progress','0','null','for approval','not yet','for review']);
    }

    private function isNullOrEmpty($status): bool
    {
        return $status === null || trim((string) $status) === '';
    }

    private function isFullyApproved($signatory): bool
    {
        $hasAtLeastOne = false;
        foreach ($this->getFields() as $field) {
            if (!property_exists($signatory, $field)) continue;
            $val = $signatory->$field;
            if ($this->isNullOrEmpty($val)) continue;
            $hasAtLeastOne = true;
            if (!$this->checkIsApproved($val)) return false;
        }
        return $hasAtLeastOne;
    }

    private function hasDeclinedApproval($signatory): bool
    {
        foreach ($this->getFields() as $field) {
            if (!property_exists($signatory, $field)) continue;
            if ($this->checkIsDeclined($signatory->$field)) return true;
        }
        return false;
    }

    private function isSubmitted($signatory): bool
    {
        if ($this->hasDeclinedApproval($signatory) || $this->isFullyApproved($signatory)) return false;
        foreach ($this->getFields() as $field) {
            if (!property_exists($signatory, $field)) continue;
            if (!$this->isNullOrEmpty($signatory->$field)) return true;
        }
        return false;
    }

    private function isNotYetSubmitted($signatory): bool
    {
        foreach ($this->getFields() as $field) {
            if (!property_exists($signatory, $field)) continue;
            if (!$this->isNullOrEmpty($signatory->$field)) return false;
        }
        return true;
    }

    private function hasPendingApproval($signatory): bool
    {
        return $this->isSubmitted($signatory);
    }

    private function calculateSingleSignatoryStats($signatories, $fieldName): array
    {
        $approved = $pending = $declined = $total = 0;
        foreach ($signatories as $signatory) {
            if (!property_exists($signatory, $fieldName)) continue;
            $total++;
            $status = $signatory->$fieldName;
            if      ($this->checkIsApproved($status)) $approved++;
            elseif  ($this->checkIsDeclined($status)) $declined++;
            else                                       $pending++;
        }
        return [
            'approved' => $approved,
            'pending'  => $pending,
            'declined' => $declined,
            'total'    => $total,
            'rate'     => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
        ];
    }

    private function getApprovalCounts($signatory): array
    {
        $approved = $pending = $declined = 0;
        foreach ($this->getFields() as $field) {
            if (!property_exists($signatory, $field)) continue;
            $status = $signatory->$field;
            if      ($this->checkIsApproved($status)) $approved++;
            elseif  ($this->checkIsDeclined($status)) $declined++;
            else                                       $pending++;
        }
        return ['approved' => $approved, 'pending' => $pending, 'declined' => $declined];
    }
}