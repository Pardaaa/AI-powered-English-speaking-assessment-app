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

          <form id="submissionForm" action="{{ route('submission.store') }}" method="POST">
            @csrf
            <input type="hidden" name="assignment_id" value="{{ request()->query('assignment_id') }}">
            <input type="hidden" name="uploaded_url" id="uploaded_url" value="">
            <input type="hidden" name="original_filename" id="original_filename" value="">

            <div class="mb-3">
              <label for="submission_file" class="form-label">Select Video or Audio File</label>
              <input class="form-control" type="file" id="submission_file" name="submission_file"
                     accept="audio/*,video/mp4,video/webm" required />
            </div>

            <div class="mb-3">
              <label class="form-label" for="notes">Notes (Optional)</label>
              <textarea id="notes" class="form-control" name="notes" rows="3"
                        placeholder="You can add some notes for your lecturer here...">{{ old('notes') }}</textarea>
            </div>

            <div class="mb-3" id="progressWrap" style="display:none;">
              <label class="form-label">Upload progress</label>
              <div class="progress" style="height: 18px;">
                <div id="progressBar" class="progress-bar" role="progressbar"
                     style="width:0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
              </div>
              <div class="small mt-2" id="progressText">Preparing...</div>
            </div>

            <div class="alert alert-info" id="statusBox" style="display:none;"></div>
            <div class="alert alert-danger" id="errorBox" style="display:none;"></div>

            <button type="button" id="btnUpload" class="btn btn-primary">
              <span class="tf-icons bx bx-upload"></span>&nbsp; Upload & Submit
            </button>
            <a href="#" class="btn btn-outline-secondary">Cancel</a>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const fileInput = document.getElementById('submission_file');
  const btnUpload = document.getElementById('btnUpload');
  const form = document.getElementById('submissionForm');

  const uploadedUrl = document.getElementById('uploaded_url');
  const originalFilenameInput = document.getElementById('original_filename');

  const progressWrap = document.getElementById('progressWrap');
  const progressBar = document.getElementById('progressBar');
  const progressText = document.getElementById('progressText');

  const statusBox = document.getElementById('statusBox');
  const errorBox = document.getElementById('errorBox');

  const CSRF = "{{ csrf_token() }}";
  const CHUNK_URL = "{{ route('upload.chunk') }}";
  const COMPLETE_URL = "{{ route('upload.complete') }}";

  function setProgress(percent, text) {
    progressWrap.style.display = 'block';
    const p = Math.max(0, Math.min(100, percent));
    progressBar.style.width = p + '%';
    progressBar.textContent = p + '%';
    progressBar.setAttribute('aria-valuenow', String(p));
    progressText.textContent = text || '';
  }

  function showStatus(msg) {
    statusBox.style.display = 'block';
    statusBox.textContent = msg;
  }

  function showError(msg) {
    errorBox.style.display = 'block';
    errorBox.textContent = msg;
  }

  function clearAlerts() {
    statusBox.style.display = 'none'; statusBox.textContent = '';
    errorBox.style.display = 'none'; errorBox.textContent = '';
  }

  async function uploadChunked(file) {
    const CHUNK_SIZE = 10 * 1024 * 1024; 
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    const uploadId = crypto.randomUUID();

    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
      const start = chunkIndex * CHUNK_SIZE;
      const end = Math.min(start + CHUNK_SIZE, file.size);
      const chunkBlob = file.slice(start, end);

      const formData = new FormData();
      formData.append("upload_id", uploadId);
      formData.append("chunk_index", String(chunkIndex));
      formData.append("total_chunks", String(totalChunks));
      formData.append("original_name", file.name);
      formData.append("chunk", chunkBlob, `chunk_${chunkIndex}.part`);

      const res = await fetch(CHUNK_URL, {
        method: "POST",
        body: formData,
        headers: { "X-CSRF-TOKEN": CSRF },
      });

      if (!res.ok) {
        const txt = await res.text();
        throw new Error(`Chunk ${chunkIndex} failed: ${txt}`);
      }

      const percent = Math.floor(((chunkIndex + 1) / totalChunks) * 100);
      setProgress(percent, `Uploading`);
    }

    setProgress(100, "Finalizing...");
    const completeRes = await fetch(COMPLETE_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF },
      body: JSON.stringify({ upload_id: uploadId, original_name: file.name }),
    });

    if (!completeRes.ok) {
      const txt = await completeRes.text();
      throw new Error(`Complete failed: ${txt}`);
    }

    return await completeRes.json();
  }

  btnUpload.addEventListener('click', async () => {
    clearAlerts();

    const file = fileInput.files[0];
    if (!file) return showError("Please select a file first.");

    btnUpload.disabled = true;
    btnUpload.innerText = "Uploading...";

    try {
      showStatus("Uploading file. Please do not close this page.");
      setProgress(0, "Preparing...");

      const result = await uploadChunked(file);
      if (!result.file_url) throw new Error("Server did not return file_url.");

      // ✅ simpan URL file final dari server
      uploadedUrl.value = result.file_url;

      // ✅ simpan nama asli file user
      originalFilenameInput.value = file.name;

      showStatus("Upload complete. AI Processing");
      setProgress(100, "Done. Please do not close this page.");

      form.submit();
    } catch (e) {
      console.error(e);
      showError(e.message || "Upload failed.");
      btnUpload.disabled = false;
      btnUpload.innerText = "Upload & Submit";
    }
  });
})();
</script>
@endpush
