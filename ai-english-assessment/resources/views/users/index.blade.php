@extends('layouts.master')

@section('title', 'User Management')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Administrasi /</span> User Management
    </h4>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 text-muted">Total Users: {{ $users->total() }}</h5>
        <div>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <span class="tf-icons bx bx-plus-circle"></span>&nbsp; Add User
            </a>
            <a href="{{ route('users.import.form') }}" class="btn btn-secondary ms-2">
                <span class="tf-icons bx bx-upload"></span>&nbsp; Import
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4" role="alert">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4" role="alert">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Registered Users</h5>
        </div>
        <ul class="list-group list-group-flush">
            @forelse ($users as $user)
                <li class="list-group-item d-flex justify-content-between align-items-center p-3 hover:bg-gray-50">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3">
                            {{-- placeholder avatar --}}
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </span>
                            {{-- foto profil, : --}}
                            {{-- <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" /> --}}
                        </div>
                        
                        <div>
                            <h6 class="mb-0 text-dark">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="me-4 d-none d-md-block">
                            @if($user->role == 'admin')
                                <span class="badge bg-label-danger"><i class="bx bx-crown me-1"></i> Admin</span>
                            @elseif($user->role == 'dosen')
                                <span class="badge bg-label-info"><i class="bx bx-briefcase me-1"></i> Dosen</span>
                            @else
                                <span class="badge bg-label-secondary"><i class="bx bx-user me-1"></i> Mahasiswa</span>
                            @endif
                        </div>

                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                
                                <a class="dropdown-item" href="mailto:{{ $user->email }}">
                                    <i class="bx bx-envelope me-1"></i> Email
                                </a>

                                @if(auth()->id() !== $user->id)
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center p-5">
                    <div class="mb-3">
                        <span class="avatar-initial rounded-circle bg-label-secondary p-4">
                            <i class="bx bx-user-x fs-1"></i>
                        </span>
                    </div>
                    <h4>No users found!</h4>
                    <p class="text-muted">Start by adding a new user or importing from Excel.</p>
                </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection