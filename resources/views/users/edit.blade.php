@extends('layouts.master')

@section('title', 'Edit User')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">User Management /</span> Edit User
    </h4>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit User Information</h5>
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

                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label" for="user-name">Full Name</label>
                            <input type="text" class="form-control" id="user-name" name="name" placeholder="John Doe" value="{{ old('name', $user->name) }}" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="user-email">Email</label>
                            <input type="email" class="form-control" id="user-email" name="email" placeholder="john.doe@example.com" value="{{ old('email', $user->email) }}" required />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="user-role">Role</label>
                            <select id="user-role" class="form-select" name="role" required>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="dosen" {{ old('role', $user->role) == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="mahasiswa" {{ old('role', $user->role) == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                            </select>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" placeholder="" autocomplete="new-password" />
                            </div>
                            <div class="form-text">Leave blank if you don't want to change the password.</div>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password_confirmation">Confirm New Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="" autocomplete="new-password" />
                            </div>
                        </div>
                        
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection