<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampanga Commuting Guide</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyACtmc6ZSEVHBJLkk9wtiRj5ssvW1RDh4s&libraries=places,geometry,directions"></script>
     <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
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
        #map {
            height: 500px;
            width: 100%;
            margin-top: 20px;
        }
        #commute-guide {
            margin-top: 20px;
        }
        #commute-guide ul {
            list-style-type: none;
            padding: 0;
        }
        #commute-guide li {
            margin-bottom: 15px;
        }

        .btn-custom{
    background-color: var(--button-color); /* Desired background color */
    color: var(--button-text-color);
}

.btn-custom:hover{
    background-color: var(--button-hover-color);
    color: white;
    transition: 0.3s;   /* Hover border color */
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

    .instruction-step {
        border-left: 4px solid #0d6efd;
        transition: all 0.3s ease;
    }
    
    .instruction-text {
        color: #424242;
        line-height: 1.6;
        font-size: 0.95rem;
    }
    
    .jeepney-instructions {
        padding: 10px;
        border-radius: 8px;
        background-color: #ffffff;
    }
    
    .route-details {
        border-left: 2px solid #e9ecef;
    }
    
    .instruction-step:hover {
        transform: translateX(5px);
        background-color: #f8f9fa;
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
<!-- main -->
<main class="main container mt-5 pt-5">
    <div class="container mt-4 pt-4">
        <div class="row">
            <!-- Input Section (Left) -->
            <div class="col-md-6">
                    <div id="input-section">
                        <div class="mb-4">
                            <h1 class="fw-bold text-custom">Pampanga<br>Commuting Guide</h1>
                            <p class="text-muted">Plan your trip with ease and get the best commuting routes.</p>
                        </div>
                        <div class="mt-4 rounded w-75">
                            <div class="mb-3">
                                <label for="start" class="form-label fw-bold">Enter your location:</label>
                                <div class="input-group">
                                    <input type="text" id="start" class="form-control shadow-sm" placeholder="e.g., Clark Freeport Zone">
                                    <button class="btn btn-custom" type="button" id="get-location">
                                        <i class="bi bi-crosshair"></i>
                                    </button>
                                </div>
                                <small id="location-status" class="form-text text-muted"></small>
                            </div>

                        <div class="mb-3">
                            <label for="end" class="form-label fw-bold">Enter your destination:</label>
                            <input type="text" id="end" class="form-control shadow-sm" placeholder="e.g., Angeles City Hall">
                        </div>

                        <div class="mt-4">
                            <button id="generate-guide" class="btn btn-custom px-4">Generate Guide</button>
                        </div>
                    </div>
                </div>

                <!-- Commute Guide Results -->
                <div id="result-section" style="display: none;">
                    <div id="commute-guide" class="p-3 rounded"></div>
                    <div class="mt-3 text-center">
                        <button id="back-button" class="btn btn-secondary px-4">Back</button>
                    </div>
                </div>
            </div>

            <!-- Map Section (Right) -->
            <div class="col-md-6">
                <div id="map" class="w-100 rounded shadow-sm" style="height: 500px; border: 1px solid #e0e0e0;"></div>
            </div>
        </div>
    </div>

    
</main>
<!-- Report Modal -->
<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Commute Instructions Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reportForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="mb-0">Reporting issue for route:</p>
                        <p class="mb-0"><strong>From:</strong> <span id="displayStart"></span></p>
                        <p class="mb-0"><strong>To:</strong> <span id="displayEnd"></span></p>
                    </div>
                    
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

<style>
.alert-info {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #495057;
}
</style>

<footer id="footer" class="footer dark-background w-100">
  <div class="container-fluid text-center py-4">
    <p>Â© <span>Copyright</span> <strong class="px-1 sitename">Lakbe Pampanga</strong> <span>All Rights Reserved</span></p>
    <div class="credits">
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed By <a href="https://themewagon.com">ThemeWagon</a>
    </div>
  </div>
</footer>

    <script>
        let map, markers = [], directionsRenderer;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 15.1347621, lng: 120.5903796 },
                zoom: 14
            });

            // Initialize the DirectionsRenderer
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: true, // We'll add our own custom markers
                polylineOptions: {
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 4
                }
            });
        }

        function addMarker(lat, lng, title, label, iconUrl) {
            const marker = new google.maps.Marker({
                position: { lat, lng },
                map: map,
                title: title,
                label: label ? { text: label, color: 'white', fontSize: '12px', fontWeight: 'bold' } : undefined,
                icon: iconUrl || 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            });
            markers.push(marker);
        }

        function clearMap() {
            markers.forEach(marker => marker.setMap(null));
            markers = [];
            if (directionsRenderer) {
                directionsRenderer.setDirections({routes: []});
            }
        }

  // Enhanced drawRoute function
  function drawRoute(path) {
    clearMap();
    
    if (!path || path.length < 2) return;
    
    // Create waypoints for the route
    const waypoints = path.slice(1, -1).map(point => ({
        location: new google.maps.LatLng(point.latitude, point.longitude),
        stopover: true
    }));

    const directionsService = new google.maps.DirectionsService();
    const origin = new google.maps.LatLng(path[0].latitude, path[0].longitude);
    const destination = new google.maps.LatLng(path[path.length - 1].latitude, path[path.length - 1].longitude);

    const request = {
        origin: origin,
        destination: destination,
        waypoints: waypoints,
        travelMode: google.maps.TravelMode.DRIVING,
        optimizeWaypoints: false
    };

    directionsService.route(request, (result, status) => {
        if (status === google.maps.DirectionsStatus.OK) {
            directionsRenderer.setDirections(result);

            // Add markers for start, transfer points, and end
            path.forEach((point, index) => {
                let markerIcon, label;
                
                if (index === 0) {
                    // Start point
                    markerIcon = 'http://maps.google.com/mapfiles/ms/icons/green-dot.png';
                    label = 'S';
                } else if (index === path.length - 1) {
                    // End point
                    markerIcon = 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png';
                    label = 'E';
                } else {
                    // Transfer point
                    markerIcon = 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png';
                    label = 'T' + index;
                }

                addMarker(
                    parseFloat(point.latitude),
                    parseFloat(point.longitude),
                    index === 0 ? 'Start' : (index === path.length - 1 ? 'End' : 'Transfer Point'),
                    label,
                    markerIcon
                );

                // Add info window for transfer points
                if (index > 0 && index < path.length - 1) {
                    const marker = markers[markers.length - 1];
                    const infoWindow = new google.maps.InfoWindow({
                        content: `<div style="padding: 10px;">
                            <h6 class="mb-2">Transfer Point ${index}</h6>
                            <p class="mb-0">Change jeepney here</p>
                        </div>`
                    });

                    marker.addListener('click', () => {
                        infoWindow.open(map, marker);
                    });
                }
            });

            // Fit bounds to show the entire route
            const bounds = new google.maps.LatLngBounds();
            path.forEach(point => {
                bounds.extend(new google.maps.LatLng(point.latitude, point.longitude));
            });
            map.fitBounds(bounds);
        }
    });
}

function initializeAutocomplete() {
    const startInput = document.getElementById('start');
    const endInput = document.getElementById('end');
    
    // Define Pampanga boundaries
    const pampangaBounds = new google.maps.LatLngBounds(
        // Southwest coordinates of Pampanga
        new google.maps.LatLng(14.9162, 120.4183),
        // Northeast coordinates of Pampanga
        new google.maps.LatLng(15.4087, 120.8245)
    );

    const options = {
        bounds: pampangaBounds,
        strictBounds: true,
        componentRestrictions: { country: 'PH' }
    };

    const autocompleteStart = new google.maps.places.Autocomplete(startInput, options);
    const autocompleteEnd = new google.maps.places.Autocomplete(endInput, options);

    autocompleteStart.setFields(['formatted_address']);
    autocompleteEnd.setFields(['formatted_address']);

    // Add listeners to validate locations are within Pampanga
    autocompleteStart.addListener('place_changed', function() {
        validatePlace(autocompleteStart, startInput, pampangaBounds);
    });

    autocompleteEnd.addListener('place_changed', function() {
        validatePlace(autocompleteEnd, endInput, pampangaBounds);
    });
};

   // Update the fetch response handler to include route segments
   document.getElementById('generate-guide').addEventListener('click', () => {
    const start = document.getElementById('start').value;
    const end = document.getElementById('end').value;

    if (!start || !end) {
        alert("Please enter both your location and destination.");
        return;
    }

    fetch('/api/commute-guide', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ start: start, end: end }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }

        // Hide the input form and show the result section
        document.getElementById('input-section').style.display = 'none';
        document.getElementById('result-section').style.display = 'block';

        // Generate route instructions HTML
        let instructionsHtml = '';
if (Array.isArray(data.commute_instructions)) {
    instructionsHtml = data.commute_instructions.map((instruction, index) => `
        <div class="instruction-step p-2 mb-3 bg-light rounded">
            <div class="d-flex align-items-start">
                <span class="step-number me-2 px-2 py-1 rounded-circle bg-primary text-white">
                    ${index + 1}
                </span>
                <p class="mb-0 instruction-text">${instruction}</p>
            </div>
        </div>
    `).join('');
} 
else {
    instructionsHtml = `
        <div class="instruction-step p-3 bg-light rounded">
            <div class="jeepney-instructions">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-truck text-primary me-2" style="font-size: 1.2rem;"></i>
                    <span class="fw-bold">Jeepney Route</span>
                </div>
                <div class="route-details ps-4">
                    <p class="mb-0 instruction-text">
                        ${data.commute_instructions}
                    </p>
                </div>
            </div>
        </div>
    `;
}

// Display the commute guide results
const guideHTML = `
    <style>
        .instruction-step {
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .instruction-step:hover {
            transform: translateX(5px);
        }
        .step-number {
            min-width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .instruction-text {
            color: #424242;
            line-height: 1.5;
        }
        .info-icon {
            color: #0d6efd;
        }
    </style>

    <h2 class="fw-bold text-center mb-4">Commute Guide</h2>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="card-text mb-3">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <strong class="d-flex align-items-center">
                        <i class="bi bi-map-fill me-2 info-icon"></i>
                        Instructions:
                    </strong>
                    <button type="button" 
                            class="btn btn-sm btn-outline-danger report-instructions" 
                            data-bs-toggle="modal" 
                            data-bs-target="#reportModal">
                        <i class="bi bi-exclamation-triangle"></i> Report Issue
                    </button>
                </div>
                ${instructionsHtml}
            </div>
            <div class="border-top pt-3">
                <p class="card-text mb-2 d-flex align-items-center">
                    <i class="bi bi-clock-fill me-2 info-icon"></i>
                    <strong>Estimated Time:</strong>
                    <span class="ms-2">${data.travel_time} minutes</span>
                </p>
                <p class="card-text d-flex align-items-center">
                    <i class="bi bi-geo-alt-fill me-2 info-icon"></i>
                    <strong>Distance:</strong>
                    <span class="ms-2">${data.distance.toFixed(2)} km</span>
                </p>
            </div>
        </div>
    </div>`;
        document.getElementById('commute-guide').innerHTML = guideHTML;

        // Draw route on map with transfer points
        if (data.path && data.path.length > 0) {
            drawRoute(data.path);
        }
    })
    .catch(error => {
        console.error("Error fetching commute guide:", error);
        alert("An error occurred while generating the commute guide.");
    });
});

document.getElementById('back-button').addEventListener('click', () => {
    // Show the input form and hide the result section
    document.getElementById('input-section').style.display = 'block';
    document.getElementById('result-section').style.display = 'none';
    document.getElementById('commute-guide').innerHTML = ''; // Clear results
});



        // Initialize map and autocomplete when the page loads
        window.onload = function() {
            initMap();
            initializeAutocomplete();
        };
        function initGeolocation() {
            const locationButton = document.getElementById('get-location');
            const locationStatus = document.getElementById('location-status');
            const startInput = document.getElementById('start');

            locationButton.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    locationStatus.textContent = 'Geolocation is not supported by your browser';
                    return;
                }

                locationStatus.textContent = 'Fetching your location...';
                locationButton.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const geocoder = new google.maps.Geocoder();
                        const latlng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };

                        geocoder.geocode({ location: latlng }, (results, status) => {
                            if (status === 'OK') {
                                if (results[0]) {
                                    startInput.value = results[0].formatted_address;
                                    locationStatus.textContent = 'Location found!';
                                    
                                    // Add marker for current location
                                    clearMap();
                                    addMarker(
                                        position.coords.latitude,
                                        position.coords.longitude,
                                        'Current Location',
                                        'C',
                                        'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                                    );
                                    
                                    // Center map on current location
                                    map.setCenter(latlng);
                                    map.setZoom(15);
                                } else {
                                    locationStatus.textContent = 'No address found for this location';
                                }
                            } else {
                                locationStatus.textContent = 'Failed to get address for location';
                            }
                            locationButton.disabled = false;
                        });
                    },
                    (error) => {
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                locationStatus.textContent = 'Location permission denied';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                locationStatus.textContent = 'Location information unavailable';
                                break;
                            case error.TIMEOUT:
                                locationStatus.textContent = 'Location request timed out';
                                break;
                            default:
                                locationStatus.textContent = 'An unknown error occurred';
                        }
                        locationButton.disabled = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            });
        }

        // Update the window.onload function
        window.onload = function() {
            initMap();
            initializeAutocomplete();
            initGeolocation(); // Initialize geolocation functionality
        };



        //report
 // Update the guide generation code to store data
document.addEventListener('DOMContentLoaded', function() {
    let currentGuideData = null;

    // Store the guide data when generated
    document.getElementById('generate-guide').addEventListener('click', async function() {
        const start = document.getElementById('start').value;
        const end = document.getElementById('end').value;
        
        console.log('Storing locations:', { start, end }); // Debug log

        currentGuideData = {
            start: start,
            end: end,
            instructions: null
        };
    });

    // Handle report button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.report-instructions')) {
            console.log('Current guide data:', currentGuideData); // Debug log
            if (currentGuideData) {
                // Update the display in the modal
                document.getElementById('displayStart').textContent = currentGuideData.start || 'Not available';
                document.getElementById('displayEnd').textContent = currentGuideData.end || 'Not available';
            } else {
                console.log('No guide data available'); // Debug log
            }
        }
    });

    // Handle form submission
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!currentGuideData) {
            alert('Please generate a route first before submitting a report.');
            return;
        }

        const formData = new FormData();
        
        // Add the form fields
        formData.append('start_location', currentGuideData.start);
        formData.append('end_location', currentGuideData.end);
        formData.append('issue_type', document.querySelector('select[name="issue_type"]').value);
        formData.append('description', document.querySelector('textarea[name="description"]').value);
        formData.append('current_instructions', currentGuideData.instructions || 'No instructions available');

        // Add CSRF token
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        console.log('Submitting data:', Object.fromEntries(formData)); // Debug log
        
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        fetch('/commuting-reports', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            console.log('Raw response:', text); // Debug log
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid JSON response: ' + text);
            }
        })
        .then(data => {
            if (data.success) {
                alert('Thank you for your report. We will review it shortly.');
                bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                this.reset();
            } else {
                alert(data.error || 'An error occurred while submitting your report.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting your report. Please try again.');
        })
        .finally(() => {
            submitButton.disabled = false;
        });
    });
});

document.addEventListener('click', function(e) {
    if (e.target.closest('.report-instructions') && currentGuideData) {
        document.getElementById('displayStart').textContent = currentGuideData.start;
        document.getElementById('displayEnd').textContent = currentGuideData.end;
    }
});
    </script>
</body>
</html>