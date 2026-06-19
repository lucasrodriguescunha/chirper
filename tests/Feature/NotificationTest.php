<?php

use App\Models\Chirp;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use App\Notifications\NewFollowerNotification;
use App\Notifications\NewLikeNotification;
use Illuminate\Support\Facades\Notification;

it('notifies user when followed', function () {
    Notification::fake();

    $followee = User::factory()->create();
    $follower = User::factory()->create();

    $this->actingAs($follower)->post(route('follows.store', $followee));

    Notification::assertSentTo($followee, NewFollowerNotification::class);
});

it('does not notify on repeated follow attempt', function () {
    Notification::fake();

    $followee = User::factory()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($followee);

    $this->actingAs($follower)->post(route('follows.store', $followee));

    Notification::assertNothingSent();
});

it('notifies chirp owner when someone comments', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $chirp = Chirp::factory()->for($owner)->create();

    $this->actingAs($commenter)->post("/chirps/{$chirp->id}/comments", ['body' => 'cool']);

    Notification::assertSentTo($owner, NewCommentNotification::class);
});

it('does not notify owner when they comment on their own chirp', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $chirp = Chirp::factory()->for($owner)->create();

    $this->actingAs($owner)->post("/chirps/{$chirp->id}/comments", ['body' => 'self talk']);

    Notification::assertNothingSent();
});

it('notifies chirp owner when someone likes their chirp', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $liker = User::factory()->create();
    $chirp = Chirp::factory()->for($owner)->create();

    $this->actingAs($liker)
        ->post("/chirps/{$chirp->id}/reaction", ['type' => 'like']);

    Notification::assertSentTo($owner, NewLikeNotification::class);
});

it('does not notify owner when they react to their own chirp', function () {
    Notification::fake();

    $owner = User::factory()->create();
    $chirp = Chirp::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->post("/chirps/{$chirp->id}/reaction", ['type' => 'like']);

    Notification::assertNothingSent();
});

it('lists notifications and marks them as read on view', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();

    $user->notify(new NewFollowerNotification($follower));

    expect($user->unreadNotifications()->count())->toBe(1);

    $response = $this->actingAs($user)->get(route('notifications.index'));

    $response->assertOk();
    $response->assertSee($follower->name);
    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

it('clears all notifications for the user', function () {
    $user = User::factory()->create();
    $follower = User::factory()->create();
    $user->notify(new NewFollowerNotification($follower));

    $this->actingAs($user)->delete(route('notifications.clear'));

    expect($user->fresh()->notifications()->count())->toBe(0);
});
