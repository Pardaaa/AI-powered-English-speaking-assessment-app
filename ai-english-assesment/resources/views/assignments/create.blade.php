@extends('layouts.master')

@section('title', 'Create New Assignment')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">My Courses / {{ $course->name }} /</span> Create New Assignment
        </h4>

        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Assignment Details</h5>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('courses.assignments.store', $course->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="assignment-title">Title</label>
                                <input type="text" class="form-control" id="assignment-title" name="title"
                                    placeholder="Example: Speaking Practice Week 1" value="{{ old('title') }}" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="assignment-description">Description</label>
                                <textarea id="assignment-description" class="form-control" name="description" rows="5"
                                    placeholder="Instruction for the assignment.">{{ old('description') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="due-date">Due Date (Optional)</label>
                                <input class="form-control" type="datetime-local" value="{{ old('due_date') }}"
                                    id="due-date" name="due_date" />
                            </div>

                            <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Assignment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
