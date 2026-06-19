<?php

use App\Models\Chirp;
use App\Models\Comment;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->chirp = Chirp::factory()->create();
});

it('requires auth to comment on a chirp', function () {
    $response = $this->post("/chirps/{$this->chirp->id}/comments", [
        'body' => 'nice chirp',
    ]);

    $response->assertRedirect('/login');
});

it('stores a comment for the authenticated user', function () {
    $response = $this->actingAs($this->user)
        ->post("/chirps/{$this->chirp->id}/comments", ['body' => 'nice chirp']);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Comment::where([
        'chirp_id' => $this->chirp->id,
        'user_id' => $this->user->id,
        'body' => 'nice chirp',
    ])->exists())->toBeTrue();
});

it('validates required body when commenting', function () {
    $response = $this->actingAs($this->user)
        ->post("/chirps/{$this->chirp->id}/comments", ['body' => '']);

    $response->assertSessionHasErrors('body');
});

it('rejects body longer than 255 chars', function () {
    $response = $this->actingAs($this->user)->post(
        "/chirps/{$this->chirp->id}/comments",
        ['body' => str_repeat('x', 256)]
    );

    $response->assertSessionHasErrors('body');
});

it('lets the owner delete their comment', function () {
    $comment = Comment::factory()
        ->for($this->user)
        ->for($this->chirp)
        ->create();

    $response = $this->actingAs($this->user)
        ->delete("/chirps/{$this->chirp->id}/comments/{$comment->id}");

    $response->assertRedirect();
    expect(Comment::find($comment->id))->toBeNull();
});

it('forbids non-owner from deleting a comment', function () {
    $comment = Comment::factory()->for($this->chirp)->create();

    $response = $this->actingAs($this->user)
        ->delete("/chirps/{$this->chirp->id}/comments/{$comment->id}");

    $response->assertForbidden();
    expect(Comment::find($comment->id))->not->toBeNull();
});

it('lets the owner update their comment', function () {
    $comment = Comment::factory()
        ->for($this->user)
        ->for($this->chirp)
        ->create(['body' => 'old text']);

    $response = $this->actingAs($this->user)
        ->put("/chirps/{$this->chirp->id}/comments/{$comment->id}", ['body' => 'new text']);

    $response->assertRedirect();
    expect($comment->fresh()->body)->toBe('new text');
});

it('forbids non-owner from updating a comment', function () {
    $comment = Comment::factory()
        ->for($this->chirp)
        ->create(['body' => 'original']);

    $response = $this->actingAs($this->user)
        ->put("/chirps/{$this->chirp->id}/comments/{$comment->id}", ['body' => 'hacked']);

    $response->assertForbidden();
    expect($comment->fresh()->body)->toBe('original');
});

it('validates body when updating a comment', function () {
    $comment = Comment::factory()
        ->for($this->user)
        ->for($this->chirp)
        ->create();

    $response = $this->actingAs($this->user)
        ->put("/chirps/{$this->chirp->id}/comments/{$comment->id}", ['body' => '']);

    $response->assertSessionHasErrors('body');
});

it('rejects update body longer than 255 chars', function () {
    $comment = Comment::factory()
        ->for($this->user)
        ->for($this->chirp)
        ->create();

    $response = $this->actingAs($this->user)->put(
        "/chirps/{$this->chirp->id}/comments/{$comment->id}",
        ['body' => str_repeat('x', 256)]
    );

    $response->assertSessionHasErrors('body');
});
