@extends('layouts.master')

@section('title', 'Submit Assignment')

@section('web-content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">My Courses / Course /</span> Submit Assignment
        </h4>

        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Upload Your Speaking Practice File</h5>
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

                        <form action="{{ route('submission.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="assignment_id" value="{{ request()->query('assignment_id') }}">

                            <div class="mb-3">
                                <label for="submission_file" class="form-label">Select Video or Audio File</label>

                                <input class="form-control @error('submission_file') is-invalid @enderror" type="file"
                                    id="submission_file" name="submission_file"
                                    accept="video/mp4, video/webm, audio/mpeg, audio/wav" required />
                                @error('submission_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text mt-2">
                                    Accepted file types: MP4, WebM, MP3, WAV. Max size: 100MB.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="notes">Notes (Optional)</label>
                                <textarea id="notes" class="form-control" name="notes" rows="3"
                                    placeholder="You can add some notes for your lecturer here...">{{ old('notes') }}</textarea> {{-- Menambahkan old() --}}
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <span class="tf-icons bx bx-upload"></span>&nbsp; Submit My Work
                            </button>
                            <a href="#" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
