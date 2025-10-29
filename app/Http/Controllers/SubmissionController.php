<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $assignmentId = request()->query('assignment_id');
        if (!$assignmentId) {
            abort(404, 'Assignment not specified.');
        }

        $assignment = Assignment::find($assignmentId);
        if (!$assignment) {
            abort(404, 'Assignment not found.');
        }


        return view('submissions.create', compact('assignment'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => [
                'required',
                Rule::exists('assignments', 'id'),

            ],
            'submission_file' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/webm,audio/mpeg,audio/wav',
                'max:102400',
            ],
            'notes' => 'nullable|string',
        ]);

        $file = $request->file('submission_file');
        $userId = Auth::id();
        $assignmentId = $validated['assignment_id'];
        $filename = 'user' . $userId . '_assign' . $assignmentId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('submissions/' . $userId, $filename, 'public');

        Submission::create([
            'user_id' => $userId,
            'assignment_id' => $assignmentId,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        $assignment = Assignment::find($assignmentId);
        return redirect()->route('student.courses.show', $assignment->course_id)
            ->with('success', 'Your work has been submitted successfully and is pending review.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Submission $submission)
    {
        $submission->load(['user', 'assignment.course']);


        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $isOwner = ($submission->user_id === $user->id);
        $isCourseOwner = ($user->role === 'dosen' && $submission->assignment->course->user_id === $user->id);
        $isAdmin = ($user->role === 'admin');


        if (!$isOwner && !$isCourseOwner && !$isAdmin) {
            abort(403);
        }


        return view('submissions.show', compact('submission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Submission $submission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Submission $submission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Submission $submission)
    {
        //
    }
}
