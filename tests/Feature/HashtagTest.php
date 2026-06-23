<?php

use App\Models\Chirp;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'author']);
});

it('extracts unique lowercased hashtags from chirp message', function () {
    $chirp = Chirp::factory()->for($this->user)->make([
        'message' => 'love #Laravel and #laravel — also #PHP #x (too short) and #valid_tag',
    ]);

    expect($chirp->extractHashtags())->toEqualCanonicalizing(['laravel', 'php', 'valid_tag']);
});

it('syncs tags from message on chirp create', function () {
    $this->actingAs($this->user)->post('/chirps', [
        'message' => 'hello #world #laravel',
    ])->assertRedirect('/');

    $chirp = Chirp::firstWhere('message', 'hello #world #laravel');

    expect($chirp->tags->pluck('name')->all())->toEqualCanonicalizing(['world', 'laravel']);
    expect(Tag::count())->toBe(2);
});

it('reuses existing tags when multiple chirps share a hashtag', function () {
    $this->actingAs($this->user)->post('/chirps', ['message' => 'first #shared']);
    $this->actingAs($this->user)->post('/chirps', ['message' => 'second #shared']);

    expect(Tag::where('name', 'shared')->count())->toBe(1);
    expect(Tag::where('name', 'shared')->first()->chirps()->count())->toBe(2);
});

it('updates pivot when chirp is edited', function () {
    $chirp = Chirp::factory()->for($this->user)->create(['message' => 'old #one']);
    $chirp->syncTagsFromMessage();

    $this->actingAs($this->user)->put("/chirps/{$chirp->id}", [
        'message' => 'new #two #three',
    ])->assertRedirect('/');

    expect($chirp->fresh()->tags->pluck('name')->all())
        ->toEqualCanonicalizing(['two', 'three']);
});

it('shows tag page with matching chirps only', function () {
    $other = User::factory()->create();
    $tagged = Chirp::factory()->for($this->user)->create(['message' => 'celebrating #release']);
    $tagged->syncTagsFromMessage();

    $unrelated = Chirp::factory()->for($other)->create(['message' => 'no hashtag here']);
    $unrelated->syncTagsFromMessage();

    $response = $this->actingAs($this->user)->get('/tag/release');

    $response->assertOk();
    $response->assertSee('celebrating');
    $response->assertDontSee('no hashtag here');
});

it('returns 404 for unknown tag slug', function () {
    $response = $this->actingAs($this->user)->get('/tag/ghost');

    $response->assertNotFound();
});

it('returns 404 for invalid tag slug format', function () {
    $response = $this->actingAs($this->user)->get('/tag/has-dash');

    $response->assertNotFound();
});

it('requires auth to view a tag page', function () {
    $response = $this->get('/tag/anything');

    $response->assertRedirect('/login');
});

it('renders hashtags as links in chirp body', function () {
    Chirp::factory()->for($this->user)->create(['message' => 'big news #laravel']);

    $response = $this->actingAs($this->user)->get('/');

    $response->assertOk();
    $response->assertSee('href="'.route('tags.show', 'laravel').'"', false);
    $response->assertSee('#laravel', false);
});

it('escapes HTML when rendering hashtags', function () {
    Chirp::factory()->for($this->user)->create([
        'message' => '<script>alert(1)</script> #safe',
    ]);

    $response = $this->actingAs($this->user)->get('/');

    $response->assertOk();
    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertSee('#safe', false);
});
