<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->other = User::factory()->create();
});

it('requires auth to follow a user', function () {
    $response = $this->post(route('follows.store', $this->other));

    $response->assertRedirect('/login');
});

it('follows another user', function () {
    $response = $this->actingAs($this->user)
        ->post(route('follows.store', $this->other));

    $response->assertRedirect();
    expect($this->user->fresh()->isFollowing($this->other))->toBeTrue();
});

it('cannot follow yourself', function () {
    $response = $this->actingAs($this->user)
        ->post(route('follows.store', $this->user));

    $response->assertForbidden();
    expect($this->user->fresh()->following()->count())->toBe(0);
});

it('is idempotent when following twice', function () {
    $this->actingAs($this->user)->post(route('follows.store', $this->other));
    $this->actingAs($this->user)->post(route('follows.store', $this->other));

    expect($this->user->following()->count())->toBe(1);
});

it('unfollows a user', function () {
    $this->user->following()->attach($this->other);

    $response = $this->actingAs($this->user)
        ->delete(route('follows.destroy', $this->other));

    $response->assertRedirect();
    expect($this->user->fresh()->isFollowing($this->other))->toBeFalse();
});

it('reports follower and following counts', function () {
    $followers = User::factory()->count(3)->create();
    foreach ($followers as $f) {
        $f->following()->attach($this->user);
    }
    $this->user->following()->attach($this->other);

    expect($this->user->followers()->count())->toBe(3);
    expect($this->user->following()->count())->toBe(1);
});
