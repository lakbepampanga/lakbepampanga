<?php

use App\Http\Controllers\ItineraryController;

Route::post('/generate-itinerary', [ItineraryController::class, 'generateItinerary']);


Route::post('/calculate-route', [ItineraryController::class, 'calculateRoute']);


