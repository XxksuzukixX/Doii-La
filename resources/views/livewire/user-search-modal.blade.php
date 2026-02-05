<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">

    {{-- モーダル本体 --}}
    <div
        class="bg-white w-full max-w-3xl rounded-lg shadow-lg p-5 max-h-[90vh] flex flex-col"
        wire:keydown.enter.prevent
    >

        {{-- ヘッダー --}}
        <div class="flex justify-between items-center pb-2 mb-4">
            <h2 class="text-lg font-semibold text-gray-800">
                ユーザー検索
            </h2>

            <button
                type="button"
                wire:click="$dispatch('closeUserModal')"
                class="text-gray-500 hover:text-gray-700 text-2xl"
            >
                ×
            </button>
        </div>

        {{-- 検索 --}}
        <div class="mb-4">
            <input
                type="text"
                placeholder="ユーザー名またはメールアドレス"
                wire:model.live.debounce.300ms="keyword"
                wire:keydown.enter.prevent
                class="w-full h-9.5 px-3
                       border border-gray-500 rounded-lg
                       focus:outline-none focus:ring-2
                       focus:ring-(--color-500)"
            >
        </div>

        {{-- 一覧 --}}
        <div class="space-y-1 flex flex-col flex-1 min-h-0">

            {{-- ヘッダー --}}
            <div class="
                grid grid-cols-[30px_2fr_2fr] 
                gap-4 min-h-8.75 items-center px-2 
                text-sm text-gray-700">
                <div></div>
                <div>名前</div>
                <div>メールアドレス</div>
            </div>
            {{-- スクロール領域　--}}
            <div class="flex-1 overflow-y-auto">

                {{-- ユーザー --}}
                @foreach ($users as $user)
                    @php
                        $selected = in_array($user->id, $selected_user_ids, true);
                    @endphp

                    <div
                        wire:click="toggleUser({{ $user->id }})"
                        class="
                            grid grid-cols-[30px_2fr_2fr]
                            gap-4 min-h-10 items-center px-2
                            cursor-pointer
                            hover:bg-(--color-50)
                        "
                    >
                        {{-- チェック --}}
                        <div class="flex justify-center">
                            <input
                                type="checkbox"
                                @checked($selected)
                                class="pointer-events-none"
                            >
                        </div>

                        {{-- 名前 --}}
                        <div class="text-gray-800">
                            {{ $user->name }}
                        </div>

                        {{-- メール --}}
                        <div class="text-gray-600 text-sm">
                            {{ $user->email }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ページング --}}
        <div class="pt-3">
            {{-- {{ $users->links('pagination::paging')  }} --}}
            {{ $users->links('livewire::page')  }}
        </div>

        {{-- フッター --}}
        <div class="flex justify-end gap-3 pt-4 border-t border-(--color-500) mt-4">
            <button
                type="button"
                wire:click="$dispatch('closeUserModal')"
                class="h-8.75 px-3 rounded-lg
                       border border-gray-300
                       text-gray-700
                       hover:bg-gray-100"
            >
                キャンセル
            </button>

            <button
                type="button"
                wire:click="addUsers"
                class="h-8.75 px-3 rounded-lg
                       bg-(--color-700) text-white
                       hover:bg-(--color-800)"
            >
                追加
            </button>
        </div>

    </div>
</div>
