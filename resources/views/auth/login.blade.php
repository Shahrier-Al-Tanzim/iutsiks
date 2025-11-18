<x-guest-layout>
    <div class="mb-6 text-center">
        <h3 class="siks-heading-3 text-gray-900">Welcome Back</h3>
        <p class="siks-body text-gray-600 mt-2">Sign in to your SIKS account</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Store redirect URL -->
        <input type="hidden" name="redirect_to" value="{{ request()->query('redirect_to', route('dashboard')) }}">

        <!-- Email Address -->
        <x-form-input 
            label="Email Address" 
            name="email" 
            type="email"
            :value="old('email')" 
            required 
            autofocus 
            autocomplete="username"
            :error="$errors->get('email')"
        />

        <!-- Password -->
        <x-form-input 
            label="Password" 
            name="password" 
            type="password"
            required 
            autocomplete="current-password"
            :error="$errors->get('password')"
        />

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" 
                   class="w-4 h-4 text-siks-primary bg-gray-100 border-gray-300 rounded focus:ring-siks-primary focus:ring-2" 
                   name="remember">
            <label for="remember_me" class="ml-2 siks-body text-gray-700">
                Remember me
            </label>
        </div>

        <div class="space-y-4">
            <button type="submit" class="siks-btn-primary w-full">
                Sign In
            </button>

            @if (Route::has('password.request'))
                <div class="text-center">
                    <a href="{{ route('password.request') }}" 
                       class="siks-body text-siks-primary hover:text-green-700 transition-colors">
                        Forgot your password?
                    </a>
                </div>
            @endif

            <div class="text-center">
                <span class="siks-body text-gray-600">Don't have an account? </span>
                <a href="{{ route('register') }}?redirect_to={{ request()->query('redirect_to', url()->previous()) }}" 
                   class="siks-body text-siks-primary hover:text-green-700 font-medium transition-colors">
                    Register here
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
