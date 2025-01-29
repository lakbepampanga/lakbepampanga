<?php

use App\Http\Controllers\ItineraryController;

Route::post('/generate-itinerary', [ItineraryController::class, 'generateItinerary']);


Route::post('/calculate-route', [ItineraryController::class, 'calculateRoute']);
Route::post('/generate-itinerary', [ItineraryController::class, 'generateItinerary']);
Route::post('/alternative-destinations', [ItineraryController::class, 'getAlternativeDestinations']);
Route::post('/update-itinerary-item', [ItineraryController::class, 'updateItineraryItem']);
Route::post('/save-itinerary', [ItineraryController::class, 'saveItinerary'])->middleware('auth');
