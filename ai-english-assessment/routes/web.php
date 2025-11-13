<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\StudentController;

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('jentestlayoutignore');
// });

Route::get('/', function () {
    return redirect()->route('login');
});

Route::post('/stt', [SttController::class, 'analyze']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('users', UserController::class);

    Route::get('/my-courses', [StudentController::class, 'myCourses'])->name('student.courses.index');
    Route::get('/student/courses/{course}', [StudentController::class, 'showCourse'])->name('student.courses.show');


    Route::post('/courses/{course}/enroll', [CourseController::class, 'enrollStudent'])->name('courses.enroll');
    Route::delete('/courses/{course}/unenroll', [CourseController::class, 'unenrollStudent'])->name('courses.unenroll');

    Route::resource('courses', CourseController::class);
    Route::resource('courses.assignments', AssignmentController::class)->except(['index', 'show']);
    Route::get('/courses/{course}/assignments/{assignment}/submissions', [AssignmentController::class, 'showSubmissions'])->name('assignments.submissions.index');


    Route::get('/submission/create', [SubmissionController::class, 'create'])->name('submission.create');
    Route::post('/submission', [SubmissionController::class, 'store'])->name('submission.store');
    Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submission.show');
});

require __DIR__ . '/auth.php';
