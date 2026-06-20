<?php

use App\Models\User;

it('redirects guests away from the home page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

it('returns a successful response for verified users', function () {
    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertStatus(200);
});
