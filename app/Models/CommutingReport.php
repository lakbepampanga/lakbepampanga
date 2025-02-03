<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommutingReport extends Model
{
    protected $fillable = [
        'user_id',
        'start_location',
        'end_location',
        'current_instructions',
        'issue_type',
        'description',
        'status',
        'admin_notes',
        'resolved_at'
    ];

    protected $casts = [
        'resolved_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}