<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JeepneyRoute extends Model
{
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
