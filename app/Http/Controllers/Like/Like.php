<?php

namespace App\Http\Controllers\Like;

use App\Http\Controllers\Controller;
use App\Models\Chirp;

class Like extends Controller
{
    public function __invoke(Chirp $chirp)
    {
        $user = auth()->user();

        try {
            // Toggle like
            $existingLike = $chirp->likes()->where('user_id', $user->id)->first();

            if ($existingLike) {
                $existingLike->delete();
            } else {
                $chirp->likes()->create(['user_id' => $user->id]);
            }

            return back();

        } catch (\Throwable $th) {

        }


        return back();
    }
}
