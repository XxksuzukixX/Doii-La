<div
    x-data="{ 
        menuOpen: false,
        showError: false,
        errorMessage: ''
    }"
    x-on:show-error-toast.window="
        errorMessage = $event.detail.message;
        showError = true;
        setTimeout(() => showError = false, 3000);
    "
    class="
        relative
        h-[calc(100dvh-100px)]
        flex flex-col
        shadow-sm
        border border-gray-200
        overflow-hidden
    "
>
    {{-- ヘッダー --}}
    <div class="
        flex bg-(--color-50)
        border-b border-(--color-200)
        justify-between pl-2 pr-8 items-center h-14
    ">
        <div class="flex items-center">
            <button
                type="button"
                class="
                    flex items-center justify-center
                    w-10 h-10
                    text-(--color-500)
                    hover:text-(--color-600)
                    active:text-(--color-600)
                "
                aria-label="戻る"
                onclick="history.back()"
            >
                <svg
                    class="w-8 h-8"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                    aria-hidden="true"
                >
                    <polygon points="15,18 9,12 15,6" />
                </svg>
            </button>


            <h1 class="
                    shrink-0
                    text-xl font-semibold
            ">
                @if($chat_room->type === 'private')
                    @php
                        $partner = $chat_room->users->firstWhere('id', '!=', auth()->id());
                    @endphp
                    {{ $partner->name ?? 'チャットルーム' }}
                @elseif($chat_room->type === 'group')
                    {{ $chat_room->name ?: 'チャットルーム'}} ({{ $member_count }})
                @endif
            </h1>
   
        </div>

        {{-- グループメニュー --}}
        @if($chat_room->type === 'group')
            <button
                id="menuButton"
                class="
                    text-(--color-500)
                    hover:text-(--color-600)
                    active:text-(--color-600)
                "
                aria-label="menu"
                x-on:click="menuOpen = true"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="5" r="1.5"/>
                    <circle cx="12" cy="12" r="1.5"/>
                    <circle cx="12" cy="19" r="1.5"/>
                </svg>
            </button>
        @endif
    </div>


    @php
        $last_date = null;
    @endphp

    {{-- メッセージ一覧 --}}
    <div
        id="chat-scroll-area"
        {{-- x-data --}}
        x-data="chatScroll()"
        x-init="init()"
        x-on:scroll-to-bottom.window="
            $el.scrollTop = $el.scrollHeight
        "
        class="flex-1 overflow-y-auto px-4 py-6 space-y-6 w-full max-w-full"
    >
    
        @foreach ($chat_messages as $chat_message)
            @php
                // 自分の送信メッセージか
                $is_me = $chat_message->sender_id === auth()->id();
                // システムメッセージか
                $from_system = $chat_message->type === 'system';
                // 送信者ID
                $sender_user = $chat_message->sender;
                // メッセージの送信された日付
                $current_date = $chat_message->created_at->format('m/d');


                // 既読表示文字列
                $read_label = $chat_room->type === 'group'
                    ? '既読 ' . $chat_message->read_count
                    : '既読';

                // 表示判定（既読数があれば表示）
                $read_status = ($is_me && $chat_message->read_count > 0)
                    ? $read_label
                    : '';

            @endphp

            {{-- 日付区切り --}}
            @if ($last_date !== $current_date)
                <div class="flex justify-center my-4">
                    <div
                        class="
                            px-4 py-1.5
                            text-xs text-gray-600
                            bg-gray-100
                            rounded-xl
                            max-w-[90%]
                            text-center
                            leading-relaxed
                            select-none
                        "
                    >
                        {{ $chat_message->display_chat_log_date }}
                    </div>
                </div>
                {{-- <x-chat.date date="{{ $chat_message->display_chat_log_date }}" /> --}}
                @php $last_date = $current_date; @endphp
            @endif

            {{-- <div class="flex gap-2 items-start"> --}}
            @if ($from_system)
                <div class="flex justify-center my-4">
                    <div
                        class="
                            px-4 py-1.5
                            text-xs text-gray-600
                            bg-gray-100
                            rounded-xl
                            max-w-[90%]
                            text-center
                            leading-relaxed
                            select-none
                        "
                    >
                        <div>{{ $chat_message->created_at->format('H:i') }}</div>
                        <div>{{ $chat_message->message }}</div>
                        
                    </div>
                </div>
            @else
                <div
                    class="
                        flex items-start
                        {{ $is_me ? 'justify-end' : 'justify-start' }}
                        gap-2
                    "
                >
                    {{-- 送信者アイコン（自分以外） --}}
                    @unless ($is_me)
                        <x-user-icon
                            :icon-path="$sender_user->icon_path"
                            size="w-12 h-12"
                        />
                    @endunless

                    <div class="flex flex-col max-w-[75%] w-full">
                        {{-- グループチャット時の送信者名 --}}
                        @unless ($is_me)
                            <span class="text-xs text-gray-500 pl-1 mb-0.5">
                                {{ $sender_user->name }}
                            </span>
                        @endunless

                        <x-chat.message
                            type="{{ $is_me ? 'me' : 'other' }}"
                            time="{{ $chat_message->created_at->format('H:i') }}"
                            status="{{ $read_status }}"
                        >
                            {{-- {!! nl2br(e($chat_message->message_html)) !!} --}}
                            {!! $chat_message->message_html !!}
                        </x-chat.message>
                    </div>
                </div>
            @endif

        @endforeach
    </div>

    {{-- 入力フォーム --}}
    <form
        wire:submit.prevent="requestSend"
        class="shrink-0 px-3 py-2 bg-white border-t border-(--color-200)"
    >
        <div class="flex items-end gap-2">
            <textarea
                x-data
                {{-- x-on:keydown.enter="
                    if (!$event.shiftKey) {
                        $event.preventDefault();
                        $wire.requestSend();
                    }
                " --}}
                
                x-on:keydown.enter="
                    if ($event.shiftKey) {  // Shift + Enter の場合
                        $event.preventDefault();
                        $wire.requestSend();  // 送信
                    } // Enter 単体の場合は何もしないので普通に改行される
                "

                wire:model.defer="input_message"
                placeholder="メッセージを入力..."
                class="
                    flex-1 h-14 resize-none
                    px-3 py-2
                    text-md
                    bg-white
                    border border-gray-300 rounded-lg
                    shadow-sm
                    focus:outline-none
                    focus:ring-2 focus:ring-(--color-500)
                    focus:border-(--color-500)
                "
            ></textarea>

            <button
                type="submit"
                class="
                    h-14 px-5
                    rounded-lg
                    text-md text-white
                    bg-(--color-500)
                    hover:bg-(--color-600)
                    active:bg-(--color-700)
                    transition
                "
            >
                送信
            </button>
        </div>
    </form>
    <div
        x-show="showError"
        x-transition
        x-cloak
        class="
            fixed bottom-24 left-1/2 -translate-x-1/2
            z-50
            bg-red-500 text-white
            px-4 py-2 rounded-lg shadow-lg
            text-sm
        "
        x-text="errorMessage"
    ></div>
    @if($chat_room->type === 'group')
        {{-- Slide Menu --}}
        <div
            x-show="menuOpen"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            x-on:click.outside="menuOpen = false"
            class="
                absolute top-0 right-0 z-50
                w-72 h-[calc(100dvh)]
                bg-(--color-50)
                shadow-lg
                flex flex-col
            "
        >
            <div class="
                {{-- h-14 px-8 flex items-center justify-between border-b --}}
                flex bg-(--color-100)
                border-b border-(--color-200)
                justify-between px-6 items-center h-14
            ">
                <span class="font-semibold text-xl">グループ</span>
                <button 
                    {{-- @click="menuOpen = false"  --}}
                    x-on:click="menuOpen = false"
                    class="pr-2 font-semibold text-(--color-500)
                ">✕</button>
            </div>

            <div class="flex-1 px-8 py-4 space-y-3 text-lg">

                <button 
                    type="button" 
                    class="w-full text-left hover:text-(--color-500)"
                    x-on:click="menuOpen = false"
                    wire:click="$dispatch('open-group-member-modal')"
                >
                    メンバー一覧
                </button>
                @if(auth()->user()->isAdmin())
                    <button 
                        type="button" 
                        class="w-full text-left hover:text-(--color-500)"
                        x-on:click="menuOpen = false"
                        wire:click="$dispatch('open-edit-group-modal')"
                    >
                        プロフィール編集
                    </button>
                    <button 
                        type="button" 
                        class="w-full text-left hover:text-(--color-500)"
                        x-on:click="menuOpen = false"
                        wire:click="$dispatch('open-add-member-modal')"
                    >
                        メンバー追加
                    </button>
                                        <button 
                        type="button" 
                        class="w-full text-left hover:text-(--color-500)"
                        x-on:click="menuOpen = false"
                        wire:click="$dispatch('open-remove-member-modal')"
                    >
                        メンバー削除
                    </button>
                @endif

                <button 
                    onclick="confirm('退出するとチャット履歴が確認できなくなります。本当に退出しますか？') || event.stopImmediatePropagation()"
                    wire:click="leftChatRoom"
                    class="w-full text-left text-red-500 hover:text-red-600">
                    チャットを退出
                </button>
            </div>
        </div>
            {{-- Overlay --}}
        <div
            id="overlay"
            class="fixed inset-0 z-40 bg-black/50 hidden"
        ></div>


        {{-- グループプロフィール編集モーダル --}}
        <livewire:edit-group-modal
            :room-id="$chat_room->id"
            title="プロフィール編集"
            open-event="open-edit-group-modal"
        />

        {{-- グループメンバー表示モーダル --}}
        <livewire:user-select-modal
            :model-class="\App\Models\User::class"
            :user-ids="$chat_user_ids"

            display-field-name="name"
            email-field-name="email"

            title="グループメンバー"
            :show-email="false"
            :show-footer="false"

            open-event="open-group-member-modal"
            {{-- next-method="makeGroupChatRoom" --}}
            :enable-toggle="false"
        />

        {{-- メンバー追加モーダル --}}
        <livewire:user-select-modal
            :model-class="\App\Models\User::class"
            :user-ids="$non_member_user_ids"

            display-field-name="name"
            email-field-name="email"

            title="追加メンバー選択"
            :show-email="true"
            :show-footer="true"
            :enable-toggle="true"

            open-event="open-add-member-modal"

            next-button-label="追加"

            next-method="addGroupMember"
        />
        {{-- メンバー削除モーダル --}}
        <livewire:user-select-modal
            :model-class="\App\Models\User::class"
            :user-ids="$other_member_user_ids"

            display-field-name="name"
            email-field-name="email"

            title="削除メンバー選択"
            :show-email="true"
            :show-footer="true"
            :enable-toggle="true"

            open-event="open-remove-member-modal"

            next-button-label="削除"
            
            next-method="removeGroupMember"
        />
    @endif

    @if($confirming)
        <div
            x-data
            x-show="@entangle('confirming')"
            x-cloak
            class="fixed inset-0 flex items-center justify-center bg-black/40 z-50"
        >
            <div
                class="bg-white w-full max-w-lg rounded-lg shadow p-6
                    max-h-[90vh] flex flex-col"
            >

                {{-- ヘッダー --}}
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">
                        送信内容の確認
                    </h2>
                    
                    <button
                        type="button"
                        wire:click="$set('confirming', false)"
                        class="text-gray-500 hover:text-gray-700 text-2xl"
                    >
                        ×
                    </button>
                </div>
                <p class="mt-3 text-sm text-gray-700">
                    送信内容に個人情報が含まれている可能性があります。
                    送信先と内容を十分にご確認のうえ、問題なければ送信してください。
                </p>

                <!-- 本文（スクロール領域） -->
                <div
                    class="mt-4 p-3 bg-gray-50 border rounded
                        text-sm text-gray-800
                        flex-1 overflow-y-auto"
                >
                    {!! $highlightedMessage !!}
                </div>

                <!-- フッター（常に表示） -->
                <div class="mt-6 flex justify-end gap-2">
                    <button
                        wire:click="$set('confirming', false)"
                        class="h-8.75 px-3 rounded-lg
                            border border-gray-300
                            text-gray-700
                            hover:bg-gray-100"
                    >
                        キャンセル
                    </button>

                    <button
                        wire:click="send"
                        class="h-8.75 px-3 rounded-lg
                            bg-(--color-500) text-white
                            hover:bg-(--color-600)"
                    >
                        送信する
                    </button>
                </div>
            </div>
        </div>

    @endif
    <script>
    function chatScroll() {
        return {
            el: null,
            loading: false,

            init() {
                this.el = document.getElementById('chat-scroll-area');

                // 初期表示は最下部
                this.$nextTick(() => {
                    this.el.scrollTop = this.el.scrollHeight;
                });

                this.el.addEventListener('scroll', async () => {
                    if (this.el.scrollTop === 0 && !this.loading) {
                        this.loading = true;

                        const prevHeight = this.el.scrollHeight;

                        // Livewire 側で過去メッセージ取得
                        await this.$wire.loadChatMessages();

                        this.$nextTick(() => {
                            // 高さ差分だけ戻す（←これが無いとガクッと動く）
                            const newHeight = this.el.scrollHeight;
                            this.el.scrollTop = newHeight - prevHeight;

                            this.loading = false;
                        });
                    }
                });
            }
        }
    }
    </script>

</div>
