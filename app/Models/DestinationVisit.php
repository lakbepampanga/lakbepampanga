<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DestinationVisit extends Model
{
    protected $fillable = [
        'destination_id',
        'user_id',
        'saved_itinerary_id',
        'visited_at'
    ];

    protected $dates = [
        'visited_at'
    ];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savedItinerary()
    {
        return $this->belongsTo(SavedItinerary::class);
    }
}