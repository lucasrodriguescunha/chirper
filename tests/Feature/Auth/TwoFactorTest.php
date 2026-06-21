<?php

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('login.test@example.com|127.0.0.1');
});

it('login redirects to challenge when 2FA is enabled', function () {
    $service = app(TwoFactorService::class);
    $secret = $service->generateSecret();

    $user = User::factory()->create([
        'email' => 'twofactor@example.com',
        'password' => bcrypt('Secret-Pass1!'),
        'email_verified_at' => now(),
    ]);

    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => json_encode(['code1-aaaaa', 'code2-bbbbb']),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $response = $this->post('/login', [
        'email' => 'twofactor@example.com',
        'password' => 'Secret-Pass1!',
    ]);

    $response->assertRedirect(route('two-factor.challenge'));
    $this->assertGuest();
    expect(session('two_factor.user_id'))->toBe($user->id);
});

it('challenge accepts a valid TOTP code', function () {
    $service = app(TwoFactorService::class);
    $secret = $service->generateSecret();

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->forceFill([
        'two_factor_secret' => $secret,
        'two_factor_recovery_codes' => json_encode([]),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $code = $google2fa->getCurrentOtp($secret);

    $this->withSession(['two_factor.user_id' => $user->id])
        ->post(route('two-factor.challenge'), ['code' => $code])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('challenge accepts a recovery code and consumes it', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $user->forceFill([
        'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        'two_factor_recovery_codes' => json_encode(['aaaaa-bbbbb', 'ccccc-ddddd']),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->withSession(['two_factor.user_id' => $user->id])
        ->post(route('two-factor.challenge'), ['recovery_code' => 'aaaaa-bbbbb'])
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->recoveryCodes())->toBe(['ccccc-ddddd']);
});

it('challenge rejects an invalid code', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->forceFill([
        'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        'two_factor_recovery_codes' => json_encode([]),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->withSession(['two_factor.user_id' => $user->id])
        ->post(route('two-factor.challenge'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    $this->assertGuest();
});

it('disables 2FA when the current password is provided', function () {
    $user = User::factory()->create([
        'password' => bcrypt('Secret-Pass1!'),
        'email_verified_at' => now(),
    ]);
    $user->forceFill([
        'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        'two_factor_recovery_codes' => json_encode([]),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->delete(route('settings.two-factor.disable'), ['password' => 'Secret-Pass1!'])
        ->assertRedirect(route('settings.two-factor.index'));

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});
