<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIKS') }} - Islamic Society of IUT</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen bg-gradient-to-br from-siks-primary to-green-700 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <div class="flex justify-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mr-3">
                            <span class="text-siks-primary font-bold text-lg">S</span>
                        </div>
                        <span class="text-2xl font-bold text-white">SIKS</span>
                    </a>
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold text-white">
                    Islamic Society of IUT
                </h2>
            </div>

            <!-- Form Container -->
            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <div class="siks-card p-8">
                    {{ $slot }}
                </div>
                
                <!-- Back to Home -->
                <div class="mt-6 text-center">
                    <a href="{{ route('home') }}" class="text-white hover:text-gray-200 text-sm font-medium transition-colors">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
