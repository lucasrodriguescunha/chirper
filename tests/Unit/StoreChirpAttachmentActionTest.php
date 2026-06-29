<?php

use App\Actions\StoreChirpAttachmentAction;
use App\Models\Chirp;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('stores file on public disk and creates attachment row', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $chirp = Chirp::factory()->for($user)->create();
    $file = UploadedFile::fake()->create('photo.png', 100, 'image/png');

    $path = (new StoreChirpAttachmentAction)->execute($chirp, $file);

    expect($path)->toBeString();
    Storage::disk('public')->assertExists($path);
    expect($chirp->attachments()->count())->toBe(1);
    expect($chirp->attachments()->first()->type)->toBe('png');
});

it('deletes a stored file from disk', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $chirp = Chirp::factory()->for($user)->create();
    $file = UploadedFile::fake()->create('photo.png', 100, 'image/png');

    $action = new StoreChirpAttachmentAction;
    $path = $action->execute($chirp, $file);

    Storage::disk('public')->assertExists($path);

    $action->delete($path);

    Storage::disk('public')->assertMissing($path);
});
