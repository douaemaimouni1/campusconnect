<?php

namespace App\Models;

use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\EventRegistration;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'bio',
        'avatar',
        'profile_completed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile_completed' => 'boolean',
        ];
    }

    public function clubs()
    {
        return $this->hasMany(Club::class, 'president_id');
    }

    public function clubMemberships()
    {
        return $this->hasMany(ClubMembership::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superAdmin';
    }
}