<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique()->after('name');
        });

        $taken = [];

        DB::table('users')->orderBy('id')->each(function ($user) use (&$taken) {
            $base = $this->slug($user->name) ?: 'user'.$user->id;
            $candidate = $base;
            $suffix = 2;

            while (in_array($candidate, $taken, true) || DB::table('users')->where('username', $candidate)->exists()) {
                $candidate = $base.'_'.$suffix;
                $suffix++;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
            $taken[] = $candidate;
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }

    private function slug(?string $name): string
    {
        $slug = Str::of((string) $name)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9_]+/', '_')
            ->trim('_')
            ->limit(30, '')
            ->toString();

        return $slug;
    }
};
