@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Add New Route</h1>
        <a href="{{ route('admin.routes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.routes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="route_name" class="form-label">Route Name</label>
                        <input type="text" class="form-control" id="route_name" name="route_name" 
                               value="{{ old('route_name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="route_color" class="form-label">Route Color</label>
                        <input type="text" class="form-control" 
                               id="route_color" name="route_color" 
                               value="{{ old('route_color') }}"
                               placeholder="e.g., red, blue, green">
                        <div class="form-text">Enter a color name (e.g., red, blue) or code (e.g., #FF0000)</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" 
                              rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="route_image" class="form-label">Route Image</label>
                    <input type="file" class="form-control" id="route_image" name="route_image" 
                           accept="image/*">
                    <div class="form-text">Accepted formats: JPG, PNG, GIF, WEBP, SVG. Max size: 2MB</div>
                    
                    <!-- Image Preview -->
                    <div id="imagePreview" class="mt-2" style="display: none;">
                        <p class="mb-2">Preview:</p>
                        <img src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Route
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Image preview functionality
document.getElementById('route_image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(e.target.files[0]);
    } else {
        preview.style.display = 'none';
    }
});
</script>
@endpush
@endsection