@extends('layouts.master')

@section('title', 'My Courses')

@section('web-content')
    <div class="card">
        <h5 class="card-header">My Courses List</h5>
        <div class="card-body">
            <a href="{{ route('courses.create') }}" class="btn btn-primary mb-4">
                <span class="tf-icons bx bx-plus"></span>&nbsp; Create New Course
            </a>

            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Semester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($courses as $course)
                            <tr>
                                <td><strong>{{ $course->name }}</strong></td>
                                <td><span class="badge bg-label-info me-1">{{ $course->code }}</span></td>
                                <td>{{ $course->semester }}</td>
                                <td>
                                    <div class="d-flex">
                                        <a class="btn btn-icon btn-sm btn-warning me-2" href="{{ route('courses.edit', $course->id) }}" data-bs-toggle="tooltip" title="Edit Course">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>

                                        <form action="{{ route('courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-danger" data-bs-toggle="tooltip" title="Delete Course"><i class="bx bx-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">You don't have any courses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection