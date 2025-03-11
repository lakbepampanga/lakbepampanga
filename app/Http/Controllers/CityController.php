<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name'
        ]);
        
        $city = City::create($validated);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true, 
                'city' => $city,
                'message' => 'City added successfully'
            ]);
        }
        
        
        return back()->with('success', 'City added successfully');
    }
    public function list()
{
    $cities = City::orderBy('name')->get();
    return response()->json(['cities' => $cities]);
}
}