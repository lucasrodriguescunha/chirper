<?php

use App\Models\Chirp;
use App\Models\Like;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->chirp = Chirp::factory()->create();
});

it('requires auth to react to a chirp', function () {
    $response = $this->postJson("/chirps/{$this->chirp->id}/reaction", ['type' => 'like']);

    $response->assertUnauthorized();
});

it('creates a like reaction', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/chirps/{$this->chirp->id}/reaction", ['type' => 'like']);

    $response->assertOk()
        ->assertJson(['likes' => 1, 'dislikes' => 0, 'currentUserReaction' => 'like']);

    expect(Like::where([
        'user_id' => $this->user->id,
        'chirp_id' => $this->chirp->id,
        'type' => 'like',
    ])->exists())->toBeTrue();
});

it('toggles off when same reaction sent twice', function () {
    Like::factory()->for($this->user)->for($this->chirp)->create(['type' => 'like']);

    $response = $this->actingAs($this->user)
        ->postJson("/chirps/{$this->chirp->id}/reaction", ['type' => 'like']);

    $response->assertOk()
        ->assertJson(['likes' => 0, 'dislikes' => 0, 'currentUserReaction' => null]);
});

it('switches from like to dislike', function () {
    Like::factory()->for($this->user)->for($this->chirp)->create(['type' => 'like']);

    $response = $this->actingAs($this->user)
        ->postJson("/chirps/{$this->chirp->id}/reaction", ['type' => 'dislike']);

    $response->assertOk()
        ->assertJson(['likes' => 0, 'dislikes' => 1, 'currentUserReaction' => 'dislike']);
});

it('rejects invalid reaction type', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/chirps/{$this->chirp->id}/reaction", ['type' => 'bogus']);

    $response->assertStatus(422);
});
