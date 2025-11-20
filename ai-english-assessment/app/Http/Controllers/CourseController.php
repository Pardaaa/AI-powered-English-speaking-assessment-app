<?php


namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {

        $courses = $request->user()->courses()->latest()->get();

        return view('courses.index', ['courses' => $courses]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('courses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:courses',
            'semester' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);


        $request->user()->courses()->create($validated);


        return redirect(route('courses.index'))->with('success', 'Course created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course): View
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }


        $availableStudents = User::where('role', 'mahasiswa')
            ->whereDoesntHave('enrolledCourses', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->orderBy('name')
            ->get();
        return view('courses.show', compact('course', 'availableStudents'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course): View
    {

        if ($course->user_id !== Auth::id()) {
            abort(403);
        }
        return view('courses.edit', ['course' => $course]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course): RedirectResponse
    {

        if ($course->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:courses,code,' . $course->id,
            'semester' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->update($validated);

        return redirect(route('courses.index'))->with('success', 'Course updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course): RedirectResponse
    {

        if ($course->user_id !== Auth::id()) {
            abort(403);
        }

        $course->delete();

        return redirect(route('courses.index'))->with('success', 'Course deleted successfully!');
    }

    public function enrollStudent(Request $request, Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([

            'student_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'mahasiswa');
                }),
            ],
        ]);


        if (!$course->students()->where('user_id', $validated['student_id'])->exists()) {
            $course->students()->attach($validated['student_id']);
            return redirect()->route('courses.show', $course->id)->with('enroll_success', 'Student enrolled successfully!');
        }

        return redirect()->route('courses.show', $course->id)->with('enroll_error', 'Student is already enrolled in this course.');
    }
    public function unenrollStudent(Request $request, Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $detached = $course->students()->detach($validated['student_id']);
        if ($detached) {
            return redirect()->route('courses.show', $course->id)->with('enroll_success', 'Student removed successfully!');
        }

        return redirect()->route('courses.show', $course->id)->with('enroll_error', 'Failed to remove student or student was not enrolled.');
    }
    public function importStudentsToCourse(Request $request, Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'student_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('student_file');

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $row = 0;
            $importedCount = 0;
            $skippedCount = 0;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row++;
                if ($row == 1) continue;

                $email = trim($data[0]);


                $student = User::where('email', $email)->where('role', 'mahasiswa')->first();

                if ($student) {
                    if (!$course->students()->where('user_id', $student->id)->exists()) {
                        $course->students()->attach($student->id);
                        $importedCount++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
            fclose($handle);

            return redirect()->route('courses.show', $course->id)
                ->with('enroll_success', "$importedCount students enrolled successfully from CSV. ($skippedCount skipped/already enrolled)");
        }

        return redirect()->route('courses.show', $course->id)->with('enroll_error', 'Failed to read file.');
    }
}
