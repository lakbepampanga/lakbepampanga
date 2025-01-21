<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampanga Itinerary Planner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyACtmc6ZSEVHBJLkk9wtiRj5ssvW1RDh4s&libraries=places"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        #map {
            height: 500px;
            width: 100%;
            margin-top: 20px;
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
    </style>
</head>
<body>
<form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
    <h1>Plan Your Pampanga Itinerary</h1>

    <!-- Button to use current location -->
    <button id="use-location">Use Current Location</button><br><br>
    <a href="/commuting-guide" style="text-decoration: none; color: white; background-color: #007BFF; padding: 10px 15px; border-radius: 5px;">
    Go to Commuting Guide</a>   
    <!-- Option to select from dropdown -->
    <label for="location">Or, select your starting location:</label>
    <select id="location">
        <option value="" disabled selected>Select your starting location</option>
        <option value="Angeles">Angeles City</option>
        <option value="Mabalacat">Mabalacat</option>
        <option value="Magalang">Magalang</option>
        <option value="Clark">Clark Freeport Zone</option>
        <option value="Auf">AUF</option>
    </select><br><br>

    <!-- Form for hours and generating itinerary -->
    <div id="itinerary-form" style="display: none;">
        <label for="hours">How many hours do you have for travel?</label>
        <input type="number" id="hours" min="1" max="12" placeholder="Enter hours">
        <button id="generate-itinerary">Generate Itinerary</button>
    </div>

    <div id="map"></div>
    <div id="itinerary"></div>

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
                let itineraryHTML = '<h2>Your Itinerary</h2><ul>';
                clearMap(); // Clear previous markers and route

                const pathCoordinates = [{ lat: userLat, lng: userLng }]; // Add starting location as the first point

                data.forEach((destination, index) => {
                    itineraryHTML += `<li>
                        <h3>${destination.name} (${destination.type})</h3>
                        <p>${destination.description}</p>
                        <p>Travel Time: ${destination.travel_time} minutes</p>
                        <p>Time to Spend: ${destination.visit_time} minutes</p>
                        <p><strong>Commute Instructions:</strong> ${destination.commute_instructions}</p>
                    </li>`;

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

                itineraryHTML += '</ul>';
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