<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChunkUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // ✅ CHUNK UPLOAD ROUTES (web, aman dari 404 & CSRF jelas)
    Route::post('/upload/chunk', [ChunkUploadController::class, 'uploadChunk'])->name('upload.chunk');
    Route::post('/upload/complete', [ChunkUploadController::class, 'completeUpload'])->name('upload.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- ADMIN ONLY ROUTES ---
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users/import', [UserController::class, 'showImportForm'])->name('users.import.form');
        Route::post('/users/import', [UserController::class, 'importUsers'])->name('users.import.store');
        Route::resource('users', UserController::class);
    });

    // --- DOSEN ONLY ROUTES ---
    Route::middleware(['role:dosen'])->group(function () {
        Route::resource('courses', CourseController::class);
        Route::resource('courses.assignments', AssignmentController::class)->except(['index', 'show']);

        Route::post('/courses/{course}/enroll', [CourseController::class, 'enrollStudent'])->name('courses.enroll');
        Route::delete('/courses/{course}/unenroll', [CourseController::class, 'unenrollStudent'])->name('courses.unenroll');
        Route::post('/courses/{course}/import-students', [CourseController::class, 'importStudentsToCourse'])->name('courses.import_students');

        Route::get('/courses/{course}/assignments/{assignment}/submissions', [AssignmentController::class, 'showSubmissions'])
            ->name('assignments.submissions.index');

        Route::put('/submissions/{submission}/grade', [SubmissionController::class, 'saveGrade'])->name('submission.grade');
    });

    // --- MAHASISWA ONLY ROUTES ---
    Route::middleware(['role:mahasiswa'])->group(function () {
        Route::get('/my-courses', [StudentController::class, 'myCourses'])->name('student.courses.index');
        Route::get('/student/courses/{course}', [StudentController::class, 'showCourse'])->name('student.courses.show');

        Route::get('/submission/create', [SubmissionController::class, 'create'])->name('submission.create');
        Route::post('/submission', [SubmissionController::class, 'store'])->name('submission.store');
    });

    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submission.show');
});

require __DIR__ . '/auth.php';
