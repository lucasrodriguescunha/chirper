<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChirpAttachment extends Model
{
    protected $fillable = [
        'chirp_id',
        'path',
        'type',
    ];

    public function chirp(): BelongsTo
    {
        return $this->belongsTo(Chirp::class);
    }

    /*
     * Returns the public URL of the file (for images, PDFs, etc.).
     */
    public function url(): ?string
    {
        return $this->path
            ? Storage::url($this->path)
            : null;
    }
}
