@props([
    'href',
    'username',
    'message' => "",
    'date' => "",
    'unread' => 0,
])

@php
    $theme = Auth::user()->theme ?? 'indigo';
@endphp

<a href="{{ $href }}"
   class="block  rounded-lg shadow-md p-3 transition
          hover:bg-(--color-50) w-full
          active:bg-(--color-50)
          bg-white border border-(--color-50)/50">

    <div class="flex flex-col gap-1">

        <!-- 1行目：ユーザー名 + 日付 -->
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold truncate">
                {{ $username }}
            </h2>

            @if($date)
                <div class="text-xs text-gray-500 flex flex-col items-end leading-tight">
                    {{ $date ?? ""}}
                </div>
            @endif
        </div>

        <!-- 2行目：メッセージ + 未読バッジ -->
        <div class="flex justify-between items-center gap-4">

            <p class="text-sm text-gray-700 line-clamp-1 flex-1 min-w-0">
                {{ $message }}
            </p>

            @if($unread > 0)
                <span class="bg-(--color-500) text-white text-xs
                             rounded-full px-2 py-0.5 font-semibold shrink-0">
                    {{ $unread }}
                </span>
            @endif

        </div>

    </div>
</a>
