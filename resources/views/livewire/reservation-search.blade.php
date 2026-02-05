
@php
    // ソートボタン用クラス
    $baseSortButtonClass = 'flex items-center gap-1 bg-white h-9.5 px-3 text-sm w-22 border rounded-lg transition';
    $activeSortClass = 'border-[var(--color-500)] bg-[var(--color-50)] text-[var(--color-600)]';
    $inactiveSortClass = 'border-gray-300 text-gray-800 hover:bg-gray-100';

    $statusLabels = [
        'draft'        => '公開前',
        'published'    => '公開中',
        'expired'      => '受付終了',
        'closed'       => '公開終了',
        'unpublished'  => '非公開',
    ];

    $statusClasses = [
        'draft'       => 'bg-yellow-500 text-white',
        'published'   => 'bg-blue-500 text-white',
        'expired'     => 'bg-gray-500 text-white',
        'closed'      => 'bg-gray-800 text-white',
        'unpublished' => 'bg-red-500 text-white',
    ];
@endphp

<div>
    
    <div class="flex justify-between items-center mb-3">

        {{-- ソート --}}
        <div class="flex flex-wrap gap-2 w-full">
            {{-- 公開日 --}}
            <button
                wire:click="sortBy('publish_at')"
                class="{{ $baseSortButtonClass }} {{ $sortField === 'publish_at' ? $activeSortClass : $inactiveSortClass }}"
            >
                公開日
                @if($sortField === 'publish_at')
                    <span class="text-xs">
                        {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                    </span>
                @endif
            </button>

            {{-- 締切日 --}}
            <button
                wire:click="sortBy('deadline_at')"
                class="{{ $baseSortButtonClass }} {{ $sortField === 'deadline_at' ? $activeSortClass : $inactiveSortClass }}"
            >
                締切日
                @if($sortField === 'deadline_at')
                    <span class="text-xs">
                        {{ $sortDirection === 'asc' ? '▲' : '▼' }}
                    </span>
                @endif
            </button> 

            {{-- 管理者用ステータスフィルター --}}
            @if(Auth::user()->isAdmin())
                <select
                    wire:model.live="statusFilter"
                    class="
                        h-9.5 px-3 text-sm bg-white text-gray-800
                        border border-gray-300 rounded-lg
                        focus:outline-none focus:ring-2
                        focus:ring-(--color-500)
                    "
                >
                    <option value="">全て</option>

                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            @endif

            {{-- 検索 --}}
            <input
                type="text"
                placeholder="タイトルまたは担当者氏名"
                wire:model.live.debounce.300ms="keyword"
                wire:keydown.enter.prevent
                class="
                    h-9.5 px-3 text-sm flex-1 bg-white
                    border border-gray-300 rounded-lg
                    focus:outline-none focus:ring-2
                    focus:ring-(--color-500)
                "
            >
        </div>
    </div>


    {{-- 予約カード --}}
    @foreach ($reservations as $reservation)
        <a
            wire:navigate
            href="/reservation/detail/{{ $reservation->id }}"
            class="
                block mb-3 p-4
                shadow-md
                rounded-lg
                hover:bg-(--color-50)
                active:bg-(--color-50)
                bg-white
                transition
                border border-(--color-50)/50
                max-w-full
                w-full
            "
        >
            <div class="space-y-2 w-full min-w-0">

                {{-- タイトル --}}
                <h2 class="flex items-center w-full min-w-0 overflow-hidden">
                    <span class="flex-1 min-w-0 text-lg font-semibold text-(--color-600) w-5
                                whitespace-nowrap overflow-hidden text-ellipsis">
                        {{ $reservation->title }}
                    </span>

                    {{-- 未読ならNEW表示 --}}
                    @if((!$reservation->myRead?->isRead() ?? true) && $reservation->status === 'published')
                        <span class="ml-2 shrink-0 px-2 py-0.5
                                    text-xs font-bold text-white bg-(--color-500) rounded-full">
                            NEW
                        </span>
                    @endif

                    @if(Auth::user()->isAdmin())
                        <span class="ml-2 shrink-0 px-2 py-0.5
                                    text-xs font-bold rounded-full
                                    {{ $statusClasses[$reservation->status] ?? 'bg-gray-500' }}
                        ">
                            {{$statusLabels[$reservation->status]}}
                        </span>
                    @else
                        @if($reservation->status === 'expired')
                            <span class="ml-2 shrink-0 px-2 py-0.5
                                text-xs font-bold rounded-full
                                {{ $statusClasses[$reservation->status] ?? 'bg-gray-500' }}
                            ">
                                {{$statusLabels[$reservation->status]}}
                            </span>
                        @endif
                    @endif
                </h2>

                <div class="flex justify-between min-w-0 items-end">
                    <div class="min-w-0 space-y-2 text-sm">

                        {{-- 担当者 --}}
                        <div class="flex flex-wrap gap-x-2">
                            <span class="text-gray-500 shrink-0">担当者</span>
                            <span class="font-medium text-gray-800 break-all">
                                {{ $reservation->staff_name }}
                            </span>
                        </div>
                        
                        {{-- 公開 --}}
                        <div class="flex flex-wrap gap-x-2">
                            @if($sortField === 'publish_at')
                                @if($sortDirection === 'asc')
                                    <span class=" text-(--color-600) shrink-0">公開▲</span>
                                @else
                                    <span class=" text-(--color-600) shrink-0">公開▼</span>
                                @endif
                            @else
                                <span class="text-gray-500  shrink-0">公開　</span>
                            @endif
                            <span class="font-medium text-gray-800">
                                {{ $reservation->publish_at->format('Y年n月j日 H:i') }}
                            </span>
                        </div>
                        
                        {{-- 締切 --}}
                        <div class="flex flex-wrap gap-x-2">
                            @if($sortField === 'deadline_at')
                                @if($sortDirection === 'asc')
                                    <span class=" text-(--color-600) shrink-0">締切▲</span>
                                @else
                                    <span class=" text-(--color-600) shrink-0">締切▼</span>
                                @endif
                            @else
                                <span class="text-gray-500  shrink-0">締切　</span>
                            @endif
                            <span class="font-medium text-gray-800">
                                {{ $reservation->deadline_at->format('Y年n月j日 H:i') }}
                            </span>
                        </div>

                    </div>

                    {{-- カテゴリー --}}

                    <div class="relative w-25 h-20 shrink-0">
                        <img
                            {{-- src="{{ asset('img/reservation/game_air_hockey.png') }}" --}}
                            {{-- src="{{ asset('img/reservation/benkyoukai_kunrenkou.png') }}" --}}
                            src="{{ asset('img/reservation/' . $reservation->purpose->image_path) }}"
                            {{-- src="{{ asset('img/reservation/syukatsu_group_mensetsu.png') }}" --}}
                            
                            alt=""
                            class="w-full h-full object-contain rounded"
                        >
                        <div class="absolute bottom-0 right-0 px-1.5 py-0.5
                                    bg-black/60 text-white text-xs w-full
                                    text-center font-bold border-x-3 border-(--color-500)">
                            {{ $reservation->purpose->name }}
                        </div>
                    </div>
                </div>

            </div>
        </a>
    @endforeach


    {{-- ページング --}}
    <div class="pt-3">
        {{ $reservations->links('livewire::page') }}
    </div>
</div>
