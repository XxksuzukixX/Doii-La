@props([
    'iconPath' => null,
    'size' => 'w-20 h-20',
])

<div class="shrink-0">
    @if($iconPath && Storage::disk('public')->exists($iconPath))
        <img
            src="{{ asset('storage/' . $iconPath) }}"
            alt="ユーザーアイコン"
            class="{{ $size }} rounded-full object-cover"
        >
    @else
        <div
            class="{{ $size }} rounded-full
                   bg-(--color-200) flex items-center justify-center
                   text-gray-800 text-sm">Doii
        </div>
    @endif
</div>