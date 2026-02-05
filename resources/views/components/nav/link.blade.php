@props(['route'])

<a
    href="{{ route($route) }}"
    {{ $attributes->merge(['class' => 'block hover:text-gray-300']) }}
>
    {{ $slot }}
</a>
