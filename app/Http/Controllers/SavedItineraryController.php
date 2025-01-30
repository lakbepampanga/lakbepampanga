<?php

namespace App\Http\Controllers;

use App\Models\SavedItinerary;
use Illuminate\Http\Request;

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