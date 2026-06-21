<?php

use App\Providers\AppServiceProvider;

test('production environment with debug enabled aborts boot', function () {
    $this->app->detectEnvironment(fn () => 'production');
    config(['app.debug' => true]);

    $provider = new AppServiceProvider($this->app);

    expect(fn () => $provider->boot())
        ->toThrow(RuntimeException::class, 'APP_DEBUG must be false in production');
});

test('production environment with debug disabled boots cleanly', function () {
    $this->app->detectEnvironment(fn () => 'production');
    config(['app.debug' => false]);

    $provider = new AppServiceProvider($this->app);

    expect(fn () => $provider->boot())->not->toThrow(RuntimeException::class);
});

test('non-production environment with debug enabled boots cleanly', function () {
    $this->app->detectEnvironment(fn () => 'local');
    config(['app.debug' => true]);

    $provider = new AppServiceProvider($this->app);

    expect(fn () => $provider->boot())->not->toThrow(RuntimeException::class);
});
