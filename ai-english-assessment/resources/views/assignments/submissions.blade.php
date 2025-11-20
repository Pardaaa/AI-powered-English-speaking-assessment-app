@extends('layouts.master')

@section('title', 'Assignment Submissions')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">My Courses / {{ $course->name }} / {{ $assignment->title }} /</span> Submissions
    </h4>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 text-muted">
                Received Submissions: <span class="text-dark">{{ $assignment->submissions->count() }}</span>
            </h5>
        </div>
        
        <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Course
        </a>
    </div>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Student Submissions List</h5>
        </div>
        
        <ul class="list-group list-group-flush">
            @forelse ($assignment->submissions as $submission)
                <li class="list-group-item d-flex flex-column flex-sm-row justify-content-between align-items-sm-center p-3 hover:bg-gray-50 transition-all">
                    
                    <div class="d-flex align-items-center mb-3 mb-sm-0">
                        <div class="avatar avatar-md me-3 flex-shrink-0">
                            <span class="avatar-initial rounded-circle bg-label-primary d-flex align-items-center justify-content-center">
                                {{ strtoupper(substr($submission->user->name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0 text-dark">{{ $submission->user->name }}</h6>
                            <small class="text-muted">
                                <i class="bx bx-time-five me-1"></i> {{ $submission->created_at->format('d M Y, H:i') }}
                            </small>
                            <div class="d-block d-sm-none mt-1">
                                <small class="text-muted"><i class="bx bx-file"></i> {{ Str::limit($submission->original_filename, 20) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-none d-md-flex align-items-center gap-4 mx-4">
                         <div class="text-center px-3 border-end">
                            <small class="d-block text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">AI Score</small>
                            @if($submission->final_score_ai)
                                <span class="fw-bold text-primary fs-5">{{ number_format($submission->final_score_ai, 0) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                         <div class="text-center px-3">
                            <small class="d-block text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Grade</small>
                            @if($submission->score_dosen)
                                <span class="fw-bold text-success fs-5">{{ $submission->score_dosen }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 justify-content-between justify-content-sm-end w-100 w-sm-auto">
                        <span class="badge
                            @if($submission->status == 'pending') bg-label-warning
                            @elseif($submission->status == 'processing') bg-label-info
                            @elseif($submission->status == 'completed') bg-label-success
                            @elseif($submission->status == 'failed') bg-label-danger
                            @endif">
                            {{ ucfirst($submission->status) }}
                        </span>

                        <a href="{{ route('submission.show', $submission->id) }}" class="btn btn-sm btn-outline-primary">
                            View & Grade
                        </a>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center p-5">
                    <div class="mb-3">
                        <span class="avatar-initial rounded-circle bg-label-secondary p-4 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bx bx-folder-open fs-1"></i>
                        </span>
                    </div>
                    <h4>No submissions yet!</h4>
                    <p class="text-muted">Students haven't submitted any work for this assignment.</p>
                </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection