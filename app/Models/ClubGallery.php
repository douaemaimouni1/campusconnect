<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubGallery extends Model
{
    protected $fillable = [
        'club_id',
        'image',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}