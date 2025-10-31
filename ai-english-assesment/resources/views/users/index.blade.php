@extends('layouts.master')

@section('title', 'User Management')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Administrasi /</span> User Management
        </h4>

        <div class="card">
            <h5 class="card-header">User List</h5>
            <div class="card-body">
                <div class="mb-4">
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <span class="tf-icons bx bx-plus-circle"></span>&nbsp; Add New User
                    </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($users as $user)
                                <tr>
                                    <td><strong>{{ $user->name }}</strong></td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-label-primary me-1">{{ ucfirst($user->role) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a class="btn btn-icon btn-sm btn-warning me-2"
                                                href="{{ route('users.edit', $user->id) }}" data-bs-toggle="tooltip"
                                                title="Edit User">
                                                <i class="bx bx-edit-alt"></i>
                                            </a>

                                            @if (auth()->id() !== $user->id)
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-icon btn-sm btn-danger"
                                                        data-bs-toggle="tooltip" title="Delete User"><i
                                                            class="bx bx-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
