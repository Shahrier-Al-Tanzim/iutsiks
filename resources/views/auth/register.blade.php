<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="siks-heading-3 text-gray-900">Join SIKS</h3>
        <p class="siks-body text-gray-600 mt-2">Create your account to get started</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Store redirect URL -->
        <input type="hidden" name="redirect_to" value="{{ request()->query('redirect_to', route('dashboard')) }}">

        <!-- Name -->
        <x-form-input 
            label="Full Name" 
            name="name" 
            type="text"
            :value="old('name')" 
            required 
            autofocus 
            autocomplete="name"
            :error="$errors->get('name')"
        />

        <!-- Email Address -->
        <x-form-input 
            label="Email Address" 
            name="email" 
            type="email"
            :value="old('email')" 
            required 
            autocomplete="username"
            :error="$errors->get('email')"
        />

        <!-- Password -->
        <x-form-input 
            label="Password" 
            name="password" 
            type="password"
            required 
            autocomplete="new-password"
            :error="$errors->get('password')"
            help="Password must be at least 8 characters long"
        />

        <!-- Confirm Password -->
        <x-form-input 
            label="Confirm Password" 
            name="password_confirmation" 
            type="password"
            required 
            autocomplete="new-password"
            :error="$errors->get('password_confirmation')"
        />

        <div class="space-y-4">
            <button type="submit" class="siks-btn-primary w-full">
                Create Account
            </button>

            <div class="text-center">
                <span class="siks-body text-gray-600">Already have an account? </span>
                <a href="{{ route('login') }}?redirect_to={{ request()->query('redirect_to', url()->previous()) }}" 
                   class="siks-body text-siks-primary hover:text-green-700 font-medium transition-colors">
                    Sign in here
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
