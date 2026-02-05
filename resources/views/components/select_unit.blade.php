@props([
    'name',
    'label' => null,
    'options' => [],
])

<div class="w-full py-2 text-lg">
    @if($label)
        <label for="{{ $name }}" class="block text-sm text-gray-800">
            {{ $label }}
        </label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full mt-2 h-[35px] rounded-lg border border-gray-300 bg-white text-gray-800
                        px-2 focus:outline-none focus:ring-2 focus:ring-(--color-500)'
        ]) }}
    >
        <option value="">-- 選択してください --</option>

        @foreach($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>

    @error($name)
        <span class="text-red-600 text-base pl-2">
            {{ $message }}
        </span>
    @enderror
</div>
