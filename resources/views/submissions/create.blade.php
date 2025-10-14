@extends('layouts.master')

@section('title', 'Submit Assignment')

@section('web-content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">My Courses / English Communication Technique /</span> Submit Assignment
    </h4>

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upload Your Speaking Practice File</h5>
                </div>
                <div class="card-body">
                    <form action="#" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="submission_file" class="form-label">Select Video or Audio File</label>
                            
                            <input 
                                class="form-control" 
                                type="file" 
                                id="submission_file" 
                                name="submission_file"
                                accept="video/mp4, video/webm, audio/mpeg, audio/wav" 
                                required 
                            />
                            
                            <div class="form-text mt-2">
                                Accepted file types: MP4, WebM, MP3, WAV. Max size: 100MB.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes (Optional)</label>
                            <textarea id="notes" class="form-control" name="notes" rows="3" placeholder="You can add some notes for your lecturer here..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <span class="tf-icons bx bx-upload"></span>
                        </button>
                        <a href="#" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection