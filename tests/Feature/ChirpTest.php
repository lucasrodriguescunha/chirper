<?php

use App\Models\Chirp;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists chirps on the home page', function () {
    Chirp::factory()->count(3)->create();

    $response = $this->get('/');

    $response->assertOk();
    $response->assertViewIs('home');
    $response->assertViewHas('chirps');
});

it('requires auth to store a chirp', function () {
    $response = $this->post('/chirps', ['message' => 'hello world']);

    $response->assertRedirect('/login');
});

it('stores a new chirp for the authenticated user', function () {
    $response = $this->actingAs($this->user)->post('/chirps', [
        'message' => 'first chirp',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('success');

    expect(Chirp::where([
        'user_id' => $this->user->id,
        'message' => 'first chirp',
    ])->exists())->toBeTrue();
});

it('validates required message when storing a chirp', function () {
    $response = $this->actingAs($this->user)->post('/chirps', ['message' => '']);

    $response->assertSessionHasErrors('message');
});

it('rejects messages longer than 255 chars', function () {
    $response = $this->actingAs($this->user)->post('/chirps', [
        'message' => str_repeat('a', 256),
    ]);

    $response->assertSessionHasErrors('message');
});

it('allows the owner to view edit page', function () {
    $chirp = Chirp::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->get("/chirps/{$chirp->id}/edit");

    $response->assertOk();
});

it('forbids non-owners from editing a chirp', function () {
    $chirp = Chirp::factory()->create();

    $response = $this->actingAs($this->user)->get("/chirps/{$chirp->id}/edit");

    $response->assertForbidden();
});

it('updates a chirp owned by the user', function () {
    $chirp = Chirp::factory()->for($this->user)->create(['message' => 'old']);

    $response = $this->actingAs($this->user)
        ->put("/chirps/{$chirp->id}", ['message' => 'new']);

    $response->assertRedirect('/');
    expect($chirp->fresh()->message)->toBe('new');
});

it('forbids updating a chirp not owned by the user', function () {
    $chirp = Chirp::factory()->create(['message' => 'old']);

    $response = $this->actingAs($this->user)
        ->put("/chirps/{$chirp->id}", ['message' => 'new']);

    $response->assertForbidden();
    expect($chirp->fresh()->message)->toBe('old');
});

it('deletes a chirp owned by the user', function () {
    $chirp = Chirp::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->delete("/chirps/{$chirp->id}");

    $response->assertRedirect('/');
    expect(Chirp::find($chirp->id))->toBeNull();
});

it('forbids deleting a chirp not owned by the user', function () {
    $chirp = Chirp::factory()->create();

    $response = $this->actingAs($this->user)->delete("/chirps/{$chirp->id}");

    $response->assertForbidden();
    expect(Chirp::find($chirp->id))->not->toBeNull();
});
