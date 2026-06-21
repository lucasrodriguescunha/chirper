<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('shows the forgot-password form', function () {
    $response = $this->get('/forgot-password');

    $response->assertOk();
});

it('sends a password reset link', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets the password with a valid token', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'New-Secret-Pass1!',
            'password_confirmation' => 'New-Secret-Pass1!',
        ]);

        $response->assertRedirect(route('login'));
        expect(Hash::check('New-Secret-Pass1!', $user->fresh()->password))->toBeTrue();

        return true;
    });
});
