
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampanga Commuting Guide</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyACtmc6ZSEVHBJLkk9wtiRj5ssvW1RDh4s&libraries=places,geometry"></script>

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
    </style>
</head>
<body>
<form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    <h1>Pampanga Commuting Guide</h1>

    <label for="start">Enter your location:</label>
    <input type="text" id="start" placeholder="e.g., Clark Freeport Zone">
    <br><br>

    <label for="end">Enter your destination:</label>
    <input type="text" id="end" placeholder="e.g., Angeles City Hall">
    <br><br>

    <button id="generate-guide">Generate Commute Guide</button>

    <div id="map"></div>
    <div id="commute-guide"></div>

    <script>
        let map, markers = [], polylines = [];

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: 15.1347621, lng: 120.5903796 },
                zoom: 14
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

        function drawPolyline(path, color) {
            const polyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: color || '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 4,
            });
            polyline.setMap(map);
            polylines.push(polyline);
        }

        function clearMap() {
            markers.forEach(marker => marker.setMap(null));
            markers = [];
            polylines.forEach(polyline => polyline.setMap(null));
            polylines = [];
        }

        function initializeAutocomplete() {
            const startInput = document.getElementById('start');
            const endInput = document.getElementById('end');
            
            const autocompleteStart = new google.maps.places.Autocomplete(startInput);
            const autocompleteEnd = new google.maps.places.Autocomplete(endInput);

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
                    // Single route response
                    const guideHTML = `
                        <h2>Commute Guide</h2>
                        <ul>
                            <li>
                                <p>${data.commute_instructions}</p>
                                <p>Estimated Time: ${data.travel_time} minutes</p>
                                <p>Distance: ${data.distance.toFixed(2)} km</p>
                            </li>
                        </ul>`;

                    document.getElementById('commute-guide').innerHTML = guideHTML;

                    // Draw route on map
                    if (data.path && data.path.length > 0) {
                        const path = data.path.map(coord => ({
                            lat: parseFloat(coord.latitude),
                            lng: parseFloat(coord.longitude)
                        }));

                        drawPolyline(path);

                        // Add start and end markers
                        addMarker(
                            path[0].lat,
                            path[0].lng,
                            'Start',
                            'S',
                            'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                        );
                        addMarker(
                            path[path.length - 1].lat,
                            path[path.length - 1].lng,
                            'End',
                            'E',
                            'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                        );

                        // Center map on the route
                        const bounds = new google.maps.LatLngBounds();
                        path.forEach(coord => bounds.extend(coord));
                        map.fitBounds(bounds);
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

        google.maps.event.addDomListener(window, 'load', initializeAutocomplete);
        window.onload = initMap;
    </script>
</body>
</html>
