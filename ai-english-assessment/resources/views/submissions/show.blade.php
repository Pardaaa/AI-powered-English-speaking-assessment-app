@extends('layouts.master')

@section('title', 'Submission Details')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">
                @if (auth()->user()->role === 'mahasiswa')
                    My Courses / {{ $submission->assignment->course->name ?? 'Course' }} / Assignment:
                    {{ $submission->assignment->title ?? 'Assignment' }} /
                @else
                    Courses / {{ $submission->assignment->course->name ?? 'Course' }} / Assignment:
                    {{ $submission->assignment->title ?? 'Assignment' }} / Submission by
                    {{ $submission->user->name ?? 'Student' }} /
                @endif
            </span> Details
        </h4>

        @if (!$submission->assignment || !$submission->assignment->course || !$submission->user)
            <div class="alert alert-danger" role="alert">
                Error: Associated assignment, course, or user data is missing for this submission.
            </div>
        @else
            <div class="row">
                {{-- LEFT: Submission info --}}
                <div class="col-md-5 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Submission Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Submitted by:</strong> {{ $submission->user->name }}</p>
                            <p><strong>Submitted at:</strong> {{ $submission->created_at->format('d M Y, H:i') }}</p>
                            <p><strong>Assignment:</strong> {{ $submission->assignment->title }}</p>
                            <p><strong>Course:</strong> {{ $submission->assignment->course->name }}</p>
                            <p><strong>Original File:</strong> {{ $submission->original_filename }}</p>
                            <p><strong>Notes:</strong> {{ $submission->notes ?? '-' }}</p>
                            <p><strong>Status:</strong>
                                <span
                                    class="badge
                                        @if ($submission->status == 'pending') bg-label-warning
                                        @elseif($submission->status == 'processing') bg-label-info
                                        @elseif($submission->status == 'completed') bg-label-success
                                        @elseif($submission->status == 'failed') bg-label-danger @endif me-1">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </p>

                            <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank"
                               class="btn btn-sm btn-outline-secondary">
                                Download Submitted File
                            </a>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: AI Assessment --}}
                <div class="col-md-7 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">AI Assessment Results</h5>

                            @if ($submission->final_score_ai)
                                <span class="badge bg-label-primary">
                                    AI Score: {{ number_format($submission->final_score_ai, 2) }} / 100
                                </span>
                            @endif
                        </div>
                        <div class="card-body">
                            @php
                                $hasAi =
                                    $submission->recognized_text_ai ||
                                    $submission->accuracy_score_ai ||
                                    $submission->fluency_score_ai ||
                                    $submission->completeness_score_ai ||
                                    $submission->pronunciation_score_ai ||
                                    $submission->final_score_ai;
                            @endphp

                            @if ($hasAi)
                                <div class="row mb-3">
                                    <div class="col-sm-3 mb-2">
                                        <h6 class="mb-1">Accuracy</h6>
                                        <p class="mb-0 fw-bold">
                                            {{ $submission->accuracy_score_ai ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="col-sm-3 mb-2">
                                        <h6 class="mb-1">Fluency</h6>
                                        <p class="mb-0 fw-bold">
                                            {{ $submission->fluency_score_ai ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="col-sm-3 mb-2">
                                        <h6 class="mb-1">Completeness</h6>
                                        <p class="mb-0 fw-bold">
                                            {{ $submission->completeness_score_ai ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="col-sm-3 mb-2">
                                        <h6 class="mb-1">Pronunciation</h6>
                                        <p class="mb-0 fw-bold">
                                            {{ $submission->pronunciation_score_ai ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                <h6>Transcript (AI):</h6>
                                <p class="bg-light p-2 rounded">
                                    <em>{{ $submission->recognized_text_ai ?? 'Transcript not available.' }}</em>
                                </p>

                                @php
                                    $audioPath = $submission->audio_path_ai ?? $submission->file_path;
                                @endphp

                                @if ($audioPath)
                                    <h6 class="mt-3">Audio Playback:</h6>
                                    <audio controls style="width: 100%;">
                                        <source src="{{ asset('storage/' . $audioPath) }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                @endif
                            @elseif($submission->status === 'pending' || $submission->status === 'processing')
                                <div class="alert alert-info" role="alert">
                                    The submission is currently being processed by the AI. Please check back later for
                                    results.
                                </div>
                            @elseif($submission->status === 'failed')
                                <div class="alert alert-danger" role="alert">
                                    AI processing failed for this submission. Please contact support or try submitting
                                    again.
                                </div>
                            @else
                                <p class="text-muted mb-0">Assessment results are not yet available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lecturer feedback --}}
            @if (in_array(auth()->user()->role, ['dosen', 'admin']))
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Lecturer Feedback & Score</h5>
                    </div>
                    <div class="card-body">
                        <form action="#" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="dosen_score" class="form-label">Score (0-100)</label>
                                    <input type="number" class="form-control" id="dosen_score" name="dosen_score"
                                           min="0" max="100"
                                           value="{{ old('dosen_score', $submission->score_dosen) }}">
                                </div>
                                <div class="col-md-9 mb-3">
                                    <label for="feedback_dosen" class="form-label">Feedback</label>
                                    <textarea class="form-control" id="feedback_dosen" name="feedback_dosen" rows="3">{{ old('feedback_dosen', $submission->feedback_dosen) }}</textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" disabled>Save Feedback</button>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection
