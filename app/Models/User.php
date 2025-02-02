<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'age',
        'role'
    ];

    protected $hidden = [
        'password',
    ];

    // Add new relationships
    public function itineraries()
    {
        return $this->hasMany(Itinerary::class);
    }

    public function completedItineraries()
    {
        return $this->hasMany(ItineraryCompletion::class);
    }

    public function destinationVisits()
    {
        return $this->hasMany(DestinationVisit::class);
    }
}