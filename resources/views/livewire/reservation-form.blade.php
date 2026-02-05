

@php
use Carbon\Carbon;
@endphp

<div class="p-6">
    <form wire:submit.prevent="save" class="space-y-6">
        @csrf
        <input type="hidden" name="slots" value='@json($slots)'>

        <x-input_unit label="募集タイトル" type="text" name="title" wire:model.live="title" />

        <x-select_unit
            name="selected_purpose"
            label="募集カテゴリー"
            :options="collect($purposes)->pluck('name', 'id')"
            wire:model.live="selected_purpose"
        />

        <x-input_unit label="担当者氏名" type="text" name="staff" wire:model.live="staff" />

        <x-textarea_unit label="説明" name="description" wire:model.live="description"></x-textarea_unit>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
            <x-input_unit label="公開開始" type="datetime-local" name="publish" wire:model.live="publish" />
            <x-input_unit label="募集締切" type="datetime-local" name="deadline" wire:model.live="deadline" />
            <x-input_unit label="公開終了" type="datetime-local" name="close" wire:model.live="close" />
        </div>

        <div class="space-y-6 pt-3">
            @foreach ($slots as $index => $slot)
                @if(empty($slot['_delete']))
                    @php
                        $start = !empty($publish) ? Carbon::parse($publish) : null;
                        $end   = !empty($close) ? Carbon::parse($close) : null;
                        $range = ($start && $end) ? $end->diffInSeconds($start) : null;

                        $events = [
                            ['key' => 'publish',  'time' => $publish,        'color' => 'bg-blue-500'],
                            ['key' => 'deadline', 'time' => $deadline,       'color' => 'bg-red-500'],
                            ['key' => 'from',     'time' => $slot['from'] ?? null, 'color' => 'bg-green-500'],
                            ['key' => 'to',       'time' => $slot['to'] ?? null,   'color' => 'bg-yellow-500'],
                            ['key' => 'close',    'time' => $close,          'color' => 'bg-gray-700'],
                        ];

                        $fromTime = !empty($slot['from']) ? Carbon::parse($slot['from']) : null;
                        $toTime   = !empty($slot['to'])   ? Carbon::parse($slot['to']) : null;
                        $deadlineTime = !empty($deadline) ? Carbon::parse($deadline) : null;
                        $closeTime    = !empty($close) ? Carbon::parse($close) : null;

                        // 開始時刻が締切〜終了時刻の間にない場合
                        $isFromOutOfRange = false;
                        if ($fromTime && $deadlineTime && $toTime) {
                            $isFromOutOfRange = !$fromTime->between($deadlineTime, $toTime);
                        }

                        // 終了時刻が開始時刻〜公開終了の間にない場合
                        $isToOutOfRange = false;
                        if ($toTime && $fromTime && $closeTime) {
                            $isToOutOfRange = !$toTime->between($fromTime, $closeTime);
                        }
                    @endphp

                    <div class="border border-gray-300 rounded-lg p-4 bg-white shadow-sm" wire:key="slot-{{ $index }}">
                        <div class="flex justify-between items-center mb-3">
                            <h2 class="text-lg font-semibold">予約枠 No. {{ $index + 1 }}</h2>
                            <button
                                type="button"
                                onclick="confirm('予約枠No.{{ $index + 1 }}を削除しますか？') || event.stopImmediatePropagation()"
                                wire:click="removeSlot({{ $index }})"
                                class="px-3 py-1 rounded-md bg-red-600 text-white hover:bg-red-700"
                            >
                                削除
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mt-2">
                            <x-input_unit label="開始時刻" type="datetime-local" name="slots.{{ $index }}.from" wire:model.live="slots.{{ $index }}.from" />
                            <x-input_unit label="終了時刻" type="datetime-local" name="slots.{{ $index }}.to" wire:model.live="slots.{{ $index }}.to" />
                            <div class="lg:max-w-40">
                                <x-input_unit label="定員" type="number" name="slots.{{ $index }}.capacity" wire:model.live="slots.{{ $index }}.capacity" />
                            </div>
                        </div>


                        <div class="mt-4 w-[95%] mx-auto">
                            <!-- ラベルと時刻を一緒に表示 -->
                            <div class="flex items-center justify-between text-xs text-gray-600">
                                @foreach ($events as $event)
                                    @if(!empty($event['time']))
                                        <div class="flex flex-col items-center space-y-1">  
                                            <div class="flex items-center space-x-1">
                                                <span class="w-2 h-2 rounded-full {{ $event['color'] }}"></span>
                                                <span class="
                                                    @if($event['key'] === 'from' && $isFromOutOfRange) text-red-600 font-semibold
                                                    @elseif($event['key'] === 'to' && $isToOutOfRange) text-red-600 font-semibold
                                                    @endif
                                                ">
                                                    @switch($event['key'])
                                                        @case('publish') 公開開始 @break
                                                        @case('deadline') 募集締切 @break
                                                        @case('from') 開始時刻 @break
                                                        @case('to') 終了時刻 @break
                                                        @case('close') 公開終了 @break
                                                    @endswitch
                                                </span>
                                            </div>
                                            <div class="text-gray-500
                                                @if($event['key'] === 'from' && $isFromOutOfRange) text-red-600 font-semibold
                                                @elseif($event['key'] === 'to' && $isToOutOfRange) text-red-600 font-semibold
                                                @endif
                                            ">
                                                {{ Carbon::parse($event['time'])->format('n/j H:i') }}
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <!-- プログレスバー -->
                            <div class="relative mt-3 h-2 bg-gray-200 rounded">
                                <div class="absolute inset-0 rounded bg-gray-300"></div>
                                @if($start && $end && $range)
                                    @foreach ($events as $event)
                                        @if(!empty($event['time']))
                                            @php
                                                $time = Carbon::parse($event['time']);
                                                $offset = $time->diffInSeconds($start);
                                                $left = min(max($offset / $range * 100, 0), 100);
                                            @endphp
                                            <div
                                                class="absolute top-1/2 w-3 h-3 rounded-full {{ $event['color'] }} shadow"
                                                style="left: {{ $left }}%; transform: translate(-50%, -50%);"
                                                title="{{ $time->format('m/d H:i') }}"
                                            ></div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                                                        <!-- 時系列の凡例 -->
                            <p class="text-sm flex flex-wrap items-center gap-2 my-6 text-gray-700">
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 "></span>
                                    <span class="font-semibold">公開開始 →</span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                    <span class="font-semibold">募集締切 →</span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    <span class="font-semibold">開始時刻 →</span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                    <span class="font-semibold">終了時刻 →</span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                                    <span class="font-semibold">公開終了</span>
                                </span>
                                <span class="ml-2 text-gray-700">の順となるように設定してください。</span>
                            </p>

                        </div>

                        <!-- 予約ユーザー一覧 -->
                        <div class="mt-6">
                            <label class="block text-sm text-gray-700 mb-2">予約ユーザー</label>
                            <div class="grid grid-cols-[32px_1fr_2fr_48px] gap-3 px-2 py-1 text-sm font-medium text-gray-600">
                                <div class="font-semibold">No</div>
                                <div class="font-semibold">名前</div>
                                <div class="font-semibold">メールアドレス</div>
                                <div></div>
                            </div>

                            <div class="space-y-1 mt-1">
                                @foreach ($slot['reserved_users'] as $user_index => $user)
                                    @if(empty($user['_delete']))
                                        <div class="grid grid-cols-[32px_1fr_2fr_48px] gap-3 items-center px-2 py-1 text-md hover:bg-gray-100 rounded"
                                            wire:key="slot-{{ $index }}-user-{{ $user_index }}">
                                            <div class="text-gray-800 font-mono">{{ $user_index + 1 }}</div>
                                            <div class="text-gray-800 truncate">{{ $user['name'] }}</div>
                                            <div class="text-gray-800 truncate">{{ $user['email'] }}</div>
                                            <div>
                                                <button
                                                    type="button"
                                                    onclick="confirm('{{ $user['name'] }}さんの予約をキャンセルしますか？') || event.stopImmediatePropagation()"
                                                    wire:click="removeUser({{ $index }}, {{ $user_index }})"
                                                    class="text-red-600 hover:underline"
                                                >削除</button>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="grid grid-cols-[32px_1fr_2fr_48px] gap-4 items-center px-2 py-1">
                                    <div></div><div></div><div></div>
                                    <div>
                                        <button type="button" wire:click="openUserModal({{ $index }})" class="text-indigo-600 hover:underline">追加</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if($show_user_modal)
                <livewire:user-search-modal
                    :exclude-user-ids="collect($slots[$active_slot_index]['reserved_users'])
                        ->filter(fn($u) => empty($u['_delete']))
                        ->pluck('id')->values()->toArray()"
                    :key="'user-modal-'.$active_slot_index"
                />
            @endif

            <div class="pt-4">
                <button type="button" wire:click="addSlot"
                    class="px-4 py-2 rounded-md border-2 border-indigo-600 text-indigo-600 bg-white hover:bg-indigo-50">予約枠の追加</button>
            </div>
        </div>

        <x-button_unit>{{ $is_edit ? '変更' : '作成' }}</x-button_unit>
    </form>
</div>
