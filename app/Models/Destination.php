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
        'image',
        'category_tags',
        'average_price',
        'family_friendly',
        'recommended_visit_time'
    ];

    protected $casts = [
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'latitude' => 'float',
        'longitude' => 'float',
        'travel_time' => 'integer',
        'priority' => 'integer',
        'family_friendly' => 'boolean',
        'average_price' => 'decimal:2',
        'recommended_visit_time' => 'integer'
    ];

    protected $validTypes = [
        'landmark',
        'restaurant',
        'museum',
        'shopping',
        'nature',
        'religious',
        'entertainment',
        'cultural',
        'park',
        'market'
    ];

    public function isValidType($type)
    {
        return in_array($type, $this->validTypes);
    }

    // Rest of your existing model methods...
    public function jeepneyRoute()
    {
        return $this->belongsTo(JeepneyRoute::class, 'route_id');
    }

    public function jeepneyStops()
    {
        return $this->hasMany(JeepneyStop::class);
    }

    public function getDescriptionAttribute($value)
    {
        return $value ?? 'No description available for this destination.';
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getFormattedOpeningTimeAttribute()
    {
        return $this->opening_time ? $this->opening_time->format('H:i') : 'Not specified';
    }

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

    public function visits()
    {
        return $this->hasMany(DestinationVisit::class);
    }
}