@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null
])

<div class="space-y-1">
    @if($label)
        <x-input-label for="{{ $name }}" :value="$label" :required="$required" />
    @endif
    
    @if($type === 'textarea')
        <textarea 
            id="{{ $name }}" 
            name="{{ $name }}" 
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            {{ $attributes->merge(['class' => 'siks-textarea' . ($error ? ' border-red-300 focus:border-red-500 focus:ring-red-500' : '')]) }}
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select 
            id="{{ $name }}" 
            name="{{ $name }}"
            @disabled($disabled)
            {{ $attributes->merge(['class' => 'siks-select' . ($error ? ' border-red-300 focus:border-red-500 focus:ring-red-500' : '')]) }}
        >
            {{ $slot }}
        </select>
    @else
        <input 
            type="{{ $type }}" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @disabled($disabled)
            {{ $attributes->merge(['class' => 'siks-input' . ($error ? ' border-red-300 focus:border-red-500 focus:ring-red-500' : '')]) }}
        />
    @endif
    
    @if($help)
        <p class="text-sm text-gray-500">{{ $help }}</p>
    @endif
    
    @if($error)
        <x-input-error :messages="$error" />
    @endif
</div>