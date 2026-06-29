<?php

namespace App\Http\Controllers\Like;

use App\Http\Controllers\Controller;
use App\Http\Requests\LikeRequest;
use App\Models\Comment;

class CommentLikeController extends Controller
{
    public function __invoke(LikeRequest $request, Comment $comment)
    {
        $user = auth()->user();
        $type = $request->input('type');

        $existingLike = $comment->likes()
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            if ($existingLike->type === $type) {
                $existingLike->delete();
            } else {
                $existingLike->update(['type' => $type]);
            }
        } else {
            $comment->likes()->create([
                'user_id' => $user->id,
                'comment_id' => $comment->id,
                'type' => $type,
            ]);
        }

        $comment->loadCount([
            'likes as likes_count' => fn ($q) => $q->where('type', 'like'),
            'likes as dislikes_count' => fn ($q) => $q->where('type', 'dislike'),
        ]);

        return response()->json([
            'success' => true,
            'likes' => $comment->likes_count,
            'dislikes' => $comment->dislikes_count,
            'currentUserReaction' => $comment->likes->where('user_id', $user->id)->first()?->type,
        ]);
    }
}
