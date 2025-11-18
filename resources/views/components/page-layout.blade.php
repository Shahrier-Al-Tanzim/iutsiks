@props([
    'title' => 'SIKS - Islamic Society of IUT'
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-50" x-data="{ open: false }">
            <div class="siks-container">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <a href="{{ route('home') }}" class="flex items-center">
                                <div class="w-8 h-8 bg-siks-primary rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white font-bold text-sm">S</span>
                                </div>
                                <span class="text-xl font-bold text-siks-primary">SIKS</span>
                            </a>
                        </div>
                        
                        <!-- Navigation Links -->
                        <div class="hidden md:flex space-x-8 ml-10">
                            <a href="{{ route('home') }}" class="text-gray-500 hover:text-siks-primary px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'text-siks-primary border-b-2 border-siks-primary' : '' }}">
                                Home
                            </a>
                            <a href="{{ route('events.index') }}" class="text-gray-500 hover:text-siks-primary px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('events.*') ? 'text-siks-primary border-b-2 border-siks-primary' : '' }}">
                                Events
                            </a>
                            <a href="{{ route('blogs.index') }}" class="text-gray-500 hover:text-siks-primary px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('blogs.*') ? 'text-siks-primary border-b-2 border-siks-primary' : '' }}">
                                Blogs
                            </a>
                            <a href="{{ route('prayer-times.index') }}" class="text-gray-500 hover:text-siks-primary px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('prayer-times.*') ? 'text-siks-primary border-b-2 border-siks-primary' : '' }}">
                                Prayer Times
                            </a>
                            <a href="{{ route('gallery.index') }}" class="text-gray-500 hover:text-siks-primary px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('gallery.*') ? 'text-siks-primary border-b-2 border-siks-primary' : '' }}">
                                Gallery
                            </a>
                            @auth
                                <a href="{{ route('fests.index') }}" class="text-gray-500 hover:text-siks-primary px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('fests.*') ? 'text-siks-primary border-b-2 border-siks-primary' : '' }}">
                                    Fests
                                </a>
                            @endauth
                        </div>
                    </div>
                    
                    <!-- Auth Links -->
                    <div class="hidden md:flex items-center space-x-4">
                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center text-gray-500 hover:text-siks-primary px-3 py-2 text-sm font-medium transition-colors">
                                    {{ Auth::user()->name }}
                                    <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[60]">
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</a>
                                    <a href="{{ route('registrations.history') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Registrations</a>
                                    
                                    @if(Auth::user()->isSuperAdmin())
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Dashboard</a>
                                        <a href="{{ route('admin.user-management') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">User Management</a>
                                    @endif

                                    @can('manage-prayer-times')
                                        <a href="{{ route('admin.prayer-times.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Prayer Times</a>
                                    @endcan

                                    @can('manage-events')
                                        <a href="{{ route('admin.registrations.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Manage Registrations</a>
                                    @endcan

                                    @can('create', App\Models\GalleryImage::class)
                                        <a href="{{ route('gallery.create') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Upload Images</a>
                                    @endcan
                                    
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="siks-btn-ghost">Login</a>
                            <a href="{{ route('register') }}" class="siks-btn-primary">Register</a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center">
                        <button @click="open = !open" class="text-gray-500 hover:text-siks-primary p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div x-show="open" x-transition class="md:hidden relative z-[55]">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t border-gray-200">
                    <a href="{{ route('home') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50 {{ request()->routeIs('home') ? 'text-siks-primary bg-green-50' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('events.index') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50 {{ request()->routeIs('events.*') ? 'text-siks-primary bg-green-50' : '' }}">
                        Events
                    </a>
                    <a href="{{ route('blogs.index') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50 {{ request()->routeIs('blogs.*') ? 'text-siks-primary bg-green-50' : '' }}">
                        Blogs
                    </a>
                    <a href="{{ route('prayer-times.index') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50 {{ request()->routeIs('prayer-times.*') ? 'text-siks-primary bg-green-50' : '' }}">
                        Prayer Times
                    </a>
                    <a href="{{ route('gallery.index') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50 {{ request()->routeIs('gallery.*') ? 'text-siks-primary bg-green-50' : '' }}">
                        Gallery
                    </a>
                    @auth
                        <a href="{{ route('fests.index') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50 {{ request()->routeIs('fests.*') ? 'text-siks-primary bg-green-50' : '' }}">
                            Fests
                        </a>
                        <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50">
                            Dashboard
                        </a>
                    @else
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-gray-500 hover:text-siks-primary hover:bg-gray-50">
                                Login
                            </a>
                            <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium text-siks-primary hover:bg-green-50">
                                Register
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <x-footer />
    </div>

    @stack('scripts')
</body>
</html>