@extends('layouts.master')

@section('title', 'Dashboard')

@section('web-content')
<div class="row">
    <div class="col-lg-8 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Welcome, {{ Auth::user()->name }}! 👋</h5>
                        <p class="mb-4">
                            Welcome to the AI English Assessment platform.
                            You can manage your courses and see student progress here.
                        </p>

                        <a href="{{ route('courses.index') }}" class="btn btn-sm btn-outline-primary">View My Courses</a>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}"
                             height="140" alt="View Badge User"
                             data-app-dark-img="illustrations/man-with-laptop-dark.png"
                             data-app-light-img="illustrations/man-with-laptop-light.png" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-4 order-1">
        <div class="row">
            <div class="col-lg-6 col-md-12 col-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary"><i class="bx bxs-briefcase"></i></span>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1">Total Courses</span>
                        <h3 class="card-title mb-2">12</h3>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-info"><i class="bx bxs-group"></i></span>
                            </div>
                        </div>
                        <span>Total Students</span>
                        <h3 class="card-title text-nowrap mb-1">150</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
