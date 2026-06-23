<?php

use App\Models\Chirp;
use App\Models\User;
use App\Notifications\NewMentionNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->author = User::factory()->create(['username' => 'author']);
});

it('parses unique mentioned usernames from chirp message', function () {
    $chirp = Chirp::factory()->for($this->author)->make([
        'message' => 'hi @alice and @Bob, also @alice again — not email@x.com or @al (too short)',
    ]);

    expect($chirp->mentionedUsernames())->toEqualCanonicalizing(['alice', 'bob']);
});

it('returns mentioned users matching existing usernames only', function () {
    User::factory()->create(['username' => 'alice']);
    User::factory()->create(['username' => 'bob']);

    $chirp = Chirp::factory()->for($this->author)->create([
        'message' => '@alice @ghost @bob',
    ]);

    $usernames = $chirp->mentionedUsers()->pluck('username')->all();

    expect($usernames)->toEqualCanonicalizing(['alice', 'bob']);
});

it('notifies mentioned users when a chirp is created', function () {
    Notification::fake();

    $alice = User::factory()->create(['username' => 'alice']);
    $bob = User::factory()->create(['username' => 'bob']);

    $this->actingAs($this->author)->post('/chirps', [
        'message' => 'hey @alice and @bob',
    ])->assertRedirect('/');

    Notification::assertSentTo($alice, NewMentionNotification::class);
    Notification::assertSentTo($bob, NewMentionNotification::class);
});

it('does not notify the author when they mention themselves', function () {
    Notification::fake();

    $this->actingAs($this->author)->post('/chirps', [
        'message' => 'talking to myself @author',
    ])->assertRedirect('/');

    Notification::assertNothingSent();
});

it('does not notify unknown usernames', function () {
    Notification::fake();

    $this->actingAs($this->author)->post('/chirps', [
        'message' => 'hello @ghost',
    ])->assertRedirect('/');

    Notification::assertNothingSent();
});

it('only notifies newly mentioned users on update', function () {
    Notification::fake();

    $alice = User::factory()->create(['username' => 'alice']);
    $bob = User::factory()->create(['username' => 'bob']);

    $chirp = Chirp::factory()->for($this->author)->create(['message' => 'hi @alice']);

    $this->actingAs($this->author)->put("/chirps/{$chirp->id}", [
        'message' => 'hi @alice and @bob',
    ])->assertRedirect('/');

    Notification::assertNothingSentTo($alice);
    Notification::assertSentTo($bob, NewMentionNotification::class);
});

it('renders mentions as links to user profiles', function () {
    $alice = User::factory()->create(['username' => 'alice']);

    Chirp::factory()->for($this->author)->create([
        'message' => 'shout out to @alice and @ghost',
    ]);

    $response = $this->actingAs($this->author)->get('/');

    $response->assertOk();
    $response->assertSee('href="'.route('users.show', $alice).'"', false);
    $response->assertSee('@alice', false);
    $response->assertSee('@ghost', false);
});

it('escapes HTML in chirp messages while linkifying mentions', function () {
    User::factory()->create(['username' => 'alice']);

    Chirp::factory()->for($this->author)->create([
        'message' => '<script>alert(1)</script> @alice',
    ]);

    $response = $this->actingAs($this->author)->get('/');

    $response->assertOk();
    $response->assertDontSee('<script>alert(1)</script>', false);
    $response->assertSee('&lt;script&gt;', false);
});
