<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'description',
        'travel_time',
        'city',
        'type',
        'priority',
        'opening_time',
        'closing_time',
        'route_id',
        'image'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'latitude' => 'float',
        'longitude' => 'float',
        'travel_time' => 'integer',
        'priority' => 'integer'
    ];

    /**
     * A destination belongs to a jeepney route.
     */
    public function jeepneyRoute()
    {
        return $this->belongsTo(JeepneyRoute::class, 'route_id');
    }

    /**
     * A destination has many jeepney stops.
     */
    public function jeepneyStops()
    {
        return $this->hasMany(JeepneyStop::class);
    }

    /**
     * Get the description attribute with a default value.
     */
    public function getDescriptionAttribute($value)
    {
        return $value ?? 'No description available for this destination.';
    }

    /**
     * Get the image URL attribute.
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /**
     * Get formatted opening time
     */
    public function getFormattedOpeningTimeAttribute()
    {
        return $this->opening_time ? $this->opening_time->format('H:i') : 'Not specified';
    }

    /**
     * Get formatted closing time
     */
    public function getFormattedClosingTimeAttribute()
    {
        return $this->closing_time ? $this->closing_time->format('H:i') : 'Not specified';
    }

    public function completion()
{
    return $this->hasOne(ItineraryCompletion::class);
}

public function isCompleted()
{
    return $this->completion()->exists();
}
// In Destination model
public function visits()
{
    return $this->hasMany(DestinationVisit::class);
}
}