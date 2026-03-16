<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GraduatesController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\FacultyController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {

    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/student', 'student')->name('student.index');

    // Student pages
    Route::view('/enrollment', 'student.enrollment')->name('student.enrollment');
    Route::view('/graduation', 'student.graduation')->name('student.graduation');
    Route::view('/scholarship', 'student.scholarship')->name('student.scholarship');

    // ✅ Graduates
    Route::get('/graduates', [GraduatesController::class, 'index'])
        ->name('graduates.index');

    // ✅ Normative Funding
    Route::get('/normative-funding', [FundingController::class, 'index'])
        ->name('normative-funding.index');

    // ====================================================
    // FACULTY ROUTES
    // ====================================================
    Route::prefix('faculty')->group(function () {
        Route::get('/overview', [DashboardController::class, 'facultyOverview'])
            ->name('stzfaculty.overview');
        Route::get('/teaching-load', [DashboardController::class, 'teachingLoad'])
            ->name('stzfaculty.teaching-load');
        Route::get('/research-performance', [DashboardController::class, 'researchPerformance'])
            ->name('stzfaculty.research-performance');
        Route::get('/approval', [DashboardController::class, 'facultyApproval'])
            ->name('stzfaculty.approval');
        Route::get('/approval/data', [DashboardController::class, 'getDashboardData'])
            ->name('faculty.faculty_approval.data');
        Route::get('/qualifications', [DashboardController::class, 'facultyQualifications'])
            ->name('stzfaculty.qualifications');

        // ✅ SUC Faculty
        Route::get('/suc-faculty', [FacultyController::class, 'index'])
            ->name('suc-faculty.index');

        // API Endpoints
        Route::prefix('api')->group(function () {
            Route::get('/faculty-stats', [DashboardController::class, 'getFacultyStats'])
                ->name('faculty.api.stats');
            Route::get('/department-stats', [DashboardController::class, 'getDepartmentStats'])
                ->name('faculty.api.department-stats');
            Route::get('/teaching-load-data', [DashboardController::class, 'getTeachingLoadData'])
                ->name('faculty.api.teaching-load');
            Route::get('/teaching-load/ajax', [DashboardController::class, 'teachingLoadAjax'])
                ->name('stzfaculty.teaching-load.ajax');
            Route::get('/departments-by-college/{collegeId}', [DashboardController::class, 'departmentsByCollege'])
                ->name('stzfaculty.departments-by-college');
            Route::get('/research-data', [DashboardController::class, 'getResearchData'])
                ->name('faculty.api.research-data');
            Route::get('/faculty/overview/ajax', [DashboardController::class, 'facultyOverviewAjax'])
                ->name('stzfaculty.overview.ajax');
            Route::get('/faculty/research-performance/ajax', [DashboardController::class, 'researchPerformanceAjax'])
                ->name('stzfaculty.research-performance.ajax');
        });
    });

    Route::get('/dashboard/faculty-overview', function () {
        return redirect()->route('stzfaculty.overview');
    });

});

require __DIR__.'/auth.php';