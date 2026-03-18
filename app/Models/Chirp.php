<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chirp extends Model
{
    protected $fillable = [
        'message'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
     * Returns all likes associated with this chirp.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(\App\Models\Like::class);
    }

    /*
    * Returns the user's response:
    * 'liked', 'disliked', or null
    */
    public function userReaction(User $user): ?string
    {
        return $this->likes()
            ->where('user_id', $user->id)
            ->value('type');
    }
}
