@extends('layouts.master')

@section('title', 'Dashboard')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="row mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="card h-100">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Welcome back, {{ explode(' ', Auth::user()->name)[0] }}!
                                    👋</h5>
                                <p class="mb-4">
                                    @if (Auth::user()->role === 'admin')
                                        Check the statistics below to see the platform growth.
                                    @else
                                        You have <span class="fw-bold">{{ $allCourses->count() }}</span> active courses
                                        regarding your learning journey.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="100"
                                    alt="View Badge User" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Quick Stats</h6>
                            <small class="text-muted">{{ ucfirst(Auth::user()->role) }}</small>
                        </div>
                        <ul class="p-0 m-0">
                            @if (auth()->user()->role === 'admin')
                                <li class="d-flex mb-3 pb-1">
                                    <div class="avatar flex-shrink-0 me-3"><span
                                            class="avatar-initial rounded bg-label-primary"><i
                                                class="bx bx-user"></i></span></div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Users</h6>
                                        </div>
                                        <div class="user-progress"><small
                                                class="fw-semibold">{{ $stats['users_count'] ?? 0 }}</small></div>
                                    </div>
                                </li>
                                <li class="d-flex mb-3 pb-1">
                                    <div class="avatar flex-shrink-0 me-3"><span
                                            class="avatar-initial rounded bg-label-warning"><i
                                                class="bx bx-book"></i></span></div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Total Courses</h6>
                                        </div>
                                        <div class="user-progress"><small
                                                class="fw-semibold">{{ $stats['courses_count_all'] ?? 0 }}</small></div>
                                    </div>
                                </li>
                            @elseif(auth()->user()->role === 'dosen')
                                <li class="d-flex mb-3 pb-1">
                                    <div class="avatar flex-shrink-0 me-3"><span
                                            class="avatar-initial rounded bg-label-success"><i
                                                class="bx bx-book"></i></span></div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">My Courses</h6>
                                        </div>
                                        <div class="user-progress"><small
                                                class="fw-semibold">{{ $stats['my_courses_count'] ?? 0 }}</small></div>
                                    </div>
                                </li>
                                <li class="d-flex mb-3 pb-1">
                                    <div class="avatar flex-shrink-0 me-3"><span
                                            class="avatar-initial rounded bg-label-info"><i
                                                class="bx bx-user-voice"></i></span></div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Total Students</h6>
                                        </div>
                                        <div class="user-progress"><small
                                                class="fw-semibold">{{ $stats['my_students_count'] ?? 0 }}</small></div>
                                    </div>
                                </li>
                            @elseif(auth()->user()->role === 'mahasiswa')
                                <li class="d-flex mb-3 pb-1">
                                    <div class="avatar flex-shrink-0 me-3"><span
                                            class="avatar-initial rounded bg-label-success"><i
                                                class="bx bx-book-reader"></i></span></div>
                                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                        <div class="me-2">
                                            <h6 class="mb-0">Enrolled</h6>
                                        </div>
                                        <div class="user-progress"><small
                                                class="fw-semibold">{{ $stats['enrolled_courses_count'] ?? 0 }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if (auth()->user()->role !== 'admin')

            @if ($recentCourses->count() > 0)
                <h5 class="py-2 mb-4 text-muted fw-light text-uppercase small ls-1 border-bottom">
                    Continue Learning
                </h5>

                <div class="row justify-content-center mb-5">
                    <div class="col-md-8 col-lg-6">
                        @php $course = $recentCourses->first(); @endphp
                        @include('components.course-card', ['course' => $course])
                    </div>
                </div>
            @endif


            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 mt-5">
                <h5 class="mb-0 text-muted fw-light text-uppercase small ls-1">All Courses</h5>

                <div class="d-none d-sm-flex gap-2">
                    <button type="button" class="btn btn-sm btn-secondary active"><i class="bx bx-grid-alt"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"><i class="bx bx-list-ul"></i></button>
                </div>
            </div>

            <div class="row g-4">
                @forelse($allCourses as $course)
                    <div class="col-md-6 col-xl-4">
                        @include('components.course-card', ['course' => $course])
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="bx bx-info-circle me-2"></i>
                            <div>You don't have any courses yet.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        @else
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="alert alert-primary">
                        <h5 class="alert-heading mb-1">Admin Dashboard</h5>
                        <p class="mb-0">Welcome to the administration panel. Use sidebar to manage data.</p>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
