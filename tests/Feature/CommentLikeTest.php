<?php

use App\Models\Chirp;
use App\Models\Comment;
use App\Models\Like;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->chirp = Chirp::factory()->create();
    $this->comment = Comment::factory()->for($this->chirp)->create();
});

it('requires auth to react to a comment', function () {
    $response = $this->postJson("/comments/{$this->comment->id}/reaction", ['type' => 'like']);

    $response->assertUnauthorized();
});

it('creates a like on a comment', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/comments/{$this->comment->id}/reaction", ['type' => 'like']);

    $response->assertOk()
        ->assertJson(['likes' => 1, 'dislikes' => 0, 'currentUserReaction' => 'like']);

    expect(Like::where([
        'user_id' => $this->user->id,
        'comment_id' => $this->comment->id,
        'type' => 'like',
    ])->exists())->toBeTrue();
});

it('toggles off when same reaction sent twice on a comment', function () {
    Like::factory()->for($this->user)->create([
        'chirp_id' => null,
        'comment_id' => $this->comment->id,
        'type' => 'like',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/comments/{$this->comment->id}/reaction", ['type' => 'like']);

    $response->assertOk()
        ->assertJson(['likes' => 0, 'dislikes' => 0, 'currentUserReaction' => null]);
});

it('switches a comment reaction from like to dislike', function () {
    Like::factory()->for($this->user)->create([
        'chirp_id' => null,
        'comment_id' => $this->comment->id,
        'type' => 'like',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/comments/{$this->comment->id}/reaction", ['type' => 'dislike']);

    $response->assertOk()
        ->assertJson(['likes' => 0, 'dislikes' => 1, 'currentUserReaction' => 'dislike']);
});

it('rejects invalid comment reaction type', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/comments/{$this->comment->id}/reaction", ['type' => 'bogus']);

    $response->assertStatus(422);
});
