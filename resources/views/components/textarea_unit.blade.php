@props([
    'name',
    'label' => null,
])

<div class="w-full py-2 text-lg">

    @if($label)
        <label for="{{ $name }}" class="block text-sm text-gray-800">
            {{ $label }}
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes }}
        class="w-full mt-2 h-100 resize-none rounded-lg bg-white text-gray-800
               border border-gray-300 px-3 py-2
               focus:outline-none focus:ring-2
               focus:ring-(--color-500)"
    >{{ $attributes->hasAny(['wire:model','wire:model.live','wire:model.lazy','wire:model.defer'])
        ? ''
        : old($name, $slot) }}</textarea>

    @error($name)
        <p class="text-red-600 text-base pl-2 mt-1">
            {{ $message }}
        </p>
    @enderror

</div>