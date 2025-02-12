@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Jeepney Route</h1>
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
            <form action="{{ route('admin.routes.update', $route) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="route_name" class="form-label">Route Name</label>
                        <input type="text" class="form-control" id="route_name" name="route_name" 
                               value="{{ old('route_name', $route->route_name) }}" required>
                    </div>
                    <div class="col-md-6">
        <label for="route_color" class="form-label">Route Color</label>
        <input type="text" class="form-control" 
               id="route_color" name="route_color" 
               value="{{ old('route_color', $route->route_color) }}"
               placeholder="e.g., red, blue, green">
        <div class="form-text">Enter a color name (e.g., red, blue) or code (e.g., #FF0000)</div>
    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" 
                              rows="3">{{ old('description', $route->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="route_image" class="form-label">Route Image</label>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <input type="file" class="form-control" id="route_image" name="route_image" 
                                   accept="image/*">
                            <div class="form-text">Accepted formats: JPG, PNG, GIF, WEBP, SVG. Max size: 2MB</div>
                        </div>
                        <div class="col-md-6">
                            @if($route->image_path)
                                <div class="current-image">
                                    <p class="mb-2">Current Image:</p>
                                    <img src="{{ asset('storage/' . $route->image_path) }}" 
                                         alt="{{ $route->route_name }}" 
                                         class="img-thumbnail" 
                                         style="max-height: 150px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" 
                                               name="remove_image" id="remove_image">
                                        <label class="form-check-label" for="remove_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">No image currently uploaded</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Route
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('route_image').addEventListener('change', function(e) {
    // Clear the "remove image" checkbox if a new image is selected
    if (document.getElementById('remove_image')) {
        document.getElementById('remove_image').checked = false;
    }
    
    // Preview the new image
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const currentImage = document.querySelector('.current-image img');
            if (currentImage) {
                currentImage.src = e.target.result;
            } else {
                const previewContainer = document.createElement('div');
                previewContainer.className = 'current-image';
                previewContainer.innerHTML = `
                    <p class="mb-2">Preview:</p>
                    <img src="${e.target.result}" 
                         alt="Preview" 
                         class="img-thumbnail" 
                         style="max-height: 150px;">
                `;
                document.querySelector('.col-md-6:last-child').innerHTML = '';
                document.querySelector('.col-md-6:last-child').appendChild(previewContainer);
            }
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
@endpush
@endsection