@extends('layouts.app')

@section('content')
<header id="header" class="header d-flex bg-white fixed-top align-items-center">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
        <!-- <a href="/" class="logo d-flex align-items-center">
            <h1 class="sitename">Lakbe Pampanga</h1>
        </a> -->

        <a href="/" class="logo d-flex align-items-center">
            <img src="{{ asset('img/lakbe-logo1.png') }}" alt="Lakbe Pampanga Logo" class="img-fluid">
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
            <li><a href="/user-home" class="{{ request()->is('user-home') ? 'active' : '' }}">Home</a></li>
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
<style>
.destination-item .btn-outline-success {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.destination-item .btn-outline-success:hover {
    background-color: #198754;
    color: white;
}

.destination-item .badge {
    padding: 0.5em 0.75em;
}

.badge.bg-success {
    font-size: 0.875rem;
}
</style>

<main class="main container mt-5 pt-5">
<div class="container py-4">
    <h1 class="text-center mb-4" id="section-title">Your Saved Itineraries</h1>

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
        <div class="d-flex flex-column gap-4">
            @foreach($itineraries as $itinerary)
                <div class="d-flex gap-4 align-items-start">
                    
                    <!-- Left: Itinerary Card -->
                    <div class="card itinerary-card flex-grow-1 shadow-sm"
                        data-itinerary="{{ json_encode($itinerary) }}" 
                        data-map-container="map-container-{{ $itinerary->id }}">

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title">{{ $itinerary->name }}</h5>
                                <small class="text-muted">{{ $itinerary->created_at->format('M d, Y') }}</small>
                            </div>

                            <p class="mb-2"><strong>Duration:</strong> {{ $itinerary->duration_hours }} hours</p>

                            <div class="destinations-list">
                                @foreach($itinerary->itinerary_data as $index => $destination)
                                    <div class="destination-item mb-3 p-3 bg-light rounded">
                                        <h6 class="mb-0">{{ $index + 1 }}. {{ $destination['name'] }}</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="text-muted small mb-1">{{ $destination['description'] }}</p>
                                            
                                            @if(isset($destination['visited']) && $destination['visited'])
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle-fill"></i> Visited
                                                </span>
                                            @else
                                                <form action="{{ route('destinations.markVisited') }}" method="POST" class="mark-visited-form">
                                                    @csrf
                                                    <input type="hidden" name="destination_id" value="{{ $destination['name'] }}" required>
                                                    <input type="hidden" name="saved_itinerary_id" value="{{ $itinerary->id }}" required>
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="bi bi-check-circle"></i> Mark Visited
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                        
                                        <div class="small">
                                            <span class="badge bg-primary">{{ $destination['type'] }}</span>
                                            <span class="ms-2">{{ $destination['visit_time'] }} mins</span>
                                        </div>
                                        
                                        <div class="small mt-2">
                                            <strong>Travel:</strong> {{ $destination['travel_time'] }} mins
                                        </div>
                                        
                                        <div class="small mt-1">
                                            <strong>Route:</strong> {{ $destination['commute_instructions'] }}
                                            <button type="button" 
                                                class="btn btn-sm btn-link text-danger report-instructions" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#reportModal"
                                                data-destination="{{ $destination['name'] }}"
                                                data-instructions="{{ $destination['commute_instructions'] }}"
                                                data-itinerary-id="{{ $itinerary->id }}">
                                                <i class="bi bi-exclamation-triangle"></i> Report Issue
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-between mt-3">
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
                            <!-- View Map Button (Mobile Only) -->
                            <button class="btn btn-outline-primary w-100 mt-3 d-md-none view-map-btn" 
                                data-map-container="map-container-{{ $itinerary->id }}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#mapModal">
                                <i class="bi bi-map"></i> View Map
                            </button>
                        </div>
                    </div>

                    <!-- Right: Map -->
                    <div id="map-container-{{ $itinerary->id }}" class="map-container d-none d-md-block" style="width: 50%; border: 1px solid #ddd; border-radius: 8px;"></div>
                </div>
            @endforeach
        </div>
    @endif
</div>

 <!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Itinerary Map</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Placeholder for the modal map -->
                <div id="modal-map-container" class="w-100" style="height: 100vh;"></div>
            </div>
        </div>
    </div>
</div>

    <!-- Report Modal -->
<div class="modal fade custom-report-modal" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Commute Instructions Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reportForm" action="{{ route('reports.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="destination_name" id="reportDestination">
                    <input type="hidden" name="itinerary_id" id="reportItinerary">
                    <input type="hidden" name="current_instructions" id="reportCurrentInstructions">
                    
                    <div class="mb-3">
                        <label class="form-label">What's wrong with these instructions?</label>
                        <select class="form-select" name="issue_type" required>
                            <option value="">Select an issue...</option>
                            <option value="incorrect_route">Incorrect Route</option>
                            <option value="outdated">Outdated Information</option>
                            <option value="unclear">Unclear Instructions</option>
                            <option value="missing_info">Missing Information</option>
                            <option value="other">Other Issue</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Please describe the issue:</label>
                        <textarea class="form-control" name="description" rows="4" required 
                                placeholder="Please provide details about what's wrong and any suggestions for improvement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

</main>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    let maps = {};
    let markers = {};
    let modalMap = null; // For the modal map instance
    let modalMapContainer = document.getElementById("modal-map-container");
    let directionsService = new google.maps.DirectionsService();
    let directionsRenderers = {};

    // Ensure Google Maps API is loaded
    if (typeof google === "undefined" || typeof google.maps === "undefined") {
        console.error("Google Maps API is not loaded.");
        return;
    }

    // Initialize maps for each itinerary
    document.querySelectorAll(".itinerary-card").forEach((card) => {
        const itineraryDataAttr = card.getAttribute("data-itinerary");
        const mapContainerId = card.getAttribute("data-map-container");

        // Ensure data attributes exist
        if (!itineraryDataAttr || !mapContainerId) {
            console.error(`Missing data attributes for card:`, card);
            return;
        }

        try {
            const itineraryData = JSON.parse(itineraryDataAttr);
            initializeMap(itineraryData, mapContainerId, card);
        } catch (error) {
            console.error("Error parsing itinerary data:", error);
        }
    });

    function initializeMap(itineraryData, containerId, itineraryCard) {
        const mapContainer = document.getElementById(containerId);

        if (!mapContainer) {
            console.error(`Map container with ID ${containerId} not found.`);
            return;
        }

        // Match map height with itinerary card height
        if (itineraryCard) {
            mapContainer.style.height = `${itineraryCard.offsetHeight}px`;
        }

        // Clear previous markers
        if (markers[containerId]) {
            markers[containerId].forEach((marker) => marker.setMap(null));
        }
        markers[containerId] = [];

        // Initialize map if not already created
        if (!maps[containerId]) {
            maps[containerId] = new google.maps.Map(mapContainer, {
                zoom: 13,
                center: {
                    lat: parseFloat(itineraryData.start_lat),
                    lng: parseFloat(itineraryData.start_lng),
                },
            });

            directionsRenderers[containerId] = new google.maps.DirectionsRenderer({
                map: maps[containerId],
                suppressMarkers: true,
            });
        }

        // Add starting point marker
        markers[containerId].push(
            new google.maps.Marker({
                position: {
                    lat: parseFloat(itineraryData.start_lat),
                    lng: parseFloat(itineraryData.start_lng),
                },
                map: maps[containerId],
                title: "Starting Point",
            })
        );

        // Add destination markers
        itineraryData.itinerary_data.forEach((destination, index) => {
            markers[containerId].push(
                new google.maps.Marker({
                    position: {
                        lat: parseFloat(destination.latitude),
                        lng: parseFloat(destination.longitude),
                    },
                    map: maps[containerId],
                    title: destination.name,
                })
            );
        });

        // Adjust map bounds
        const bounds = new google.maps.LatLngBounds();
        markers[containerId].forEach((marker) =>
            bounds.extend(marker.getPosition())
        );
        maps[containerId].fitBounds(bounds);
    }

    // Handle "View Map" button clicks for each itinerary
    document.querySelectorAll(".view-map-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const mapContainerId = this.getAttribute("data-map-container");
            const originalMap = maps[mapContainerId];

            if (!originalMap) {
                console.error(`No map found for container: ${mapContainerId}`);
                return;
            }

            // Initialize modal map if not already created
            if (!modalMap) {
                modalMap = new google.maps.Map(modalMapContainer, {
                    zoom: 13,
                    center: originalMap.getCenter(),
                });
            }

            // Synchronize modal map with the original map
            modalMap.setCenter(originalMap.getCenter());
            modalMap.setZoom(originalMap.getZoom());

            // Remove old markers from the modal map
            if (markers["modal-map"]) {
                markers["modal-map"].forEach((marker) => marker.setMap(null));
            }
            markers["modal-map"] = [];

            // Add all markers from the original map to the modal map
            markers[mapContainerId].forEach((marker) => {
                const position = marker.getPosition();
                const title = marker.getTitle();

                const modalMarker = new google.maps.Marker({
                    position,
                    map: modalMap,
                    title,
                });

                markers["modal-map"].push(modalMarker);
            });

            // Adjust bounds in the modal map
            const bounds = new google.maps.LatLngBounds();
            markers["modal-map"].forEach((marker) =>
                bounds.extend(marker.getPosition())
            );
            modalMap.fitBounds(bounds);

            // Trigger map resize event to make it display properly in the modal
            google.maps.event.trigger(modalMap, "resize");
        });
    });
});
</script>


@endpush




@endsection