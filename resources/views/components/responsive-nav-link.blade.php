@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-siks-primary text-start text-base font-medium text-siks-primary bg-siks-primary/10 focus:outline-none focus:text-siks-dark focus:bg-siks-primary/20 focus:border-siks-dark transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-siks-primary hover:bg-siks-primary/5 hover:border-siks-primary/30 focus:outline-none focus:text-siks-primary focus:bg-siks-primary/5 focus:border-siks-primary/30 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
