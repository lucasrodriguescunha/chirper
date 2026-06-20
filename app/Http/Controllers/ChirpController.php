<?php

namespace App\Http\Controllers;

use App\Actions\StoreChirpAttachmentAction;
use App\Http\Requests\ChirpRequest;
use App\Models\Chirp;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChirpController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $chirps = Chirp::with(['user', 'attachments', 'likes', 'comments.user', 'comments.likes'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('home', ['chirps' => $chirps]);
    }

    public function create()
    {
        //
    }

    /**
     * @throws Throwable
     */
    public function store(ChirpRequest $request, StoreChirpAttachmentAction $attachmentAction)
    {
        $validated = $request->validated();
        $file = $request->file('attachment');
        $path = null;

        DB::beginTransaction();

        try {
            $chirp = auth()->user()->chirps()->create([
                'message' => $validated['message'],
            ]);

            if ($file) {
                $path = $attachmentAction->execute($chirp, $file);
            }

            // throw new \Exception('Forced error to test rollback!');

            DB::commit();
        } catch (Throwable) {
            DB::rollBack();

            if ($path) {
                $attachmentAction->delete($path);
            }

            return redirect('/')
                ->with('error', 'There was an error uploading your file.')
                ->withInput();
        }

        return redirect('/')->with('success', 'Your chirp has been posted!');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        return view('components.chirps.edit', compact('chirp'));
    }

    public function update(ChirpRequest $request, Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $chirp->update($request->validated());

        return redirect('/')->with('success', 'Your chirp has been updated!');
    }

    public function destroy(Chirp $chirp)
    {
        $this->authorize('update', $chirp);

        $chirp->delete();

        return redirect('/')->with('success', 'Your chirp has been deleted!');
    }
}
