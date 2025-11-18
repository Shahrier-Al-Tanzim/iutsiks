@props(['disabled' => false, 'error' => false])

@php
$classes = 'siks-input';
if ($error) {
    $classes .= ' border-red-300 focus:border-red-500 focus:ring-red-500';
}
@endphp

<input @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
