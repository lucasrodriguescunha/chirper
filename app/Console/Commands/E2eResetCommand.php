<?php

namespace App\Console\Commands;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class E2eResetCommand extends Command
{
    protected $signature = 'e2e:reset';

    protected $description = 'Reset database with deterministic users for Playwright E2E tests';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to run in production.');
            return self::FAILURE;
        }

        $this->call('migrate:fresh', ['--force' => true]);

        $alice = new User([
            'name' => 'Alice E2E',
            'username' => 'alice',
            'email' => 'alice@e2e.test',
            'password' => Hash::make('password123'),
            'bio' => 'Hi, I am Alice.',
        ]);
        $alice->email_verified_at = now();
        $alice->save();

        $bob = new User([
            'name' => 'Bob E2E',
            'username' => 'bob',
            'email' => 'bob@e2e.test',
            'password' => Hash::make('password123'),
            'bio' => 'Hi, I am Bob.',
        ]);
        $bob->email_verified_at = now();
        $bob->save();

        $bobChirp = new Chirp(['message' => 'Hello world from Bob — testing chirps.']);
        $bobChirp->user_id = $bob->id;
        $bobChirp->save();

        $aliceChirp = new Chirp(['message' => 'Alice first chirp for search test.']);
        $aliceChirp->user_id = $alice->id;
        $aliceChirp->save();

        $this->info('E2E DB reset: alice@e2e.test / bob@e2e.test (password: password123)');

        return self::SUCCESS;
    }
}
