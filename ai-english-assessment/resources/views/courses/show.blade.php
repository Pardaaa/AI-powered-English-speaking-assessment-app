@extends('layouts.master')

@section('title', 'Course Details')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">My Courses /</span> {{ $course->name }}
    </h4>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Course Information</h5>
            <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-sm btn-primary">Edit Course</a>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Course Name:</strong><br> {{ $course->name }}</p>
                    <p class="mb-0"><strong>Course Code:</strong><br> <span class="badge bg-label-info">{{ $course->code }}</span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Semester:</strong><br> {{ $course->semester }}</p>
                    <p class="mb-0"><strong>Description:</strong><br> {{ $course->description ?? 'No description provided.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100"> 
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assignments</h5>
                    <a href="{{ route('courses.assignments.create', $course->id) }}" class="btn btn-sm btn-primary">New Assignment</a>
                </div>
                <div class="card-body">
                     @if (session('success') && Str::contains(session('success'), 'assignment'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                     @if (session('error') && Str::contains(session('error'), 'assignment'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse ($course->assignments as $assignment)
                                    <tr>
                                        <td>
                                            <a href="{{ route('assignments.submissions.index', [$course->id, $assignment->id]) }}">
                                                <strong>{{ $assignment->title }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $assignment->due_date ? $assignment->due_date->format('d M Y, H:i') : 'No due date' }}</td>
                                        <td>
                                            <div class="d-flex">
                                                <a class="btn btn-icon btn-sm btn-warning me-2" href="{{ route('courses.assignments.edit', [$course->id, $assignment->id]) }}" data-bs-toggle="tooltip" title="Edit Assignment">
                                                    <i class="bx bx-edit-alt"></i>
                                                </a>
                                                <form action="{{ route('courses.assignments.destroy', [$course->id, $assignment->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this assignment?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete Assignment"><i class="bx bx-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No assignments created yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100"> 
                <div class="card-header">
                    <h5 class="mb-0">Enrolled Students</h5>
                 </div>
                 <div class="card-body">
                     @if (session('enroll_success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('enroll_success') }}
                        </div>
                    @endif
                     @if (session('enroll_error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('enroll_error') }}
                        </div>
                    @endif

                    <form action="{{ route('courses.enroll', $course->id) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="input-group">
                            <select class="form-select" name="student_id" required>
                                <option value="" disabled selected>Select student to enroll...</option>
                                @foreach ($availableStudents as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary" type="submit">Enroll Student</button>
                        </div>
                         @if($availableStudents->isEmpty() && $course->students->count() > 0) 
                            <small class="form-text text-muted">All available students are already enrolled in this course.</small>
                         @elseif($availableStudents->isEmpty() && $course->students->count() == 0) 
                            <small class="form-text text-muted">No students available to enroll. <a href="{{ route('users.create') }}">Add new student?</a></small>
                         @endif
                    </form>

                    <hr class="my-4">

                    <h6 class="mb-3">Currently Enrolled:</h6>
                    @if($course->students->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach ($course->students as $student)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-0"> 
                                    {{ $student->name }} ({{ $student->email }})
                                    <form action="{{ route('courses.unenroll', $course->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove {{ $student->name }} from this course?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                        <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2">Remove</button> 
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No students enrolled yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

