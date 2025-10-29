<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Course;
use App\Models\User;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function myCourses(Request $request): View
    {


        $student = $request->user();
        if (!$student) {
            abort(401);
        }
        $enrolledCourses = $student->enrolledCourses()->orderBy('name')->get();

        return view('student.courses.index', compact('enrolledCourses'));
    }

    public function showCourse(Request $request, Course $course): View
    {
        $student = $request->user();

        if (!$student || !$student->enrolledCourses()->where('course_id', $course->id)->exists()) {
            abort(403);
        }

        $course->load(['assignments' => function ($query) use ($student) {
            $query->with(['submissions' => function ($subQuery) use ($student) {
                $subQuery->where('user_id', $student->id)->latest()->limit(1);
            }]);
        }]);

        return view('student.courses.show', compact('course'));
    }
}
