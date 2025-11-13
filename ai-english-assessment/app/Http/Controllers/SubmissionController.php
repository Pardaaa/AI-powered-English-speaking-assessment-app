<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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
                'mimes:mp3,wav,m4a,mp4,webm',
                'max:102400',
            ],

            'notes' => 'nullable|string',
        ]);

        $file        = $request->file('submission_file');
        $userId      = Auth::id();
        $assignmentId = $validated['assignment_id'];

        // 1. Simpan file seperti semula
        $filename = 'user' . $userId . '_assign' . $assignmentId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('submissions/' . $userId, $filename, 'public');
        $fullPath = Storage::disk('public')->path($path);

        // 2. Panggil service Python (Flask) -> /stt
        $client = new Client([
            'base_uri' => env('PY_STT_URL', 'http://127.0.0.1:5000'),
            'timeout'  => 90,
        ]);

        $result     = null;
        $finalScore = null;
        $status     = 'pending';

        try {
            $response = $client->post('/stt', [
                'multipart' => [
                    [
                        'name'     => 'audio', // HARUS sama dengan request.files['audio'] di Flask
                        'contents' => fopen($fullPath, 'r'),
                        'filename' => basename($fullPath),
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            Log::info('AI result', $result ?? []);

            if (
                isset($result['accuracy_score']) &&
                isset($result['fluency_score']) &&
                isset($result['completeness_score']) &&
                isset($result['pronunciation_score'])
            ) {
                $finalScore = (
                    $result['accuracy_score'] +
                    $result['fluency_score'] +
                    $result['completeness_score'] +
                    $result['pronunciation_score']
                ) / 4;

                $status = 'completed';
            } else {
                // AI balas tapi datanya tidak lengkap
                $status = 'failed';
            }
        } catch (\Exception $e) {
            // AI gagal → kita log error & tandai status failed
            Log::error('AI error: ' . $e->getMessage());
            $status = 'failed';
            $result = null;
        }

        // 3. Simpan submission + hasil AI (kalau ada)
        $submission = Submission::create([
            'user_id'           => $userId,
            'assignment_id'     => $assignmentId,
            'file_path'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'notes'             => $validated['notes'],
            'status'            => $status,

            'audio_path_ai'          => $path,
            'recognized_text_ai'     => $result['recognized_text'] ?? null,
            'accuracy_score_ai'      => $result['accuracy_score'] ?? null,
            'fluency_score_ai'       => $result['fluency_score'] ?? null,
            'completeness_score_ai'  => $result['completeness_score'] ?? null,
            'pronunciation_score_ai' => $result['pronunciation_score'] ?? null,
            'final_score_ai'         => $finalScore,
        ]);

        $assignment = Assignment::find($assignmentId);

        return redirect()
            ->route('student.courses.show', $assignment->course_id)
            ->with('success', 'Your work has been submitted and processed by AI.');
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
