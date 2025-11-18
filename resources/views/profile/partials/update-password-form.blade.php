<section>
    <header>
        <h2 class="siks-heading-3 mb-2">
            {{ __('Update Password') }}
        </h2>

        <p class="siks-body text-gray-600 mb-6">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="siks-label">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="siks-input" autocomplete="current-password" />
            @error('current_password', 'updatePassword')
                <p class="siks-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="siks-label">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" class="siks-input" autocomplete="new-password" />
            @error('password', 'updatePassword')
                <p class="siks-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="siks-label">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="siks-input" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')
                <p class="siks-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="siks-btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="siks-body-small text-green-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
