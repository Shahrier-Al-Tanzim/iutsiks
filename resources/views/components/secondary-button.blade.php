@props(['variant' => 'outline', 'size' => 'md'])

@php
$baseClasses = 'siks-btn-base';
$variantClasses = match($variant) {
    'primary' => 'siks-btn-primary',
    'secondary' => 'siks-btn-secondary',
    'outline' => 'siks-btn-outline',
    'ghost' => 'siks-btn-ghost',
    default => 'siks-btn-outline'
};
$sizeClasses = match($size) {
    'sm' => 'px-3 py-1.5 text-xs',
    'md' => 'px-4 py-2 text-sm',
    'lg' => 'px-6 py-3 text-base',
    default => 'px-4 py-2 text-sm'
};
@endphp

<button {{ $attributes->merge(['type' => 'button', 'class' => $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses]) }}>
    {{ $slot }}
</button>
