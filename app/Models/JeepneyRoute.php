<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JeepneyRoute extends Model
{
    protected $fillable = [
        'route_name',
        'route_color',
        'description',
        'image_path'
    ];
        // Define the relationship with JeepneyStop
        public function stops()
        {
            return $this->hasMany(JeepneyStop::class);
        }
    use HasFactory;
    public function jeepneyStops()
{
    return $this->hasMany(JeepneyStop::class);
}
public function jeepneyRoute()
{
    return $this->belongsTo(JeepneyRoute::class, 'jeepney_route_id');
}

}
