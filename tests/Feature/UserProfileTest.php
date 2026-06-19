<?php

use App\Models\Chirp;
use App\Models\User;

beforeEach(function () {
    $this->viewer = User::factory()->create();
    $this->profile = User::factory()->create(['bio' => 'hello world']);
});

it('requires auth to view a profile', function () {
    $response = $this->get(route('users.show', $this->profile));

    $response->assertRedirect('/login');
});

it('shows a user profile with bio and counts', function () {
    Chirp::factory()->count(2)->for($this->profile)->create();
    $this->viewer->following()->attach($this->profile);

    $response = $this->actingAs($this->viewer)->get(route('users.show', $this->profile));

    $response->assertOk();
    $response->assertSee($this->profile->name);
    $response->assertSee('hello world');
    $response->assertSee('Following');
    $response->assertSee('Followers');
});

it('lists only the profile owner chirps', function () {
    $mine = Chirp::factory()->for($this->profile)->create(['message' => 'my chirp']);
    $other = Chirp::factory()->create(['message' => 'someone else chirp']);

    $response = $this->actingAs($this->viewer)->get(route('users.show', $this->profile));

    $response->assertSee('my chirp');
    $response->assertDontSee('someone else chirp');
});

it('shows Follow button when not following', function () {
    $response = $this->actingAs($this->viewer)->get(route('users.show', $this->profile));

    $response->assertSee('Follow');
});

it('shows Unfollow button when already following', function () {
    $this->viewer->following()->attach($this->profile);

    $response = $this->actingAs($this->viewer)->get(route('users.show', $this->profile));

    $response->assertSee('Unfollow');
});

it('hides follow button on own profile', function () {
    $response = $this->actingAs($this->viewer)->get(route('users.show', $this->viewer));

    $response->assertDontSee('>Follow<', false);
    $response->assertDontSee('>Unfollow<', false);
});

it('saves bio when updating profile', function () {
    $user = User::factory()->create(['bio' => null]);

    $this->actingAs($user)->patch('/settings/profile', [
        'name' => $user->name,
        'bio' => 'new bio text',
    ]);

    expect($user->fresh()->bio)->toBe('new bio text');
});

it('rejects bio longer than 255 chars', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/profile', [
        'name' => $user->name,
        'bio' => str_repeat('x', 256),
    ]);

    $response->assertSessionHasErrors('bio');
});
