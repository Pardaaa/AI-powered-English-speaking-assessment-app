<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Assignment;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }

        return view('assignments.create', compact('course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course)
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $course->assignments()->create($validated);

        return redirect()->route('courses.show', $course->id)->with('success', 'New assignment has been created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Course $course, Assignment $assignment)
    {
        if ($assignment->course_id !== $course->id || $course->user_id !== Auth::id()) {
            abort(403);
        }

        return view('assignments.edit', compact('course', 'assignment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course, Assignment $assignment)
    {
        if ($assignment->course_id !== $course->id || $course->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $assignment->update($validated);

        return redirect()->route('courses.show', $course->id)->with('success', 'Assignment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course, Assignment $assignment)
    {
        if ($assignment->course_id !== $course->id || $course->user_id !== Auth::id()) {
            return back()->with('error', 'You do not have permission to delete this assignment.');
        }

        $assignment->delete();

        return redirect()->route('courses.show', $course->id)->with('success', 'Assignment deleted successfully!');
    }

    public function showSubmissions(Course $course, Assignment $assignment): View
    {
        if ($course->user_id !== Auth::id()) {
            abort(403);
        }
        if ($assignment->course_id !== $course->id) {
            abort(404);
        }

        $assignment->load(['submissions.user']);

        return view('assignments.submissions', compact('course', 'assignment'));
    }
}
