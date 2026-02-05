<div>
    @if(Auth::user()->isAdmin())
        <x-admin.panel title="グループチャット管理">
            <x-slot:actions>
                <button
                    type="button"
                    href="{{ route('reservation.create') }}"
                    wire:click="$dispatch('open-group-member-modal')"
                    class="
                        h-9 px-5 rounded-lg bg-(--color-500) hover:bg-(--color-600) text-white text-md
                        flex items-center justify-center
                ">
                    グループ作成
        </button>
            </x-slot:actions>
        </x-admin-panel>
    @endif

    <div class="w-[90%] max-w-200 mx-auto py-10  flex flex-col">

        {{-- ヘッダー --}}
        <div class="flex justify-between items-start pb-3">
            <h1 class="text-2xl font-semibold">
                チャット履歴
            </h1>
            <div class="flex flex-col gap-2">
                <button
                    type="button"
                    {{-- wire:click="openChatUserModal" --}}
                    wire:click="$dispatch('open-chat-user-modal')"
                    class="text-lg text-(--color-700) hover:underline"
                >
                    チャットルーム選択
                </button>
            </div>

        </div>

        {{-- チャット一覧 --}}
        <div class="flex-1 p-4 space-y-3 overflow-y-auto">
            @if ($chat_rooms->isEmpty())
                <div
                    class="
                        rounded-lg p-6
                        bg-white
                        shadow-md
                        border border-(--color-50)/50
                        text-center
                        space-y-3
                    "
                >
                    <p class="text-gray-700 font-medium">
                        チャット履歴はまだありません
                    </p>

                    <p class="text-sm text-gray-500">
                        ユーザーを選択して新しいチャットを開始できます。
                    </p>

                    <button
                        type="button"
                        wire:click="$dispatch('open-chat-user-modal')"
                        class="
                            inline-block mt-2
                            text-(--color-600)
                            hover:underline
                            text-sm font-medium
                        "
                    >
                        チャットを開始する
                    </button>
                </div>
            @endif


            @foreach ($chat_rooms as $room)
                @php
                    $partner = $room->users->firstWhere('id', '!=', auth()->id());
                @endphp
                @if($room->type == 'private')
                    <div class="flex gap-2">
                        <x-user-icon :icon-path="$partner->icon_path" />
                        <x-chat.list_card
                            href="/chat/room/{{ $room->id }}"
                            username="{{$partner->name ?? 'メンバーがいません'  }}"
                            date="{{ $room->latestChat->display_date ?? ''}}"
                            message="{{ $room->latestChat->message ?? ''}}"
                            unread="{{ $room->unread_count }}"
                        />
                    </div>
                @elseif($room->type == 'group')
                    <div class="flex gap-2">
                        <x-user-icon :icon-path="$room->icon_path" />
                        <x-chat.list_card
                            href="/chat/room/{{ $room->id }}"
                            username="{{$room->name}} ({{$room->active_members_count}})"

                            date="{{ $room->latestChat->display_date ?? ''}}"
                            message="{{ $room->latestChat->message ?? ''}}"
                            unread="{{ $room->unread_count }}"
                        />
                    </div>
                @endif

            @endforeach

            {{-- 無限スクロールトリガー --}}
            <div
                x-data
                x-intersect="$wire.loadMore()"
                class="h-1"
            ></div>
        </div>
        
        {{-- チャットユーザー選択モーダル --}}
        <livewire:user-select-modal
            :model-class="\App\Models\User::class"
            :user-ids="$chat_user_ids"

            display-field-name="name"
            email-field-name="email"

            title="ユーザー選択"
            :show-email="true"
            :show-footer="true"
            :single-select="true"
            next-button-label="チャット開始"

            open-event="open-chat-user-modal"
            next-method="makeChatRoom"
            :enable-toggle="true"
        />

        {{-- グループメンバー選択モーダル --}}
        <livewire:user-select-modal
            :model-class="\App\Models\User::class"
            :user-ids="$chat_user_ids"

            display-field-name="name"
            email-field-name="email"

            title="グループメンバー選択"
            :show-email="true"
            :show-footer="true"
            next-button-label="次へ"

            open-event="open-group-member-modal"
            {{-- next-method="makeGroupChatRoom" --}}
            next-method="openMakeGroup"
            :enable-toggle="true"
        />

        {{-- グループチャット作成モーダル --}}
        <livewire:make-group-modal
            :model-class="\App\Models\User::class"
            :user-ids="$active_user_ids"

            display-field-name="name"
            email-field-name="email"

            title="グループ作成"
            :show-email="true"
            :show-footer="true"
            next-button-label="グループ作成"

            open-event="open-make-group-modal"
            next-method="makeGroupChatRoom"
            :enable-toggle="false"
            :reset-on-close="true"
            :hide-active-on-ui="true"
        />
    </div>
</div>