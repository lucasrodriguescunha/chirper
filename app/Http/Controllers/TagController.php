<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $name = Str::of($slug)->lower()->toString();

        if (! preg_match('/^[a-z0-9_]{2,50}$/', $name)) {
            throw new NotFoundHttpException();
        }

        $tag = Tag::where('name', $name)->firstOrFail();

        $chirps = $tag->chirps()
            ->with(['user', 'attachments', 'likes', 'comments.user', 'comments.likes', 'bookmarks'])
            ->latest('chirps.created_at')
            ->paginate(10)
            ->withQueryString();

        return view('tags.show', ['tag' => $tag, 'chirps' => $chirps]);
    }
}
