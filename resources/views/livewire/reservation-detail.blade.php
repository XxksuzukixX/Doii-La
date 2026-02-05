@php
    $user = Auth::user();
    $isAdmin = $user->isAdmin();

    $statusLabels = [
        'published'    => '公開中',
        'unpublished'  => '非公開',
        'draft'        => '公開前',
        'expired'      => '受付終了',
        'closed'       => '公開終了',
    ];

    $statusClasses = [
        'published'   => 'bg-blue-500 text-white',
        'unpublished' => 'bg-red-500 text-white',
        'draft'       => 'bg-yellow-500 text-white',
        'expired'     => 'bg-gray-500 text-white',
        'closed'      => 'bg-gray-800 text-white',
    ];
@endphp

<div>
    {{-- 管理ステータス --}}
    @if($isAdmin)
        <x-admin.panel title="公開状態">

            {{-- ステータス --}}
            <x-slot:status>
                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusClasses[$reservation->status] ?? '' }}">
                    {{ $statusLabels[$reservation->status] ?? '' }}
                </span>
            </x-slot:status>

            {{-- メタ情報 --}}
            <x-slot:meta>
                <div class="grid grid-cols-[8em_1fr]">
                    <span>公開日時：</span>
                    <span class="font-medium">
                        {{ $reservation->publish_at?->format('Y/m/d H:i') ?? '未設定' }}
                    </span>
                </div>

                <div class="grid grid-cols-[8em_1fr]">
                    <span>受付終了日時：</span>
                    <span class="font-medium">
                        {{ $reservation->deadline_at?->format('Y/m/d H:i') ?? '未設定' }}
                    </span>
                </div>

                <div class="grid grid-cols-[8em_1fr]">
                    <span>公開終了日時：</span>
                    <span class="font-medium">
                        {{ $reservation->close_at?->format('Y/m/d H:i') ?? '未設定' }}
                    </span>
                </div>
            </x-slot:meta>

            {{-- 操作 --}}
            <x-slot:actions>
                @if(in_array($reservation->status, ['draft','unpublished']))
                    <button
                        type="button"
                        onclick="confirm('{{ $reservation->title }}を公開します。よろしいですか？') || event.stopImmediatePropagation()"
                        wire:click="publish"
                        class="h-9 px-5 rounded-lg bg-(--color-500) hover:bg-(--color-600) text-white text-md"
                    >
                        公開する
                    </button>

                @elseif(in_array($reservation->status, ['published','expired']))
                    <button
                        type="button"
                        onclick="confirm('{{ $reservation->title }}を非公開にします。よろしいですか？') || event.stopImmediatePropagation()"
                        wire:click="unpublish"
                        class="h-9 px-5 rounded-lg bg-(--color-500) hover:bg-(--color-600) text-white text-md"
                    >
                        非公開にする
                    </button>

                @else
                    <button disabled
                        class="h-9 px-5 rounded-lg bg-gray-300 text-gray-600 text-md cursor-not-allowed">
                        公開終了
                    </button>
                @endif
            </x-slot:actions>

        </x-admin-panel>
        {{-- 募集情報編集パネル --}}
        <x-admin.panel title="募集情報">
            {{-- メタ情報 --}}
            <x-slot:meta>
                <div class="grid grid-cols-[8em_1fr]">
                    <span>作成日時：</span>
                    <span class="font-medium">
                        {{ $reservation->created_at?->format('Y/m/d H:i') ?? '未設定' }}
                    </span>
                </div>

                <div class="grid grid-cols-[8em_1fr]">
                    <span>最終変更日時：</span>
                    <span class="font-medium">
                        {{ $reservation->updated_at?->format('Y/m/d H:i') ?? '' }}
                    </span>
                </div>

            </x-slot:meta>

            <x-slot:actions>
                <div class="flex flex-col gap-2">
                    <button
                        type="button"
                        wire:click="editReservation({{ $reservation->id }})"
                        onclick="if(!confirm('編集作業中は募集が非公開となり一般ユーザーはアクセス不能となります。また、編集作業を途中で終了すると非公開のままとなりますのでご注意ください。')) event.stopImmediatePropagation()"
                        class="h-9 px-5 rounded-lg bg-(--color-500) hover:bg-(--color-600) text-white text-md"
                    >
                        編集する
                    </button>
                    <button
                        type="button"
                        wire:click="exportCsv"
                        {{-- wire:click="exportPdf" --}}
                        onclick="if(!confirm('募集データをCSVファイルに出力します。よろしいですか？')) event.stopImmediatePropagation()"
                        class="h-9 px-5 rounded-lg bg-(--color-500) hover:bg-(--color-600) text-white text-md"
                    >
                        CSV出力
                    </button>
                    <button
                        type="button"
                        wire:click="exportPdf"
                        onclick="if(!confirm('募集データをPDFファイルに出力します。よろしいですか？')) event.stopImmediatePropagation()"
                        class="h-9 px-5 rounded-lg bg-(--color-500) hover:bg-(--color-600) text-white text-md"
                    >
                        PDF出力
                    </button>
                </div>
                
            </x-slot:actions>

        </x-admin-panel>
    @endif

    {{-- <a wire:navigate href="{{ route("reservation.list") }}">
        一覧に戻る
    </a> --}}

    {{-- メインコンテンツ --}}
    <div class="max-w-200 w-[90%] mx-auto py-10 space-y-6">

        {{-- ページタイトル --}}
        <div class="flex justify-between items-end pb-3">
            <h1 class="text-2xl font-semibold">募集詳細</h1>
        </div>

        {{-- 募集概要 --}}
        <div 
            class="
                block mb-3 px-4
                shadow-md
                rounded-lg
                bg-white
                pt-8
                py-15
                space-y-5
                border border-(--color-50)/50
        ">

            <div class="flex justify-between items-end  flex-wrap break-all px-2">
                <h2 class="text-xl font-semibold text-(--color-600)">
                    {{ $reservation->title }}
                </h2>
                <div>
                    <span class="text-lg text-gray-500  pt-2 px-2">
                        担当者
                    </span>
                    <span class="text-lg text-gray-800 font-medium pt-2 px-2">
                        {{ $reservation->staff_name }}
                    </span>
                </div>


            </div>

            <p class="text-lg leading-relaxed text-gray-800 px-4 pt-2 break-all">
                {!! nl2br(e($reservation->description)) !!}
            </p>
            <div class="flex flex-wrap">
                <span class="text-lg text-gray-500  pt-2 px-2">
                    募集締切
                </span>
                <span class="text-lg text-gray-900 font-semibold  pt-2 px-2">
                   {{ $reservation->deadline_at->format('Y年n月j日 H:i') }}
                </span>
            </div>

            {{-- 日付順にソート --}}
            @php
                $groupedSlots = $reservation->slots
                    ->sortBy('start_at')
                    ->groupBy(fn ($s) => $s->start_at->toDateString());
            @endphp

            {{-- 募集枠一覧 --}}
            <div class="space-y-12 px-4">

                @foreach($groupedSlots as $date => $slots)
                    <div class="relative" >
                        {{-- 日付ヘッダー --}}
                        <div class="flex items-center gap-4 mb-6">
                            <div class="text-xl font-semibold text-gray-900">
                                {{-- {{ \Carbon\Carbon::parse($date)->format('n/j（D）') }} --}}
                                {{ \Carbon\Carbon::parse($date)->locale('ja')->isoFormat('M月D日（ddd）') }}
                            </div>
                            <div class="flex-1 border-t"></div>
                        </div>

                        {{-- 縦線 --}}
                        <div class="absolute left-4 top-10 bottom-0 w-px bg-gray-300"></div>

                        {{-- タイムライン --}}
                        <div class="relative pl-5 space-y-6">
                            {{-- <div class="absolute left-4 top-0 -bottom-3 w-px bg-gray-300"></div> --}}

                            @foreach ($slots as $index => $slot)
                                <div class="relative flex gap-6" wire:key="slot-{{ $index }} ">

                                    {{-- 時刻 --}}
                                    <div class="w-18 text-md text-gray-800 text-right shrink-0">
                                        <div class="font-medium">
                                            {{ $slot->start_at->format('H:i') }}
                                        </div>
                                        <div class="text-sm text-gray-400 mt-2">
                                            {{-- - {{ $slot->end_at->format('H:i') }} --}}
                                            - {{ $slot->display_end_time }}
                                        </div>
                                    </div>
                                    <div class="flex-wrap flex justify-between w-full gap-3">
                                        {{-- 内容 --}}
                                        <div class="
                                            flex flex-col gap-2
                                        ">

                                            {{-- 状態 --}}
                                            <div class="shrink-0">
                                                @if($slot->isReserved)
                                                    <span class="px-2 py-0.5 text-md rounded-full bg-(--color-500) text-white">
                                                        予約済
                                                    </span>
                                                @elseif($slot->start_at->isFuture())
                                                
                                                    @if($slot->isFull)
                                                        <span class="px-2 py-0.5 text-md rounded-full bg-gray-500 text-white">
                                                            満員
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 text-md rounded-full bg-gray-200 text-gray-700">
                                                            空きあり
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="px-2 py-0.5 text-md rounded-full bg-gray-500 text-white">
                                                        終了
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- 人数 --}}
                                            {{-- @if($isAdmin) --}}
                                            <div class="text-md text-gray-600 shrink-0">
                                                現在{{ $slot->current_count }}名 / 定員{{ $slot->capacity }}名
                                            </div>
                                            {{-- @endif --}}
                                            @if($isAdmin)
                                                <div>
                                                    <button 
                                                        type="button" 
                                                        class="w-full text-left text-(--color-500) hover:underline "
                                                        x-on:click="menuOpen = false"
                                                        {{-- wire:click="$dispatch('open-reserved-user-modal')" --}}
                                                        wire:click="getReservedUser({{ $slot->id }})"
                                                    >
                                                        予約ユーザー表示
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- 予約操作 --}}
                                        <div class="ml-auto flex justify-end">

                                            {{-- 予約枠の開始時刻を過ぎている --}}
                                            @if($slot->start_at->isPast())
                                                <button 
                                                    class="
                                                    h-9 px-2 rounded-lg w-25
                                                    bg-gray-500 cursor-not-allowed
                                                    text-white text-md
                                                ">
                                                    受付終了
                                                </button>

                                            {{-- 予約全体が公開中 --}}
                                            @elseif($reservation->isPublished)

                                                @if($slot->isReserved)
                                                    <button
                                                        type="button"
                                                        onclick="confirm('予約をキャンセルしますか？') || event.stopImmediatePropagation()"
                                                        wire:click="cancel({{ $slot }})"
                                                        class="
                                                            h-9 px-2 rounded-lg w-25
                                                            bg-(--color-700) hover:bg-(--color-800)
                                                            text-white text-lg
                                                        ">
                                                        キャンセル
                                                    </button>

                                                @elseif($slot->isFull)
                                                    <button class="
                                                        h-9 px-2 rounded-lg w-25
                                                        bg-gray-500 cursor-not-allowed
                                                        text-white text-md
                                                    ">
                                                        満員
                                                    </button>

                                                @else
                                                    <button
                                                        type="button"
                                                        wire:click="reserve({{ $slot }})"
                                                        class="
                                                            h-9 px-2 rounded-lg w-25
                                                            bg-(--color-500) hover:bg-(--color-600)
                                                            text-white text-lg
                                                        ">
                                                        予約
                                                    </button>
                                                @endif

                                            @elseif($reservation->isUnpublished)
                                                <button class="h-9 px-2 rounded-lg w-25 bg-gray-500 cursor-not-allowed text-white text-md">
                                                    非公開中
                                                </button>

                                            @elseif($reservation->isExpired)
                                                <button class="h-9 px-2 rounded-lg w-25 bg-gray-500 cursor-not-allowed text-white text-md">
                                                    受付終了
                                                </button>

                                            @elseif($reservation->isClosed)
                                                <button class="h-9 px-2 rounded-lg w-25 bg-gray-500 cursor-not-allowed text-white text-md">
                                                    公開終了
                                                </button>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
        {{-- 予約ユーザー表示モーダル --}}
        <livewire:user-select-modal
            :model-class="\App\Models\User::class"
            :user-ids="$modalUserIds"

            display-field-name="name"
            email-field-name="email"

            title="予約ユーザー一覧"
            :show-email="true"
            :show-footer="false"
            hide-active-on-ui="false"

            open-event="open-reserved-user-modal"
            :enable-toggle="false"
        />
        {{-- 削除 --}}
        @if($isAdmin)
            <div class="flex justify-end mt-10">
                <form action="{{ route('reservation.destroy', $reservation) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button
                        onclick="return confirm('{{ $reservation->title }}を本当に削除しますか？')"
                        class="h-9 px-5 rounded-lg text-lg bg-red-700 text-white hover:bg-red-800">
                        募集削除
                    </button>
                </form>
            </div>
        @endif
    
</div>
