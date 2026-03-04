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
        
        if (!$activeSemester) {
            $activeSemester = Semester::orderBy('sem_id', 'desc')->first();
        }
        
        // Apply filters
        $filters = [
            'semester' => $request->get('semester', $activeSemester->sem_id ?? null),
            'sector' => $request->get('sector', 'Academic'),
            'college' => $request->get('college', 'all'),
            'department' => $request->get('department', 'all'),
        ];
        
        // ==============================================
        // 1. GET ALL FACULTY FROM PROFILE TABLE
        // ==============================================
        
        // Get all faculty with their string employee IDs for joining
        $allFaculty = DB::connection('ewms')
            ->table('table_faculty_profile as fp')
            ->join('table_faculty_status as fs', function($join) use ($filters) {
                $join->on('fp.id', '=', 'fs.f_id')
                     ->where('fs.sem_id', $filters['semester'])
                     ->where('fs.is_active', 'Yes');
            })
            ->when($filters['college'] !== 'all', function($q) use ($filters) {
                $q->where('fp.college', $filters['college']);
            })
            ->when($filters['department'] !== 'all', function($q) use ($filters) {
                $q->where('fp.department', $filters['department']);
            })
            ->select('fp.id', 'fp.employeeID', 'fp.fname', 'fp.lname', 'fp.college', 'fp.department')
            ->get();
        
        // Create a mapping of employeeID to faculty ID
        $employeeIdMap = [];
        foreach ($allFaculty as $faculty) {
            if (!empty($faculty->employeeID)) {
                $employeeIdMap[trim($faculty->employeeID)] = $faculty->id;
            }
        }
        
        // ==============================================
        // 2. GET ALL DESIGNATIONS
        // ==============================================
        
        // Get all designations for this semester
        $allDesignations = DB::connection('ewms')
            ->table('table_faculty_designations')
            ->where('sem_id', $filters['semester'])
            ->get();
        
        // Group designations by employee ID (f_id is string)
        $designationsByEmployeeId = [];
        foreach ($allDesignations as $designation) {
            $empId = trim($designation->f_id);
            if (!isset($designationsByEmployeeId[$empId])) {
                $designationsByEmployeeId[$empId] = [];
            }
            $designationsByEmployeeId[$empId][] = $designation;
        }
        
        // ==============================================
        // 3. FILTER FACULTY BY SECTOR
        // ==============================================
        
        $filteredFacultyIds = [];
        
        foreach ($allFaculty as $faculty) {
            // Skip if no employeeID
            if (empty($faculty->employeeID)) continue;
            
            $empId = trim($faculty->employeeID);
            $facultyDesignations = $designationsByEmployeeId[$empId] ?? [];
            $designationTypes = array_unique(array_column($facultyDesignations, 'type'));
            
            // Clean up types (remove spaces)
            $designationTypes = array_map('trim', $designationTypes);
            
            switch ($filters['sector']) {
                case 'Academic':
                    // Include if has Academic designation OR (no designations and from academic college)
                    if (in_array('Academic', $designationTypes)) {
                        $filteredFacultyIds[] = $faculty->id;
                    } elseif (empty($facultyDesignations)) {
                        // Check if from academic college (you can define this list)
                        $academicColleges = [1,2,3,4,5,6,7,8,9]; // Adjust based on your college IDs
                        if (in_array($faculty->college, $academicColleges)) {
                            $filteredFacultyIds[] = $faculty->id;
                        }
                    }
                    break;
                    
                case 'Research':
                    if (in_array('Research', $designationTypes)) {
                        $filteredFacultyIds[] = $faculty->id;
                    }
                    break;
                    
                case 'Admin':
                    if (in_array('Admin', $designationTypes)) {
                        $filteredFacultyIds[] = $faculty->id;
                    }
                    break;
                    
                case 'Others':
                    if (in_array('Others', $designationTypes)) {
                        $filteredFacultyIds[] = $faculty->id;
                    }
                    break;
                    
                default:
                    $filteredFacultyIds[] = $faculty->id;
            }
        }
        
// ==============================================
// 4. BASIC STATISTICS - FIXED
// ==============================================

// Active faculty count (already filtered by sector and is_active='Yes')
$activeFacultyIds = $filteredFacultyIds; // These are already filtered by sector
$activeCount = count($activeFacultyIds);

// Get on leave faculty IDs (filtered by sector but is_active='No')
$onLeaveFaculty = DB::connection('ewms')
    ->table('table_faculty_profile as fp')
    ->join('table_faculty_status as fs', function($join) use ($filters) {
        $join->on('fp.id', '=', 'fs.f_id')
             ->where('fs.sem_id', $filters['semester'])
             ->where('fs.is_active', 'No');
    })
    ->when($filters['college'] !== 'all', function($q) use ($filters) {
        $q->where('fp.college', $filters['college']);
    })
    ->when($filters['department'] !== 'all', function($q) use ($filters) {
        $q->where('fp.department', $filters['department']);
    })
    ->select('fp.id', 'fp.employeeID')
    ->get();

$onLeaveEmployeeMap = [];
foreach ($onLeaveFaculty as $f) {
    if (!empty($f->employeeID)) {
        $onLeaveEmployeeMap[trim($f->employeeID)] = $f->id;
    }
}

// Filter on leave by sector
$filteredOnLeaveIds = [];
foreach ($onLeaveFaculty as $faculty) {
    if (empty($faculty->employeeID)) continue;
    
    $empId = trim($faculty->employeeID);
    $facultyDesignations = $designationsByEmployeeId[$empId] ?? [];
    $designationTypes = array_unique(array_column($facultyDesignations, 'type'));
    $designationTypes = array_map('trim', $designationTypes);
    
    switch ($filters['sector']) {
        case 'Academic':
            if (in_array('Academic', $designationTypes)) {
                $filteredOnLeaveIds[] = $faculty->id;
            }
            break;
        case 'Research':
            if (in_array('Research', $designationTypes)) {
                $filteredOnLeaveIds[] = $faculty->id;
            }
            break;
        case 'Admin':
            if (in_array('Admin', $designationTypes)) {
                $filteredOnLeaveIds[] = $faculty->id;
            }
            break;
        case 'Others':
            if (in_array('Others', $designationTypes)) {
                $filteredOnLeaveIds[] = $faculty->id;
            }
            break;
    }
}

$onLeaveCount = count($filteredOnLeaveIds);

// TOTAL FACULTY = Active + On Leave (both filtered by sector)
$totalFaculty = $activeCount + $onLeaveCount;

// Keep $filteredFacultyIds for other queries that need only active faculty
// $filteredFacultyIds remains unchanged for other sections

// ==============================================
// 5. QUALIFICATION DATA - FIXED
// ==============================================

// Get ALL faculty in the semester (both active AND on leave) for qualification stats
$allFacultyInSemesterIds = DB::connection('ewms')
    ->table('table_faculty_profile as fp')
    ->join('table_faculty_status as fs', function($join) use ($filters) {
        $join->on('fp.id', '=', 'fs.f_id')
             ->where('fs.sem_id', $filters['semester']);
        // REMOVE the is_active filter - we want ALL faculty for qualifications
    })
    ->when($filters['college'] !== 'all', function($q) use ($filters) {
        $q->where('fp.college', $filters['college']);
    })
    ->when($filters['department'] !== 'all', function($q) use ($filters) {
        $q->where('fp.department', $filters['department']);
    })
    ->pluck('fp.id')
    ->toArray();

// Also get active faculty IDs for reference
$activeFacultyIds = $filteredFacultyIds; // These are already filtered by sector and is_active='Yes'

// Get on leave faculty IDs (already filtered by sector)
$onLeaveFacultyIds = $filteredOnLeaveIds;

// Total faculty should be active + on leave
$totalFaculty = count($activeFacultyIds) + count($onLeaveFacultyIds);
$activeCount = count($activeFacultyIds);
$onLeaveCount = count($onLeaveFacultyIds);

// Now calculate PhD and Masters holders from ALL faculty (active + on leave)
$phdHolders = DB::connection('ewms')
    ->table('table_faculty_academic_degree as fad')
    ->whereIn('fad.f_id', $allFacultyInSemesterIds) // Use ALL faculty IDs
    ->whereNotNull('fad.phd_degree_title')
    ->where('fad.phd_degree_title', '!=', '')
    ->where('fad.phd_degree_title', '!=', 'N/A')
    ->distinct()
    ->count('fad.f_id');

$mastersHolders = DB::connection('ewms')
    ->table('table_faculty_academic_degree as fad')
    ->whereIn('fad.f_id', $allFacultyInSemesterIds) // Use ALL faculty IDs
    ->whereNotNull('fad.ms_degree_title')
    ->where('fad.ms_degree_title', '!=', '')
    ->where('fad.ms_degree_title', '!=', 'N/A')
    ->where(function($q) {
        $q->whereNull('fad.phd_degree_title')
          ->orWhere('fad.phd_degree_title', '=', '')
          ->orWhere('fad.phd_degree_title', '=', 'N/A');
    })
    ->distinct()
    ->count('fad.f_id');   
    
    // ==============================================
// 6. FACULTY CATEGORIES (Employment Status) - ADD THIS
// ==============================================

$categories = DB::connection('ewms')
    ->table('table_faculty_status as fs')
    ->join('table_faculty_profile as fp', 'fs.f_id', '=', 'fp.id')
    ->where('fs.sem_id', $filters['semester'])
    ->where('fs.is_active', 'Yes')
    ->whereIn('fp.id', $filteredFacultyIds)
    ->selectRaw('fs.category_of_faculty, COUNT(DISTINCT fp.id) as count')
    ->groupBy('fs.category_of_faculty')
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
// 7. SECTOR DISTRIBUTION (for the new donut chart)
// ==============================================

// Get sector distribution from ALL faculty (not filtered by sector)
$activeFaculty = DB::connection('ewms')
    ->table('table_faculty_status')
    ->where('sem_id', $filters['semester'])
    ->where('is_active', 'Yes')
    ->pluck('f_id');

$sectorDistribution = DB::connection('ewms')
    ->table('table_faculty_profile as fp')
    ->join('table_faculty_designations as fd', 'fp.employeeID', '=', 'fd.f_id')
    ->whereIn('fp.id', $activeFaculty)
    ->when($filters['college'] !== 'all', function($q) use ($filters) {
        $q->where('fp.college', $filters['college']);
    })
    ->when($filters['department'] !== 'all', function($q) use ($filters) {
        $q->where('fp.department', $filters['department']);
    })
    ->select('fd.type', DB::raw('COUNT(DISTINCT fp.id) as count'))
    ->groupBy('fd.type')
    ->get()
    ->mapWithKeys(function($item) {
        return [trim($item->type) => $item->count];
    })
    ->toArray();// Ensure all sectors exist even if count is 0
$sectorDistribution = array_merge([
    'Academic' => 0,
    'Research' => 0,
    'Admin' => 0,
    'Others' => 0
], $sectorDistribution);

// Total for center of donut
$totalSectorFaculty = array_sum($sectorDistribution);
        
        // ==============================================
        // 8. FACULTY COUNT RANKING
        // ==============================================
        
        if ($filters['college'] !== 'all' && $filters['department'] !== 'all') {
            // Department level - show departments of that college with highlight
            $rankingData = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id', $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->whereIn('fp.id', $filteredFacultyIds)
                ->where('fp.college', $filters['college'])
                ->select(
                    'd.department_acro',
                    'd.department_id',
                    DB::raw('COUNT(DISTINCT fp.id) as total_faculty')
                )
                ->groupBy('d.department_id', 'd.department_acro')
                ->orderByDesc('total_faculty')
                ->get();
                
            // Add department_acro to filters for highlighting
            if ($filters['department'] !== 'all') {
                $deptInfo = DB::connection('ewms')
                    ->table('table_department')
                    ->where('department_id', $filters['department'])
                    ->first();
                $filters['department_acro'] = $deptInfo->department_acro ?? '';
            }
        } 
        elseif ($filters['college'] !== 'all') {
            // College level - show departments
            $rankingData = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id', $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->join('table_department as d', 'fp.department', '=', 'd.department_id')
                ->whereIn('fp.id', $filteredFacultyIds)
                ->where('fp.college', $filters['college'])
                ->select(
                    'd.department_acro',
                    DB::raw('COUNT(DISTINCT fp.id) as total_faculty')
                )
                ->groupBy('d.department_id', 'd.department_acro')
                ->orderByDesc('total_faculty')
                ->get();
        } 
        else {
            // All offices - show colleges
            $rankingData = DB::connection('ewms')
                ->table('table_faculty_profile as fp')
                ->join('table_faculty_status as fs', function($join) use ($filters) {
                    $join->on('fp.id', '=', 'fs.f_id')
                         ->where('fs.sem_id', $filters['semester'])
                         ->where('fs.is_active', 'Yes');
                })
                ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
                ->whereIn('fp.id', $filteredFacultyIds)
                ->select(
                    'cu.college_acro as college',
                    DB::raw('COUNT(DISTINCT fp.id) as total_faculty')
                )
                ->groupBy('cu.college_acro')
                ->orderByDesc('total_faculty')
                ->get();
                
            // Rename to match expected variable name in view
            $collegeStats = $rankingData;
        }
        
// ==============================================
// 9. QUALIFICATION DISTRIBUTION BY DEPARTMENT (Stacked Bar) - FIXED (WORKING VERSION)
// ==============================================

// Get faculty by department for qualification distribution - FILTERED BY SECTOR
$facultyByDept = DB::connection('ewms')
    ->table('table_faculty_profile as fp')
    ->join('table_faculty_status as fs', function($join) use ($filters) {
        $join->on('fp.id', '=', 'fs.f_id')
             ->where('fs.sem_id', $filters['semester'])
             ->where('fs.is_active', 'Yes');
    })
    ->join('table_department as d', 'fp.department', '=', 'd.department_id')
    ->whereIn('fp.id', $filteredFacultyIds)  // ← CRITICAL: Filter by sector
    ->when($filters['college'] !== 'all', function($q) use ($filters) {
        $q->where('fp.college', $filters['college']);
    })
    ->select(
        'd.department_id',
        'd.department_acro',
        'd.department',
        DB::raw('COUNT(DISTINCT fp.id) as total_faculty')
    )
    ->groupBy('d.department_id', 'd.department_acro', 'd.department')
    ->get();

$phdByDepartment = collect();

foreach ($facultyByDept as $dept) {
    // Get faculty IDs for this department - FILTERED BY SECTOR
    $deptFacultyIds = DB::connection('ewms')
        ->table('table_faculty_profile as fp')
        ->join('table_faculty_status as fs', function($join) use ($filters) {
            $join->on('fp.id', '=', 'fs.f_id')
                 ->where('fs.sem_id', $filters['semester'])
                 ->where('fs.is_active', 'Yes');
        })
        ->whereIn('fp.id', $filteredFacultyIds)  // ← CRITICAL: Filter by sector
        ->where('fp.department', $dept->department_id)
        ->pluck('fp.id')
        ->toArray();
    
    if (empty($deptFacultyIds)) {
        continue;
    }
    
    // PhD count
    $phdCount = DB::connection('ewms')
        ->table('table_faculty_academic_degree as fad')
        ->whereIn('fad.f_id', $deptFacultyIds)
        ->whereNotNull('fad.phd_degree_title')
        ->where('fad.phd_degree_title', '!=', '')
        ->where('fad.phd_degree_title', '!=', 'N/A')
        ->distinct()
        ->count('fad.f_id');
    
    // Masters count (without PhD)
    $mastersCount = DB::connection('ewms')
        ->table('table_faculty_academic_degree as fad')
        ->whereIn('fad.f_id', $deptFacultyIds)
        ->whereNotNull('fad.ms_degree_title')
        ->where('fad.ms_degree_title', '!=', '')
        ->where('fad.ms_degree_title', '!=', 'N/A')
        ->where(function($q) {
            $q->whereNull('fad.phd_degree_title')
              ->orWhere('fad.phd_degree_title', '=', '')
              ->orWhere('fad.phd_degree_title', '=', 'N/A');
        })
        ->distinct()
        ->count('fad.f_id');
    
    // Get faculty with any degree record
    $facultyWithDegree = DB::connection('ewms')
        ->table('table_faculty_academic_degree')
        ->whereIn('f_id', $deptFacultyIds)
        ->distinct()
        ->pluck('f_id')
        ->toArray();
    
    // Calculate bachelors (faculty with degree but not PhD or Masters)
    $bachelorsCount = 0;
    foreach ($deptFacultyIds as $fid) {
        if (!in_array($fid, $facultyWithDegree)) {
            // No degree record
            continue;
        }
        // Check if not PhD and not Masters
        $degree = DB::connection('ewms')
            ->table('table_faculty_academic_degree')
            ->where('f_id', $fid)
            ->first();
            
        $hasPhD = !empty($degree->phd_degree_title) && $degree->phd_degree_title !== 'N/A' && $degree->phd_degree_title !== '';
        $hasMasters = !$hasPhD && !empty($degree->ms_degree_title) && $degree->ms_degree_title !== 'N/A' && $degree->ms_degree_title !== '';
        
        if (!$hasPhD && !$hasMasters && !empty($degree->degree_title) && $degree->degree_title !== 'N/A' && $degree->degree_title !== '') {
            $bachelorsCount++;
        }
    }
    
    // No degree count
    $noDegreeCount = count($deptFacultyIds) - count($facultyWithDegree);
    
    $total = $dept->total_faculty;
    
    $phdByDepartment->push((object)[
        'department_id' => $dept->department_id,
        'department_acro' => $dept->department_acro,
        'department' => $dept->department,
        'total_faculty' => $total,
        'phd_count' => $phdCount,
        'masters_count' => $mastersCount,
        'bachelors_count' => $bachelorsCount + $noDegreeCount, // Combine bachelors and no degree or separate as needed
        'phd_percentage' => $total > 0 ? round(($phdCount / $total) * 100, 1) : 0,
        'masters_percentage' => $total > 0 ? round(($mastersCount / $total) * 100, 1) : 0,
        'bachelors_percentage' => $total > 0 ? round((($bachelorsCount + $noDegreeCount) / $total) * 100, 1) : 0
    ]);
}

$phdByDepartment = $phdByDepartment->sortByDesc('total_faculty')->values();
        // ==============================================
        // 10. GENERATE DYNAMIC TITLES
        // ==============================================
        
        // Get selected semester text
        $selectedSemester = Semester::find($filters['semester']);
        $semesterText = $selectedSemester ? $selectedSemester->semester . ' ' . $selectedSemester->sy : '';
        
        // Get selected college/department names
        $selectedCollege = null;
        $selectedDepartment = null;
        
        if ($filters['college'] !== 'all') {
            $selectedCollege = CollegeUnit::find($filters['college']);
        }
        
        if ($filters['department'] !== 'all') {
            $selectedDepartment = Department::find($filters['department']);
        }
        
        // Generate main title
        if ($filters['sector'] === 'Academic') {
            if ($filters['college'] !== 'all' && $filters['department'] !== 'all') {
                $mainTitle = $selectedDepartment->department_acro . ' Faculty Profile (' . $semesterText . ')';
            } elseif ($filters['college'] !== 'all') {
                $mainTitle = $selectedCollege->college_acro . ' Faculty Profile (' . $semesterText . ')';
            } else {
                $mainTitle = 'Academic Faculty Profile (' . $semesterText . ')';
            }
        } else {
            $mainTitle = $filters['sector'] . ' Faculty Profile (' . $semesterText . ')';
        }
        
        // ==============================================
        // GET FILTER OPTIONS
        // ==============================================
        
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
        
        // Initialize with defaults if null
        $collegeStats = $collegeStats ?? collect();
        $rankingData = $rankingData ?? collect();
        $phdByDepartment = $phdByDepartment ?? collect();
        
        return view('stzfaculty.overview', compact(
            'totalFaculty',
            'activeCount',
            'onLeaveCount',
            'phdHolders',
            'mastersHolders',
            'categories',
            'collegeStats',
            'rankingData',
            'phdByDepartment',
            'filters',
            'semesters',
            'colleges',
            'departments',
            'mainTitle',
            'selectedCollege',
            'selectedDepartment',
            'semesterText',
            'sectorDistribution',
            'totalSectorFaculty'
        ));
    }

// Add this updated method to your DashboardController.php
public function teachingLoad(Request $request)
{
    // ---------------------------------------------
    // 0. Default Academic Colleges
    // ---------------------------------------------
    $academicCollegeAcros = ['CED', 'CASS', 'CAG', 'CEN', 'COS', 'CVSM', 'CHSI', 'CBA', 'CF'];

    // ---------------------------------------------
    // 1. Active Semester
    // ---------------------------------------------
    $activeSemester = Semester::where('status', 1)
        ->orderBy('sem_id', 'desc')
        ->first();

    if (!$activeSemester) {
        $activeSemester = Semester::orderBy('sem_id', 'desc')->first();
    }

    $filters = [
        'semester'   => $request->get('semester', $activeSemester->sem_id ?? null),
        'college'    => $request->get('college', 'all'),
        'department' => $request->get('department', 'all'),
    ];

    $drillDown = $filters['college'] !== 'all';

    // ---------------------------------------------
    // 2. Overall Stats (stat cards)
    // ---------------------------------------------
    $overallStats = Summary::query()
        ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
        ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
        ->when($filters['semester'], fn($q) => $q->where('table_summary.sem_id', $filters['semester']))
        ->when(!$drillDown, fn($q) => $q->whereIn('cu.college_acro', $academicCollegeAcros))
        ->when($drillDown, fn($q) => $q->where('fp.college', $filters['college']))
        ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
        ->selectRaw('AVG(table_summary.actual_atl) as avg_atl, COUNT(DISTINCT table_summary.f_id) as total_faculty')
        ->first();

    $avgAtl       = $overallStats->avg_atl ?? 0;
    $totalFaculty = $overallStats->total_faculty ?? 0;

    // ---------------------------------------------
    // 3. Class Stats
    // ---------------------------------------------
    $classStats = ClassSchedule::query()
        ->when($filters['semester'], fn($q) => $q->where('sem_id', $filters['semester']))
        ->when(!$drillDown, fn($q) =>
            $q->whereHas('department.college', fn($sq) => $sq->whereIn('college_acro', $academicCollegeAcros))
        )
        ->when($drillDown, fn($q) =>
            $q->whereHas('department', fn($sq) => $sq->where('college_id', $filters['college']))
        )
        ->when($filters['department'] !== 'all', fn($q) => $q->where('department_id', $filters['department']))
        ->selectRaw('COUNT(DISTINCT subject_title) as total_subjects, SUM(no_of_student) as total_students')
        ->first();

    $totalSubjects = $classStats->total_subjects ?? 0;
    $totalStudents = $classStats->total_students ?? 0;

    // ---------------------------------------------
    // 4. Chart Data
    // ---------------------------------------------
    if (!$drillDown) {
        // Non-drilldown: Group by College
        $chartStats = Summary::query()
            ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
            ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
            ->leftJoin(DB::raw("
                (
                    SELECT department_id,
                           COUNT(DISTINCT sched_id) AS total_subjects,
                           SUM(no_of_student) AS total_students
                    FROM table_class_schedule
                    WHERE sem_id = {$filters['semester']}
                    GROUP BY department_id
                ) AS cs
            "), 'fp.department', '=', 'cs.department_id')
            ->when($filters['semester'], fn($q) => $q->where('table_summary.sem_id', $filters['semester']))
            ->whereIn('cu.college_acro', $academicCollegeAcros)
            ->selectRaw('
                cu.c_u_id          AS group_id,
                cu.college_acro    AS group_label,
                COUNT(DISTINCT fp.id)        AS faculty_count,
                AVG(table_summary.actual_atl) AS avg_atl,
                COALESCE(SUM(cs.total_subjects), 0) AS total_subjects,
                COALESCE(SUM(cs.total_students), 0) AS total_students,
                SUM(CASE WHEN table_summary.actual_atl < 10 THEN 1 ELSE 0 END) AS low,
                SUM(CASE WHEN table_summary.actual_atl >= 10 AND table_summary.actual_atl < 15 THEN 1 ELSE 0 END) AS moderate,
                SUM(CASE WHEN table_summary.actual_atl >= 15 AND table_summary.actual_atl < 20 THEN 1 ELSE 0 END) AS high,
                SUM(CASE WHEN table_summary.actual_atl >= 20 THEN 1 ELSE 0 END) AS very_high
            ')
            ->groupBy('cu.c_u_id', 'cu.college_acro')
            ->having('faculty_count', '>', 0)
            ->orderBy('avg_atl', 'desc')
            ->get();

        $chartGroupLabel = 'College';
    } else {
        // Drilldown: Group by Department
        $chartStats = Summary::query()
            ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
            ->join('table_department as d', 'fp.department', '=', 'd.department_id')
            ->leftJoin(DB::raw("
                (
                    SELECT department_id,
                           COUNT(DISTINCT sched_id) AS total_subjects,
                           SUM(no_of_student) AS total_students
                    FROM table_class_schedule
                    WHERE sem_id = {$filters['semester']}
                    GROUP BY department_id
                ) AS cs
            "), 'fp.department', '=', 'cs.department_id')
            ->when($filters['semester'], fn($q) => $q->where('table_summary.sem_id', $filters['semester']))
            ->where('fp.college', $filters['college'])
            ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
            ->selectRaw('
                d.department_id    AS group_id,
                d.department_acro  AS group_label,
                COUNT(DISTINCT fp.id)                              AS faculty_count,
                AVG(table_summary.actual_atl)                     AS avg_atl,
                COALESCE(cs.total_subjects, 0) AS total_subjects,
                COALESCE(cs.total_students, 0) AS total_students,
                SUM(CASE WHEN table_summary.actual_atl < 10 THEN 1 ELSE 0 END) AS low,
                SUM(CASE WHEN table_summary.actual_atl >= 10 AND table_summary.actual_atl < 15 THEN 1 ELSE 0 END) AS moderate,
                SUM(CASE WHEN table_summary.actual_atl >= 15 AND table_summary.actual_atl < 20 THEN 1 ELSE 0 END) AS high,
                SUM(CASE WHEN table_summary.actual_atl >= 20 THEN 1 ELSE 0 END) AS very_high
            ')
            ->groupBy('d.department_id', 'd.department_acro')
            ->having('faculty_count', '>', 0)
            ->orderBy('avg_atl', 'desc')
            ->get();

        $chartGroupLabel = 'Department';
    }

    // ---------------------------------------------
    // 5. Workload Distribution
    // ---------------------------------------------
    $workloadDistribution = Summary::query()
        ->join('table_faculty_profile as fp', 'table_summary.f_id', '=', 'fp.id')
        ->join('table_college_unit as cu', 'fp.college', '=', 'cu.c_u_id')
        ->when($filters['semester'], fn($q) => $q->where('table_summary.sem_id', $filters['semester']))
        ->when(!$drillDown, fn($q) => $q->whereIn('cu.college_acro', $academicCollegeAcros))
        ->when($drillDown, fn($q) => $q->where('fp.college', $filters['college']))
        ->when($filters['department'] !== 'all', fn($q) => $q->where('fp.department', $filters['department']))
        ->selectRaw('
            SUM(CASE WHEN table_summary.actual_atl < 10 THEN 1 ELSE 0 END) AS low,
            SUM(CASE WHEN table_summary.actual_atl >= 10 AND table_summary.actual_atl < 15 THEN 1 ELSE 0 END) AS moderate,
            SUM(CASE WHEN table_summary.actual_atl >= 15 AND table_summary.actual_atl < 20 THEN 1 ELSE 0 END) AS high,
            SUM(CASE WHEN table_summary.actual_atl >= 20 THEN 1 ELSE 0 END) AS very_high
        ')
        ->first();

    // ---------------------------------------------
    // 6. Filter Options
    // ---------------------------------------------
    $semesters = Semester::orderBy('sem_id', 'desc')->get();
    $colleges = CollegeUnit::orderBy('college_acro')->get();
    $departments = $drillDown
        ? Department::where('college_id', $filters['college'])->orderBy('department')->get()
        : collect();

    $selectedCollege = $drillDown
        ? CollegeUnit::find($filters['college'])
        : null;

    // ---------------------------------------------
    // 7. Return View
    // ---------------------------------------------
    return view('stzfaculty.teaching-load', compact(
        'avgAtl',
        'totalFaculty',
        'totalSubjects',
        'totalStudents',
        'chartStats',
        'chartGroupLabel',
        'workloadDistribution',
        'filters',
        'semesters',
        'colleges',
        'departments',
        'selectedCollege',
        'drillDown'
    ));
}

// Paste this method inside your controller class (replace the existing researchPerformance method)

public function researchPerformance(Request $request)
{
    // ── Active semester (default filter) ──────────────────────────────────
    $activeSemester = Semester::where('status', 1)
        ->orderBy('sem_id', 'desc')
        ->first();

    if (!$activeSemester) {
        $activeSemester = Semester::orderBy('sem_id', 'desc')->first();
    }

    // ── Collect filter values ──────────────────────────────────────────────
    // 'semester'   → sem_id (default: active semester)
    // 'department' → department_id  (maps to Unit/Office in the UI)
    // 'role_type'  → Sector: Academic | Research | Admin | Others | all
    $filters = [
        'semester'      => $request->get('semester',  $activeSemester->sem_id ?? null),
        'department'    => $request->get('department', 'all'),
        'role_type'     => $request->get('role_type',  'all'),
        'activity_type' => $request->get('activity_type', 'all'),
    ];

    // ══════════════════════════════════════════════════════════════════════
    // 1. RESEARCH & DESIGNATION LOAD (ETL)
    //    Filters applied: semester, department
    //    NOTE: role_type / sector does NOT filter research load —
    //          that field lives on table_faculty_designations, not here.
    // ══════════════════════════════════════════════════════════════════════
    $researchLoad = AssignmentInStudentRS::query()
        ->join('table_faculty_profile as fp', 'table_assignment_in_student_rs.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->selectRaw('
            d.department,
            d.department_acro,
            COUNT(DISTINCT fp.id)                          AS faculty_with_research,
            SUM(table_assignment_in_student_rs.etl)        AS total_etl,
            AVG(table_assignment_in_student_rs.etl)        AS avg_etl,
            COUNT(DISTINCT table_assignment_in_student_rs.id) AS research_count
        ')
        // ── Semester filter ──────────────────────────────────────────────
        ->when($filters['semester'] && $filters['semester'] !== 'all', function ($q) use ($filters) {
            $q->where('table_assignment_in_student_rs.sem_id', $filters['semester']);
        })
        // ── Unit/Office (department) filter ──────────────────────────────
        ->when($filters['department'] !== 'all', function ($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        ->groupBy('d.department_id', 'd.department', 'd.department_acro')
        ->orderBy('total_etl', 'desc')
        ->get();

    // ══════════════════════════════════════════════════════════════════════
    // 2. ADMINISTRATIVE & RECOGNIZED ROLES (Designations)
    //    Filters applied: semester, department, role_type (Sector)
    // ══════════════════════════════════════════════════════════════════════
    $adminRoles = FacultyDesignations::query()
        ->join('table_faculty_profile as fp', 'table_faculty_designations.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->selectRaw('
            d.department_acro,
            table_faculty_designations.designation,
            table_faculty_designations.type,
            COUNT(DISTINCT fp.id)                                               AS faculty_count,
            SUM(CAST(table_faculty_designations.etl AS DECIMAL(10,2)))          AS total_etl
        ')
        // ── Semester filter ──────────────────────────────────────────────
        ->when($filters['semester'] && $filters['semester'] !== 'all', function ($q) use ($filters) {
            $q->where('table_faculty_designations.sem_id', $filters['semester']);
        })
        // ── Unit/Office (department) filter ──────────────────────────────
        ->when($filters['department'] !== 'all', function ($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        // ── Sector (role_type) filter ─────────────────────────────────────
        ->when($filters['role_type'] !== 'all', function ($q) use ($filters) {
            $q->where('table_faculty_designations.type', $filters['role_type']);
        })
        ->groupBy('d.department_acro', 'table_faculty_designations.designation', 'table_faculty_designations.type')
        ->orderBy('total_etl', 'desc')
        ->get();

    // ══════════════════════════════════════════════════════════════════════
    // 3. PUBLICATIONS PER FACULTY / DEPARTMENT
    //    Filters applied: department, activity_type
    //    NOTE: table_publication has no sem_id — semester filter skipped here.
    // ══════════════════════════════════════════════════════════════════════
    $publications = Publication::query()
        ->join('table_faculty_profile as fp', 'table_publication.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->selectRaw('
            d.department,
            d.department_acro,
            COUNT(table_publication.id)       AS publication_count,
            COUNT(DISTINCT fp.id)             AS faculty_with_publications,
            GROUP_CONCAT(DISTINCT table_publication.type) AS publication_types
        ')
        // ── Unit/Office (department) filter ──────────────────────────────
        ->when($filters['department'] !== 'all', function ($q) use ($filters) {
            $q->where('fp.department', $filters['department']);
        })
        // ── Activity type filter ──────────────────────────────────────────
        ->when($filters['activity_type'] !== 'all', function ($q) use ($filters) {
            $q->where('table_publication.type', $filters['activity_type']);
        })
        ->groupBy('d.department_id', 'd.department', 'd.department_acro')
        ->orderBy('publication_count', 'desc')
        ->get();

    // ══════════════════════════════════════════════════════════════════════
    // 4. RESEARCH CONSULTATION SCHEDULES
    //    Filters applied: semester
    // ══════════════════════════════════════════════════════════════════════
    $researchSchedules = DB::connection('ewms')
        ->table('table_schedule_of_student_research_consultation')
        ->join('table_faculty_profile as fp', 'table_schedule_of_student_research_consultation.f_id', '=', 'fp.id')
        ->join('table_department as d', 'fp.department', '=', 'd.department_id')
        ->selectRaw('
            d.department_acro,
            COUNT(DISTINCT fp.id) AS consulting_faculty,
            GROUP_CONCAT(DISTINCT table_schedule_of_student_research_consultation.days) AS consultation_days
        ')
        ->when($filters['semester'] && $filters['semester'] !== 'all', function ($q) use ($filters) {
            $q->where('table_schedule_of_student_research_consultation.sem_id', $filters['semester']);
        })
        ->groupBy('d.department_acro')
        ->get();

    // ── Filter dropdowns data ──────────────────────────────────────────────
    $semesters   = Semester::orderBy('sem_id', 'desc')->get();
    // Departments = Unit/Office list, sorted alphabetically by acronym for the dropdown
    $departments = Department::orderBy('department_acro')->get();

    // Publication types (for future use if you add that filter back)
    $publicationTypes = Publication::select('type')
        ->distinct()
        ->orderBy('type')
        ->get()
        ->pluck('type')
        ->filter();

    // roleTypes is no longer dynamic — Sector options are hardcoded in the blade
    // (Academic | Research | Admin | Others) but we still pass it for safety
    $roleTypes = collect(['Academic', 'Research', 'Admin', 'Others']);

    $activityTypes = collect(['Research', 'Instruction', 'Admin', 'Extension', 'Production'])
        ->map(fn($type) => (object)['type' => $type]);

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
   
            //wertyuioplkjhgfdsdfghj,mnbvcxchjgfghjk
        // Add this method to your DashboardController.php

public function facultyApproval(Request $request)
{
    // =============================================
    // 1. GET ACTIVE SEMESTER & FILTERS
    // =============================================
    $activeSemester = Semester::where('status', 1)
        ->orderBy('sem_id', 'desc')
        ->first();
    
    if (!$activeSemester) {
        $activeSemester = Semester::orderBy('sem_id', 'desc')->first();
    }
    
    // Apply filters
    $filters = [
        'main_semester' => $request->get('main_semester', $activeSemester->sem_id ?? null),
        'main_signatory' => $request->get('main_signatory', null),
        'timeline_signatory' => $request->get('timeline_signatory', null),
    ];
    
    // =============================================
    // 2. GET AVAILABLE SEMESTERS FOR DROPDOWN
    // =============================================
    $availableSemesters = Semester::orderBy('start_date', 'desc')->get();
    
    // =============================================
    // 3. MAIN DASHBOARD QUERY
    // =============================================
    $mainQuery = DB::connection('ewms')->table('table_signatory');
    
    // Apply MAIN semester filter
    if ($filters['main_semester'] && $filters['main_semester'] !== 'all') {
        $semester = Semester::find($filters['main_semester']);
        if ($semester) {
            $mainQuery->whereBetween('date_submitted', [$semester->start_date, $semester->end_date]);
        }
    }
    
    $mainSignatories = $mainQuery->get();
    
    // Calculate MAIN dashboard statistics
    $totalDocuments = $mainSignatories->count();
    $fullyApproved = $mainSignatories->filter(fn($i) => $this->isFullyApproved($i))->count();
    $pendingApproval = $mainSignatories->filter(fn($i) => $this->hasPendingApproval($i) && !$this->hasDeclinedApproval($i))->count();
    $declined = $mainSignatories->filter(fn($i) => $this->hasDeclinedApproval($i))->count();
    
    $overallApproved = $overallPending = $overallDeclined = 0;
    foreach ($mainSignatories as $signatory) {
        $counts = $this->getApprovalCounts($signatory);
        $overallApproved += $counts['approved'];
        $overallPending  += $counts['pending'];
        $overallDeclined += $counts['declined'];
    }
    
    // Calculate stats for each signatory type
    $dhStats = $this->calculateSingleSignatoryStats($mainSignatories, 'dh_approval');
    $deanStats = $this->calculateSingleSignatoryStats($mainSignatories, 'dean_approval');
    $directorStats = $this->calculateSingleSignatoryStats($mainSignatories, 'director_supervisor');
    $dsStats = $this->calculateSingleSignatoryStats($mainSignatories, 'ds_approval');
    $dotUniStats = $this->calculateSingleSignatoryStats($mainSignatories, 'dot_uni_approval');
    $nstpStats = $this->calculateSingleSignatoryStats($mainSignatories, 'nstp_approval');
    $eteeapStats = $this->calculateSingleSignatoryStats($mainSignatories, 'eteeap_approval');
    $vpaaStats = $this->calculateSingleSignatoryStats($mainSignatories, 'vpaa_approval');
    
    $signatoryRows = [
        ['label' => 'Department Head', 'filter' => 'dh', 'stats' => $dhStats],
        ['label' => 'Dean', 'filter' => 'dean', 'stats' => $deanStats],
        ['label' => 'Director/Supervisor', 'filter' => 'director', 'stats' => $directorStats],
        ['label' => 'DS', 'filter' => 'ds', 'stats' => $dsStats],
        ['label' => 'DOT UNI', 'filter' => 'dot_uni', 'stats' => $dotUniStats],
        ['label' => 'NSTP', 'filter' => 'nstp', 'stats' => $nstpStats],
        ['label' => 'ETEEAP', 'filter' => 'eteeap', 'stats' => $eteeapStats],
        ['label' => 'VPAA', 'filter' => 'vpaa', 'stats' => $vpaaStats],
    ];
    
    // =============================================
    // 4. TIMELINE DATA
    // =============================================
    $allTimelineDocs = DB::connection('ewms')->table('table_signatory')->get();
    
    // Get available years for timeline
    $timelineYears = DB::connection('ewms')->table('table_signatory')
        ->select(DB::raw('YEAR(date_submitted) as year'))
        ->whereNotNull('date_submitted')
        ->distinct()
        ->orderBy('year', 'asc')
        ->pluck('year')
        ->toArray();
    
    if (empty($timelineYears)) {
        $currentYear = date('Y');
        $timelineYears = range($currentYear - 4, $currentYear);
    }
    
    // Calculate yearly statistics
    $yearlyDocumentCounts = [];
    $yearlyApprovedCounts = [];
    $yearlyDeclinedCounts = [];
    $yearlyPendingCounts = [];
    $yearlyApprovalRates = [];
    
    foreach ($timelineYears as $year) {
        $yearlyDocs = $allTimelineDocs->filter(function ($item) use ($year) {
            $submittedDate = $item->date_submitted ?? null;
            if (!$submittedDate) return false;
            return date('Y', strtotime($submittedDate)) == $year;
        });
        
        $totalCount = $yearlyDocs->count();
        $yearlyDocumentCounts[$year] = $totalCount;
        
        if ($filters['timeline_signatory']) {
            $field = $this->getTimelineField($filters['timeline_signatory']);
            $approved = $yearlyDocs->filter(fn($i) => $this->checkIsApproved($i->$field ?? null))->count();
            $declined = $yearlyDocs->filter(fn($i) => $this->checkIsDeclined($i->$field ?? null))->count();
            $pending = max(0, $totalCount - $approved - $declined);
        } else {
            $approved = $yearlyDocs->filter(fn($i) => $this->isFullyApproved($i))->count();
            $declined = $yearlyDocs->filter(fn($i) => $this->hasDeclinedApproval($i))->count();
            $pending = $yearlyDocs->filter(fn($i) => $this->hasPendingApproval($i) && !$this->hasDeclinedApproval($i))->count();
        }
        
        $yearlyApprovedCounts[$year] = $approved;
        $yearlyDeclinedCounts[$year] = $declined;
        $yearlyPendingCounts[$year] = $pending;
        $yearlyApprovalRates[$year] = $totalCount > 0 ? round(($approved / $totalCount) * 100, 1) : 0;
    }
    
    // =============================================
    // 5. RETURN VIEW WITH ALL DATA
    // =============================================
    return view('stzfaculty.approval', compact(
        'availableSemesters',
        'timelineYears',
        'totalDocuments',
        'fullyApproved',
        'pendingApproval',
        'declined',
        'overallApproved',
        'overallPending',
        'overallDeclined',
        'signatoryRows',
        'dhStats',
        'deanStats',
        'directorStats',
        'dsStats',
        'dotUniStats',
        'nstpStats',
        'eteeapStats',
        'vpaaStats',
        'yearlyDocumentCounts',
        'yearlyApprovedCounts',
        'yearlyDeclinedCounts',
        'yearlyPendingCounts',
        'yearlyApprovalRates',
        'filters'
    ));
}

// =============================================
// HELPER METHODS (add these to your controller)
// =============================================

private function getTimelineField($signatoryFilter)
{
    $mapping = [
        'dh' => 'dh_approval',
        'dean' => 'dean_approval',
        'director' => 'director_supervisor',
        'ds' => 'ds_approval',
        'dot_uni' => 'dot_uni_approval',
        'nstp' => 'nstp_approval',
        'eteeap' => 'eteeap_approval',
        'vpaa' => 'vpaa_approval',
    ];
    return $mapping[$signatoryFilter] ?? null;
}

private function calculateSingleSignatoryStats($signatories, $fieldName)
{
    $approved = $pending = $declined = $total = 0;
    
    foreach ($signatories as $signatory) {
        if (!property_exists($signatory, $fieldName)) continue;
        $status = $signatory->$fieldName;
        $total++;
        
        if ($status === null || $status === '') { 
            $pending++; 
            continue; 
        }
        
        $s = trim((string)$status);
        if ($this->checkIsApproved($s)) {
            $approved++;
        } elseif ($this->checkIsDeclined($s)) {
            $declined++;
        } else {
            $pending++;
        }
    }
    
    return [
        'approved' => $approved,
        'pending' => $pending,
        'declined' => $declined,
        'total' => $total,
        'rate' => $total > 0 ? ($approved / $total) * 100 : 0,
    ];
}

private function checkIsApproved($status)
{
    if ($status === null || $status === '') return false;
    return in_array(strtolower(trim($status)), [
        'approved', 'approve', 'yes', '1', 'true', 'accept', 'accepted'
    ]);
}

private function checkIsDeclined($status)
{
    if ($status === null || $status === '') return false;
    return in_array(strtolower(trim($status)), [
        'declined', 'rejected', 'reject', 'deny', 'denied', 'no', 
        'disapproved', 'disapprove'
    ]);
}

private function checkIsPending($status)
{
    if ($status === null || $status === '') return true;
    return in_array(strtolower(trim($status)), [
        'pending', 'waiting', 'in progress', '0', 'null', 
        'for approval', 'not yet', 'for review'
    ]);
}

private function isFullyApproved($signatory)
{
    $fields = [
        'dh_approval', 'dean_approval', 'director_supervisor', 
        'ds_approval', 'dot_uni_approval', 'nstp_approval', 
        'eteeap_approval', 'vpaa_approval'
    ];
    
    $hasAtLeastOne = false;
    foreach ($fields as $field) {
        if (!property_exists($signatory, $field) || 
            $signatory->$field === null || 
            $signatory->$field === '') {
            continue;
        }
        $hasAtLeastOne = true;
        if (!$this->checkIsApproved($signatory->$field)) {
            return false;
        }
    }
    return $hasAtLeastOne;
}

private function hasPendingApproval($signatory)
{
    $fields = [
        'dh_approval', 'dean_approval', 'director_supervisor', 
        'ds_approval', 'dot_uni_approval', 'nstp_approval', 
        'eteeap_approval', 'vpaa_approval'
    ];
    
    foreach ($fields as $field) {
        if (!property_exists($signatory, $field)) continue;
        if ($this->checkIsPending($signatory->$field)) {
            return true;
        }
    }
    return false;
}

private function hasDeclinedApproval($signatory)
{
    $fields = [
        'dh_approval', 'dean_approval', 'director_supervisor', 
        'ds_approval', 'dot_uni_approval', 'nstp_approval', 
        'eteeap_approval', 'vpaa_approval'
    ];
    
    foreach ($fields as $field) {
        if (!property_exists($signatory, $field)) continue;
        $status = $signatory->$field;
        if ($status === null || $status === '') continue;
        if ($this->checkIsDeclined($status)) {
            return true;
        }
    }
    return false;
}

private function getApprovalCounts($signatory)
{
    $fields = [
        'dh_approval', 'dean_approval', 'director_supervisor', 
        'ds_approval', 'dot_uni_approval', 'nstp_approval', 
        'eteeap_approval', 'vpaa_approval'
    ];
    
    $approved = $pending = $declined = 0;
    
    foreach ($fields as $field) {
        if (!property_exists($signatory, $field)) continue;
        $status = $signatory->$field;
        
        if ($status === null || $status === '') {
            $pending++;
        } elseif ($this->checkIsApproved($status)) {
            $approved++;
        } elseif ($this->checkIsDeclined($status)) {
            $declined++;
        } else {
            $pending++;
        }
    }
    
    return ['approved' => $approved, 'pending' => $pending, 'declined' => $declined];
}
}