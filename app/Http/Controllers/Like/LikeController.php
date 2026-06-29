<?php

namespace App\Http\Controllers\Like;

use App\Http\Controllers\Controller;
use App\Http\Requests\LikeRequest;
use App\Models\Chirp;
use App\Notifications\NewLikeNotification;

class LikeController extends Controller
{
    public function __invoke(LikeRequest $request, Chirp $chirp)
    {
        $user = auth()->user();
        $type = $request->input('type');

        $existingLike = $chirp->likes()
            ->where('user_id', $user->id)
            ->first();

        $notify = false;

        if ($existingLike) {
            if ($existingLike->type === $type) {
                $existingLike->delete();
            } else {
                $existingLike->update(['type' => $type]);
                $notify = true;
            }
        } else {
            $chirp->likes()->create([
                'user_id' => $user->id,
                'type' => $type,
            ]);
            $notify = true;
        }

        if ($notify && $chirp->user_id && $chirp->user_id !== $user->id) {
            $chirp->user->notify(new NewLikeNotification($user, $chirp, $type));
        }

        $chirp->loadCount([
            'likes as likes_count' => function ($query) {
                $query->where('type', 'like');
            },
            'likes as dislikes_count' => function ($query) {
                $query->where('type', 'dislike');
            },
        ]);

        return response()->json([
            'success' => true,
            'likes' => $chirp->likes_count,
            'dislikes' => $chirp->dislikes_count,
            'currentUserReaction' => $chirp->likes->where('user_id', $user->id)->first()?->type,
        ]);
    }
}
