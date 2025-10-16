@extends('layouts.master')

@section('title', 'Create New Course')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">My Courses /</span> Create New Course
        </h4>

        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">New Course Information</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('courses.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label" for="course-name">Course Name</label>
                                <input type="text" class="form-control" id="course-name" name="name"
                                    placeholder="Example: English Communication Technique" value="{{ old('name') }}"
                                    required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="course-code">Course Code</label>
                                <input type="text" class="form-control" id="course-code" name="code"
                                    placeholder="Example: IN200" value="{{ old('code') }}" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="course-semester">Semester</label>
                                <input type="text" class="form-control" id="course-semester" name="semester"
                                    placeholder="Example: Ganjil 2025/2026" value="{{ old('semester') }}" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="course-description">Description</label>
                                <textarea id="course-description" class="form-control" name="description"
                                    placeholder="Description of the course. (Optional)">{{ old('description') }}</textarea>
                            </div>

                            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Course</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
