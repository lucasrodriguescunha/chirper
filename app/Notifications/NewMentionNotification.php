<?php

namespace App\Notifications;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMentionNotification extends Notification
{
    public function __construct(
        public User $mentioner,
        public Chirp $chirp,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mention',
            'mentioner_id' => $this->mentioner->id,
            'mentioner_name' => $this->mentioner->name,
            'mentioner_username' => $this->mentioner->username,
            'chirp_id' => $this->chirp->id,
            'preview' => Str::limit($this->chirp->message, 80),
            'url' => route('users.show', $this->chirp->user_id),
            'message' => "{$this->mentioner->name} mentioned you in a chirp.",
        ];
    }
}
