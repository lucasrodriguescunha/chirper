<?php

namespace App\Http\Controllers;

use App\Models\User;

class FollowController extends Controller
{
    public function store(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot follow yourself.');

        auth()->user()->following()->syncWithoutDetaching([$user->id]);

        return back()->with('success', "You are now following {$user->name}.");
    }

    public function destroy(User $user)
    {
        auth()->user()->following()->detach($user->id);

        return back()->with('success', "You unfollowed {$user->name}.");
    }
}
