@extends('layouts.master')

@section('title', 'My Enrolled Courses')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="card mb-4 bg-transparent shadow-none border-0">
        <div class="card-body p-0">
            <div class="row justify-content-between align-items-center">
                <div class="col-md-8">
                     <h4 class="fw-bold py-3 mb-0">
                        <span class="text-muted fw-light">Mahasiswa Area /</span> My Enrolled Courses
                    </h4>
                     <small class="text-muted">Track your progress and access assignments</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        @forelse ($enrolledCourses as $course)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 hover-shadow-md transition-all border shadow-sm">
                    <div class="position-relative">
                        <img class="card-img-top" src="{{ asset('assets/img/elements/2.jpg') }}" alt="Course Banner" style="height: 160px; object-fit: cover;" />
                        <span class="badge bg-label-primary position-absolute top-0 end-0 m-3 shadow-sm">{{ $course->code }}</span>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title mb-1">
                            <a href="{{ route('student.courses.show', $course->id) }}" class="text-body text-decoration-none stretched-link">
                                {{ $course->name }}
                            </a>
                        </h5>

                        <p class="text-muted small mb-3">
                            <i class="bx bx-calendar me-1"></i> {{ $course->semester }}
                        </p>

                        <p class="card-text text-secondary small">
                            {{ Str::limit($course->description ?? 'No description available.', 80) }}
                        </p>
                    </div>

                    <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                        <small class="text-muted d-flex align-items-center" title="Lecturer">
                            <i class="bx bx-user-voice me-1"></i> 
                            {{ $course->user->name ?? 'Unknown Lecturer' }}
                        </small>
                        
                         <small class="text-muted d-flex align-items-center">
                            <i class="bx bx-task me-1"></i> 
                            {{ $course->assignments->count() }} Assignments
                        </small>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border border-dashed">
                    <div class="card-body text-center p-5">
                        <div class="mb-3">
                             <span class="avatar-initial rounded-circle bg-label-secondary p-4">
                                <i class="bx bx-book-bookmark fs-1"></i>
                            </span>
                        </div>
                        <h4>You are not enrolled in any courses!</h4>
                        <p class="text-muted">Contact your lecturer to get enrolled in a class.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection