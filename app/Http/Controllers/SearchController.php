<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $users = collect();
        $chirps = null;

        if ($q !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

            $users = User::query()
                ->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orderBy('name')
                ->limit(10)
                ->get();

            $chirps = Chirp::query()
                ->with(['user', 'attachments', 'likes', 'comments.user', 'comments.likes'])
                ->where('message', 'like', $like)
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }

        return view('search.index', [
            'q' => $q,
            'users' => $users,
            'chirps' => $chirps,
        ]);
    }

    public function suggest(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json(['users' => [], 'chirps' => []]);
        }

        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $q) . '%';

        $users = User::query()
            ->where('name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatarUrl(),
                'url' => route('users.show', $u),
            ]);

        $chirps = Chirp::query()
            ->with('user:id,name')
            ->where('message', 'like', $like)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Chirp $c) => [
                'id' => $c->id,
                'message' => \Illuminate\Support\Str::limit($c->message, 120),
                'author' => $c->user->name,
                'url' => route('search', ['q' => $q]) . '#chirp-' . $c->id,
            ]);

        return response()->json([
            'users' => $users,
            'chirps' => $chirps,
        ]);
    }
}
