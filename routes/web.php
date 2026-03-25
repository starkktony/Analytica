<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GraduatesController;
use App\Http\Controllers\FundingController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\EIS\BidController;
use App\Http\Controllers\EIS\FacilitiesController;
use App\Http\Controllers\EIS\IGPController;
use App\Http\Controllers\Radiis\AgenciesController;
use App\Http\Controllers\Radiis\IPRightsController;
use App\Http\Controllers\Radiis\PresentationController;
use App\Http\Controllers\Radiis\ProgramController;
use App\Http\Controllers\Radiis\ProjectController;
use App\Http\Controllers\Radiis\PublicationController;
use App\Http\Controllers\Radiis\AwardController;
use App\Http\Controllers\Radiis\LinkagesController;
use App\Http\Controllers\Radiis\StudyController;
use App\Http\Controllers\Radiis\ResearcherController;
use App\Http\Controllers\EIS\FundController;

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
        Route::prefix('data')->group(function () {
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
            Route::get('/faculty-pie', [FacultyController::class, 'facultyPie'])
                ->name('faculty.api.pie');
        });
    });

        Route::prefix('data')->group(function () {

        // Graduates
        Route::get('/graduates-summary',           [GraduatesController::class, 'summary']);
        Route::get('/graduates-by-college',        [GraduatesController::class, 'byCollege']);
        Route::get('/graduates-gender-by-college', [GraduatesController::class, 'genderByCollege']);
        Route::get('/graduates-by-program',        [GraduatesController::class, 'byProgram']);

        // Funding
        Route::get('/income-data',      [FundingController::class, 'getIncomeData']);
        Route::get('/allotment-data',   [FundingController::class, 'getAllotmentData']);
        Route::get('/expenditure-data', [FundingController::class, 'getExpenditureData']);
    });

    // ====================================================
    // RADIIS ROUTES
    // ====================================================
    Route::prefix('radiis')->group(function () {
        Route::get('/programs', [ProgramController::class, 'index'])->name('radiis.programs');
        Route::get('/projects', [ProjectController::class, 'index'])->name('radiis.projects');
        Route::get('/studies', [StudyController::class, 'index'])->name('radiis.studies');
        Route::get('/publications', [PublicationController::class, 'index'])->name('radiis.publications');
        Route::get('/presentations', [PresentationController::class, 'index'])->name('radiis.presentations');
        Route::get('/iprights', [IPRightsController::class, 'index'])->name('radiis.iprights');
        Route::get('/awards', [AwardController::class, 'index'])->name('radiis.awards');
        Route::get('/linkages', [LinkagesController::class, 'index'])->name('radiis.linkages');
        Route::get('/researchers', [ResearcherController::class, 'index'])->name('radiis.researchers');
        Route::get('/fundagency', [AgenciesController::class, 'index'])->name('radiis.agencies');
    });

    // ====================================================
    // EIS ROUTES
    // ====================================================
    Route::prefix('eis')->group(function () {
        Route::get('/igps', [IGPController::class, 'index'])->name('eis.igp');
        Route::get('/funds', [FundController::class, 'index'])->name('eis.fund');
        Route::get('/facilities', [FacilitiesController::class, 'index'])->name('eis.facility');
        Route::get('/bids', [BidController::class, 'index'])->name('eis.bid');
    });

    Route::get('/dashboard/faculty-overview', function () {
        return redirect()->route('stzfaculty.overview');
    });

});

require __DIR__.'/auth.php';