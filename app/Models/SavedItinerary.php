<?php

namespace App\Models;
use App\Models\Destination;
use App\Models\DestinationVisit;


use Illuminate\Database\Eloquent\Model;

class SavedItinerary extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'itinerary_data',
        'start_lat',
        'start_lng',
        'duration_hours'
    ];

    protected $casts = [
        'itinerary_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


                


}