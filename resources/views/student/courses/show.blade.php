@extends('layouts.master')

@section('title', 'Course Details')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">My Enrolled Courses /</span> {{ $course->name }}
        </h4>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Course Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Course Name:</strong><br> {{ $course->name }}</p>
                        <p class="mb-0"><strong>Course Code:</strong><br> <span
                                class="badge bg-label-info">{{ $course->code }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Semester:</strong><br> {{ $course->semester }}</p>
                        <p class="mb-0"><strong>Description:</strong><br>
                            {{ $course->description ?? 'No description provided.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Assignments</h5>
            </div>
            <div class="card-body">
                @if ($course->assignments->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach ($course->assignments as $assignment)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $assignment->title }}</h6>
                                    <small class="text-muted">
                                        Due:
                                        {{ $assignment->due_date ? $assignment->due_date->format('d M Y, H:i') : 'No due date' }}
                                    </small>
                                </div>
                                <a href="{{ route('submission.create') }}?assignment_id={{ $assignment->id }}"
                                    class="btn btn-sm btn-outline-primary">View/Submit</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted mb-0">There are no assignments for this course yet.</p>
                @endif
            </div>
        </div>

    </div>
@endsection
