<?php

namespace App\Http\Controllers;

use App\Models\Chirp;

class BookmarkController extends Controller
{
    public function index()
    {
        $chirps = auth()->user()
            ->bookmarkedChirps()
            ->with(['user', 'attachments', 'likes', 'comments.user', 'comments.likes', 'bookmarks'])
            ->orderByPivot('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('bookmarks.index', ['chirps' => $chirps]);
    }

    public function store(Chirp $chirp)
    {
        auth()->user()->bookmarkedChirps()->syncWithoutDetaching([$chirp->id]);

        return back()->with('success', 'Chirp saved to bookmarks.');
    }

    public function destroy(Chirp $chirp)
    {
        auth()->user()->bookmarkedChirps()->detach($chirp->id);

        return back()->with('success', 'Bookmark removed.');
    }
}
