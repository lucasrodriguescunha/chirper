<?php

use App\Models\Chirp;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('requires auth to bookmark', function () {
    $chirp = Chirp::factory()->create();

    $this->post(route('bookmarks.store', $chirp))->assertRedirect('/login');
});

it('requires auth to view bookmarks index', function () {
    $this->get(route('bookmarks.index'))->assertRedirect('/login');
});

it('bookmarks a chirp for the authenticated user', function () {
    $chirp = Chirp::factory()->create();

    $response = $this->actingAs($this->user)->post(route('bookmarks.store', $chirp));

    $response->assertRedirect();
    $this->assertDatabaseHas('bookmarks', [
        'user_id' => $this->user->id,
        'chirp_id' => $chirp->id,
    ]);
});

it('does not duplicate bookmarks on repeat saves', function () {
    $chirp = Chirp::factory()->create();

    $this->actingAs($this->user)->post(route('bookmarks.store', $chirp));
    $this->actingAs($this->user)->post(route('bookmarks.store', $chirp));

    expect($this->user->bookmarkedChirps()->count())->toBe(1);
});

it('removes a bookmark', function () {
    $chirp = Chirp::factory()->create();
    $this->user->bookmarkedChirps()->attach($chirp);

    $response = $this->actingAs($this->user)->delete(route('bookmarks.destroy', $chirp));

    $response->assertRedirect();
    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $this->user->id,
        'chirp_id' => $chirp->id,
    ]);
});

it('shows the bookmarked chirps on the index page', function () {
    $mine = Chirp::factory()->create(['message' => 'saved one']);
    $other = Chirp::factory()->create(['message' => 'not saved']);
    $this->user->bookmarkedChirps()->attach($mine);

    $response = $this->actingAs($this->user)->get(route('bookmarks.index'));

    $response->assertOk();
    $response->assertSee('saved one');
    $response->assertDontSee('not saved');
});

it('lets the owner bookmark their own chirp', function () {
    $chirp = Chirp::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->post(route('bookmarks.store', $chirp));

    $response->assertRedirect();
    $this->assertDatabaseHas('bookmarks', [
        'user_id' => $this->user->id,
        'chirp_id' => $chirp->id,
    ]);
});

it('removes a bookmark without deleting the chirp', function () {
    $chirp = Chirp::factory()->for($this->user)->create();
    $this->user->bookmarkedChirps()->attach($chirp);

    $this->actingAs($this->user)->delete(route('bookmarks.destroy', $chirp));

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $this->user->id,
        'chirp_id' => $chirp->id,
    ]);
    expect(Chirp::find($chirp->id))->not->toBeNull();
});

it('cascade-deletes bookmarks when chirp deleted', function () {
    $chirp = Chirp::factory()->create();
    $this->user->bookmarkedChirps()->attach($chirp);

    $chirp->delete();

    $this->assertDatabaseMissing('bookmarks', [
        'user_id' => $this->user->id,
        'chirp_id' => $chirp->id,
    ]);
});
