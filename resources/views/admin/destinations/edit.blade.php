@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Edit Destination: {{ $destination->name }}</h1>
        <a href="{{ route('admin.destinations.index') }}" class="btn btn-secondary">
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
            <form action="{{ route('admin.destinations.update', $destination) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $destination->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="landmark" {{ old('type', $destination->type) == 'landmark' ? 'selected' : '' }}>Landmark</option>
                            <option value="restaurant" {{ old('type', $destination->type) == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                            <option value="museum" {{ old('type', $destination->type) == 'museum' ? 'selected' : '' }}>Museum & Art</option>
                            <option value="shopping" {{ old('type', $destination->type) == 'shopping' ? 'selected' : '' }}>Shopping</option>
                            <option value="nature" {{ old('type', $destination->type) == 'nature' ? 'selected' : '' }}>Nature & Outdoors</option>
                            <option value="religious" {{ old('type', $destination->type) == 'religious' ? 'selected' : '' }}>Religious Site</option>
                            <option value="entertainment" {{ old('type', $destination->type) == 'entertainment' ? 'selected' : '' }}>Entertainment</option>
                            <option value="cultural" {{ old('type', $destination->type) == 'cultural' ? 'selected' : '' }}>Cultural Site</option>
                            <option value="park" {{ old('type', $destination->type) == 'park' ? 'selected' : '' }}>Park & Recreation</option>
                            <option value="market" {{ old('type', $destination->type) == 'market' ? 'selected' : '' }}>Local Market</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="city" class="form-label">City</label>
                        <select class="form-select" id="city" name="city" required>
                            <option value="Angeles" {{ old('city', $destination->city) == 'Angeles' ? 'selected' : '' }}>Angeles</option>
                            <option value="Mabalacat" {{ old('city', $destination->city) == 'Mabalacat' ? 'selected' : '' }}>Mabalacat</option>
                            <option value="Magalang" {{ old('city', $destination->city) == 'Magalang' ? 'selected' : '' }}>Magalang</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', $destination->priority) }}" min="1" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $destination->latitude) }}" step="any" required>
                    </div>
                    <div class="col-md-6">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $destination->longitude) }}" step="any" required>
                    </div>
                </div>

                <!-- New Fields -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="average_price" class="form-label">Average Price (₱)</label>
                        <input type="number" class="form-control" id="average_price" name="average_price" value="{{ old('average_price', $destination->average_price) }}" step="0.01" min="0">
                    </div>
                    <div class="col-md-6">
                        <label for="recommended_visit_time" class="form-label">Recommended Visit Time (minutes)</label>
                        <input type="number" class="form-control" id="recommended_visit_time" name="recommended_visit_time" value="{{ old('recommended_visit_time', $destination->recommended_visit_time) }}" min="1">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="opening_time" class="form-label">Opening Time</label>
                        <input type="time" class="form-control" id="opening_time" name="opening_time" value="{{ old('opening_time', $destination->opening_time) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="closing_time" class="form-label">Closing Time</label>
                        <input type="time" class="form-control" id="closing_time" name="closing_time" value="{{ old('closing_time', $destination->closing_time) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="category_tags" class="form-label">Category Tags (comma-separated)</label>
                        <input type="text" class="form-control" id="category_tags" name="category_tags" value="{{ old('category_tags', $destination->category_tags) }}" placeholder="e.g., historic, food, culture">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="family_friendly" name="family_friendly" value="1" {{ old('family_friendly', $destination->family_friendly) ? 'checked' : '' }}>
                        <label class="form-check-label" for="family_friendly">
                            Family Friendly
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $destination->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="travel_time" class="form-label">Travel Time (minutes)</label>
                    <input type="number" class="form-control" id="travel_time" name="travel_time" value="{{ old('travel_time', $destination->travel_time) }}" min="1" required>
                </div>

                <!-- Image Upload -->
                <div class="mb-3">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <!-- Display Existing Image -->
                @if($destination->image)
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div>
                            <img src="{{ asset('storage/' . $destination->image) }}" alt="Destination Image" class="img-fluid" width="200">
                        </div>
                    </div>
                @endif

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Destination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection