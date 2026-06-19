<?php

namespace App\Notifications;

use App\Models\Chirp;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewCommentNotification extends Notification
{
    public function __construct(
        public User $commenter,
        public Chirp $chirp,
        public Comment $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'comment',
            'commenter_id' => $this->commenter->id,
            'commenter_name' => $this->commenter->name,
            'chirp_id' => $this->chirp->id,
            'comment_id' => $this->comment->id,
            'url' => route('users.show', $this->chirp->user_id),
            'message' => "{$this->commenter->name} commented: \"" . Str::limit($this->comment->body, 60) . '"',
        ];
    }
}
