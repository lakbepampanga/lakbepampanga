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
    <h1>Plan Your Pampanga Itinerary</h1>

    <!-- Button to use current location -->
    <button id="use-location">Use Current Location</button><br><br>

    <!-- Option to select from dropdown -->
    <label for="location">Or, select your starting location:</label>
    <select id="location">
        <option value="" disabled selected>Select your starting location</option>
        <option value="Angeles">Angeles City</option>
        <option value="Mabalacat">Mabalacat</option>
        <option value="Magalang">Magalang</option>
        <option value="Clark">Clark Freeport Zone</option>
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
        let map, userLat, userLng;

        // Initialize Google Map
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: 15.1347621, lng: 120.5903796}, // Default center (Angeles City)
                zoom: 14
            });
        }

        // Event listener for "Use Current Location" button
        document.getElementById('use-location').addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    userLat = position.coords.latitude;
                    userLng = position.coords.longitude;

                    // Enable the itinerary form and hide the dropdown
                    document.getElementById('itinerary-form').style.display = 'block';
                    document.getElementById('location').style.display = 'none';

                    // Initialize the map centered on the user's location
                    map.setCenter(new google.maps.LatLng(userLat, userLng));
                });
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        });

        // Event listener for location dropdown change
        document.getElementById('location').addEventListener('change', (event) => {
            const selectedLocation = event.target.value;

            if (selectedLocation === 'Angeles') {
                userLat = 15.1347621;
                userLng = 120.5903796;
            } else if (selectedLocation === 'Mabalacat') {
                userLat = 15.2443337;
                userLng = 120.5642501;
            } else if (selectedLocation === 'Magalang') {
                userLat = 15.2144206;
                userLng = 120.6612414;
            } else if (selectedLocation === 'Clark') {
                userLat = 15.1674883;
                userLng = 120.5801295;
            }

            // Enable the itinerary form and hide the dropdown
            document.getElementById('itinerary-form').style.display = 'block';
            document.getElementById('location').style.display = 'none';

            // Initialize the map centered on the selected location
            map.setCenter(new google.maps.LatLng(userLat, userLng));
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

                        data.forEach((destination) => {
                            itineraryHTML += `<li>
                                <h3>${destination.name} (${destination.type})</h3>
                                <p>${destination.description}</p>
                                <p>Travel Time: ${destination.travel_time} minutes</p>
                                <p>Time to Spend: ${destination.visit_time} minutes</p>
                                <p><strong>Commute Instructions:</strong> ${destination.commute_instructions}</p>
                            </li>`;
                        });

                        itineraryHTML += '</ul>';
                        document.getElementById('itinerary').innerHTML = itineraryHTML;
                    } else {
                        alert("Error: Received data is not in the expected format.");
                    }
                })
                .catch((error) => {
                    console.error("Error generating itinerary:", error);
                    alert("An error occurred while generating the itinerary.");
                });
        });

        // Initialize map when the page loads
        window.onload = initMap;
    </script>
</body>
</html>
