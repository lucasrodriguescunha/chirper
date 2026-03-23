<?php

namespace App\Actions;

use App\Models\Chirp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreChirpAttachmentAction
{
    /**
     * Stores the file and creates the attachment record in the database.
     */
    public function execute(Chirp $chirp, UploadedFile $file): string|false
    {
        $path = $file->store('attachments', 'public');
        $extension = $file->getClientOriginalExtension();

        $chirp->attachments()->create([
            'path' => $path,
            'type' => $extension,
        ]);

        return $path;
    }

    /**
     * Removes the file from disk (used in rollback).
     */
    public function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
    }
}
