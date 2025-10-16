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
                        <p class="mb-0"><strong>Course Code:</strong><br> <span
                                class="badge bg-label-info">{{ $course->code }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Semester:</strong><br> {{ $course->semester }}</p>
                        <p class="mb-0"><strong>Description:</strong><br>
                            {{ $course->description ?? 'No description.' }}</p>
                    </div>
                </div>
            </div>
        </div>


        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Assignments</h5>
                <a href="{{ route('courses.assignments.create', $course->id) }}" class="btn btn-sm btn-primary">New
                    Assignment</a>
            </div>
            <div class="card-body">
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
                                    <td><strong>{{ $assignment->title }}</strong></td>
                                    <td>{{ $assignment->due_date ? $assignment->due_date->format('d M Y, H:i') : 'No due date' }}
                                    </td>
                                    <td>
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
@endsection
