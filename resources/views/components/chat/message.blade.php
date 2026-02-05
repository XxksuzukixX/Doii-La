@props([
    'type',   // me | other
    'time',
    'status' => null,
])

@php
    $isMe = $type === 'me';
@endphp

<div class="flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} gap-2 items-end">
    <div
        class="max-w-[80%] rounded-2xl px-4 py-2 text-md wrap-break-word break-all
               {{ $isMe
                    ? "bg-(--color-500) text-white"
                    : "bg-(--color-50) text-gray-800 border border-(--color-300)"
               }}"
    >
        {{-- {!! nl2br(e($slot)) !!} --}}
        {{$slot}}
    </div>

    <div class="text-xs text-gray-500 whitespace-nowrap">
        @if($isMe && $status)
            <div>{{ $status }}</div>
        @endif
        <div>{{ $time }}</div>
    </div>
</div>
