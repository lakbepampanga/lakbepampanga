@extends('layouts.app')

@section('content')
<header id="header" class="header d-flex bg-white fixed-top align-items-center">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
        <a href="/" class="logo d-flex align-items-center">
            <h1 class="sitename">Lakbe Pampanga</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="/index" class="{{ request()->is('index') ? 'active' : '' }}">Plan</a></li>
                <li><a href="/saved-itinerary" class="{{ request()->is('saved-itinerary') ? 'active' : '' }}">Saved Itineraries</a></li>
                <li><a href="/commuting-guide" class="{{ request()->is('commuting-guide') ? 'active' : '' }}">Commuting Guide</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-custom rounded-pill btn-md px-3 py-2">Logout</button>
                    </form>
                </li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
    </div>
</header>

<main class="main container mt-5 pt-5">
    <div class="container py-4">
        <h1 class="text-center mb-4">Your Saved Itineraries</h1>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($itineraries->isEmpty())
            <div class="text-center">
                <p class="text-muted">You haven't saved any itineraries yet.</p>
                <a href="{{ route('index') }}" class="btn btn-custom">Create New Itinerary</a>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 g-4">
                @foreach($itineraries as $itinerary)
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title">{{ $itinerary->name }}</h5>
                                    <small class="text-muted">{{ $itinerary->created_at->format('M d, Y') }}</small>
                                </div>
                                
                                <p class="mb-2"><strong>Duration:</strong> {{ $itinerary->duration_hours }} hours</p>
                                
                                <div class="destinations-list">
                                    @foreach($itinerary->itinerary_data as $index => $destination)
                                        <div class="destination-item mb-3 p-3 bg-light rounded">
                                            <h6 class="mb-2">{{ $index + 1 }}. {{ $destination['name'] }}</h6>
                                            <p class="text-muted small mb-1">{{ $destination['description'] }}</p>
                                            <div class="small">
                                                <span class="badge bg-primary">{{ $destination['type'] }}</span>
                                                <span class="ms-2">{{ $destination['visit_time'] }} mins</span>
                                            </div>
                                            <div class="small mt-2">
                                                <strong>Travel:</strong> {{ $destination['travel_time'] }} mins
                                            </div>
                                            <div class="small mt-1">
                                                <strong>Route:</strong> {{ $destination['commute_instructions'] }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-between mt-3">
                                    <button class="btn btn-sm btn-custom view-map" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#mapModal"
                                            data-itinerary="{{ json_encode($itinerary) }}">
                                        <i class="bi bi-map"></i> View Map
                                    </button>
                                    
                                    <form action="{{ route('itineraries.destroy', $itinerary->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this itinerary?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Map Modal -->
            <div class="modal fade" id="mapModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Itinerary Map</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="itineraryMap" style="height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</main>

@push('scripts')
<script>
let map, markers = [], directionsService, directionsRenderer;

// Initialize map modal functionality
document.querySelectorAll('.view-map').forEach(button => {
    button.addEventListener('click', function() {
        const itineraryData = JSON.parse(this.dataset.itinerary);
        const modalElement = document.getElementById('mapModal');
        const modal = new bootstrap.Modal(modalElement);
        
        // Clean up when modal is hidden
        modalElement.addEventListener('hidden.bs.modal', function () {
            // Clear markers
            markers.forEach(marker => marker.setMap(null));
            markers = [];
            
            // Clear directions
            if (directionsRenderer) {
                directionsRenderer.setMap(null);
            }
            
            // Remove modal backdrop if it's stuck
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            // Ensure body classes are cleaned up
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });

        // Initialize map after modal is shown
        modalElement.addEventListener('shown.bs.modal', function() {
            initializeMap(itineraryData);
        }, { once: true });

        modal.show();
    });
});

function initializeMap(itineraryData) {
    // Clear previous markers and routes
    markers.forEach(marker => marker.setMap(null));
    markers = [];
    if (directionsRenderer) {
        directionsRenderer.setMap(null);
    }

    // Initialize map
    map = new google.maps.Map(document.getElementById('itineraryMap'), {
        zoom: 13,
        center: { 
            lat: parseFloat(itineraryData.start_lat), 
            lng: parseFloat(itineraryData.start_lng) 
        }
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: true
    });

    // Add markers and draw route
    const pathCoordinates = [
        { lat: parseFloat(itineraryData.start_lat), lng: parseFloat(itineraryData.start_lng) }
    ];

    // Add starting point marker
    addMarker(
        parseFloat(itineraryData.start_lat),
        parseFloat(itineraryData.start_lng),
        'Starting Point',
        'S'
    );

    // Add destination markers
    itineraryData.itinerary_data.forEach((destination, index) => {
        const lat = parseFloat(destination.latitude);
        const lng = parseFloat(destination.longitude);
        
        addMarker(lat, lng, destination.name, (index + 1).toString());
        pathCoordinates.push({ lat, lng });
    });

    // Draw route
    drawRoute(pathCoordinates);
    
    // Fit bounds to show all markers
    const bounds = new google.maps.LatLngBounds();
    markers.forEach(marker => bounds.extend(marker.getPosition()));
    map.fitBounds(bounds);
}

function addMarker(lat, lng, title, label) {
    const marker = new google.maps.Marker({
        position: { lat, lng },
        map: map,
        title: title,
        label: {
            text: label,
            color: 'white',
            fontSize: '12px',
            fontWeight: 'bold'
        }
    });
    markers.push(marker);
}

function drawRoute(pathCoordinates) {
    if (pathCoordinates.length < 2) return;

    const waypoints = pathCoordinates.slice(1, -1).map(coord => ({
        location: new google.maps.LatLng(coord.lat, coord.lng),
        stopover: true
    }));

    directionsService.route({
        origin: new google.maps.LatLng(pathCoordinates[0].lat, pathCoordinates[0].lng),
        destination: new google.maps.LatLng(
            pathCoordinates[pathCoordinates.length - 1].lat,
            pathCoordinates[pathCoordinates.length - 1].lng
        ),
        waypoints: waypoints,
        travelMode: google.maps.TravelMode.DRIVING
    }, (response, status) => {
        if (status === google.maps.DirectionsStatus.OK) {
            directionsRenderer.setDirections(response);
        }
    });
}
</script>
@endpush

@endsection