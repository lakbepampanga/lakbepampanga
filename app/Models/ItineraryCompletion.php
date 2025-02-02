<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItineraryCompletion extends Model
{
    protected $fillable = [
        'saved_itinerary_id',
        'user_id',
        'completed_at'
    ];

    protected $dates = [
        'completed_at',
        'created_at',
        'updated_at'
    ];

    public function savedItinerary()
    {
        return $this->belongsTo(SavedItinerary::class, 'saved_itinerary_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}