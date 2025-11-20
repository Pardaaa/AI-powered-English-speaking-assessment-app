@extends('layouts.master')

@section('title', 'Import Users')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">User Management /</span> Import Users
    </h4>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upload User CSV File</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="alert('Please create a CSV file with columns: Name, Email, Password, Role')">Info Template</button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-info-circle me-1"></i> CSV Format Instructions:</h6>
                        <p class="mb-0 small">Please ensure your file is in <strong>.csv</strong> format (Comma delimited). The first row (header) will be ignored. Required columns in order:</p>
                        <code class="d-block mt-2 bg-white p-2 rounded border">Name, Email, Password, Role</code>
                    </div>

                    <form action="{{ route('users.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <p class="mb-1"><strong>Import failed:</strong></p>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if (session('import_success'))
                            <div class="alert alert-success" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('import_success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                <i class="bx bx-x-circle me-1"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="file_import" class="form-label">Select CSV File</label>
                            <input class="form-control" type="file" id="file_import" name="file_import" required accept=".csv">
                            <div class="form-text mt-2">
                                Maximum file size: 2MB.
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <span class="tf-icons bx bx-upload"></span> Start Import
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection