<?php

namespace App\Models;

use App\Models\User;
use App\Models\Event;
use App\Models\ClubMembership;
use App\Models\ClubPost;
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
    'banner',
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

    public function posts()
    {
        return $this->hasMany(ClubPost::class)->latest();
    }

    public function presidencyTransfers()
    {
        return $this->hasMany(ClubPresidencyTransfer::class);
    }

    public function pendingPresidencyTransfer()
    {
        return $this->presidencyTransfers()
            ->pending()
            ->latest()
            ->first();
    }
}