@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
])

@php
    $hasWireModel = $attributes->hasAny([
        'wire:model',
        'wire:model.live',
        'wire:model.lazy',
        'wire:model.defer',
    ]);
@endphp

<div class="w-full py-2 text-lg">

    @if($label)
        <label for="{{ $name }}" class="block text-sm text-gray-800">
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"

        @unless($hasWireModel)
            value="{{ old($name, $value) }}"
        @endunless

        {{ $attributes->merge([
            'class' => 'w-full mt-2 h-[35px] rounded-lg border border-gray-300 bg-white text-gray-800
                        px-3 focus:outline-none focus:ring-2 
                        focus:ring-(--color-500)'
        ]) }}
    >

    @error($name)
        <span class="text-red-600 text-base pl-2">
            {{ $message }}
        </span>
    @enderror

</div>