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
}
