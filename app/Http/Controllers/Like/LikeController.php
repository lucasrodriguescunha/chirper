<?php

namespace App\Http\Controllers\Like;

use App\Http\Controllers\Controller;
use App\Http\Requests\LikeRequest;
use App\Models\Chirp;

class LikeController extends Controller
{
    public function __invoke(LikeRequest $request, Chirp $chirp)
    {
        $user = auth()->user();
        $type = $request->input('type');

        $existingLike = $chirp->likes()
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            if ($existingLike->type === $type) {
                $existingLike->delete();
            } else {
                $existingLike->update(['type' => $type]);
            }
        } else {
            $chirp->likes()->create([
                'user_id' => $user->id,
                'type' => $type,
            ]);
        }

        $chirp->load('likes');

        return response()->json([
            'success' => true,
            'likes' => $chirp->likes->where('type', 'like')->count(),
            'dislikes' => $chirp->likes->where('type', 'dislike')->count(),
            'userType' => $chirp->likes->where('user_id', $user->id)->first()?->type,
        ]);
    }
}
