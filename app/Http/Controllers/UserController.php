<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function show(User $user)
    {
        $chirps = $user->chirps()
            ->with(['user', 'attachments', 'likes', 'comments.user', 'comments.likes'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.show', [
            'user' => $user,
            'chirps' => $chirps,
        ]);
    }
}
