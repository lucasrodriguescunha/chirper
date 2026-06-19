<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Chirp;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(CommentRequest $request, Chirp $chirp)
    {
        $chirp->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->validated()['body'],
        ]);

        return back()->with('success', 'Comment posted!');
    }

    public function update(CommentRequest $request, Chirp $chirp, Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $comment->update(['body' => $request->validated()['body']]);

        return back()->with('success', 'Comment updated!');
    }

    public function destroy(Chirp $chirp, Comment $comment)
    {
        abort_if($comment->user_id !== auth()->id(), 403);

        $comment->delete();

        return back()->with('success', 'Comment deleted!');
    }
}
