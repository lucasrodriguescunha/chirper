<?php

namespace App\Models;

use App\Http\Controllers\Like\Like;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->$hasMany(Like::class);
    }

    /*
     * Check if a specific user has already liked this chirp.
     */
    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
