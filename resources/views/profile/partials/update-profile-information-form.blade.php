<section>
    <header>
        <h2 class="siks-heading-3 mb-2">
            {{ __('Profile Information') }}
        </h2>

        <p class="siks-body text-gray-600 mb-6">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="siks-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" class="siks-input" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <p class="siks-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="siks-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="siks-input" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')
                <p class="siks-error">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="siks-body-small text-yellow-800 mb-2">
                        {{ __('Your email address is unverified.') }}
                    </p>
                    <button form="send-verification" class="siks-link text-yellow-700 hover:text-yellow-900">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 siks-body-small text-green-700 font-medium">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="siks-btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
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
