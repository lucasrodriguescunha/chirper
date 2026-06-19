<?php

use App\Models\Chirp;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->for($user)->create();

    expect($comment->user->id)->toBe($user->id);
});

it('belongs to a chirp', function () {
    $chirp = Chirp::factory()->create();
    $comment = Comment::factory()->for($chirp)->create();

    expect($comment->chirp->id)->toBe($chirp->id);
});

it('has many likes via comment_id', function () {
    $comment = Comment::factory()->create();
    Like::factory()->count(2)->create([
        'chirp_id' => null,
        'comment_id' => $comment->id,
    ]);

    expect($comment->likes)->toHaveCount(2);
});
