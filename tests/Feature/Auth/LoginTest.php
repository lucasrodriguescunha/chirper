<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

it('logs in a user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('correct-pass'),
    ]);

    $response = $this->post('/login', [
        'email' => 'login@example.com',
        'password' => 'correct-pass',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid password', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('correct-pass'),
    ]);

    $response = $this->post('/login', [
        'email' => 'login@example.com',
        'password' => 'wrong-pass',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('blocks login after too many attempts', function () {
    User::factory()->create([
        'email' => 'limited@example.com',
        'password' => Hash::make('correct-pass'),
    ]);

    foreach (range(1, 3) as $i) {
        $this->post('/login', [
            'email' => 'limited@example.com',
            'password' => 'wrong',
        ]);
    }

    $response = $this->post('/login', [
        'email' => 'limited@example.com',
        'password' => 'correct-pass',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    RateLimiter::clear(strtolower('limited@example.com').'|127.0.0.1');
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

it('blocks logout for an unverified user', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticatedAs($user);
});
