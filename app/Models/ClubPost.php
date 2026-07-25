<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubPost extends Model
{
    protected $fillable = [
        'club_id',
        'user_id',
        'event_id',
        'content',
        'image',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}