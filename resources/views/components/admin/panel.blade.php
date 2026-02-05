@props([
    'title' => '管理情報',
])

<div class=" bg-(--color-50) border-b border-(--color-100)">
    <div class="flex items-start justify-between gap-6 p-4 px-6">

        {{-- 左：管理情報 --}}
        <div class="flex flex-col gap-3">
            <div class="flex items-center gap-3">
                <span class="text-md font-semibold text-gray-800">
                    {{ $title }}
                </span>

                @isset($status)
                    {{ $status }}
                @endisset
            </div>

            @isset($meta)
                <div class="text-xs text-gray-600 space-y-1 pl-1">
                    {{ $meta }}
                </div>
            @endisset
        </div>

        {{-- 右：操作 --}}
        @isset($actions)
            <div class="shrink-0 flex items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
