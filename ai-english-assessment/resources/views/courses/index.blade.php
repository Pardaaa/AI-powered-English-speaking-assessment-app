@extends('layouts.master')

@section('title', 'My Courses')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-4 bg-transparent shadow-none border-0">
            <div class="card-body p-0">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-6">
                        <h4 class="fw-bold py-3 mb-0">
                            <span class="text-muted fw-light">Dosen Area /</span> My Courses
                        </h4>
                        <small class="text-muted">Manage your classes and assignments</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="{{ route('courses.create') }}" class="btn btn-primary">
                            <span class="tf-icons bx bx-plus"></span>&nbsp; Create New Course
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mb-4" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="row mb-5">
            @forelse ($courses as $course)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 hover-shadow-md transition-all">
                        <div class="position-relative">
                            <!-- Updated Image Source to use the Model Accessor -->
                            <img class="card-img-top" src="{{ $course->image_url }}" alt="Course Banner"
                                style="height: 160px; object-fit: cover;" />
                            <span
                                class="badge bg-label-primary position-absolute top-0 end-0 m-3">{{ $course->code }}</span>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('courses.show', $course->id) }}"
                                        class="text-body text-decoration-none stretched-link">
                                        {{ $course->name }}
                                    </a>
                                </h5>


                                <div class="dropdown position-relative" style="z-index: 2;">
                                    <button class="btn p-0" type="button" id="courseOpt{{ $course->id }}"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end"
                                        aria-labelledby="courseOpt{{ $course->id }}">
                                        <a class="dropdown-item" href="{{ route('courses.edit', $course->id) }}">
                                            <i class="bx bx-edit-alt me-1"></i> Edit
                                        </a>
                                        <form action="{{ route('courses.destroy', $course->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this course?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted small mb-3">
                                <i class="bx bx-calendar me-1"></i> {{ $course->semester }}
                            </p>

                            <p class="card-text text-secondary">
                                {{ Str::limit($course->description ?? 'No description available for this course.', 80) }}
                            </p>
                        </div>

                        <div
                            class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center">
                            <small class="text-muted d-flex align-items-center">
                                <i class="bx bx-user me-1"></i>
                                {{ $course->students->count() }} Students
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
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <div class="mb-3">
                                <span class="avatar-initial rounded-circle bg-label-secondary p-4">
                                    <i class="bx bx-book-open fs-1"></i>
                                </span>
                            </div>
                            <h4>No courses found!</h4>
                            <p class="text-muted">You haven't created any courses yet. Start by creating your first class.
                            </p>
                            <a href="{{ route('courses.create') }}" class="btn btn-primary">Create Course Now</a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection