<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [];
        $recentCourses = collect(); 
        $allCourses = collect();    
        switch ($user->role) {
            case 'admin':
                $stats = [
                    'users_count'       => User::count(),
                    'courses_count_all' => Course::count(),
                    'dosen_count'       => User::where('role', 'dosen')->count(),
                    'mahasiswa_count'   => User::where('role', 'mahasiswa')->count(),
                ];
                $allCourses = Course::latest()->take(5)->get();
                break;

            case 'dosen':
                $user->load('courses.students');
                $stats['my_courses_count'] = $user->courses->count();

                $stats['my_students_count'] = $user->courses
                    ->flatMap->students
                    ->unique('id')
                    ->count();

                $allCourses = $user->courses()->latest()->get();
                $recentCourses = $user->courses()->latest()->take(1)->get();;
                break;

            case 'mahasiswa':
                $stats['enrolled_courses_count'] = $user->enrolledCourses()->count();

                $allCourses = $user->enrolledCourses()->latest()->get();
                $recentCourses = $user->enrolledCourses()->latest()->take(1)->get();
                break;
        }


        return view('dashboard', compact('user', 'stats', 'recentCourses', 'allCourses'));
    }
}
