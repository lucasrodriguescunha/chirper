<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chirp extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'file_path',
        'file_type',
        'path',
        'type'
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
}
