<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\DashboardController;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| Protected Routes (Auth Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::view('/student', 'student')->name('student.index');

    // Student pages
    Route::view('/enrollment', 'student.enrollment')->name('student.enrollment');
    Route::view('/graduation', 'student.graduation')->name('student.graduation');
    Route::view('/scholarship', 'student.scholarship')->name('student.scholarship'); 


    // ====================================================
    // FACULTY DASHBOARD ROUTES
    // ====================================================
    Route::prefix('faculty')->group(function () {
        // Faculty Overview Dashboard (Page 1)
        Route::get('/overview', [DashboardController::class, 'facultyOverview'])
            ->name('stzfaculty.overview');
        
        // Teaching Load (Page 2) - Will be created later
        Route::get('/teaching-load', [DashboardController::class, 'teachingLoad'])
            ->name('stzfaculty.teaching-load');
        
        // Research Performance (Page 3) - Will be created later
        Route::get('/research-performance', [DashboardController::class, 'researchPerformance'])
            ->name('stzfaculty.research-performance');
        
        Route::get('/qualifications', [DashboardController::class, 'facultyQualifications'])   
            ->name('stzfaculty.qualifications');

        
        
        // API Endpoints for AJAX data
        Route::prefix('api')->group(function () {
            Route::get('/faculty-stats', [DashboardController::class, 'getFacultyStats'])
                ->name('faculty.api.stats');
            Route::get('/department-stats', [DashboardController::class, 'getDepartmentStats'])
                ->name('faculty.api.department-stats');
            Route::get('/teaching-load-data', [DashboardController::class, 'getTeachingLoadData'])
                ->name('faculty.api.teaching-load');
            Route::get('/research-data', [DashboardController::class, 'getResearchData'])
                ->name('faculty.api.research-data');
        });
    });

    // ====================================================
    // OPTIONAL: Redirect /dashboard/faculty-overview to new route
    // ====================================================
    Route::get('/dashboard/faculty-overview', function () {
        return redirect()->route('faculty.overview');
    });

});


require __DIR__.'/auth.php';