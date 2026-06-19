<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('requires auth to edit profile', function () {
    $response = $this->get('/settings/profile');

    $response->assertRedirect('/login');
});

it('shows the profile edit page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/profile');

    $response->assertOk();
    $response->assertViewIs('settings.profile.edit');
});

it('updates the user name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)
        ->patch('/settings/profile', ['name' => 'New Name']);

    $response->assertRedirect(route('settings.profile.edit'));
    expect($user->fresh()->name)->toBe('New Name');
});

it('uploads an avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($user)
        ->patch('/settings/profile', [
            'name' => $user->name,
            'avatar' => $file,
        ]);

    $response->assertRedirect(route('settings.profile.edit'));
    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
});
