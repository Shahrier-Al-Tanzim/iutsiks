<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="siks-heading-3 text-gray-900">Reset Password</h3>
        <p class="siks-body text-gray-600 mt-2">
            Forgot your password? No problem. Just enter your email address and we'll send you a password reset link.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <x-form-input 
            label="Email Address" 
            name="email" 
            type="email"
            :value="old('email')" 
            required 
            autofocus
            :error="$errors->get('email')"
        />

        <div class="space-y-4">
            <button type="submit" class="siks-btn-primary w-full">
                Send Reset Link
            </button>

            <div class="text-center">
                <a href="{{ route('login') }}" 
                   class="siks-body text-siks-primary hover:text-green-700 transition-colors">
                    ← Back to Sign In
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
