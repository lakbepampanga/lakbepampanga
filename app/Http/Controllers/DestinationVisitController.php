<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\DestinationVisit; // Fixed namespace - using Models instead of Controllers
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestinationVisitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function markVisited(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }

        try {
            // Validate the request
            $request->validate([
                'destination_id' => 'required',
                'saved_itinerary_id' => 'required|exists:saved_itineraries,id'
            ]);

            // Find the destination by name
            $destination = Destination::where('name', $request->destination_id)->firstOrFail();

            // Check if visit already exists
            $existingVisit = DestinationVisit::where([
                'destination_id' => $destination->id,
                'user_id' => Auth::id(),
                'saved_itinerary_id' => $request->saved_itinerary_id
            ])->first();

            if (!$existingVisit) {
                // Create new visit record
                DestinationVisit::create([
                    'destination_id' => $destination->id,
                    'user_id' => Auth::id(),
                    'saved_itinerary_id' => $request->saved_itinerary_id,
                    'visited_at' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Destination marked as visited successfully'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Destination was already marked as visited'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Destination not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while marking the destination as visited: ' . $e->getMessage()
            ], 500);
        }
    }
}