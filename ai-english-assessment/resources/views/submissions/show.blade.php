@extends('layouts.master')

@section('title', 'Submission Details')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    {{-- 1. BREADCRUMB (Judul Halaman) - Baris Sendiri --}}
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">
            @if(auth()->user()->role === 'mahasiswa')
                My Courses / {{ $submission->assignment->course->name ?? 'Course' }} / Assignment: {{ $submission->assignment->title ?? 'Assignment' }} /
            @else
                Courses / {{ $submission->assignment->course->name ?? 'Course' }} / Assignment: {{ $submission->assignment->title ?? 'Assignment' }} / Submission by {{ $submission->user->name ?? 'Student' }} /
            @endif
        </span> Details
    </h4>

    {{-- 2. BARIS TOMBOL BACK (Terpisah di bawah judul, rata kanan) --}}
    <div class="d-flex justify-content-end align-items-center mb-4">
        @if(auth()->user()->role === 'mahasiswa')
            {{-- Mahasiswa: Kembali ke halaman detail kelas --}}
            <a href="{{ route('student.courses.show', $submission->assignment->course_id) }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Course
            </a>
        @else
            {{-- Dosen/Admin: Kembali ke daftar submission --}}
            <a href="{{ route('assignments.submissions.index', [$submission->assignment->course_id, $submission->assignment_id]) }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back to Submissions
            </a>
        @endif
    </div>

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
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Submitted by:</dt>
                            <dd class="col-sm-8">{{ $submission->user->name }}</dd>

                            <dt class="col-sm-4">Submitted at:</dt>
                            <dd class="col-sm-8">{{ $submission->created_at->format('d M Y, H:i') }}</dd>

                            <dt class="col-sm-4">Assignment:</dt>
                            <dd class="col-sm-8">{{ $submission->assignment->title }}</dd>

                            <dt class="col-sm-4">Original File:</dt>
                            <dd class="col-sm-8 text-break">{{ $submission->original_filename }}</dd>

                            <dt class="col-sm-4">Notes:</dt>
                            <dd class="col-sm-8">{{ $submission->notes ?? '-' }}</dd>

                            <dt class="col-sm-4">Status:</dt>
                            <dd class="col-sm-8">
                                <span class="badge
                                    @if($submission->status == 'pending') bg-label-warning
                                    @elseif($submission->status == 'processing') bg-label-info
                                    @elseif($submission->status == 'completed') bg-label-success
                                    @elseif($submission->status == 'failed') bg-label-danger
                                    @endif">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </dd>
                        </dl>
                        
                                                @php
                            $downloadUrl = \Illuminate\Support\Str::startsWith($submission->file_path, ['http://','https://'])
                                ? $submission->file_path
                                : \Illuminate\Support\Facades\Storage::disk('public')->url($submission->file_path);
                        @endphp

                        <a href="{{ $downloadUrl }}" target="_blank" class="btn btn-outline-primary w-100">
                            <i class="bx bx-download me-1"></i> Download File
                        </a>

                    </div>
                </div>
            </div>

            {{-- RIGHT: AI Assessment Results --}}
            <div class="col-md-7 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">AI Assessment Results</h5>
                        @if($submission->final_score_ai)
                            <span class="badge bg-label-primary fs-6">AI Score: {{ number_format($submission->final_score_ai, 2) }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($submission->status === 'completed')
                            <div class="row text-center mb-4">
                                <div class="col-3">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block mb-1">Accuracy</small>
                                        <h5 class="mb-0 text-primary">{{ $submission->accuracy_score_ai ?? '-' }}</h5>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block mb-1">Fluency</small>
                                        <h5 class="mb-0 text-primary">{{ $submission->fluency_score_ai ?? '-' }}</h5>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block mb-1">Completeness</small>
                                        <h5 class="mb-0 text-primary">{{ $submission->completeness_score_ai ?? '-' }}</h5>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="border rounded p-2">
                                        <small class="text-muted d-block mb-1">Pronunciation</small>
                                        <h5 class="mb-0 text-primary">{{ $submission->pronunciation_score_ai ?? '-' }}</h5>
                                    </div>
                                </div>
                            </div>

                            {{-- ⬇⬇⬇ BLOK BARU: PENJELASAN AI --}}
                            @if ($submission->gpt_feedback_ai)
                                <div class="mb-4">
                                    <h6 class="fw-bold">AI Feedback (Gemini):</h6>
                                    <div class="bg-label-secondary p-3 rounded text-dark" style="white-space: pre-line;">
                                        {!! nl2br(e($submission->gpt_feedback_ai)) !!}
                                    </div>
                                </div>
                            @endif
                            {{-- ⬆⬆⬆ --}}

                            <div class="mb-4">
                                <h6 class="fw-bold">Transcript (AI):</h6>
                                <div class="bg-lighter p-3 rounded text-secondary">
                                    <em>"{{ $submission->recognized_text_ai ?? 'Transcript not available.' }}"</em>
                                </div>
                            </div>

                            @if(is_array($submission->mispronounced_words_ai) && count($submission->mispronounced_words_ai) > 0)
                                <div class="mb-4">
                                    <h6 class="fw-bold text-danger">Mispronounced Words:</h6>
                                    <div>
                                        @foreach($submission->mispronounced_words_ai as $word)
                                            <span class="badge bg-label-danger me-1 mb-1">{{ $word }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        @elseif($submission->status === 'pending' || $submission->status === 'processing')
                             <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bx bx-loader-circle bx-spin me-2"></i>
                                <div>Processing by AI... Please check back later.</div>
                            </div>
                        @elseif($submission->status === 'failed')
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bx bx-error-circle me-2"></i>
                                <div>AI processing failed. Please contact support.</div>
                            </div>
                        @else
                            <p class="text-muted mb-0">Assessment results are not yet available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- BAWAH: Lecturer Feedback --}}
        @php
            $isCourseOwner = (auth()->user()->role === 'dosen' && $submission->assignment->course->user_id === auth()->id());
            $hasFeedback = $submission->score_dosen !== null || $submission->feedback_dosen !== null;
        @endphp

        @if ($isCourseOwner)
            <div class="card mt-4">
                <div class="card-header border-bottom">
                    <h5 class="mb-0">Lecturer Grading</h5>
                </div>
                <div class="card-body pt-4">
                    @if (session('grade_success'))
                        <div class="alert alert-success mb-3">{{ session('grade_success') }}</div>
                    @endif
                    
                    <form action="{{ route('submission.grade', $submission->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="dosen_score" class="form-label fw-bold">Final Score (0-100)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="dosen_score" name="dosen_score" min="0" max="100" value="{{ old('dosen_score', $submission->score_dosen) }}" required>
                                    <span class="input-group-text">/ 100</span>
                                </div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label for="feedback_dosen" class="form-label fw-bold">Feedback</label>
                                <textarea class="form-control" id="feedback_dosen" name="feedback_dosen" rows="3" placeholder="Write your feedback here...">{{ old('feedback_dosen', $submission->feedback_dosen) }}</textarea>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-save me-1"></i> Save Grade & Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @elseif ($hasFeedback)
            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white"><i class="bx bx-check-shield me-2"></i>Lecturer Feedback</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center mb-3 mb-md-0 border-end">
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">Final Grade</h6>
                            <h1 class="display-4 fw-bold text-success mb-0">{{ $submission->score_dosen }}</h1>
                            <span class="text-muted">out of 100</span>
                        </div>
                        <div class="col-md-9 ps-md-4">
                            <h6 class="fw-bold text-dark mb-2">Feedback:</h6>
                            <div class="p-3 bg-label-secondary rounded text-dark">
                                {!! nl2br(e($submission->feedback_dosen)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @endif
</div>
@endsection