<?php

namespace App\Http\Controllers\Like;

use App\Http\Controllers\Controller;
use App\Models\Chirp;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __invoke(Request $request, Chirp $chirp)
    {
        $user = auth()->user();

        $type = $request->input('type');

        if (!in_array($type, ['like', 'dislike'])) {
            return back()->with('error', 'Invalid action');
        }

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
                'type' => $type
            ]);
        }

        return response()->json([
           'success' => true
        ]);
    }
}
