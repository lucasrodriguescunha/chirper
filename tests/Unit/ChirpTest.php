<?php

use App\Models\Chirp;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $chirp = Chirp::factory()->for($user)->create();

    expect($chirp->user->id)->toBe($user->id);
});

it('has many likes', function () {
    $chirp = Chirp::factory()->create();
    Like::factory()->count(2)->for($chirp)->create();

    expect($chirp->likes)->toHaveCount(2);
});

it('has many comments', function () {
    $chirp = Chirp::factory()->create();
    Comment::factory()->count(3)->for($chirp)->create();

    expect($chirp->comments)->toHaveCount(3);
});
