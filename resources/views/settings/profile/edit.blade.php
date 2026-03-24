<x-layout>
    <x-slot:title>
        Edit Profile
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mt-8">Edit Profile</h1>

        <div class="card bg-base-100 mt-8">
            <div class="card-body">
                <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    {{-- Avatar atual --}}
                    <div class="flex items-center gap-4 mb-6">
                        <div class="avatar">
                            <div class="size-16 rounded-full">
                                <img src="{{ $user->avatarUrl() }}" alt="Your avatar" />
                            </div>
                        </div>
                        <span class="text-sm text-base-content/60">Current avatar</span>
                    </div>

                    {{-- Upload de avatar --}}
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Avatar</span>
                        </label>
                        <input
                            type="file"
                            name="avatar"
                            accept="image/*"
                            class="file-input file-input-bordered w-full @error('avatar') file-input-error @enderror"
                        />
                        @error('avatar')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                        @enderror
                    </div>

                    {{-- Nome --}}
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="input input-bordered w-full @error('name') input-error @enderror"
                            maxlength="255"
                        />
                        @error('name')
                        <div class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </div>
                        @enderror
                    </div>

                    {{-- Email (somente leitura) --}}
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input
                            type="email"
                            value="{{ $user->email }}"
                            class="input input-bordered w-full opacity-60"
                            disabled
                        />
                    </div>

                    <div class="card-actions justify-between mt-4">
                        <a href="/" class="btn btn-ghost btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
