@extends('layouts.master')

@section('title', 'Course Details')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">My Enrolled Courses /</span> {{ $course->name }}
        </h4>

        <div class="card mb-4 bg-transparent shadow-none border-0">
            <div class="card-body p-0">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xl me-3">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-book-open fs-3"></i></span>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $course->name }} ({{ $course->code }})</h5>
                        <small class="text-muted">{{ $course->semester }}</small>
                        <p class="mt-1 mb-0 text-secondary small">{{ $course->description ?? 'No description provided.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-4" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        <h5 class="py-2 mb-2 text-muted fw-light">Assignments</h5>

        <div class="row g-4">
            @forelse ($course->assignments as $assignment)
                @php
                    $submission = $assignment->submissions->first();
                @endphp

                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 border shadow-sm hover-shadow-md transition-all">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar me-2">
                                        <span
                                            class="avatar-initial rounded bg-label-{{ $submission ? 'success' : 'info' }}">
                                            <i class="bx {{ $submission ? 'bx-check-circle' : 'bx-task' }}"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="card-title mb-0 text-truncate" style="max-width: 180px;"
                                            title="{{ $assignment->title }}">
                                            {{ $assignment->title }}
                                        </h6>
                                        <small class="text-muted">
                                            @if ($assignment->due_date)
                                                <i class="bx bx-time-five me-1"></i>
                                                {{ $assignment->due_date->format('d M, H:i') }}
                                            @else
                                                No due date
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                @if ($submission)
                                    <span class="badge bg-label-success rounded-pill">Submitted</span>
                                @else
                                    <span class="badge bg-label-warning rounded-pill">To Do</span>
                                @endif
                            </div>

                            <p class="card-text text-secondary small mb-4">
                                {{ Str::limit($assignment->description ?? 'No instructions provided.', 80) }}
                            </p>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div>
                                    @if ($submission)
                                        <small class="text-muted">
                                            Status: <span
                                                class="fw-bold {{ $submission->status == 'completed' ? 'text-success' : 'text-warning' }}">{{ ucfirst($submission->status) }}</span>
                                        </small>
                                    @else
                                        <small class="text-muted">Not submitted yet</small>
                                    @endif
                                </div>

                                @if ($submission)
                                    <a href="{{ route('submission.show', $submission->id) }}"
                                        class="btn btn-sm btn-outline-info">
                                        <i class="bx bx-show me-1"></i> View Result
                                    </a>
                                @else
                                    <a href="{{ route('submission.create', ['assignment_id' => $assignment->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bx bx-upload me-1"></i> Submit Work
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border border-dashed">
                        <div class="card-body text-center p-5">
                            <div class="mb-3">
                                <span class="avatar-initial rounded-circle bg-label-secondary p-4">
                                    <i class="bx bx-task fs-1"></i>
                                </span>
                            </div>
                            <h4>No assignments yet!</h4>
                            <p class="text-muted">Your lecturer hasn't posted any assignments for this course.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection
