@extends('layouts.master')

@section('title', 'My Enrolled Courses')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        My Enrolled Courses
    </h4>

    <div class="row">
        @forelse ($enrolledCourses as $course)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $course->name }}</h5>
                        <h6 class="card-subtitle text-muted mb-2">{{ $course->code }} - {{ $course->semester }}</h6>
                        <p class="card-text">
                            {{ Str::limit($course->description ?? 'No description available.', 100) }}
                        </p>
                        <a href="{{ route('student.courses.show', $course->id) }}" class="card-link">View Course</a> 
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    You are not currently enrolled in any courses.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection