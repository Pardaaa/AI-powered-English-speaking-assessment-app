<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index()
    {
        //
    }

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assignment_id'      => ['required', Rule::exists('assignments', 'id')],
            'uploaded_url'       => ['required', 'string'],
            'original_filename'  => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string'],
        ]);

        $userId = Auth::id();
        $assignmentId = (int) $validated['assignment_id'];
        $fileUrl = trim($validated['uploaded_url']);

        $originalName = $validated['original_filename'] ?? null;

        if (!$originalName) {
            try {
                $path = parse_url($fileUrl, PHP_URL_PATH);
                $originalName = $path ? basename($path) : null;
            } catch (\Throwable $e) {
                $originalName = null;
            }
        }

        $client = new Client([
            'base_uri'        => env('PY_STT_URL', 'http://127.0.0.1:5000'),
            'timeout'         => 900,
            'connect_timeout' => 10,
            'http_errors'     => false,
        ]);

        $result = null;
        $finalScore = null;
        $status = 'pending';

        try {
            $response = $client->post('/stt_by_url', [
                'json' => [
                    'file_url' => $fileUrl
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();

            $result = json_decode($body, true);

            if ($statusCode >= 400) {
                Log::error("AI error HTTP {$statusCode}: " . $body);
                $status = 'failed';
                $result = null;
            } else {
                Log::info('AI result', $result ?? []);

                if (is_array($result) && isset($result['accuracy_score'])) {
                    $finalScore = (
                        ($result['accuracy_score'] ?? 0) +
                        ($result['fluency_score'] ?? 0) +
                        ($result['completeness_score'] ?? 0) +
                        ($result['pronunciation_score'] ?? 0)
                    ) / 4;

                    $status = 'completed';
                } else {
                    $status = 'failed';
                    $result = null;
                }
            }
        } catch (\Throwable $e) {
            Log::error('AI exception: ' . $e->getMessage());
            $status = 'failed';
            $result = null;
        }

        Submission::create([
            'user_id' => $userId,
            'assignment_id' => $assignmentId,

            'file_path' => $fileUrl,

            'original_filename' => $originalName,

            'notes' => $validated['notes'] ?? null,
            'status' => $status,

            'audio_path_ai' => $fileUrl,

            'recognized_text_ai' => $result['recognized_text'] ?? null,
            'accuracy_score_ai' => $result['accuracy_score'] ?? null,
            'fluency_score_ai' => $result['fluency_score'] ?? null,
            'completeness_score_ai' => $result['completeness_score'] ?? null,
            'pronunciation_score_ai' => $result['pronunciation_score'] ?? null,
            'final_score_ai' => $finalScore,
            'mispronounced_words_ai' => $result['mispronounced_words'] ?? null,
            'gpt_feedback_ai' => $result['gpt_feedback'] ?? null,
        ]);

        $assignment = Assignment::find($assignmentId);

        return redirect()
            ->route('student.courses.show', $assignment->course_id)
            ->with('success', 'Your work has been submitted and processed by AI.');
    }

    public function show(Submission $submission): View
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

        return view('submissions.show', [
            'submission' => $submission
        ]);
    }

    public function saveGrade(Request $request, Submission $submission)
    {
        $user = Auth::user();

        $isCourseOwner = ($user->role === 'dosen' && $submission->assignment->course->user_id === $user->id);
        $isAdmin = ($user->role === 'admin');

        if (!$isCourseOwner && !$isAdmin) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'dosen_score' => 'nullable|numeric|min:0|max:100',
            'feedback_dosen' => 'nullable|string',
        ]);

        $submission->update([
            'score_dosen' => $validated['dosen_score'],
            'feedback_dosen' => $validated['feedback_dosen'],
        ]);

        return redirect()
            ->route('submission.show', $submission->id)
            ->with('grade_success', 'Feedback has been saved successfully.');
    }
}
