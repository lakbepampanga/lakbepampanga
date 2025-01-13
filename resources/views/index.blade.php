<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pampanga Itinerary Planner</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
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
    </style>
</head>
<body>
    <h1>Plan Your Pampanga Itinerary</h1>
    <button id="use-location">Use Current Location</button>

    <div id="itinerary-form" style="display: none; margin-top: 20px;">
        <label for="hours">How many hours do you have for travel?</label>
        <input type="number" id="hours" min="1" max="12" placeholder="Enter hours">
        <button id="generate-itinerary">Generate Itinerary</button>
    </div>

    <div id="map"></div>
    <div id="itinerary"></div>

    <script>
        let map, userMarker, userLat, userLng;

        // Event listener for "Use Current Location" button
        document.getElementById('use-location').addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    // Store user's location
                    userLat = position.coords.latitude;
                    userLng = position.coords.longitude;

                    // Show the itinerary form
                    document.getElementById('itinerary-form').style.display = 'block';

                    // Initialize the map
                    map = L.map('map').setView([userLat, userLng], 14);

                    // Add OpenStreetMap tiles
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap contributors',
                    }).addTo(map);

                    // Add a marker for the user's location
                    userMarker = L.marker([userLat, userLng])
                        .addTo(map)
                        .bindPopup("You are here")
                        .openPopup();
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
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
                }),
            })
            .then((response) => response.json())
            .then((data) => {
                // Generate itinerary list
                let itineraryHTML = '<h2>Your Itinerary</h2><ul>';
                data.forEach(destination => {
                    itineraryHTML += `<li>
                        <h3>${destination.name} (${destination.type})</h3>
                        <p>${destination.description}</p>
                        <p>Travel Time: ${destination.travel_time} minutes</p>
                        <p>Time to Spend: ${destination.time_to_spend} minutes</p>
                    </li>`;

                    // Add markers to the map
                    L.marker([destination.latitude, destination.longitude])
                        .addTo(map)
                        .bindPopup(`<strong>${destination.name}</strong><br>${destination.description}`)
                        .openPopup();
                });
                itineraryHTML += '</ul>';
                document.getElementById('itinerary').innerHTML = itineraryHTML;
            })
            .catch((error) => {
                console.error("Error generating itinerary:", error);
                alert("An error occurred while generating the itinerary.");
            });
        });

        
    </script>
</body>
</html>
