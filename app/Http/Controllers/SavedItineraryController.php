<?php

namespace App\Http\Controllers;

use App\Models\SavedItinerary;
use Illuminate\Http\Request;
use App\Models\DestinationVisit;

class SavedItineraryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index()
    {
        $itineraries = SavedItinerary::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all visited destinations for this user
        $visitedDestinations = DestinationVisit::where('user_id', auth()->id())
            ->pluck('destination_id', 'saved_itinerary_id')
            ->toArray();

        // Modify the itinerary data to include visited status
        foreach ($itineraries as $itinerary) {
            $itineraryData = $itinerary->itinerary_data;
            
            foreach ($itineraryData as $index => $destination) {
                // Check if this destination has been visited
                $destinationId = \App\Models\Destination::where('name', $destination['name'])->first()?->id;
                
                if ($destinationId) {
                    $isVisited = DestinationVisit::where([
                        'destination_id' => $destinationId,
                        'user_id' => auth()->id(),
                        'saved_itinerary_id' => $itinerary->id
                    ])->exists();
                    
                    $itineraryData[$index]['visited'] = $isVisited;
                }
            }
            
            $itinerary->itinerary_data = $itineraryData;
        }

        return view('saved-itinerary', compact('itineraries'));
    }

    public function destroy($id)
    {
        try {
            $itinerary = SavedItinerary::where('user_id', auth()->id())
                ->findOrFail($id);
                
            $itinerary->delete();
            
            return redirect()->back()
                ->with('success', 'Itinerary deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete itinerary');
        }
    }
}