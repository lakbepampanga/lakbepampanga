<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use Illuminate\Http\Request;
use App\Http\Controllers\ReportController; // Import the ReportController
use Illuminate\Support\Facades\Auth;

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
     * @param  ReportController  $reportController
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(ReportController $reportController)
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

        // Get the user's reports using the ReportController
        $userReports = $reportController->getUserReports();

        return view('user-home', compact('destinationStats', 'savedItineraries', 'userReports'));
    }
}