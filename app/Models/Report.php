<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'user_id',
        'saved_itinerary_id',
        'destination_name',
        'current_instructions',
        'issue_type',
        'description',
        'status',
        'admin_notes',
        'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savedItinerary()
    {
        return $this->belongsTo(SavedItinerary::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function markAsResolved()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now()
        ]);
    }
}