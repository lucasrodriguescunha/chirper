<?php

namespace App\Notifications;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NewLikeNotification extends Notification
{
    public function __construct(
        public User $liker,
        public Chirp $chirp,
        public string $reaction,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $verb = $this->reaction === 'like' ? 'liked' : 'disliked';

        return [
            'type' => 'like',
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'chirp_id' => $this->chirp->id,
            'reaction' => $this->reaction,
            'url' => route('users.show', $this->chirp->user_id),
            'message' => "{$this->liker->name} {$verb} your chirp.",
        ];
    }
}
