<?php

use App\Models\Chirp;
use App\Models\User;
use App\Policies\ChirpPolicy;

it('allows owner to update and delete their chirp', function () {
    $user = User::factory()->create();
    $chirp = Chirp::factory()->for($user)->create();
    $policy = new ChirpPolicy;

    expect($policy->update($user, $chirp))->toBeTrue();
    expect($policy->delete($user, $chirp))->toBeTrue();
});

it('blocks non-owners from updating or deleting a chirp', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $chirp = Chirp::factory()->for($owner)->create();
    $policy = new ChirpPolicy;

    expect($policy->update($stranger, $chirp))->toBeFalse();
    expect($policy->delete($stranger, $chirp))->toBeFalse();
});

it('denies viewAny, view, create, restore, and forceDelete by default', function () {
    $policy = new ChirpPolicy;

    expect($policy->viewAny())->toBeFalse();
    expect($policy->view())->toBeFalse();
    expect($policy->create())->toBeFalse();
    expect($policy->restore())->toBeFalse();
    expect($policy->forceDelete())->toBeFalse();
});
