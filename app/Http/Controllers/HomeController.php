<?php

namespace App\Http\Controllers;
use App\Models\Destination;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get destination analytics
        $destinationStats = Destination::withCount(['visits'])
            ->with(['visits.user'])
            ->get()
            ->map(function ($destination) {
                $visits = $destination->visits;
                
                return [
                    'name' => $destination->name,
                    'total_visits' => $visits->count(),
                    'image' => $destination->image, // Make sure this matches your image field name
                    'description' => $destination->description,
                ];
            })
            ->sortByDesc('total_visits');

            $savedItineraries = \App\Models\SavedItinerary::where('user_id', auth()->id())
        ->latest()
        ->take(3)
        ->get();

        return view('user-home', compact('destinationStats', 'savedItineraries'));
    }
}
