@props([
    'title' => null,
    'subtitle' => null,
    'background' => 'white', // white, gray, primary
    'padding' => 'medium' // small, medium, large
])

@php
    $backgroundClasses = [
        'white' => 'bg-white',
        'gray' => 'bg-gray-50',
        'primary' => 'bg-siks-primary'
    ];
    
    $paddingClasses = [
        'small' => 'py-8',
        'medium' => 'py-12 md:py-16',
        'large' => 'py-16 md:py-20 lg:py-24'
    ];
@endphp

<section class="{{ $backgroundClasses[$background] }} {{ $paddingClasses[$padding] }}">
    <div class="siks-container">
        @if($title || $subtitle)
            <div class="text-center mb-12">
                @if($title)
                    <h2 class="siks-heading-2 {{ $background === 'primary' ? 'text-white' : '' }} mb-4">{{ $title }}</h2>
                @endif
                @if($subtitle)
                    <p class="siks-body {{ $background === 'primary' ? 'text-white/90' : 'text-gray-600' }} max-w-3xl mx-auto">{{ $subtitle }}</p>
                @endif
            </div>
        @endif
        
        {{ $slot }}
    </div>
</section>