<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\NewFollowerNotification;

class FollowController extends Controller
{
    public function store(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot follow yourself.');

        $changes = auth()->user()->following()->syncWithoutDetaching([$user->id]);

        if (!empty($changes['attached'])) {
            $user->notify(new NewFollowerNotification(auth()->user()));
        }

        return back()->with('success', "You are now following {$user->name}.");
    }

    public function destroy(User $user)
    {
        auth()->user()->following()->detach($user->id);

        return back()->with('success', "You unfollowed {$user->name}.");
    }
}
