<?php
// app/Http/Controllers/DashboardController.php

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

    // Add this method to your DashboardController class
public function facultyQualifications(Request $request)
{
    // Get active semester
    $activeSemester = Semester::where('status', 1)
        ->orderBy('sem_id', 'desc')
        ->first();
    
    if (!$activeSemester) {
        $activeSemester = Semester::orderBy('sem_id', 'desc')->first();
    }
    
    // Apply filters
    $filters = [
        'semester' => $request->get('semester', $activeSemester->sem_id ?? null),
        'college' => $request->get('college', 'all'),
        'department' => $request->get('department', 'all'),
        'rank' => $request->get('rank', 'all'),
    ];
    
    // =============================================
    // 1. QUALIFICATION STATISTICS (for stat cards)
    // =============================================
    
    // Total faculty with academic degrees
    $facultyWithDegrees = DB::connection('ewms')->table('table_faculty_academic_degree')
        ->join('table_faculty_profile as fp', 'table_faculty_academic_degree.f_id', '=', 'fp.id')
        ->when($filters['semester'], function($q) use ($filters) {
            // Join with faculty status to check active faculty in semester
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->when($filters['rank'] !== 'all', function($q) use ($filters) {
            $q->where('fp.generic_faculty_rank', $filters['rank']);
        })
        ->distinct('table_faculty_academic_degree.f_id')
        ->count('table_faculty_academic_degree.f_id');
    
    // Count faculty by highest degree obtained
    $highestDegrees = DB::connection('ewms')->table('table_faculty_academic_degree')
        ->join('table_faculty_profile as fp', 'table_faculty_academic_degree.f_id', '=', 'fp.id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->when($filters['rank'] !== 'all', function($q) use ($filters) {
            $q->where('fp.generic_faculty_rank', $filters['rank']);
        })
        ->selectRaw("
            COUNT(CASE WHEN phd_degree_title IS NOT NULL AND phd_degree_title != '' THEN 1 END) as phd_count,
            COUNT(CASE WHEN ms_degree_title IS NOT NULL AND ms_degree_title != '' 
                      AND (phd_degree_title IS NULL OR phd_degree_title = '') THEN 1 END) as masters_count,
            COUNT(CASE WHEN degree_title LIKE '%Ph.D%' OR degree_title LIKE '%Doctor%' THEN 1 END) as doctorate_count,
            COUNT(CASE WHEN degree_title LIKE '%Master%' OR ms_degree_title LIKE '%Master%' THEN 1 END) as total_masters,
            COUNT(*) as total_faculty
        ")
        ->first();
    
    // Faculty with thesis experience
    $thesisExperience = DB::connection('ewms')->table('table_faculty_academic_degree')
        ->join('table_faculty_profile as fp', 'table_faculty_academic_degree.f_id', '=', 'fp.id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->selectRaw("
            COUNT(CASE WHEN wrote_thesis = 'Yes' OR ms_wrote_thesis = 'Yes' OR phd_wrote_thesis = 'Yes' THEN 1 END) as with_thesis,
            COUNT(*) as total_faculty
        ")
        ->first();
    
    // International education experience
    $internationalEducation = DB::connection('ewms')->table('table_faculty_academic_degree')
        ->join('table_faculty_profile as fp', 'table_faculty_academic_degree.f_id', '=', 'fp.id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->selectRaw("
            COUNT(CASE WHEN where_obtained LIKE '%abroad%' 
                      OR where_obtained LIKE '%International%'
                      OR ms_where_obtained LIKE '%abroad%'
                      OR phd_where_obtained LIKE '%abroad%' 
                      OR where_obtained NOT LIKE '%Philippines%'
                      OR ms_where_obtained NOT LIKE '%Philippines%'
                      OR phd_where_obtained NOT LIKE '%Philippines%' THEN 1 END) as international_count,
            COUNT(*) as total_faculty
        ")
        ->first();
    
    // =============================================
    // 2. DEGREE DISTRIBUTION BY DEPARTMENT
    // =============================================
    $degreeByDepartment = DB::connection('ewms')->table('table_faculty_academic_degree as fad')
        ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->selectRaw("
            d.department_acro,
            COUNT(DISTINCT fp.id) as total_faculty,
            COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END) as phd_count,
            COUNT(CASE WHEN fad.ms_degree_title IS NOT NULL AND fad.ms_degree_title != '' THEN 1 END) as masters_count,
            COUNT(CASE WHEN fad.degree_title LIKE '%Ph.D%' OR fad.degree_title LIKE '%Doctor%' THEN 1 END) as doctorate_count,
            ROUND(COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END) * 100.0 / COUNT(DISTINCT fp.id), 1) as phd_percentage
        ")
        ->groupBy('d.department_id', 'd.department_acro')
        ->orderBy('phd_percentage', 'desc')
        ->limit(10)
        ->get();
    
    // =============================================
    // 3. HIGHEST DEGREE DISTRIBUTION (Pie Chart)
    // =============================================
    $highestDegreeDistribution = DB::connection('ewms')->table('table_faculty_academic_degree')
        ->join('table_faculty_profile as fp', 'table_faculty_academic_degree.f_id', '=', 'fp.id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->selectRaw("
            COUNT(CASE WHEN phd_degree_title IS NOT NULL AND phd_degree_title != '' THEN 1 END) as doctorate,
            COUNT(CASE WHEN ms_degree_title IS NOT NULL AND ms_degree_title != '' 
                      AND (phd_degree_title IS NULL OR phd_degree_title = '') THEN 1 END) as masters,
            COUNT(CASE WHEN (phd_degree_title IS NULL OR phd_degree_title = '') 
                      AND (ms_degree_title IS NULL OR ms_degree_title = '')
                      AND degree_title IS NOT NULL THEN 1 END) as bachelors,
            COUNT(CASE WHEN degree_title IS NULL OR degree_title = '' THEN 1 END) as no_degree
        ")
        ->first();
    
    // =============================================
    // 4. QUALIFICATION BY FACULTY RANK
    // =============================================
    $qualificationByRank = DB::connection('ewms')->table('table_faculty_academic_degree as fad')
        ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->selectRaw("
            fp.generic_faculty_rank as faculty_rank,
            COUNT(DISTINCT fp.id) as total_faculty,
            COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END) as phd_count,
            COUNT(CASE WHEN fad.ms_degree_title IS NOT NULL AND fad.ms_degree_title != '' THEN 1 END) as masters_count,
            ROUND(COUNT(CASE WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 1 END) * 100.0 / COUNT(DISTINCT fp.id), 1) as phd_percentage
        ")
        ->whereNotNull('fp.generic_faculty_rank')
        ->where('fp.generic_faculty_rank', '!=', '')
        ->groupBy('fp.generic_faculty_rank')
        ->orderBy('phd_percentage', 'desc')
        ->get();
    
    // =============================================
    // 5. THESIS EXPERIENCE BY DEPARTMENT
    // =============================================
    $thesisByDepartment = DB::connection('ewms')->table('table_faculty_academic_degree as fad')
        ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->selectRaw("
            d.department_acro,
            COUNT(DISTINCT fp.id) as total_faculty,
            COUNT(CASE WHEN fad.wrote_thesis = 'Yes' OR fad.ms_wrote_thesis = 'Yes' OR fad.phd_wrote_thesis = 'Yes' THEN 1 END) as with_thesis_count,
            ROUND(COUNT(CASE WHEN fad.wrote_thesis = 'Yes' OR fad.ms_wrote_thesis = 'Yes' OR fad.phd_wrote_thesis = 'Yes' THEN 1 END) * 100.0 / COUNT(DISTINCT fp.id), 1) as thesis_percentage
        ")
        ->groupBy('d.department_id', 'd.department_acro')
        ->orderBy('thesis_percentage', 'desc')
        ->get();
    
    // =============================================
    // 6. DETAILED FACULTY QUALIFICATIONS TABLE
    // =============================================
    $facultyQualifications = DB::connection('ewms')->table('table_faculty_academic_degree as fad')
        ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
        ->leftJoin('table_department as d', 'fp.department', '=', 'd.department_id')
        ->leftJoin('table_college_unit as c', 'fp.college', '=', 'c.c_u_id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->when($filters['rank'] !== 'all', function($q) use ($filters) {
            $q->where('fp.generic_faculty_rank', $filters['rank']);
        })
        ->selectRaw("
            fp.id,
            CONCAT(fp.fname, ' ', fp.lname) as faculty_name,
            c.college_acro,
            d.department_acro,
            fp.generic_faculty_rank,
            fad.degree_title as bachelors_degree,
            fad.ms_degree_title as masters_degree,
            fad.phd_degree_title as doctoral_degree,
            CASE 
                WHEN fad.phd_degree_title IS NOT NULL AND fad.phd_degree_title != '' THEN 'Doctorate'
                WHEN fad.ms_degree_title IS NOT NULL AND fad.ms_degree_title != '' THEN 'Masters'
                WHEN fad.degree_title IS NOT NULL AND fad.degree_title != '' THEN 'Bachelors'
                ELSE 'No Degree'
            END as highest_degree,
            CASE 
                WHEN fad.wrote_thesis = 'Yes' OR fad.ms_wrote_thesis = 'Yes' OR fad.phd_wrote_thesis = 'Yes' 
                THEN 'Yes' 
                ELSE 'No' 
            END as thesis_experience,
            CASE 
                WHEN fad.where_obtained LIKE '%abroad%' 
                     OR fad.ms_where_obtained LIKE '%abroad%'
                     OR fad.phd_where_obtained LIKE '%abroad%' 
                THEN 'Yes' 
                ELSE 'No' 
            END as international_education
        ")
        ->orderBy('d.department_acro')
        ->orderBy('fp.lname')
        ->limit(50)
        ->get();
    
    // =============================================
    // GET FILTER OPTIONS
    // =============================================
    $semesters = Semester::orderBy('sem_id', 'desc')->get();
    $colleges = CollegeUnit::orderBy('college_unit')->get();
    
    // Get departments based on college filter
    if ($filters['college'] !== 'all') {
        $departments = Department::where('college_id', $filters['college'])
            ->orderBy('department')
            ->get();
    } else {
        $departments = Department::orderBy('department')->get();
    }
    
    // Get unique faculty ranks
    $facultyRanks = FacultyProfile::whereNotNull('generic_faculty_rank')
        ->where('generic_faculty_rank', '!=', '')
        ->select('generic_faculty_rank')
        ->distinct()
        ->orderBy('generic_faculty_rank')
        ->get();
    
    // Initialize with defaults if null
    $highestDegrees = $highestDegrees ?? (object)[
        'phd_count' => 0,
        'masters_count' => 0,
        'doctorate_count' => 0,
        'total_masters' => 0,
        'total_faculty' => 0
    ];
    
    $thesisExperience = $thesisExperience ?? (object)[
        'with_thesis' => 0,
        'total_faculty' => 0
    ];
    
    $internationalEducation = $internationalEducation ?? (object)[
        'international_count' => 0,
        'total_faculty' => 0
    ];
    
    $highestDegreeDistribution = $highestDegreeDistribution ?? (object)[
        'doctorate' => 0,
        'masters' => 0,
        'bachelors' => 0,
        'no_degree' => 0
    ];
    
    // Initialize empty collections if queries return null
    $degreeByDepartment = $degreeByDepartment ?? collect();
    $qualificationByRank = $qualificationByRank ?? collect();
    $thesisByDepartment = $thesisByDepartment ?? collect();
    $facultyQualifications = $facultyQualifications ?? collect();
    
    return view('stzfaculty.qualifications', compact(
        'facultyWithDegrees',
        'highestDegrees',
        'thesisExperience',
        'internationalEducation',
        'degreeByDepartment',
        'highestDegreeDistribution',
        'qualificationByRank',
        'thesisByDepartment',
        'facultyQualifications',
        'filters',
        'semesters',
        'colleges',
        'departments',
        'facultyRanks'
    ));
}


    // Faculty Overview Page (PAGE 1)

public function facultyOverview(Request $request)
{
    // Get active semester
    $activeSemester = Semester::where('status', 1)
        ->orderBy('sem_id', 'desc')
        ->first();
    
    // Apply filters
    $filters = [
        'semester' => $request->get('semester', $activeSemester->sem_id ?? null),
        'college' => $request->get('college', 'all'),
        'department' => $request->get('department', 'all'),
    ];
    
    // ==============================================
    // 1. BASIC FACULTY STATISTICS (for stat cards)
    // ==============================================
    
    // Total faculty count (all faculty, regardless of active status)
    $totalFaculty = FacultyProfile::query()
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('department', $filters['department']);
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('college', $filters['college']);
        })
        ->count();
    
    // Active faculty count (filtered by semester)
    $activeCount = FacultyStatus::where('is_active', 'Yes')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('department', $filters['department']);
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('college', $filters['college']);
            });
        })
        ->count();
    
    // On leave count
    $onLeaveCount = FacultyStatus::where('is_active', 'No')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('department', $filters['department']);
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('college', $filters['college']);
            });
        })
        ->count();
    
    // ==============================================
    // 2. QUALIFICATION DATA WITH SEMESTER FILTER
    // ==============================================
    
    // Get ALL faculty in the selected semester (for PhD/Masters calculations)
    $facultyInSemester = DB::connection('ewms')
        ->table('table_faculty_profile as fp')
        ->join('table_faculty_status as fs', 'fp.id', '=', 'fs.f_id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('fs.sem_id', $filters['semester'])
              ->where('fs.is_active', 'Yes');
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->select('fp.id')
        ->distinct()
        ->get()
        ->pluck('id');
    
    // PhD holders count (from the faculty in the selected semester)
    $phdHolders = DB::connection('ewms')
        ->table('table_faculty_academic_degree as fad')
        ->whereIn('fad.f_id', $facultyInSemester)
        ->whereNotNull('fad.phd_degree_title')
        ->where('fad.phd_degree_title', '!=', '')
        ->distinct()
        ->count('fad.f_id');
    
    // Masters holders count (from the faculty in the selected semester)
    $mastersHolders = DB::connection('ewms')
        ->table('table_faculty_academic_degree as fad')
        ->whereIn('fad.f_id', $facultyInSemester)
        ->whereNotNull('fad.ms_degree_title')
        ->where('fad.ms_degree_title', '!=', '')
        ->distinct()
        ->count('fad.f_id');
    
    // ==============================================
    // 3. FACULTY CATEGORIES
    // ==============================================
    $categories = FacultyStatus::when($filters['semester'], function($q) use ($filters) {
            $q->where('sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('department', $filters['department']);
            });
        })
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('college', $filters['college']);
            });
        })
        ->selectRaw('category_of_faculty, COUNT(*) as count')
        ->groupBy('category_of_faculty')
        ->get()
        ->map(function($item) {
            $categoryNames = [
                1 => 'Regular',
                2 => 'Contractual',
                3 => 'Part-Time',
                4 => 'Temporary'
            ];
            return [
                'category' => $categoryNames[$item->category_of_faculty] ?? 'Other',
                'count' => $item->count
            ];
        });
    
    // ==============================================
    // 4. FACULTY BY DEPARTMENT (for "Faculty by Department" chart)
    // ==============================================
    $departmentStats = Department::withCount(['faculty' => function($query) use ($filters) {
            if ($filters['college'] !== 'all') {
                $query->where('college', $filters['college']);
            }
        }])
        ->when($filters['college'] !== 'all', function($q) use ($filters) {
            $q->where('college_id', $filters['college']);
        })
        ->orderBy('faculty_count', 'desc')
        ->limit(10)
        ->get()
        ->map(function($dept) {
            return [
                'code' => $dept->department_acro,
                'name' => $dept->department,
                'count' => $dept->faculty_count
            ];
        });
    
    // ==============================================
    // 5. PHD DISTRIBUTION BY DEPARTMENT - FIXED LOGIC
    // ==============================================

    // First, get ALL faculty by department for the selected semester
    $allFacultyByDept = DB::connection('ewms')
        ->table('table_faculty_profile as fp')
        ->join('table_faculty_status as fs', 'fp.id', '=', 'fs.f_id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->when(!empty($filters['semester']), function ($q) use ($filters) {
            $q->where('fs.sem_id', $filters['semester'])
              ->where('fs.is_active', 'Yes');
        })
        ->when(isset($filters['college']) && $filters['college'] !== 'all', function ($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->select(
            'd.department_id',
            'd.department_acro',
            DB::raw('COUNT(DISTINCT fp.id) as total_faculty')
        )
        ->groupBy('d.department_id', 'd.department_acro')
        ->get()
        ->keyBy('department_id');

    // Get list of faculty IDs in the selected semester
    $facultyIdsInSemester = DB::connection('ewms')
        ->table('table_faculty_profile as fp')
        ->join('table_faculty_status as fs', 'fp.id', '=', 'fs.f_id')
        ->when(!empty($filters['semester']), function ($q) use ($filters) {
            $q->where('fs.sem_id', $filters['semester'])
              ->where('fs.is_active', 'Yes');
        })
        ->when(isset($filters['college']) && $filters['college'] !== 'all', function ($q) use ($filters) {
            $q->where('fp.college', $filters['college']);
        })
        ->pluck('fp.id')
        ->toArray();

    // Get PhD counts by department (only for faculty in the selected semester)
    $phdCountsByDept = DB::connection('ewms')
        ->table('table_faculty_academic_degree as fad')
        ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->whereIn('fp.id', $facultyIdsInSemester)
        ->whereNotNull('fad.phd_degree_title')
        ->where('fad.phd_degree_title', '!=', '')
        ->select(
            'd.department_id',
            DB::raw('COUNT(DISTINCT fp.id) as phd_count')
        )
        ->groupBy('d.department_id')
        ->get()
        ->keyBy('department_id');

    // Masters counts (only for faculty in the selected semester)
    $mastersCountsByDept = DB::connection('ewms')
        ->table('table_faculty_academic_degree as fad')
        ->join('table_faculty_profile as fp', 'fad.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->whereIn('fp.id', $facultyIdsInSemester)
        ->whereNotNull('fad.ms_degree_title')
        ->where('fad.ms_degree_title', '!=', '')
        ->where(function ($q) {
            $q->whereNull('fad.phd_degree_title')
              ->orWhere('fad.phd_degree_title', '=', '');
        })
        ->select(
            'd.department_id',
            DB::raw('COUNT(DISTINCT fp.id) as masters_count')
        )
        ->groupBy('d.department_id')
        ->get()
        ->keyBy('department_id');

    // Combine the data properly
    $phdByDepartment = collect();

    foreach ($allFacultyByDept as $deptId => $dept) {
        $totalFacultyInDept = $dept->total_faculty;

        $phdCount = $phdCountsByDept->get($deptId)->phd_count ?? 0;
        $mastersCount = $mastersCountsByDept->get($deptId)->masters_count ?? 0;

        // Calculate bachelors only
        $bachelorsCount = max(0, $totalFacultyInDept - $phdCount - $mastersCount);

        $phdByDepartment->push((object)[
            'department_acro'   => $dept->department_acro,
            'total_faculty'     => $totalFacultyInDept,
            'phd_count'         => $phdCount,
            'masters_count'     => $mastersCount,
            'bachelors_count'   => $bachelorsCount,
            'phd_percentage'    => $totalFacultyInDept > 0 
                ? round(($phdCount / $totalFacultyInDept) * 100, 1) 
                : 0
        ]);
    }

    // Sort by PhD count descending and limit to top 10
    $phdByDepartment = $phdByDepartment
        ->sortByDesc('phd_count')
        ->take(10)
        ->values();

    // ==============================================
    // GET FILTER OPTIONS
    // ==============================================

    $semesters = Semester::orderBy('sem_id', 'desc')->get();
    $colleges = CollegeUnit::orderBy('college_unit')->get();

    if (isset($filters['college']) && $filters['college'] !== 'all') {
        $departments = Department::where('college_id', $filters['college'])
            ->orderBy('department')
            ->get();
    } else {
        $departments = Department::orderBy('department')->get();
    }

    return view('stzfaculty.overview', compact(
        'totalFaculty',
        'activeCount',
        'onLeaveCount',
        'phdHolders',
        'mastersHolders',
        'categories',
        'departmentStats',
        'phdByDepartment',
        'filters',
        'semesters',
        'colleges',
        'departments'
    ));
}

// Add this updated method to your DashboardController.php

public function teachingLoad(Request $request)
{
    // Get active semester
    $activeSemester = Semester::where('status', 1)
        ->orderBy('sem_id', 'desc')
        ->first();

    if (!$activeSemester) {
        $activeSemester = Semester::orderBy('sem_id', 'desc')->first();
    }

    // Apply filters
    $filters = [
        'semester' => $request->get('semester', $activeSemester->sem_id ?? null),
        'department' => $request->get('department', 'all'),
        'faculty' => $request->get('faculty', 'all'),
    ];

    // =============================================
    // 1. OVERALL STATISTICS (for stat cards)
    // =============================================
    $overallStats = Summary::query()
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('department', $filters['department']);
            });
        })
        ->when($filters['faculty'] !== 'all', function($q) use ($filters) {
            $q->where('f_id', $filters['faculty']);
        })
        ->selectRaw('
            AVG(actual_atl) as avg_atl,
            COUNT(DISTINCT f_id) as total_faculty
        ')
        ->first();

    $avgAtl = $overallStats->avg_atl ?? 0;
    $totalFaculty = $overallStats->total_faculty ?? 0;

    // Total subjects and students from class schedule
    $classStats = ClassSchedule::query()
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('department_id', $filters['department']);
        })
        ->selectRaw('
            COUNT(DISTINCT subject_title) as total_subjects,
            SUM(no_of_student) as total_students
        ')
        ->first();

    $totalSubjects = $classStats->total_subjects ?? 0;
    $totalStudents = $classStats->total_students ?? 0;

    // =============================================
    // 2. DEPARTMENT STATISTICS (for charts & table)
    // =============================================
    $departmentStats = Summary::query()
        ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->leftJoin('table_class_schedule as cs', function($join) use ($filters) {
            $join->on('d.department_id', '=', 'cs.department_id');
            if ($filters['semester']) {
                $join->where('cs.sem_id', '=', $filters['semester']);
            }
        })
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('table_summary.sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->selectRaw('
            d.department,
            d.department_acro,
            COUNT(DISTINCT fp.id) as faculty_count,
            AVG(table_summary.actual_atl) as avg_atl,
            COUNT(DISTINCT cs.subject_title) as total_subjects,
            SUM(cs.no_of_student) as total_students,
            CASE 
                WHEN COUNT(DISTINCT fp.id) > 0 
                THEN SUM(cs.no_of_student) / COUNT(DISTINCT fp.id)
                ELSE 0 
            END as students_per_faculty,
            CASE 
                WHEN COUNT(DISTINCT fp.id) > 0 
                THEN COUNT(DISTINCT cs.subject_title) / COUNT(DISTINCT fp.id)
                ELSE 0 
            END as subjects_per_faculty
        ')
        ->groupBy('d.department_id', 'd.department', 'd.department_acro')
        ->orderBy('avg_atl', 'desc')
        ->get();

    // =============================================
    // 3. WORKLOAD DISTRIBUTION (for pie chart)
    // =============================================
    $workloadDistribution = Summary::query()
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->whereHas('faculty', function($subQ) use ($filters) {
                $subQ->where('department', $filters['department']);
            });
        })
        ->selectRaw('
            SUM(CASE WHEN actual_atl < 10 THEN 1 ELSE 0 END) as low,
            SUM(CASE WHEN actual_atl >= 10 AND actual_atl < 15 THEN 1 ELSE 0 END) as moderate,
            SUM(CASE WHEN actual_atl >= 15 AND actual_atl < 20 THEN 1 ELSE 0 END) as high,
            SUM(CASE WHEN actual_atl >= 20 THEN 1 ELSE 0 END) as very_high
        ')
        ->first();

    // =============================================
    // 4. FACULTY DETAILS (for detailed table)
    // =============================================
    $facultyDetails = Summary::query()
        ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->leftJoin(DB::raw('(
            SELECT 
                cs.department_id,
                COUNT(DISTINCT cs.subject_title) as subject_count,
                SUM(cs.no_of_student) as total_students,
                AVG(CAST(cs.hours_per_week AS DECIMAL(10,2))) as avg_hours
            FROM table_class_schedule cs
            ' . ($filters['semester'] ? 'WHERE cs.sem_id = ' . $filters['semester'] : '') . '
            GROUP BY cs.department_id
        ) as cs_agg'), 'd.department_id', '=', 'cs_agg.department_id')
        ->when($filters['semester'], function($q) use ($filters) {
            $q->where('table_summary.sem_id', $filters['semester']);
        })
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->when($filters['faculty'] !== 'all', function($q) use ($filters) {
            $q->where('fp.id', $filters['faculty']);
        })
        ->selectRaw('
            fp.id,
            fp.fname,
            fp.lname,
            d.department_acro,
            table_summary.actual_atl,
            COALESCE(cs_agg.subject_count, 0) as subject_count,
            COALESCE(cs_agg.total_students, 0) as total_students,
            COALESCE(cs_agg.avg_hours, 0) as avg_hours
        ')
        ->orderBy('table_summary.actual_atl', 'desc')
        ->limit(50) // Top 50 faculty by ATL
        ->get();

    // =============================================
    // GET FILTER OPTIONS
    // =============================================
    $semesters = Semester::orderBy('sem_id', 'desc')->get();
    $departments = Department::orderBy('department')->get();

    // Get faculty for filter
    $facultyList = FacultyProfile::query()
        ->when($filters['department'] !== 'all', function($q) use ($filters) {
            $q->where('department', $filters['department']);
        })
        ->orderBy('lname')
        ->get(['id', 'fname', 'mname', 'lname']);

    return view('stzfaculty.teaching-load', compact(
        'avgAtl',
        'totalFaculty',
        'totalSubjects',
        'totalStudents',
        'departmentStats',
        'workloadDistribution',
        'facultyDetails',
        'filters',
        'semesters',
        'departments',
        'facultyList'
    ));
}

        // Add this method to your controller
        public function researchPerformance(Request $request)
        {
            // Get active semester
            $activeSemester = Semester::where('status', 1)
                ->orderBy('sem_id', 'desc')
                ->first();
            
            if (!$activeSemester) {
                $activeSemester = Semester::orderBy('sem_id', 'desc')->first();
            }
            
            // Apply filters
            $filters = [
                'semester' => $request->get('semester', $activeSemester->sem_id ?? null),
                'department' => $request->get('department', 'all'),
                'role_type' => $request->get('role_type', 'all'),
                'activity_type' => $request->get('activity_type', 'all'),
            ];
            
            // ======================
            // 1. RESEARCH & DESIGNATION LOAD (ETL)
            // ======================
            $researchLoad = AssignmentInStudentRS::query()
                ->join('table_faculty_profile as fp', 'table_assignment_in_student_rs.f_id', '=', 'fp.id')
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->selectRaw('
                    d.department,
                    d.department_acro,
                    COUNT(DISTINCT fp.id) as faculty_with_research,
                    SUM(table_assignment_in_student_rs.etl) as total_etl,
                    AVG(table_assignment_in_student_rs.etl) as avg_etl,
                    COUNT(DISTINCT table_assignment_in_student_rs.id) as research_count
                ')
                ->when($filters['semester'], function($q) use ($filters) {
                    $q->where('table_assignment_in_student_rs.sem_id', $filters['semester']);
                })
                ->when($filters['department'] !== 'all', function($q) use ($filters) {
                    $q->where('fp.department', $filters['department']);
                })
                ->groupBy('d.department_id', 'd.department', 'd.department_acro')
                ->orderBy('total_etl', 'desc')
                ->get();
            
            // ======================
            // 2. ADMINISTRATIVE & RECOGNIZED ROLES
            // ======================
            $adminRoles = FacultyDesignations::query()
                ->join('table_faculty_profile as fp', 'table_faculty_designations.f_id', '=', 'fp.id')
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->selectRaw('
                    d.department_acro,
                    table_faculty_designations.designation,
                    table_faculty_designations.type,
                    COUNT(DISTINCT fp.id) as faculty_count,
                    SUM(CAST(table_faculty_designations.etl AS DECIMAL(10,2))) as total_etl
                ')
                ->when($filters['semester'], function($q) use ($filters) {
                    $q->where('table_faculty_designations.sem_id', $filters['semester']);
                })
                ->when($filters['department'] !== 'all', function($q) use ($filters) {
                    $q->where('fp.department', $filters['department']);
                })
                ->when($filters['role_type'] !== 'all', function($q) use ($filters) {
                    $q->where('table_faculty_designations.type', $filters['role_type']);
                })
                ->groupBy('d.department_acro', 'table_faculty_designations.designation', 'table_faculty_designations.type')
                ->orderBy('total_etl', 'desc')
                ->get();
            
            // Get unique role types for filter
            $roleTypes = FacultyDesignations::select('type')
                ->distinct()
                ->orderBy('type')
                ->get()
                ->pluck('type')
                ->filter();
            
            // ======================
            // 3. PUBLICATIONS PER FACULTY/DEPARTMENT
            // ======================
            $publications = Publication::query()
                ->join('table_faculty_profile as fp', 'table_publication.f_id', '=', 'fp.id')
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->selectRaw('
                    d.department,
                    d.department_acro,
                    COUNT(table_publication.id) as publication_count,
                    COUNT(DISTINCT fp.id) as faculty_with_publications,
                    GROUP_CONCAT(DISTINCT table_publication.type) as publication_types
                ')
                ->when($filters['semester'], function($q) use ($filters) {
                    // Note: Publications might not have semester_id, adjust based on your schema
                })
                ->when($filters['department'] !== 'all', function($q) use ($filters) {
                    $q->where('fp.department', $filters['department']);
                })
                ->when($filters['activity_type'] !== 'all', function($q) use ($filters) {
                    $q->where('table_publication.type', $filters['activity_type']);
                })
                ->groupBy('d.department_id', 'd.department', 'd.department_acro')
                ->orderBy('publication_count', 'desc')
                ->get();
            
            // Get publication types for filter
            $publicationTypes = Publication::select('type')
                ->distinct()
                ->orderBy('type')
                ->get()
                ->pluck('type')
                ->filter();
            
            // ======================
            // 4. RESEARCH CONSULTATION SCHEDULES
            // ======================
            $researchSchedules = DB::connection('ewms')->table('table_schedule_of_student_research_consultation')
                ->join('table_faculty_profile as fp', 'table_schedule_of_student_research_consultation.f_id', '=', 'fp.id')
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->selectRaw('
                    d.department_acro,
                    COUNT(DISTINCT fp.id) as consulting_faculty,
                    GROUP_CONCAT(DISTINCT table_schedule_of_student_research_consultation.days) as consultation_days
                ')
                ->when($filters['semester'], function($q) use ($filters) {
                    $q->where('table_schedule_of_student_research_consultation.sem_id', $filters['semester']);
                })
                ->groupBy('d.department_acro')
                ->get();
            
            // ======================
            // GET FILTER OPTIONS
            // ======================
            $semesters = Semester::orderBy('sem_id', 'desc')->get();
            $departments = Department::orderBy('department')->get();
            
            // Get unique activity types
            $activityTypes = collect(['Research', 'Instruction', 'Admin', 'Extension', 'Production'])
                ->map(function($type) {
                    return (object)['type' => $type];
                });
            
            return view('stzfaculty.research-performance', compact(
                'researchLoad',
                'adminRoles',
                'publications',
                'researchSchedules',
                'filters',
                'semesters',
                'departments',
                'roleTypes',
                'publicationTypes',
                'activityTypes'
            ));
        }
}