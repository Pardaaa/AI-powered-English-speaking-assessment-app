@extends('layouts.master')

@section('title', 'Assignment Submissions')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">My Courses / {{ $course->name }} / Assignment: {{ $assignment->title }} /</span> Submissions
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Student Submissions</h5>
            <a href="{{ route('courses.show', $course->id) }}" class="btn btn-sm btn-secondary">Back to Course</a>
        </div>
        <div class="card-body">

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($assignment->submissions as $submission)
                            <tr>
                                <td><strong>{{ $submission->user->name }}</strong></td>
                                <td>{{ $submission->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    <span class="badge
                                        @if($submission->status == 'pending') bg-label-warning
                                        @elseif($submission->status == 'processing') bg-label-info
                                        @elseif($submission->status == 'completed') bg-label-success
                                        @elseif($submission->status == 'failed') bg-label-danger
                                        @endif me-1">
                                        {{ ucfirst($submission->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('submission.show', $submission->id) }}" class="btn btn-sm btn-outline-info">View Details / Grade</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No submissions received for this assignment yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
