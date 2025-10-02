<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('jentestlayoutignore');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
    Route::resource('courses', CourseController::class);


    Route::get('/submission/create', [SubmissionController::class, 'create'])->name('submission.create');
});

require __DIR__.'/auth.php';
