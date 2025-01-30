<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JeepneyStop extends Model
{
    protected $fillable = [
        'jeepney_route_id',
        'stop_name',
        'order_in_route',
        'latitude',
        'longitude'
    ];
     // Define the inverse relationship with JeepneyRoute
     public function route()
     {
         return $this->belongsTo(JeepneyRoute::class, 'jeepney_route_id');
     }
    use HasFactory;
}
