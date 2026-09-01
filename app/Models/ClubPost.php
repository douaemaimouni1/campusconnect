<?php

namespace App\Models;

use App\Notifications\ClubPostCreated;
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

    /**
     * Dès qu'un post est créé (peu importe l'endroit du code qui appelle
     * ClubPost::create(), il y en a plusieurs), notifie tous les membres
     * acceptés du club, sauf l'auteur du post lui-même (toujours le
     * président, pas de sens de se notifier soi-même).
     */
    protected static function booted(): void
    {
        static::created(function (ClubPost $post) {
            $members = ClubMembership::where('club_id', $post->club_id)
                ->where('status', 'accepted')
                ->where('user_id', '!=', $post->user_id)
                ->with('user')
                ->get();

            foreach ($members as $membership) {
                $membership->user->notify(new ClubPostCreated($post));
            }
        });
    }
}