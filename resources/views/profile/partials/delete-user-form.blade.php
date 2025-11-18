<section class="space-y-6">
    <header>
        <h2 class="siks-heading-3 mb-2 text-red-600">
            {{ __('Delete Account') }}
        </h2>

        <p class="siks-body text-gray-600 mb-6">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500"
    >{{ __('Delete Account') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="siks-heading-3 mb-4 text-red-600">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="siks-body text-gray-600 mb-6">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mb-6">
                <label for="password" class="sr-only">{{ __('Password') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="siks-input w-3/4"
                    placeholder="{{ __('Password') }}"
                />
                @error('password', 'userDeletion')
                    <p class="siks-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="siks-btn-ghost">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="siks-btn-base bg-red-600 text-white hover:bg-red-700 focus:ring-red-500">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
