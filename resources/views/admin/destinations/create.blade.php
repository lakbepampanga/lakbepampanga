@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Add New Destination</h1>
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
            <form action="{{ route('admin.destinations.store') }}" method="POST" enctype="multipart/form-data"> 
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="landmark" {{ old('type') == 'landmark' ? 'selected' : '' }}>Landmark</option>
                            <option value="restaurant" {{ old('type') == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="city" class="form-label">City</label>
                        <div class="input-group">
                            <select class="form-select" id="city" name="city" required>
                                <option value="">Select City</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->name }}" {{ old('city') == $city->name ? 'selected' : '' }}>{{ $city->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCityModal">
                                <i class="bi bi-plus"></i> Add City
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="priority" class="form-label">Priority</label>
                        <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', 1) }}" min="1" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" step="any" required>
                    </div>
                    <div class="col-md-6">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" step="any" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="opening_time" class="form-label">Opening Time</label>
                        <input type="time" class="form-control" id="opening_time" name="opening_time" value="{{ old('opening_time') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="closing_time" class="form-label">Closing Time</label>
                        <input type="time" class="form-control" id="closing_time" name="closing_time" value="{{ old('closing_time') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="travel_time" class="form-label">Travel Time (minutes)</label>
                    <input type="number" class="form-control" id="travel_time" name="travel_time" value="{{ old('travel_time') }}" min="1" required>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Upload Image</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Destination
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add City Modal -->
<div class="modal fade" id="addCityModal" tabindex="-1" aria-labelledby="addCityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCityModalLabel">Add New City</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCityForm">
                    @csrf
                    <div class="mb-3">
                        <label for="cityName" class="form-label">City Name</label>
                        <input type="text" class="form-control" id="cityName" name="name" required>
                    </div>
                    <div id="cityFormError" class="alert alert-danger d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCityBtn">Save City</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
   document.addEventListener('DOMContentLoaded', function() {
    const saveCityBtn = document.getElementById('saveCityBtn');
    const cityNameInput = document.getElementById('cityName');
    const cityFormError = document.getElementById('cityFormError');
    const citySelect = document.getElementById('city');
    const addCityModal = document.getElementById('addCityModal');
    
    // Create a Bootstrap modal instance when the page loads
    const modalInstance = new bootstrap.Modal(addCityModal);
    
    saveCityBtn.addEventListener('click', function() {
        const cityName = cityNameInput.value.trim();
        
        if (!cityName) {
            showError('City name is required');
            return;
        }
        
        // Get the CSRF token
        const csrfToken = document.querySelector('input[name="_token"]').value;
        
        // Prepare form data
        const formData = new FormData();
        formData.append('name', cityName);
        formData.append('_token', csrfToken);
        
        // Send AJAX request to add city
        fetch('{{ route('cities.store') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Add the new city to the dropdown
                const option = new Option(data.city.name, data.city.name, true, true);
                citySelect.appendChild(option);
                
                // Reset form and close modal
                cityNameInput.value = '';
                cityFormError.classList.add('d-none');
                
                // Close the modal using the stored instance
                modalInstance.hide();
                
                // Show success notification
                alert('City added successfully!');
            } else {
                showError(data.message || 'Failed to add city');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Even if there's an error in the JavaScript, check if the city was added
            // by reloading the cities from the server
            reloadCities();
        });
    });
    
    function showError(message) {
        cityFormError.textContent = message;
        cityFormError.classList.remove('d-none');
    }
    
    // Function to reload cities in case of error
    function reloadCities() {
        fetch('{{ route('admin.cities.list') }}')
            .then(response => response.json())
            .then(data => {
                // Clear existing options except the first one
                while (citySelect.options.length > 1) {
                    citySelect.remove(1);
                }
                
                // Add cities from the server
                data.cities.forEach(city => {
                    const option = new Option(city.name, city.name);
                    citySelect.appendChild(option);
                });
                
                // Close the modal
                modalInstance.hide();
                
                // Reset form
                cityNameInput.value = '';
                cityFormError.classList.add('d-none');
                
                alert('City added successfully!');
            })
            .catch(error => {
                console.error('Error reloading cities:', error);
                alert('City may have been added. Please refresh the page to see updated list.');
            });
    }
});
</script>
@endsection