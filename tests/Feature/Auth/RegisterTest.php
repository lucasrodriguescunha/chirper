<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a new user and logs them in', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertRedirect('/email/verify');

    $user = User::where('email', 'alice@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Alice')
        ->and($user->username)->toBe('alice')
        ->and(Hash::check('Secret-Pass1!', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('rejects registration without a username', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors('username');
});

it('rejects registration with invalid username characters', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'username' => 'has space',
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors('username');
});

it('rejects registration when username already exists', function () {
    User::factory()->create(['username' => 'taken']);

    $response = $this->post('/register', [
        'name' => 'Carl',
        'username' => 'taken',
        'email' => 'carl@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors('username');
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

it('rejects registration with invalid email format', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'username' => 'alice',
        'email' => 'not-an-email',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects registration with missing password confirmation', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors('password');
});

it('rejects registration with username shorter than 3 chars', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'username' => 'al',
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors(['username' => 'Username must be at least 3 characters.']);
});

it('rejects registration with username longer than 30 chars', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'username' => str_repeat('a', 31),
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors(['username' => 'Username must be 30 characters or less.']);
});

it('rejects registration with username containing punctuation', function () {
    $response = $this->post('/register', [
        'name' => 'Alice',
        'username' => 'user!',
        'email' => 'alice@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors(['username' => 'Username may contain only letters, numbers, and underscores.']);
});

it('returns the expected error message when username is taken', function () {
    User::factory()->create(['username' => 'taken']);

    $response = $this->post('/register', [
        'name' => 'Carl',
        'username' => 'taken',
        'email' => 'carl@example.com',
        'password' => 'Secret-Pass1!',
        'password_confirmation' => 'Secret-Pass1!',
    ]);

    $response->assertSessionHasErrors(['username' => 'This username is taken.']);
});
