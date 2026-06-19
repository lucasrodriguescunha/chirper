<?php

use App\Models\Chirp;
use App\Models\Like;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('returns fallback url when no avatar is set', function () {
    $user = User::factory()->make(['avatar' => null, 'email' => 'foo@bar.com']);

    expect($user->avatarUrl())->toContain('avatars.laravel.cloud');
});

it('returns stored avatar url when avatar exists', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $path = UploadedFile::fake()->create('a.jpg', 100)->store('avatars', 'public');
    $user->update(['avatar' => $path]);

    expect($user->avatarUrl())->toContain($path);
});

it('has many chirps', function () {
    $user = User::factory()->create();
    Chirp::factory()->count(2)->for($user)->create();

    expect($user->chirps)->toHaveCount(2);
});

it('has many likes', function () {
    $user = User::factory()->create();
    Like::factory()->count(2)->for($user)->create();

    expect($user->likes)->toHaveCount(2);
});
