<?php

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists chirps on the home page', function () {
    Chirp::factory()->count(3)->create();

    $response = $this->actingAs($this->user)->get('/');

    $response->assertOk();
    $response->assertViewIs('home');
    $response->assertViewHas('chirps');
    $response->assertViewHas('feed', 'for-you');
});

it('for-you feed shows chirps from non-followed users', function () {
    $stranger = User::factory()->create();
    Chirp::factory()->for($stranger)->create(['message' => 'stranger chirp']);

    $response = $this->actingAs($this->user)->get('/');

    $response->assertOk();
    $chirps = $response->viewData('chirps');
    expect($chirps->pluck('message')->all())->toContain('stranger chirp');
});

it('following feed only shows chirps from followed users and self', function () {
    $followed = User::factory()->create();
    $stranger = User::factory()->create();

    $this->user->following()->attach($followed->id);

    Chirp::factory()->for($followed)->create(['message' => 'followed chirp']);
    Chirp::factory()->for($stranger)->create(['message' => 'stranger chirp']);
    Chirp::factory()->for($this->user)->create(['message' => 'self chirp']);

    $response = $this->actingAs($this->user)->get('/?feed=following');

    $response->assertOk();
    $response->assertViewHas('feed', 'following');
    $messages = $response->viewData('chirps')->pluck('message')->all();
    expect($messages)->toContain('followed chirp');
    expect($messages)->toContain('self chirp');
    expect($messages)->not->toContain('stranger chirp');
});

it('following feed is empty when user follows nobody and has no chirps', function () {
    Chirp::factory()->count(3)->create();

    $response = $this->actingAs($this->user)->get('/?feed=following');

    $response->assertOk();
    expect($response->viewData('chirps')->total())->toBe(0);
});

it('treats unknown feed values as for-you', function () {
    $response = $this->actingAs($this->user)->get('/?feed=bogus');

    $response->assertOk();
    $response->assertViewHas('feed', 'for-you');
});

it('redirects guests from the home page to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

it('redirects unverified users from the home page to verification notice', function () {
    $unverified = User::factory()->unverified()->create();

    $response = $this->actingAs($unverified)->get('/');

    $response->assertRedirect(route('verification.notice'));
});

it('requires auth to store a chirp', function () {
    $response = $this->post('/chirps', ['message' => 'hello world']);

    $response->assertRedirect('/login');
});

it('stores a new chirp for the authenticated user', function () {
    $response = $this->actingAs($this->user)->post('/chirps', [
        'message' => 'first chirp',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('success');

    expect(Chirp::where([
        'user_id' => $this->user->id,
        'message' => 'first chirp',
    ])->exists())->toBeTrue();
});

it('validates required message when storing a chirp', function () {
    $response = $this->actingAs($this->user)->post('/chirps', ['message' => '']);

    $response->assertSessionHasErrors('message');
});

it('rejects messages longer than 255 chars', function () {
    $response = $this->actingAs($this->user)->post('/chirps', [
        'message' => str_repeat('a', 256),
    ]);

    $response->assertSessionHasErrors('message');
});

it('allows the owner to view edit page', function () {
    $chirp = Chirp::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->get("/chirps/{$chirp->id}/edit");

    $response->assertOk();
});

it('forbids non-owners from editing a chirp', function () {
    $chirp = Chirp::factory()->create();

    $response = $this->actingAs($this->user)->get("/chirps/{$chirp->id}/edit");

    $response->assertForbidden();
});

it('updates a chirp owned by the user', function () {
    $chirp = Chirp::factory()->for($this->user)->create(['message' => 'old']);

    $response = $this->actingAs($this->user)
        ->put("/chirps/{$chirp->id}", ['message' => 'new']);

    $response->assertRedirect('/');
    expect($chirp->fresh()->message)->toBe('new');
});

it('forbids updating a chirp not owned by the user', function () {
    $chirp = Chirp::factory()->create(['message' => 'old']);

    $response = $this->actingAs($this->user)
        ->put("/chirps/{$chirp->id}", ['message' => 'new']);

    $response->assertForbidden();
    expect($chirp->fresh()->message)->toBe('old');
});

it('deletes a chirp owned by the user', function () {
    $chirp = Chirp::factory()->for($this->user)->create();

    $response = $this->actingAs($this->user)->delete("/chirps/{$chirp->id}");

    $response->assertRedirect('/');
    expect(Chirp::find($chirp->id))->toBeNull();
});

it('forbids deleting a chirp not owned by the user', function () {
    $chirp = Chirp::factory()->create();

    $response = $this->actingAs($this->user)->delete("/chirps/{$chirp->id}");

    $response->assertForbidden();
    expect(Chirp::find($chirp->id))->not->toBeNull();
});

it('stores a chirp with a valid image attachment', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($this->user)->post('/chirps', [
        'message' => 'chirp with attachment',
        'attachment' => $file,
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('success');

    $chirp = Chirp::where('message', 'chirp with attachment')->firstOrFail();
    $attachment = $chirp->attachments()->first();

    expect($attachment)->not->toBeNull();
    expect($attachment->type)->toBe('jpg');
    Storage::disk('public')->assertExists($attachment->path);
});

it('rejects an attachment with disallowed mime type', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('virus.exe', 100, 'application/octet-stream');

    $response = $this->actingAs($this->user)->post('/chirps', [
        'message' => 'bad file',
        'attachment' => $file,
    ]);

    $response->assertSessionHasErrors('attachment');
    expect(Chirp::where('message', 'bad file')->exists())->toBeFalse();
});

it('rejects an attachment larger than 2MB', function () {
    Storage::fake('public');
    $file = UploadedFile::fake()->create('big.pdf', 3000, 'application/pdf');

    $response = $this->actingAs($this->user)->post('/chirps', [
        'message' => 'too big',
        'attachment' => $file,
    ]);

    $response->assertSessionHasErrors('attachment');
    expect(Chirp::where('message', 'too big')->exists())->toBeFalse();
});
