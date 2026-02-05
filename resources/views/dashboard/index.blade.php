<x-app title="{{config('app.name')}}">
    <x-header_sidebar>
        <div class="max-w-200 w-[90%] mx-auto pt-10 ">

            <h1 class="text-2xl font-semibold pb-3 text-left">
                トップページ
            </h1>
            

            <!-- 予約一覧 -->
            <h2 class="text-lg font-semibold px-3 text-left">
                お知らせ
            </h2>
            <div class="p-5 overflow-y-auto space-y-4 max-h-[calc(100vh-300px)]">

                <!-- お知らせカード -->
                <a 
                    href="{{ route('reservation.list') }}" 
                    class=
                        "block rounded-lg shadow-md p-4 transition
                        bg-white hover:bg-(--color-50) active:bg-(--color-50) 
                        border border-(--color-50)/50"
                >
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500">募集</p>
                        <p class="text-gray-800 font-medium">
                            @if($unread_reservation_count>0)
                                新着の募集が <span class="text-(--color-600)">{{ $unread_reservation_count }}件</span> あります。
                            @else 
                                新着の募集はありません。
                            @endif
                        </p>
                    </div>
                </a>

                <a 
                    href="{{ route('chat.list') }}" 
                    class=
                        "block rounded-lg shadow-md p-4 transition
                        bg-white hover:bg-(--color-50) active:bg-(--color-50) 
                        border border-(--color-50)/50"
                >
                    <div class="space-y-1">
                        <p class="text-sm text-gray-500">チャット</p>
                        <p class="text-gray-800 font-medium">
                            @if($unread_message_count>0)
                                未読のメッセージが <span class="text-(--color-600)">{{ $unread_message_count }}件</span> あります。
                            @else 
                                新着のメッセージはありません。
                            @endif

                        </p>
                    </div>
                </a>

            </div>
            
            <h2 class="text-lg font-semibold px-3 text-left">
                {{ Auth::user()->name }}さんの予約状況
            </h2>
            <div class="p-5 space-y-4 ">   
                @if ($slots->isEmpty())
                    <a 
                        href="{{ route('reservation.list') }}" 
                        class=
                            "block rounded-lg shadow-md p-4 transition
                            bg-white hover:bg-(--color-50) active:bg-(--color-50) 
                            border border-(--color-50)/50"
                    >
                        <div class="space-y-1">
                            <p class="text-sm text-gray-500">予約</p>
                            <p class="text-gray-800 font-medium">
                                    現在予約している募集はありません。
                            </p>
                        </div>
                    </a>
                @endif
                {{-- 予約カード --}}
                @foreach ($slots as $slot)
                    @if ($slot->reservation)

                        <a
                            {{-- wire:navigate --}}
                            href="/reservation/detail/{{ $slot->reservation->id }}"
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
                                        {{ $slot->reservation->title }}
                                    </span>
                                </h2>

                                <div class="flex justify-between min-w-0 items-end">
                                    <div class="min-w-0 space-y-2 text-sm">

                                        {{-- 担当者 --}}
                                        <div class="flex flex-wrap gap-x-2">
                                            <span class="text-gray-500 shrink-0">担当者</span>
                                            <span class="font-medium text-gray-800 break-all">
                                                {{ $slot->reservation->staff_name }}
                                            </span>
                                        </div>
                                        {{-- 締切 --}}
                                        <div class="flex flex-wrap gap-x-2">
                                            <span class="text-gray-500 shrink-0">日程　</span>
                                            <span class="font-medium text-gray-800">
                                                {{ $slot->start_at->format('Y年n月j日 H:i-') }}
                                            </span>
                                        </div>

                                    </div>

                                    {{-- カテゴリー --}}
                                    

                                    <div class="relative w-25 h-20 shrink-0">
                                        <img
                                            src="{{ asset('img/reservation/' . $slot->reservation->purpose->image_path) }}"
                                            {{-- src="{{ asset('img/reservation/syukatsu_group_mensetsu.png') }}" --}}
                                            alt=""
                                            class="w-full h-full object-contain rounded"
                                        >
                                        <div class="absolute bottom-0 right-0 px-1.5 py-0.5
                                                    bg-black/60 text-white text-xs w-full
                                                    text-center font-bold border-x-3 border-(--color-500)">
                                            {{ $slot->reservation->purpose->name }}
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </x-header_sidebar>
</x-app>