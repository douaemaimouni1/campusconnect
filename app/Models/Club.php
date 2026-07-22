<?php

namespace App\Models;

use App\Models\User;
use App\Models\Event;
use App\Models\ClubMembership;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'logo',
        'president_id',
    ];
    public function president()
    {
        return $this->belongsTo(User::class, 'president_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function memberships()
    {
        return $this->hasMany(ClubMembership::class);
    }
}