<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destination;
use App\Models\User;
use App\Models\JeepneyRoute;
use App\Models\JeepneyStop;
use Illuminate\Support\Facades\Storage;
use App\Models\DestinationVisit;
use App\Models\ItineraryCompletion;  // Add this line




class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'destinations' => Destination::count(),
            'users' => User::count(),
            'routes' => JeepneyRoute::count(),
            'stops' => JeepneyStop::count()
        ];

        // Get destination analytics
        $destinationStats = Destination::withCount(['visits'])
            ->with(['visits.user'])
            ->get()
            ->map(function ($destination) {
                $visits = $destination->visits;
                
                // Calculate age groups
                $ageGroups = $visits->map->user->groupBy(function ($user) {
                    if ($user->age < 18) return 'Under 18';
                    if ($user->age < 25) return '18-24';
                    if ($user->age < 35) return '25-34';
                    if ($user->age < 45) return '35-44';
                    return '45+';
                })->map->count();

                // Calculate gender distribution
                $genderDistribution = $visits->map->user
                    ->groupBy('gender')
                    ->map->count();

                return [
                    'name' => $destination->name,
                    'total_visits' => $visits->count(),
                    'age_distribution' => $ageGroups->toArray(),
                    'gender_distribution' => $genderDistribution->toArray()
                ];
            });

        // Get overall analytics
        $overallStats = [
            'total_completed_itineraries' => ItineraryCompletion::count(),
            'total_visits' => DestinationVisit::count(),
            'gender_distribution' => User::groupBy('gender')
                ->selectRaw('gender, count(*) as count')
                ->pluck('count', 'gender')
                ->toArray(),
            'age_groups' => User::selectRaw('
                CASE 
                    WHEN age < 18 THEN "Under 18"
                    WHEN age < 25 THEN "18-24"
                    WHEN age < 35 THEN "25-34"
                    WHEN age < 45 THEN "35-44"
                    ELSE "45+"
                END as age_group,
                COUNT(*) as count
            ')
            ->groupBy('age_group')
            ->pluck('count', 'age_group')
            ->toArray()
        ];

        return view('admin.dashboard', compact('stats', 'destinationStats', 'overallStats'));
    }

    // Destinations Management
    public function destinations()
    {
        $destinations = Destination::orderBy('name')->paginate(10);
        return view('admin.destinations.index', compact('destinations'));
    }

    public function createDestination()
    {
        $routes = JeepneyRoute::all();
        return view('admin.destinations.create', compact('routes'));
    }


    public function storeDestination(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'nullable|string',
            'travel_time' => 'required|integer|min:1',
            'city' => 'required|in:Angeles,Mabalacat,Magalang',
            'type' => 'required|in:landmark,restaurant',
            'priority' => 'required|integer|min:1',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'route_id' => 'nullable|exists:jeepney_routes,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        // Create destination data array
        $destinationData = [
            'name' => $validated['name'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'description' => $validated['description'],
            'travel_time' => $validated['travel_time'],
            'city' => $validated['city'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'opening_time' => $validated['opening_time'] ?? null,  // Handle null case
            'closing_time' => $validated['closing_time'] ?? null,  // Handle null case
            'route_id' => $validated['route_id'] ?? null,         // Handle null case
        ];
    
        // Handle image upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('destinations', 'public');
            $destinationData['image'] = $path;
        }
    
        // Create the destination with explicit data
        Destination::create($destinationData);
    
        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destination created successfully.');
    }
    
    public function editDestination(Destination $destination)
    {
        $routes = JeepneyRoute::all();
        return view('admin.destinations.edit', compact('destination', 'routes'));
    }
    
    public function updateDestination(Request $request, Destination $destination)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'description' => 'nullable|string',
            'travel_time' => 'required|integer',
            'city' => 'required|in:Angeles,Mabalacat,Magalang',
            'type' => 'required|in:landmark,restaurant',
            'priority' => 'required|integer|min:1',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'route_id' => 'nullable|exists:jeepney_routes,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image
        ]);
    
        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($destination->image) {
                Storage::disk('public')->delete($destination->image);
            }
            $validated['image'] = $request->file('image')->store('destinations', 'public');
        }
    
        $destination->update($validated);
    
        return redirect()->route('admin.destinations.index')->with('success', 'Destination updated successfully.');
    }
    

    public function deleteDestination(Destination $destination)
    {
        $destination->delete();
        return redirect()->route('admin.destinations.index')->with('success', 'Destination deleted successfully');
    }

    // Jeepney Routes Management
    public function routes()
    {
        $routes = JeepneyRoute::with('stops')->paginate(10);
        return view('admin.routes.index', compact('routes'));
    }

    public function createRoute()
    {
        return view('admin.routes.create');
    }

    public function storeRoute(Request $request)
{
    $validated = $request->validate([
        'route_name' => 'required|string|max:255',
        'route_color' => 'nullable|string|max:50',
        'description' => 'nullable|string',
        'route_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    $routeData = $validated;
    if ($request->hasFile('route_image')) {
        $routeData['image_path'] = $request->file('route_image')->store('route-images', 'public');
    }

    JeepneyRoute::create($routeData);
    return redirect()->route('admin.routes.index')->with('success', 'Route created successfully');
}

public function editRoute(JeepneyRoute $route)
{
    return view('admin.routes.edit', compact('route'));
}

public function updateRoute(Request $request, JeepneyRoute $route)
{
    $validated = $request->validate([
        'route_name' => 'required|string|max:255',
        'route_color' => 'nullable|string|max:50',
        'description' => 'nullable|string',
        'route_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'remove_image' => 'nullable|boolean'
    ]);

    if ($request->hasFile('route_image')) {
        if ($route->image_path) {
            Storage::disk('public')->delete($route->image_path);
        }
        $validated['image_path'] = $request->file('route_image')->store('route-images', 'public');
    }

    if ($request->has('remove_image') && $route->image_path) {
        Storage::disk('public')->delete($route->image_path);
        $validated['image_path'] = null;
    }

    $route->update($validated);
    return redirect()->route('admin.routes.index')->with('success', 'Route updated successfully');
}

public function deleteRoute(JeepneyRoute $route)
{
    if ($route->image_path) {
        Storage::disk('public')->delete($route->image_path);
    }
    $route->stops()->delete(); // Delete associated stops
    $route->delete();
    return redirect()->route('admin.routes.index')->with('success', 'Route and associated stops deleted successfully');
}

    // Jeepney Stops Management
    public function stops(JeepneyRoute $route)
    {
        $stops = $route->stops()->orderBy('order_in_route')->paginate(10);
        return view('admin.stops.index', compact('route', 'stops'));
    }

    public function createStop(JeepneyRoute $route)
    {
        return view('admin.stops.create', compact('route'));
    }

    public function storeStop(Request $request, JeepneyRoute $route)
    {
        $validated = $request->validate([
            'stop_name' => 'required|string|max:255',
            'order_in_route' => 'required|integer|min:1',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $route->stops()->create($validated);
        return redirect()->route('admin.routes.stops', $route)->with('success', 'Stop created successfully');
    }

    public function editStop(JeepneyStop $stop)
    {
        return view('admin.stops.edit', compact('stop'));
    }

    public function updateStop(Request $request, JeepneyStop $stop)
    {
        $validated = $request->validate([
            'stop_name' => 'required|string|max:255',
            'order_in_route' => 'required|integer|min:1',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $stop->update($validated);
        return redirect()->route('admin.routes.stops', $stop->jeepney_route_id)->with('success', 'Stop updated successfully');
    }

    public function deleteStop(JeepneyStop $stop)
    {
        $routeId = $stop->jeepney_route_id;
        $stop->delete();
        return redirect()->route('admin.routes.stops', $routeId)->with('success', 'Stop deleted successfully');
    }

    // Users Management
    public function users()
    {
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'gender' => 'nullable|string',
            'age' => 'nullable|integer|min:1'
        ]);

        $user->update($validated);
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully');
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }


}