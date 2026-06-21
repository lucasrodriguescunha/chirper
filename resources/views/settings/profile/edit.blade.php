<x-layout>
    <x-slot:title>
        Edit Profile
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl sm:text-3xl font-bold mt-6 sm:mt-8">Edit Profile</h1>

        <div class="card bg-base-100 mt-6 sm:mt-8">
            <div class="card-body">
                <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 mb-6">
                        <div class="avatar shrink-0">
                            <div class="size-14 sm:size-16 rounded-full">
                                <img src="{{ $user->avatarUrl() }}" alt="Your avatar" class="object-cover w-full h-full"/>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1">
                            <span class="text-sm text-base-content/60">Following: {{ $user->following()->count() }}</span>
                            <span class="text-sm text-base-content/60">Followers: {{ $user->followers()->count() }}</span>
                        </div>
                    </div>

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
                            <x-alert type="error" size="sm" soft class="mt-2">{{ $message }}</x-alert>
                        @enderror
                    </div>

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
                            <x-alert type="error" size="sm" soft class="mt-2">{{ $message }}</x-alert>
                        @enderror
                    </div>

                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Bio</span>
                        </label>
                        <textarea
                            name="bio"
                            rows="3"
                            maxlength="255"
                            data-counter
                            class="textarea textarea-bordered w-full @error('bio') textarea-error @enderror"
                            placeholder="Tell people about yourself"
                        >{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <x-alert type="error" size="sm" soft class="mt-2">{{ $message }}</x-alert>
                        @enderror
                    </div>

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
                        <a href="/" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>

                <div class="divider mt-6"></div>
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="font-semibold">Two-Factor Authentication</h2>
                        <p class="text-sm text-base-content/60">
                            {{ $user->hasTwoFactorEnabled() ? 'Enabled' : 'Not enabled' }}
                        </p>
                    </div>
                    <a href="{{ route('settings.two-factor.index') }}" class="btn btn-outline btn-sm">Manage</a>
                </div>
            </div>
        </div>
    </div>
</x-layout>
