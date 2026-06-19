<?php

use App\Models\Chirp;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('requires auth to search', function () {
    $response = $this->get(route('search', ['q' => 'hello']));

    $response->assertRedirect('/login');
});

it('shows empty prompt when no query', function () {
    $response = $this->actingAs($this->user)->get(route('search'));

    $response->assertOk();
    $response->assertSee('Type a query');
});

it('finds users by name', function () {
    $alice = User::factory()->create(['name' => 'Alice Wonderland']);
    User::factory()->create(['name' => 'Bob Builder']);

    $response = $this->actingAs($this->user)->get(route('search', ['q' => 'Alice']));

    $response->assertOk();
    $response->assertSee('Alice Wonderland');
    $response->assertDontSee('Bob Builder');
});

it('finds chirps by message', function () {
    Chirp::factory()->create(['message' => 'pineapple pizza is great']);
    Chirp::factory()->create(['message' => 'tacos forever']);

    $response = $this->actingAs($this->user)->get(route('search', ['q' => 'pineapple']));

    $response->assertOk();
    $response->assertSee('pineapple pizza is great');
    $response->assertDontSee('tacos forever');
});

it('shows no results message when query matches nothing', function () {
    $response = $this->actingAs($this->user)->get(route('search', ['q' => 'zzzzznothing']));

    $response->assertOk();
    $response->assertSee('No users matched');
    $response->assertSee('No chirps matched');
});

it('escapes LIKE wildcards so % does not match all', function () {
    Chirp::factory()->create(['message' => 'plain text']);

    $response = $this->actingAs($this->user)->get(route('search', ['q' => '%']));

    $response->assertOk();
    $response->assertDontSee('plain text');
});
