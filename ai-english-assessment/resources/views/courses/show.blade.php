@extends('layouts.master')

@section('title', 'Course Details')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">My Courses /</span> {{ $course->name }}
            </h4>
            <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-outline-primary">
                <i class="bx bx-cog me-1"></i> Settings
            </a>
        </div>


        <div class="card mb-4 bg-transparent shadow-none border-0">
            <div class="card-body p-0">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xl me-3">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-book-open fs-3"></i></span>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $course->name }} ({{ $course->code }})</h5>
                        <small class="text-muted">{{ $course->semester }} &bull; {{ $course->students->count() }}
                            Students</small>
                        <p class="mt-1 mb-0 text-secondary small">{{ $course->description }}</p>
                    </div>
                </div>
            </div>
        </div>


        @if (session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mb-4">{{ session('error') }}</div>
        @endif
        @if (session('enroll_success'))
            <div class="alert alert-success mb-4">{{ session('enroll_success') }}</div>
        @endif
        @if (session('enroll_error'))
            <div class="alert alert-danger mb-4">{{ session('enroll_error') }}</div>
        @endif



        <div class="nav-align-top mb-4">
            <ul class="nav nav-pills mb-3 nav-fill" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active border shadow-sm mx-1" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-assignments" aria-controls="navs-assignments" aria-selected="true">
                        <i class="bx bx-task me-1"></i> Assignments
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link border shadow-sm mx-1" role="tab" data-bs-toggle="tab"
                        data-bs-target="#navs-students" aria-controls="navs-students" aria-selected="false">
                        <i class="bx bx-group me-1"></i> Enrolled Students
                        <span
                            class="badge rounded-pill badge-center h-px-20 w-px-20 bg-label-primary ms-1">{{ $course->students->count() }}</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content shadow-none border-0 p-0 bg-transparent">

                <div class="tab-pane fade show active" id="navs-assignments" role="tabpanel">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 text-muted">Classwork</h5>
                        <a href="{{ route('courses.assignments.create', $course->id) }}" class="btn btn-primary">
                            <span class="tf-icons bx bx-plus"></span> New Assignment
                        </a>
                    </div>

                    <div class="row g-4">
                        @forelse ($course->assignments as $assignment)
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 border shadow-sm hover-shadow-md transition-all">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-2">
                                                    <span class="avatar-initial rounded bg-label-info"><i
                                                            class="bx bx-task"></i></span>
                                                </div>
                                                <div>
                                                    <h6 class="card-title mb-0 text-truncate" style="max-width: 180px;"
                                                        title="{{ $assignment->title }}">
                                                        <a href="{{ route('assignments.submissions.index', [$course->id, $assignment->id]) }}"
                                                            class="text-body stretched-link">
                                                            {{ $assignment->title }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        @if ($assignment->due_date)
                                                            <i class="bx bx-time-five me-1"></i>
                                                            {{ $assignment->due_date->format('d M, H:i') }}
                                                        @else
                                                            No due date
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="dropdown position-relative" style="z-index: 2;">
                                                <button class="btn p-0" type="button" data-bs-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item"
                                                        href="{{ route('courses.assignments.edit', [$course->id, $assignment->id]) }}">
                                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                                    </a>
                                                    <form
                                                        action="{{ route('courses.assignments.destroy', [$course->id, $assignment->id]) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Delete this assignment?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bx bx-trash me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="card-text text-secondary small mb-4">
                                            {{ Str::limit($assignment->description ?? 'No description provided.', 80) }}
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-user-check me-1 text-muted"></i>
                                                <span class="fw-semibold">{{ $assignment->submissions->count() }}</span>
                                                <span class="text-muted small ms-1">/ {{ $course->students->count() }}
                                                    Submitted</span>
                                            </div>
                                            <a href="{{ route('assignments.submissions.index', [$course->id, $assignment->id]) }}"
                                                class="btn btn-icon btn-sm btn-outline-primary position-relative"
                                                style="z-index: 2;">
                                                <i class="bx bx-right-arrow-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card border border-dashed">
                                    <div class="card-body text-center p-5">
                                        <div class="mb-3">
                                            <span class="avatar-initial rounded-circle bg-label-secondary p-4">
                                                <i class="bx bx-task fs-1"></i>
                                            </span>
                                        </div>
                                        <h4>No assignments created yet!</h4>
                                        <p class="text-muted">Create your first assignment to get started.</p>
                                        <a href="{{ route('courses.assignments.create', $course->id) }}"
                                            class="btn btn-primary">Create Assignment</a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="tab-pane fade" id="navs-students" role="tabpanel">
                    <div class="row">
                        <div class="col-md-8 mb-4">
                            <div class="card h-100 border shadow-sm">
                                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Class Roster</h5>
                                    <span class="badge bg-label-primary">{{ $course->students->count() }} Students</span>
                                </div>
                                <div class="table-responsive text-nowrap" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Student</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="table-border-bottom-0">
                                            @forelse ($course->students as $student)
                                                <tr>
                                                    <td width="50">
                                                        <div class="avatar">
                                                            <span
                                                                class="avatar-initial rounded-circle bg-label-secondary">{{ substr($student->name, 0, 2) }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $student->name }}</strong><br>
                                                        <small class="text-muted">{{ $student->email }}</small>
                                                    </td>
                                                    <td class="text-end">
                                                        <form action="{{ route('courses.unenroll', $course->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Remove {{ $student->name }} from class?');">
                                                            @csrf @method('DELETE')
                                                            <input type="hidden" name="student_id"
                                                                value="{{ $student->id }}">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="tooltip" title="Remove Student">
                                                                <i class="bx bx-user-minus"></i> Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center py-5 text-muted">
                                                        <i class="bx bx-group fs-1 mb-2 d-block"></i>
                                                        No students enrolled yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card border shadow-sm">
                                <div class="card-header bg-lighter border-bottom">
                                    <h5 class="mb-0">Enroll Students</h5>
                                </div>
                                <div class="card-body mt-3">


                                    <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
                                        <li class="nav-item">
                                            <button type="button" class="nav-link active" role="tab"
                                                data-bs-toggle="tab" data-bs-target="#navs-manual"
                                                aria-controls="navs-manual" aria-selected="true">
                                                <i class="bx bx-user me-1"></i> Manual
                                            </button>
                                        </li>
                                        <li class="nav-item">
                                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                                data-bs-target="#navs-import" aria-controls="navs-import"
                                                aria-selected="false">
                                                <i class="bx bx-upload me-1"></i> Import CSV
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content p-0 shadow-none border-0">

                                        <div class="tab-pane fade show active" id="navs-manual" role="tabpanel">
                                            <form action="{{ route('courses.enroll', $course->id) }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">Select Student</label>
                                                    <select class="form-select" name="student_id" required
                                                        size="6">
                                                        @forelse ($availableStudents as $student)
                                                            <option value="{{ $student->id }}">{{ $student->name }}
                                                            </option>
                                                        @empty
                                                            <option disabled>No students available</option>
                                                        @endforelse
                                                    </select>
                                                    @if ($availableStudents->isEmpty())
                                                        <div class="form-text mt-2 text-warning small">
                                                            <i class="bx bx-info-circle"></i> All students enrolled.
                                                        </div>
                                                    @endif
                                                </div>
                                                <button class="btn btn-primary w-100" type="submit"
                                                    {{ $availableStudents->isEmpty() ? 'disabled' : '' }}>
                                                    <i class="bx bx-user-plus me-1"></i> Enroll Selected
                                                </button>
                                            </form>
                                            <div class="mt-3 text-center">
                                            </div>
                                        </div>

                                        <div class="tab-pane fade" id="navs-import" role="tabpanel">
                                            <form action="{{ route('courses.import_students', $course->id) }}"
                                                method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="student_file" class="form-label">Upload CSV File</label>
                                                    <input class="form-control" type="file" id="student_file"
                                                        name="student_file" accept=".csv" required>
                                                    <div class="form-text mt-2 small">
                                                        Format: <strong>Email</strong> (1 column only).<br>
                                                        First row (header) is ignored.
                                                    </div>
                                                    <div class="bg-light p-2 rounded mt-2 border">
                                                        <small class="d-block text-muted mb-1">Example CSV:</small>
                                                        <code class="d-block text-dark small">Email</code>
                                                        <code class="d-block text-dark small">student1@email.com</code>
                                                        <code class="d-block text-dark small">student2@email.com</code>
                                                    </div>
                                                </div>
                                                <button class="btn btn-secondary w-100" type="submit">
                                                    <i class="bx bx-upload me-1"></i> Import & Enroll
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
