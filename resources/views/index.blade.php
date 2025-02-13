<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampanga Itinerary Planner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyACtmc6ZSEVHBJLkk9wtiRj5ssvW1RDh4s&libraries=places"></script>

    <!-- Favicons -->
    <link href="{{ asset('img/lakbe2.png') }}" rel="icon">
    <link href="{{ asset('img/apple-touch-icon.png') }}" rel="apple-touch-icon">


  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect"   crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

<!-- Main CSS File -->
<link href="{{ asset('css/main2.css') }}" rel="stylesheet">


  <!-- Bootstrap CSS and JS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/main.js') }}"></script>
    <style>
     
        
.btn-custom{
    background-color: var(--button-color); /* Desired background color */
    color: var(--button-text-color);
}

.btn-custom:hover{
    background-color: var(--button-hover-color);
    color: white;
    transition: 0.3s;   /* Hover border color */
}

        #itinerary {
            margin-top: 20px;
        }
        #itinerary ul {
            list-style-type: none;
            padding: 0;
        }
        #itinerary li {
            margin-bottom: 15px;
        }
        #itinerary-form {
            margin-top: 20px;
        }


/* Form Styling */

.form-group {
    background-color: var(--surface-color);
    padding: 20px;
    border-radius: 8px;
    transition: all 0.3s ease-in-out;
}

.form-control{
    border-radius: 30px;
    border: 2px solid var(--accent-color);
    background-color: var(--background-color);
}

.form-label {
    font-weight: 600;
    font-size: 1rem;
    color: var(--heading-color);
}

.form-select {
    border-radius: 30px;
    padding: 10px;
    font-size: 0.95rem;
    border: 2px solid var(--accent-color);
    background-color: var(--background-color);
    color: var(--default-color);
    transition: border-color 0.3s ease-in-out;
}

.form-select:focus {
    border-color: var(--nav-hover-color);
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
}

.form-select option {
    background-color: var(--surface-color);
    color: var(--default-color);
}

.form-select option:hover,
.form-select option:checked {
    background-color: var(--accent-color) !important;
    color: var(--contrast-color) !important;
}

/* For better compatibility in some browsers */
.form-select:focus option:checked {
    background-color: var(--accent-color) !important;
    color: var(--contrast-color) !important;
}

#itinerary-form {
    max-width: 500px;
    margin: 0 auto;
}

/* Google Places Autocomplete styling */
.pac-container {
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--accent-color);
    margin-top: 2px;
}

.pac-item {
    padding: 8px 12px;
    font-family: var(--font-primary);
}

.pac-item:hover {
    background-color: var(--surface-color);
}

.pac-item-selected {
    background-color: var(--accent-color);
    color: var(--contrast-color);
}
.card {
    transition: transform 0.2s ease;
}

.card:hover {
    transform: translateY(-5px);
}

.badge {
    font-size: 0.8rem;
    padding: 0.5em 1em;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-content {
    background: white;
    padding: 2.5rem 3rem;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    min-width: 300px;
}

.spinner-ring {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--button-color);
    border-radius: 50%;
    margin: 0 auto 1.5rem;
    animation: spin 0.8s linear infinite;
}

.loading-text-container {
    text-align: center;
}

.loading-title {
    color: #333;
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
    padding: 0;
    display: inline-block;
}

.loading-dots {
    display: inline-block;
    margin-left: 2px;
}

.loading-dots .dot {
    display: inline-block;
    animation: dots 1.4s infinite;
    font-size: 1.2rem;
    line-height: 1;
    color: var(--button-color);
}

.loading-dots .dot:nth-child(2) {
    animation-delay: 0.2s;
}

.loading-dots .dot:nth-child(3) {
    animation-delay: 0.4s;
}

.loading-subtext {
    margin: 0.8rem 0 0 0;
    color: #666;
    font-size: 0.9rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes dots {
    0%, 20% {
        transform: translateY(0);
        opacity: 1;
    }
    50% {
        transform: translateY(-5px);
        opacity: 0.5;
    }
    80%, 100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .loading-content {
        min-width: auto;
        width: 85%;
        padding: 2rem;
    }
    
    .spinner-ring {
        width: 40px;
        height: 40px;
    }
    
    .loading-title {
        font-size: 1.1rem;
    }
}
    </style>

</head>
<body>
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

<main class="main container mt-5 pt-5 mb-5">
<div id="loading-spinner" class="loading-overlay" style="display: none;">
    <div class="loading-content">
        <div class="spinner-ring"></div>
        <div class="loading-text-container">
            <h3 class="loading-title">Generating your itinerary</h3>
            <div class="loading-dots">
                <span class="dot">.</span>
                <span class="dot">.</span>
                <span class="dot">.</span>
            </div>
            <p class="loading-subtext">This may take a few moments</p>
        </div>
    </div>
</div>

<div class="container mt-5 py-5">
    <!-- Heading -->
    <h1 class="text-center mb-4 " id="section-title">Plan Your Pampanga Itinerary</h2>

    <!-- Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3 mb-4">
        <button id="use-location" class="btn btn-custom rounded-pill">Use Current Location</button>

    </div>

    <!-- Location Selection -->
    <div class="form-group mb-4 text-center">
    <label for="location" class="form-label fw-bold">Or, enter your starting location:</label>
    <input type="text" id="location" class="form-control shadow-sm w-50 mx-auto" placeholder="Enter your starting location">
</div>

    <!-- Hours Input and Generate Itinerary Button -->
    <div id="itinerary-form" class="p-4 rounded" style="display: none;">
        <label for="hours" class="form-label fw-bold">How many hours do you have for travel?</label>
        <input type="number" id="hours" class="form-control shadow-sm mb-3 rounded-pill" min="1" max="12" placeholder="Enter hours">
        
        <div class="text-center">
            <button id="generate-itinerary" class="btn btn-custom rounded-pill w-auto">Generate Itinerary</button>
        </div>
    </div>

</div>

<!-- Maps and Itinerary -->
<div class="container mt-4">
    <div class="row">
        <!-- Itinerary Section (Left) -->
        <div class="col-md-6">
            <div id="itinerary" class="p-3 bg-light rounded shadow-sm" style="height: 500px; overflow-y: auto;">
                <h5 class="fw-bold mb-3">Your Itinerary</h5>
                
                <div id="itinerary-content">
                    @if(isset($itinerary) && count($itinerary) > 0)
                        @foreach($itinerary as $index => $item)
                            <div class="card mb-3 shadow-sm border-0" data-destination-id="{{ $index }}">
                                <div class="row g-0">
                                    <!-- Left Column: Placeholder Image -->
                                    <div class="col-md-4">
                                        <img src="https://placehold.co/100x300" 
                                             class="img-fluid rounded-start" 
                                             alt="Placeholder for {{ $item->name }}">
                                    </div>
                                    <!-- Right Column: Destination Content -->
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h5 class="card-title">{{ $item->name }} ({{ $item->type }})</h5>
                                                <button class="btn btn-sm btn-outline-primary edit-destination" 
                                                        data-index="{{ $index }}"
                                                        data-lat="{{ $item->latitude }}"
                                                        data-lng="{{ $item->longitude }}">
                                                    <i class="bi bi-pencil"></i> Change
                                                </button>
                                            </div>
                                            <p class="card-text text-muted">{{ $item->description }}</p>
                                            <p class="card-text"><strong>Travel Time:</strong> {{ $item->travel_time }}</p>
                                            <p class="card-text"><strong>Time to Spend:</strong> {{ $item->visit_time }}</p>
                                            <p class="card-text"><strong>Commute Instructions:</strong> {{ $item->commute_instructions }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Your generated itinerary will appear here.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Map Section (Right) -->
        <div class="col-md-6">
            <div id="map-container" class="d-none d-md-block"> <!-- Hidden on mobile -->
                <div id="map" class="w-100 rounded shadow-sm" style="height: 500px; border: 1px solid #e0e0e0;"></div>
            </div>
        </div>
    </div>

    <!-- View Map Button (Visible on Mobile Only) -->
    <button id="view-map-btn" class="btn btn-custom rounded-pill d-md-none position-fixed start-50 translate-middle-x" style="bottom: 140px; z-index: 1050;">
    View Map
</button>

</div>

<!-- Modal for Mobile Map -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Map View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0"> <!-- Remove padding to ensure full-screen map -->
                <div id="map-modal-placeholder" class="w-100 h-100"></div> 
            </div>
        </div>
    </div>
</div>

<!-- Add a modal for alternative destinations -->
<div class="modal fade" id="alternativeDestinationsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Choose Alternative Destination</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="alternative-destinations" class="list-group">
                    <!-- Alternative destinations will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

    </main>

    <footer id="footer" class="footer dark-background w-100">
  <div class="container-fluid text-center py-4">
    <p>© <span>Copyright</span> <strong class="px-1 sitename">Lakbe Pampanga</strong> <span>All Rights Reserved</span></p>
    <div class="credits">
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed By <a href="https://themewagon.com">ThemeWagon</a>
    </div>
  </div>
</footer>


    <!-- scripts -->
    <script>
    let map, userLat, userLng, markers = [], initialMarker = null, directionsService, directionsRenderer;
    let currentItineraryData = [];
    // Initialize Google Map
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 15.1347621, lng: 120.5903796 }, // Default center (Angeles City)
            zoom: 14
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true // Suppress default markers to use custom ones
        });
    }

    // Add marker with label to the map
    function addMarker(lat, lng, title, label) {
        const marker = new google.maps.Marker({
            position: { lat, lng },
            map: map,
            title: title,
            label: {
                text: label.toString(), // Convert label to string for the marker label
                color: "white",
                fontSize: "12px",
                fontWeight: "bold"
            },
            icon: {
                url: "http://maps.google.com/mapfiles/ms/icons/red.png", // Custom marker icon
                labelOrigin: new google.maps.Point(15, 10) // Adjust label position
            }
        });
        markers.push(marker);
    }

    // Clear all markers and route
    function clearMap() {
        // Clear markers
        markers.forEach(marker => marker.setMap(null));
        markers = [];

        // Clear route
        if (directionsRenderer) {
            directionsRenderer.set('directions', null);
        }
    }

    // Draw route using Google Directions Service
    function drawRoute(pathCoordinates) {
    if (pathCoordinates.length < 2) return; // No need to draw a route if fewer than 2 points

    const walkingRendererOptions = {
        map: map,
        polylineOptions: {
            strokeColor: '#00FF00', // Green for walking
            strokeOpacity: 0.6,
            strokeWeight: 4,
            icons: [{
                icon: { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3 }, // Dashed line
                offset: '0',
                repeat: '10px'
            }]
        },
        suppressMarkers: true
    };

    const drivingRendererOptions = {
        map: map,
        polylineOptions: {
            strokeColor: '#FF0000', // Red for driving
            strokeOpacity: 0.8,
            strokeWeight: 4
        },
        suppressMarkers: true
    };

    const routeQueue = [];

    for (let i = 0; i < pathCoordinates.length - 1; i++) {
        const start = new google.maps.LatLng(pathCoordinates[i].lat, pathCoordinates[i].lng);
        const end = new google.maps.LatLng(pathCoordinates[i + 1].lat, pathCoordinates[i + 1].lng);

        const distance = google.maps.geometry.spherical.computeDistanceBetween(start, end);
        const travelMode = distance < 1000 ? google.maps.TravelMode.WALKING : google.maps.TravelMode.DRIVING;

        routeQueue.push({
            origin: start,
            destination: end,
            travelMode: travelMode,
            rendererOptions: travelMode === google.maps.TravelMode.WALKING ? walkingRendererOptions : drivingRendererOptions
        });
    }

    // Process the route queue sequentially
    function processQueue(index) {
        if (index >= routeQueue.length) return;

        const { origin, destination, travelMode, rendererOptions } = routeQueue[index];

        const renderer = new google.maps.DirectionsRenderer(rendererOptions);

        directionsService.route(
            {
                origin: origin,
                destination: destination,
                travelMode: travelMode
            },
            (result, status) => {
                if (status === google.maps.DirectionsStatus.OK) {
                    renderer.setDirections(result);
                    processQueue(index + 1); // Process the next route in the queue
                } else {
                    console.error('Error drawing route:', status);
                    processQueue(index + 1); // Continue processing even if one fails
                }
            }
        );
    }

    processQueue(0); // Start processing the queue
}



    // Adjust the map to fit all markers
    function updateMapBounds() {
        const bounds = new google.maps.LatLngBounds();
        markers.forEach(marker => bounds.extend(marker.getPosition()));
        map.fitBounds(bounds);
    }

    // Event listener for "Use Current Location" button
    document.getElementById('use-location').addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((position) => {
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;

                document.getElementById('itinerary-form').style.display = 'block';

                map.setCenter(new google.maps.LatLng(userLat, userLng));
                setInitialLocationMarker(userLat, userLng, "Your Current Location");
            });
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    });

    // Set or update the initial location marker
    function setInitialLocationMarker(lat, lng, title) {
        if (initialMarker) {
            initialMarker.setMap(null); // Remove the previous initial marker
        }
        initialMarker = new google.maps.Marker({
            position: { lat, lng },
            map: map,
            title: title,
            icon: 'http://maps.google.com/mapfiles/kml/shapes/placemark_circle.png' // Blue marker icon for initial location
        });
    }

// Initialize Google Places Autocomplete
function initAutocomplete() {
    const input = document.getElementById('location');
    const autocomplete = new google.maps.places.Autocomplete(input, {
        componentRestrictions: { country: 'PH' }, // Restrict to Philippines
        bounds: new google.maps.LatLngBounds(
            new google.maps.LatLng(15.0843, 120.5200), // SW bounds of Pampanga
            new google.maps.LatLng(15.2843, 120.7200)  // NE bounds of Pampanga
        ),
        strictBounds: true
    });

    // When a place is selected
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        
        if (!place.geometry) {
            alert("No details available for this location");
            return;
        }

        // Set user location
        userLat = place.geometry.location.lat();
        userLng = place.geometry.location.lng();

        // Show the itinerary form
        document.getElementById('itinerary-form').style.display = 'block';

        // Center map and add marker
        map.setCenter(new google.maps.LatLng(userLat, userLng));
        setInitialLocationMarker(userLat, userLng, place.name || "Selected Location");
    });
}

    // Event listener for "Generate Itinerary" button
    // Update this part in your existing script section in the blade template
    document.getElementById('generate-itinerary').addEventListener('click', () => {
    const hours = document.getElementById('hours').value;

    if (!hours || hours <= 0) {
        alert("Please enter a valid number of hours.");
        return;
    }

    document.getElementById('loading-spinner').style.display = 'flex';


    fetch('/api/generate-itinerary', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            latitude: userLat,
            longitude: userLng,
            hours: hours,
            selected_location: null,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            document.getElementById('loading-spinner').style.display = 'none';

            if (Array.isArray(data)) {
                // Initialize currentItineraryData
                currentItineraryData = [...data];

                // Clear previous map markers/routes
                clearMap(); 

                // Setup path coordinates starting with user location
                const pathCoordinates = [{ lat: userLat, lng: userLng }]; 

                // Generate itinerary HTML
                let itineraryHTML = `
                    <div class="text-center mb-3">
                        <button class="btn btn-custom save-itinerary">
                            <i class="bi bi-save"></i> Save Itinerary
                        </button>
                    </div>`;

                    currentItineraryData.forEach((destination, index) => {
    console.log('Destination commute instructions:', destination.commute_instructions);
    console.log('Full destination data:', destination);
                        itineraryHTML += `
    <div class="card mb-3 shadow-sm border-0" data-destination-id="${index}">
        <div class="row g-0">
            <!-- Left Column: Image -->
            <div class="col-md-4 position-relative">
                <img src="${destination.image_url || 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg'}" 
                     class="img-fluid rounded-start" 
                     alt="${destination.name}"
                     onerror="this.src='https://www.svgrepo.com/show/508699/landscape-placeholder.svg'"
                     style="width: 100%; height: 250px; object-fit: cover;">
                <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge ${destination.type === 'restaurant' ? 'bg-success' : 'bg-primary'} rounded-pill">
                        <i class="bi ${destination.type === 'restaurant' ? 'bi-shop' : 'bi-geo-alt'}"></i>
                        ${destination.type.charAt(0).toUpperCase() + destination.type.slice(1)}
                    </span>
                </div>
            </div>
            <!-- Right Column: Content -->
            <div class="col-md-8">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title">
                            ${destination.name} (${destination.type})
                        </h5>
                    </div>
                    <p class="card-text text-muted">${destination.description}</p>
                    <p class="card-text"><strong>Travel Time:</strong> ${destination.travel_time}</p>
                    <p class="card-text"><strong>Time to Spend:</strong> ${destination.visit_time}</p>
<p class="card-text"><strong>Commute Instructions:</strong> ${destination.commute_instructions.map(instruction => instruction.instruction).join(' ')}</p>

                    <button class="btn btn-sm btn-custom edit-destination" 
                            data-index="${index}"
                            data-lat="${destination.latitude}"
                            data-lng="${destination.longitude}">
                        <i class="bi bi-pencil"></i> Change
                    </button>
                </div>
            </div>
        </div>
    </div>`;

    const lat = parseFloat(destination.latitude);
    const lng = parseFloat(destination.longitude);

    if (!isNaN(lat) && !isNaN(lng)) {
        addMarker(lat, lng, `${index + 1}. ${destination.name}`, index + 1);
        pathCoordinates.push({ lat: lat, lng: lng });
    }
});


                // Update the DOM
                document.getElementById('itinerary-content').innerHTML = itineraryHTML;

                // Add event listeners
                document.querySelector('.save-itinerary').addEventListener('click', () => {
    console.log('Current itinerary data before saving:', currentItineraryData);
    saveItinerary(currentItineraryData);
});

                document.querySelectorAll('.edit-destination').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const index = parseInt(e.target.closest('.edit-destination').dataset.index);
                        const lat = parseFloat(e.target.closest('.edit-destination').dataset.lat);
                        const lng = parseFloat(e.target.closest('.edit-destination').dataset.lng);
                        editingIndex = index; // Set the global editingIndex
                        fetchAlternativeDestinations(lat, lng);
                    });
                });

                // Draw the route using Google Directions Service
                if (pathCoordinates.length > 1) {
                    drawRoute(pathCoordinates);
                }

                // Adjust map to fit all markers
                updateMapBounds();
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            alert("An error occurred while generating the itinerary.");
        });
});

// Add this where you handle destination selection
async function handleDestinationSelect(newDestination) {
    try {
        const response = await fetch('/api/update-itinerary-item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                previousDestination: editingIndex > 0 ? currentItineraryData[editingIndex - 1] : null,
                newDestination: newDestination,
                nextDestination: editingIndex < currentItineraryData.length - 1 ? currentItineraryData[editingIndex + 1] : null,
                startLat: userLat,
                startLng: userLng
            }),
        });

        if (!response.ok) throw new Error('Failed to update itinerary');
        
        const updatedItem = await response.json();
        
        // Update the current itinerary data with the new destination
        currentItineraryData[editingIndex] = {
            name: newDestination.name,
            type: newDestination.type,
            description: newDestination.description,
            latitude: newDestination.latitude,
            longitude: newDestination.longitude,
            image_url: newDestination.image_url, // Make sure to include image_url
            travel_time: updatedItem.travel_time,
            visit_time: updatedItem.visit_time,
            commute_instructions: updatedItem.commute_instructions
        };

        // Update UI
        let itineraryHTML = `
            <div class="text-end mb-3">
                <button class="btn btn-custom save-itinerary">
                    <i class="bi bi-save"></i> Save Itinerary
                </button>
            </div>`;

        // Clear previous markers and routes
        clearMap();

        const pathCoordinates = [{ lat: userLat, lng: userLng }];

        // Rebuild the itinerary display with current data
        currentItineraryData.forEach((destination, index) => {
    console.log('Destination commute instructions:', destination.commute_instructions);
    console.log('Full destination data:', destination);
            itineraryHTML += `
                <div class="card mb-3 shadow-sm border-0" data-destination-id="${index}">
                    <div class="row g-0">
                        <!-- Left Column: Image -->
                        <div class="col-md-4">
                            <img src="${destination.image_url || 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg'}" 
                                 class="img-fluid rounded-start" 
                                 alt="${destination.name}"
                                 style="width: 100%; height: 250px; object-fit: cover;"
                                 onerror="this.src='https://www.svgrepo.com/show/508699/landscape-placeholder.svg'">
                        </div>
                        <!-- Right Column: Content -->
                        <div class="col-md-8">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title">
                                        ${destination.name} (${destination.type})
                                    </h5>
                                </div>
                                <p class="card-text text-muted">${destination.description}</p>
                                <p class="card-text"><strong>Travel Time:</strong> ${destination.travel_time}</p>
                                <p class="card-text"><strong>Time to Spend:</strong> ${destination.visit_time}</p>
<p class="card-text"><strong>Commute Instructions:</strong> ${destination.commute_instructions.map(instruction => instruction.instruction).join(' ')}</p>
                               <button class="btn btn-sm btn-custom edit-destination" 
                                        data-index="${index}"
                                        data-lat="${destination.latitude}"
                                        data-lng="${destination.longitude}">
                                    <i class="bi bi-pencil"></i> Change
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;

            const lat = parseFloat(destination.latitude);
            const lng = parseFloat(destination.longitude);

            if (!isNaN(lat) && !isNaN(lng)) {
                addMarker(lat, lng, `${index + 1}. ${destination.name}`, index + 1);
                pathCoordinates.push({ lat: lat, lng: lng });
            }
        });

        document.getElementById('itinerary-content').innerHTML = itineraryHTML;

        // Reattach event listeners
        document.querySelector('.save-itinerary').addEventListener('click', () => {
            console.log('Current itinerary data before saving:', currentItineraryData);
            saveItinerary(currentItineraryData);
        });

        document.querySelectorAll('.edit-destination').forEach(button => {
            button.addEventListener('click', (e) => {
                const btn = e.target.closest('.edit-destination');
                editingIndex = parseInt(btn.dataset.index);
                fetchAlternativeDestinations(
                    parseFloat(btn.dataset.lat),
                    parseFloat(btn.dataset.lng)
                );
            });
        });

        // Update the map
        if (pathCoordinates.length > 1) {
            drawRoute(pathCoordinates);
        }
        updateMapBounds();

        alternativesModal.hide();
        editingIndex = null;

    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update itinerary');
    }
}

// Function to update the itinerary UI
function updateItineraryUI() {
    let itineraryHTML = `
        <div class="text-end mb-3">
            <button class="btn btn-custom save-itinerary">
                <i class="bi bi-save"></i> Save Itinerary
            </button>
        </div>`;

   currentItineraryData.forEach((destination, index) => {
    console.log('Destination commute instructions:', destination.commute_instructions);
    console.log('Full destination data:', destination);
        itineraryHTML += `
            <div class="card mb-3 shadow-sm border-0" data-destination-id="${index}">
            <div class="row g-0">
                <!-- Left Column: Placeholder Image -->
                <div class="col-md-4">
                    <img src="https://www.svgrepo.com/show/508699/landscape-placeholder.svg" 
                         class="img-fluid rounded-start" 
                         alt="Placeholder for ${destination.name}">
                </div>
                <!-- Right Column: Content -->
                <div class="col-md-8">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title">
                                ${destination.name} (${destination.type})
                                
                            </h5>
                        </div>
                            <p class="card-text text-muted">${destination.description}</p>
                        <p class="card-text"><strong>Travel Time:</strong> ${destination.travel_time}</p>
                        <p class="card-text"><strong>Time to Spend:</strong> ${destination.visit_time}</p>
<p class="card-text"><strong>Commute Instructions:</strong> ${destination.commute_instructions.map(instruction => instruction.instruction).join(' ')}</p>
                        <button class="btn btn-sm btn-custom edit-destination" 
                                    data-index="${index}"
                                    data-lat="${destination.latitude}"
                                    data-lng="${destination.longitude}">
                                <i class="bi bi-pencil"></i> Change
                            </button>
                       
                        
                    </div>
                </div>
            </div>
        </div>`;
    });

    document.getElementById('itinerary-content').innerHTML = itineraryHTML;

    // Reattach event listeners
    document.querySelector('.save-itinerary').addEventListener('click', saveItinerary);
    document.querySelectorAll('.edit-destination').forEach(button => {
        button.addEventListener('click', (e) => {
            const index = parseInt(e.target.closest('.edit-destination').dataset.index);
            const lat = parseFloat(e.target.closest('.edit-destination').dataset.lat);
            const lng = parseFloat(e.target.closest('.edit-destination').dataset.lng);
            fetchAlternativeDestinations(lat, lng, index);
        });
    });
}

// Update your saveItinerary function
async function saveItinerary() {
    try {
        console.log('Saving itinerary data:', currentItineraryData);

        const response = await fetch('/api/save-itinerary', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                itinerary_data: currentItineraryData,
                start_lat: userLat,
                start_lng: userLng,
                duration_hours: parseInt(document.getElementById('hours').value)
            }),
        });

        if (!response.ok) {
            const result = await response.json();
            throw new Error(result.error || 'Failed to save itinerary');
        }
        
        const alertHTML = `
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050;" role="alert">
                Itinerary saved successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('afterbegin', alertHTML);
        
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) alert.remove();
        }, 3000);
    } catch (error) {
        console.error('Save Error:', error);
        const alertHTML = `
            <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 1050;" role="alert">
                ${error.message || 'Failed to save itinerary. Please try again.'}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('afterbegin', alertHTML);
        
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) alert.remove();
        }, 3000);
    }
}

    // Initialize map when the page loads
    // Initialize map when the page loads
window.onload = function() {
    initMap();
    initAutocomplete();
};
let editingIndex = null;
const alternativesModal = new bootstrap.Modal(document.getElementById('alternativeDestinationsModal'));
// Function to fetch alternative destinations
async function fetchAlternativeDestinations(lat, lng) {
    try {
        const response = await fetch('/api/alternative-destinations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                radius: 5.0
            }),
        });

        if (!response.ok) throw new Error('Failed to fetch alternatives');
        
        const destinations = await response.json();
        const container = document.getElementById('alternative-destinations');
        container.innerHTML = destinations.map(dest => `
            <button class="list-group-item list-group-item-action" 
                    onclick="selectNewDestination(${JSON.stringify(dest).replace(/"/g, '&quot;')})">
                <div class="d-flex w-100">
                    <div class="flex-shrink-0" style="width: 100px; height: 100px;">
                        <img src="${dest.image_url || 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg'}" 
                             class="img-fluid rounded"
                             alt="${dest.name}"
                             style="width: 100px; height: 100px; object-fit: cover;"
                             onerror="this.src='https://www.svgrepo.com/show/508699/landscape-placeholder.svg'">
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h6 class="mb-1">${dest.name}</h6>
                        <p class="mb-1 text-muted small">${dest.description || 'No description available.'}</p>
                        <small>Type: ${dest.type}</small>
                    </div>
                </div>
            </button>
        `).join('');

        alternativesModal.show();
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load alternative destinations');
    }
}

// Function to handle destination selection
// Update the selectNewDestination function to include starting coordinates
async function selectNewDestination(newDestination) {
    try {
        console.log('Selected new destination:', newDestination); // Debug log
        
        const response = await fetch('/api/update-itinerary-item', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                previousDestination: editingIndex > 0 ? currentItineraryData[editingIndex - 1] : null,
                newDestination: newDestination,
                nextDestination: editingIndex < currentItineraryData.length - 1 ? currentItineraryData[editingIndex + 1] : null,
                startLat: userLat,
                startLng: userLng
            }),
        });

        if (!response.ok) throw new Error('Failed to update itinerary');
        
        const updatedItem = await response.json();
        console.log('Received updated item:', updatedItem); // Debug log
        
        // Update currentItineraryData with the new destination
        currentItineraryData[editingIndex] = {
            ...updatedItem,
            latitude: newDestination.latitude,
            longitude: newDestination.longitude,
            description: newDestination.description,
            image_url: newDestination.image_url // Make sure we keep the image URL
        };

        console.log('Updated itinerary data:', currentItineraryData[editingIndex]); // Debug log
        
        // Update the specific card in the DOM
        const itemElement = document.querySelector(`[data-destination-id="${editingIndex}"]`);
        if (itemElement) {
            const newCard = document.createElement('div');
            newCard.className = "card mb-3 shadow-sm border-0";
            newCard.setAttribute("data-destination-id", editingIndex);
            
            // Create the card HTML with the updated data
            newCard.innerHTML = `
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="${currentItineraryData[editingIndex].image_url || 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg'}" 
                             class="img-fluid rounded-start" 
                             alt="${currentItineraryData[editingIndex].name}"
                             style="width: 100%; height: 250px; object-fit: cover;"
                             onerror="this.src='https://www.svgrepo.com/show/508699/landscape-placeholder.svg'">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title">
                                    ${currentItineraryData[editingIndex].name} (${currentItineraryData[editingIndex].type})
                                </h5>
                            </div>
                            <p class="card-text text-muted">${currentItineraryData[editingIndex].description}</p>
                            <p class="card-text"><strong>Travel Time:</strong> ${currentItineraryData[editingIndex].travel_time}</p>
                            <p class="card-text"><strong>Time to Spend:</strong> ${currentItineraryData[editingIndex].visit_time}</p>
                            <p class="card-text"><strong>Commute Instructions:</strong> ${currentItineraryData[editingIndex].commute_instructions}</p>
                            <button class="btn btn-sm btn-custom edit-destination" 
                                    data-index="${editingIndex}"
                                    data-lat="${currentItineraryData[editingIndex].latitude}"
                                    data-lng="${currentItineraryData[editingIndex].longitude}">
                                <i class="bi bi-pencil"></i> Change
                            </button>
                        </div>
                    </div>
                </div>`;

            // Replace the old card with the new one
            itemElement.replaceWith(newCard);
            
            // Reattach event listener to the new edit button
            newCard.querySelector('.edit-destination').addEventListener('click', (e) => {
                const btn = e.target.closest('.edit-destination');
                editingIndex = parseInt(btn.dataset.index);
                fetchAlternativeDestinations(
                    parseFloat(btn.dataset.lat),
                    parseFloat(btn.dataset.lng)
                );
            });
        }

        // Update map
        updateMap();
        
        alternativesModal.hide();
        editingIndex = null;
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update itinerary');
    }
}
// Function to update a single itinerary item in the UI
function updateItineraryItem(index, updatedItem, newDestination) {
    const itemElement = document.querySelector(`[data-destination-id="${index}"]`);

    if (itemElement) {
        // Create a new card element and replace the existing one
        const newCard = document.createElement('div');
        newCard.className = "card mb-3 border-0";
        newCard.setAttribute("data-destination-id", index);
        newCard.innerHTML = `
            <div class="row g-0">
                <!-- Left Column: Placeholder Image -->
                <div class="col-md-4">
                    <img src="https://www.svgrepo.com/show/508699/landscape-placeholder.svg" 
                         class="img-fluid rounded-start" 
                         alt="Placeholder for ${newDestination.name}">
                </div>
                <!-- Right Column: Content -->
                <div class="col-md-8">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title">
                                ${newDestination.name} (${newDestination.type})
                            </h5>
                        </div>
                         <p class="card-text text-muted">${newDestination.description}</p>
                <p class="card-text"><strong>Travel Time:</strong> ${updatedItem.travel_time}</p>
                <p class="card-text"><strong>Time to Spend:</strong> ${updatedItem.visit_time}</p>
                <p class="card-text"><strong>Commute Instructions:</strong> ${updatedItem.commute_instructions}</p>
                        <button class="btn btn-sm btn-custom edit-destination" 
                                data-index="${index}"
                                data-lat="${newDestination.latitude}"
                                data-lng="${newDestination.longitude}">
                            <i class="bi bi-pencil"></i> Change
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Replace the old card with the new card
        itemElement.replaceWith(newCard);
    }
}


// Event delegation for edit buttons
document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-destination')) {
        const btn = e.target.closest('.edit-destination');
        editingIndex = parseInt(btn.dataset.index);
        fetchAlternativeDestinations(
            parseFloat(btn.dataset.lat),
            parseFloat(btn.dataset.lng)
        );
    }
});
// Function to update map
function updateMap() {
    clearMap();
    
    const pathCoordinates = [{ lat: userLat, lng: userLng }];
    
    document.querySelectorAll('[data-destination-id]').forEach((el, index) => {
        const btn = el.querySelector('.edit-destination');
        const lat = parseFloat(btn.dataset.lat);
        const lng = parseFloat(btn.dataset.lng);
        const name = el.querySelector('.card-title').textContent.split(' (')[0];
        
        if (!isNaN(lat) && !isNaN(lng)) {
            addMarker(lat, lng, `${index + 1}. ${name}`, index + 1);
            pathCoordinates.push({ lat, lng });
        }
    });
    
    if (pathCoordinates.length > 1) {
        drawRoute(pathCoordinates);
    }
    
    updateMapBounds();
}
</script>

<!-- JavaScript to Toggle Mobile Map -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const viewMapButton = document.getElementById("view-map-btn");
        const mapModal = new bootstrap.Modal(document.getElementById("mapModal"));
        const mapContainer = document.getElementById("map-container");
        const mapModalPlaceholder = document.getElementById("map-modal-placeholder");
        const originalMapParent = mapContainer.parentNode; // Store original position
        
        // When the modal is shown, move the map-container into the modal
        document.getElementById("mapModal").addEventListener("show.bs.modal", function () {
            mapModalPlaceholder.appendChild(mapContainer);
            mapContainer.classList.remove("d-none");

            // Trigger resize event for Google Maps to adjust correctly
            setTimeout(() => {
                if (typeof google !== "undefined" && google.maps && google.maps.event) {
                    google.maps.event.trigger(map, "resize");
                }
            }, 300);
        });

        // When the modal is hidden, move the map-container back to its original location
        document.getElementById("mapModal").addEventListener("hidden.bs.modal", function () {
            originalMapParent.appendChild(mapContainer);
            mapContainer.classList.add("d-none", "d-md-block"); // Hide on mobile again
        });

        // Show the modal when "View Map" button is clicked
        viewMapButton.addEventListener("click", function () {
            mapModal.show();
        });
    });
</script>

</body>
</html>