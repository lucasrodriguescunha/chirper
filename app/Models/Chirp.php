<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chirp extends Model
{
    use HasFactory;

    public const MENTION_REGEX = '/(?<![\w@])@([A-Za-z0-9_]{3,30})\b/';

    public const HASHTAG_REGEX = '/(?<![\w&])#([A-Za-z0-9_]{2,50})\b/';

    protected $fillable = [
        'message',
        'file_path',
        'file_type',
        'path',
        'type'
    ];

    public function mentionedUsernames(): array
    {
        preg_match_all(self::MENTION_REGEX, (string) $this->message, $matches);

        return array_values(array_unique(array_map('strtolower', $matches[1] ?? [])));
    }

    public function mentionedUsers(): Collection
    {
        $usernames = $this->mentionedUsernames();

        if (empty($usernames)) {
            return User::query()->whereRaw('1 = 0')->get();
        }

        return User::query()
            ->whereIn('username', $usernames)
            ->get();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
     * Returns all likes associated with this chirp.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function attachments(): HasOne
    {
        return $this->hasOne(ChirpAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'chirp_tag')->withTimestamps();
    }

    public function extractHashtags(): array
    {
        preg_match_all(self::HASHTAG_REGEX, (string) $this->message, $matches);

        return array_values(array_unique(array_map('strtolower', $matches[1] ?? [])));
    }

    public function syncTagsFromMessage(): void
    {
        $names = $this->extractHashtags();

        if (empty($names)) {
            $this->tags()->sync([]);
            return;
        }

        $ids = collect($names)
            ->map(fn ($name) => Tag::firstOrCreate(['name' => $name])->id)
            ->all();

        $this->tags()->sync($ids);
    }
}
