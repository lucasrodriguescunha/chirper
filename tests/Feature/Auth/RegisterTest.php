<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a new user and logs them in', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'secret-pass',
        'password_confirmation' => 'secret-pass',
    ]);

    $response->assertRedirect('/email/verify');

    $user = User::where('email', 'alice@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Alice')
        ->and(Hash::check('secret-pass', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('rejects registration with mismatched password confirmation', function () {
    $response = $this->post('/register', [
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'password' => 'secret-pass',
        'password_confirmation' => 'other-pass',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

it('rejects registration when email already exists', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post('/register', [
        'name' => 'Carl',
        'email' => 'taken@example.com',
        'password' => 'secret-pass',
        'password_confirmation' => 'secret-pass',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects registration with short password', function () {
    $response = $this->post('/register', [
        'name' => 'Dan',
        'email' => 'dan@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
});
