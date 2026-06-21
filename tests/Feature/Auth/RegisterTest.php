<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a new user and logs them in', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertRedirect('/email/verify');

    $user = User::where('email', 'alice@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Alice')
        ->and(Hash::check('Secret-Pass1!', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('rejects registration with mismatched password confirmation', function () {
    $response = $this->post('/register', [
        'name' => 'Bob',
        'email' => 'bob@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Other-Pass1!',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

it('rejects registration when email already exists', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post('/register', [
        'name' => 'Carl',
        'email' => 'taken@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
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

it('rejects registration with weak password missing uppercase, number, or symbol', function () {
    $response = $this->post('/register', [
        'name' => 'Eve',
        'email' => 'eve@example.com',
        'password' => 'lowercaseonly',
        'password_confirmation' => 'lowercaseonly',
    ]);

    $response->assertSessionHasErrors('password');
});
