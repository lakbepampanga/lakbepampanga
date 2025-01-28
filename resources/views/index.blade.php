<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampanga Itinerary Planner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyACtmc6ZSEVHBJLkk9wtiRj5ssvW1RDh4s&libraries=places"></script>

    <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
<link href="{{ asset('img/apple-touch-icon.png') }}" rel="apple-touch-icon">


  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect"   crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">

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
    color: var(--accent-color);
}

.btn-custom:hover{
    background-color: #683842; /* Hover background color */
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
.form-label {
    font-weight: 600;
    font-size: 1rem;
}

.form-select {
    border-radius: 30px;
    padding: 10px;
    font-size: 0.95rem;
}

#itinerary-form {
    max-width: 500px;
    margin: 0 auto;
}

    </style>

</head>
<body>
<header id="header" class="header d-flex  fixed-top align-items-center">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

        <a href="/" class="logo d-flex align-items-center">
            <h1 class="sitename">Lakbe Pampanga</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="#" class="active">Plan</a></li>
                <li><a href="#about">Saved Itineraries</a></li>
                <li><a href="/commuting-guide">Commuting Guide</a></li>
                <li><form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-custom btn-md px-3 py-2">Logout</button>
                    </form>
                </li>
                
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

    </div>
</header>
<main class="main container mt-5 pt-5">

<div class="container py-4">
    <!-- Heading -->
    <h1 class="text-center mb-4" id="section-title">Plan Your Pampanga Itinerary</h2>

    <!-- Action Buttons -->
    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3 mb-4">
        <button id="use-location" class="btn btn-custom">Use Current Location</button>
    </div>

    <!-- Location Selection -->
    <div class="form-group mb-4 text-center">
        <label for="location" class="form-label fw-bold">Or, select your starting location:</label>
        <select id="location" class="form-select shadow-sm w-50 mx-auto">
            <option value="" disabled selected>Select your starting location</option>
            <option value="Angeles">Angeles City</option>
            <option value="Mabalacat">Mabalacat</option>
            <option value="Magalang">Magalang</option>
            <option value="Clark">Clark Freeport Zone</option>
            <option value="Auf">AUF</option>
        </select>
    </div>

    <!-- Hours Input and Generate Itinerary Button -->
    <div id="itinerary-form" class="bg-light p-4 rounded shadow-sm" style="display: none;">
    <label for="hours" class="form-label fw-bold">How many hours do you have for travel?</label>
    <input type="number" id="hours" class="form-control shadow-sm mb-3" min="1" max="12" placeholder="Enter hours">
    
    <div class="text-center">
        <button id="generate-itinerary" class="btn btn-success w-50">Generate Itinerary</button>
    </div>
</div>

</div>

<!-- maps -->
    <div class="container">
    <div class="row">
        <div class="col-12">
            <div id="map" class="w-100" style="height: 500px;"></div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div id="itinerary" class="mt-4"></div>
</div>

    </main>


    <!--  -->
    <script>
    let map, userLat, userLng, markers = [], initialMarker = null, directionsService, directionsRenderer;

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

    // Event listener for location dropdown change
    document.getElementById('location').addEventListener('change', (event) => {
        const selectedLocation = event.target.value;

        const locations = {
            Angeles: { lat: 15.1347621, lng: 120.5903796 },
            Mabalacat: { lat: 15.2443337, lng: 120.5642501 },
            Magalang: { lat: 15.2144206, lng: 120.6612414 },
            Clark: { lat: 15.1674883, lng: 120.5801295 }, 
            Auf: { lat: 15.1453018, lng: 120.5948856 }
        };

        userLat = locations[selectedLocation].lat;
        userLng = locations[selectedLocation].lng;

        document.getElementById('itinerary-form').style.display = 'block';

        map.setCenter(new google.maps.LatLng(userLat, userLng));
        setInitialLocationMarker(userLat, userLng, selectedLocation);
    });

    // Event listener for "Generate Itinerary" button
    document.getElementById('generate-itinerary').addEventListener('click', () => {
    const hours = document.getElementById('hours').value;

    if (!hours || hours <= 0) {
        alert("Please enter a valid number of hours.");
        return;
    }

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
            if (Array.isArray(data)) {
    let itineraryHTML = `
        <h2 class="text-center my-4">Your Itinerary</h2>
        <div class="row g-3">`; // Bootstrap row with gutter spacing

    clearMap(); // Clear previous markers and route

    const pathCoordinates = [{ lat: userLat, lng: userLng }]; // Add starting location as the first point

    data.forEach((destination, index) => {
        itineraryHTML += `
            <div class="col-md-4"> <!-- Column for each card -->
                <div class="card h-100 shadow-sm border-0"> <!-- Bootstrap card -->
                    <div class="card-body">
                        <h5 class="card-title text-primary">${destination.name} (${destination.type})</h5>
                        <p class="card-text text-muted">${destination.description}</p>
                        <p class="card-text"><strong>Travel Time:</strong> ${destination.travel_time}</p>
                        <p class="card-text"><strong>Time to Spend:</strong> ${destination.visit_time}</p>
                        <p class="card-text"><strong>Commute Instructions:</strong> ${destination.commute_instructions}</p>
                    </div>
                </div>
            </div>`;

        const lat = parseFloat(destination.latitude);
        const lng = parseFloat(destination.longitude);

        if (!isNaN(lat) && !isNaN(lng)) {
            // Add marker with a label showing the location order
            addMarker(lat, lng, `${index + 1}. ${destination.name}`, index + 1);

            // Add to route path coordinates
            pathCoordinates.push({ lat: lat, lng: lng });
        } else {
            console.error(`Invalid coordinates for destination: ${destination.name}`);
        }
    });

    itineraryHTML += `</div>`; // Close the row
    document.getElementById('itinerary').innerHTML = itineraryHTML;

    // Draw the route using Google Directions Service
    if (pathCoordinates.length > 1) {
        drawRoute(pathCoordinates);
    }

    // Adjust map to fit all markers
    updateMapBounds();
} else {
    alert("Error: Received data is not in the expected format.");
}

        })
        .catch((error) => {
            console.error("Error fetching itinerary:", error);
            alert("An error occurred while generating the itinerary.");
        });
});


    // Initialize map when the page loads
    window.onload = initMap;
</script>



</body>
</html>