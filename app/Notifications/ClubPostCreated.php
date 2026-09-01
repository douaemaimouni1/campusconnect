<?php

namespace App\Notifications;

use App\Models\ClubPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ClubPostCreated extends Notification
{
    use Queueable;

    public function __construct(public ClubPost $post)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $content = trim(strip_tags($this->post->content));
        $preview = mb_strlen($content) > 80
            ? mb_substr($content, 0, 80) . '…'
            : $content;

        return [
            'post_id' => $this->post->id,
            'club_id' => $this->post->club_id,
            'club_name' => $this->post->club->name,
            'content_preview' => $preview,
            'event_id' => $this->post->event_id,
        ];
    }
}