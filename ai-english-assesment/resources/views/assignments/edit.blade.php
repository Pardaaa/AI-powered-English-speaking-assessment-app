@extends('layouts.master')

@section('title', 'Edit Assignment')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">My Courses / {{ $course->name }} /</span> Edit Assignment
        </h4>

        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Assignment Details</h5>
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

                        <form action="{{ route('courses.assignments.update', [$course->id, $assignment->id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label" for="assignment-title">Title</label>
                                <input type="text" class="form-control" id="assignment-title" name="title"
                                    placeholder="Example: Speaking Practice Week 1"
                                    value="{{ old('title', $assignment->title) }}" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="assignment-description">Description</label>
                                <textarea id="assignment-description" class="form-control" name="description" rows="5"
                                    placeholder="Provide instructions for the assignment.">{{ old('description', $assignment->description) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="due-date">Due Date (Optional)</label>
                                <input class="form-control" type="datetime-local"
                                    value="{{ old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d\TH:i') : '') }}"
                                    id="due-date" name="due_date" />
                            </div>

                            <a href="{{ route('courses.show', $course->id) }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Assignment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
