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
    color: var(--accent-color);
}

.btn-custom:hover{
    background-color: #683842; /* Hover background color */
    color: white;
    transition: 0.3s;   /* Hover border color */
}
    </style>
</head>
<body>
<header id="header" class="header d-flex bg-white fixed-top align-items-center">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

        <a href="/" class="logo d-flex align-items-center">
            <h1 class="sitename">Lakbe Pampanga</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="/index">Plan</a></li>
                <li><a href="#about">Saved Itineraries</a></li>
                <li><a href="/commuting-guide" class="active">Commuting Guide</a></li>
                <li><form method="POST" action="{{ route('logout') }}">
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
                <div class="mb-4">
                    <h1 class="fw-bold">Pampanga<br>Commuting Guide</h1>
                    <p class="text-muted">Plan your trip with ease and get the best commuting routes.</p>
                </div>
                <div class="mt-4 rounded w-75">
                    <div class="mb-3">
                        <label for="start" class="form-label fw-bold">Enter your location:</label>
                        <div class="input-group">
                            <input type="text" id="start" class="form-control shadow-sm" placeholder="e.g., Clark Freeport Zone">
                            <span class="input-group-text bg-white border shadow-sm">
                                <i class="bi bi-geo-alt-fill text-muted"></i> <!-- Bootstrap Icons -->
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="end" class="form-label fw-bold">Enter your destination:</label>
                        <input type="text" id="end" class="form-control shadow-sm" placeholder="e.g., Angeles City Hall">
                    </div>

                    <div class=" mt-4">
                        <button id="generate-guide" class="btn btn-custom px-4">Generate Guide</button>
                    </div>

                    <!-- Commute Guide Section -->
                    <div id="commute-guide" class="mt-4 p-3 bg-light rounded shadow-sm">
                        <h5 class="fw-bold">Commute Guide</h5>
                        <p class="text-muted">Your results will appear here after generating.</p>
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


<footer id="footer" class="footer dark-background w-100">
  <div class="container-fluid text-center py-4">
    <p>© <span>Copyright</span> <strong class="px-1 sitename">Lakbe Pampanga</strong> <span>All Rights Reserved</span></p>
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

        function drawRoute(origin, destination) {
            const directionsService = new google.maps.DirectionsService();

            const request = {
                origin: origin,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            };

            directionsService.route(request, (result, status) => {
                if (status === google.maps.DirectionsStatus.OK) {
                    directionsRenderer.setDirections(result);

                    // Add custom markers for start and end points
                    const route = result.routes[0].legs[0];
                    addMarker(
                        route.start_location.lat(),
                        route.start_location.lng(),
                        'Start',
                        'S',
                        'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    );
                    addMarker(
                        route.end_location.lat(),
                        route.end_location.lng(),
                        'End',
                        'E',
                        'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                    );

                    // Fit bounds to show the entire route
                    const bounds = new google.maps.LatLngBounds();
                    bounds.extend(route.start_location);
                    bounds.extend(route.end_location);
                    map.fitBounds(bounds);
                }
            });
        }

        function initializeAutocomplete() {
            const startInput = document.getElementById('start');
            const endInput = document.getElementById('end');
            
            const autocompleteStart = new google.maps.places.Autocomplete(startInput, {
                componentRestrictions: { country: 'PH' }
            });
            const autocompleteEnd = new google.maps.places.Autocomplete(endInput, {
                componentRestrictions: { country: 'PH' }
            });

            autocompleteStart.setFields(['formatted_address']);
            autocompleteEnd.setFields(['formatted_address']);
        }

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

                clearMap();
                
                if (data.commute_instructions) {
                    const guideHTML = `
                         <h2 class="fw-bold text-center mb-4">Commute Guide</h2>
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <p class="card-text mb-3">
                                    <strong>Instructions:</strong> ${data.commute_instructions}
                                </p>
                                <p class="card-text mb-2">
                                    <strong>Estimated Time:</strong> ${data.travel_time}
                                </p>
                                <p class="card-text">
                                    <strong>Distance:</strong> ${data.distance.toFixed(2)} km
                                </p>
                            </div>
                        </div>`;

                    document.getElementById('commute-guide').innerHTML = guideHTML;

                    // Draw route on map using actual road directions
                    if (data.path && data.path.length > 0) {
                        // Create LatLng objects for start and end points
                        const startPoint = new google.maps.LatLng(
                            parseFloat(data.path[0].latitude),
                            parseFloat(data.path[0].longitude)
                        );
                        const endPoint = new google.maps.LatLng(
                            parseFloat(data.path[data.path.length - 1].latitude),
                            parseFloat(data.path[data.path.length - 1].longitude)
                        );
                        
                        // Draw the route
                        drawRoute(startPoint, endPoint);
                    }
                } else {
                    document.getElementById('commute-guide').innerHTML = 
                        '<p>No routes found for the given locations.</p>';
                }
            })
            .catch(error => {
                console.error("Error fetching commute guide:", error);
                alert("An error occurred while generating the commute guide.");
            });
        });

        // Initialize map and autocomplete when the page loads
        window.onload = function() {
            initMap();
            initializeAutocomplete();
        };
    </script>
</body>
</html>