<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name'];

    public function chirps(): BelongsToMany
    {
        return $this->belongsToMany(Chirp::class, 'chirp_tag')->withTimestamps();
    }

    public function getRouteKeyName(): string
    {
        return 'name';
    }
}
