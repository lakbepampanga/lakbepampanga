<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'latitude', 'longitude', 'description'];

    /**
     * A destination belongs to a jeepney route.
     */
    public function jeepneyRoute()
    {
        return $this->belongsTo(JeepneyRoute::class);
    }

    /**
     * A destination has many jeepney stops.
     */
    public function jeepneyStops()
    {
        return $this->hasMany(JeepneyStop::class);
    }
    public function getDescriptionAttribute($value)
{
    return $value ?? 'No description available for this destination.';
}

}
