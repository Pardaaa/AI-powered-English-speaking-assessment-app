<div class="card h-100 hover-shadow-md transition-all group border shadow-sm">
    
    <div class="position-relative">
        <img class="card-img-top" 
             src="{{ $course->image ? asset('storage/' . $course->image) : asset('assets/img/elements/2.jpg') }}" 
             alt="Course Banner" 
             style="height: 160px; object-fit: cover;" />
             
        <span class="badge bg-white text-primary position-absolute top-0 end-0 m-3 shadow-sm">
            {{ $course->code }}
        </span>
    </div>

    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0 text-truncate">
                @php
                    $route = auth()->user()->role == 'dosen' 
                        ? route('courses.show', $course->id) 
                        : route('student.courses.show', $course->id);
                @endphp
                
                <a href="{{ $route }}" class="text-body text-decoration-none stretched-link">
                    {{ $course->name }}
                </a>
            </h5>

            @if(auth()->user()->role == 'dosen')
                <div class="dropdown position-relative" style="z-index: 2;">
                    <button class="btn p-0" type="button" id="courseOpt{{ $course->id }}" 
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="courseOpt{{ $course->id }}">
                        <a class="dropdown-item" href="{{ route('courses.edit', $course->id) }}">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <form action="{{ route('courses.destroy', $course->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this course?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bx bx-trash me-1"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <p class="text-muted small mb-3">
            <i class="bx bx-calendar me-1"></i> {{ $course->semester }}
        </p>

        <p class="card-text text-secondary flex-grow-1">
            {{ Str::limit($course->description ?? 'No description available for this course.', 80) }}
        </p>
    </div>

    <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center py-3">
        <small class="text-muted d-flex align-items-center">
            <i class="bx bx-user me-1"></i>
            {{ $course->students->count() }} Students
        </small>
        <small class="text-muted d-flex align-items-center">
            <i class="bx bx-task me-1"></i>
            {{ $course->assignments->count() }} Tasks
        </small>
    </div>
</div>