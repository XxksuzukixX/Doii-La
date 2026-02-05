<div>
    @if($showModal)
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        wire:click.self="close"
    >

        {{-- モーダル本体 --}}
        <div class="w-full max-w-3xl max-h-[90vh] bg-white rounded-lg shadow-lg p-5 flex flex-col"
            wire:keydown.enter.prevent>

            {{-- ヘッダー --}}
            <div class="flex justify-between items-center pb-2 mb-4">
                <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
                <button type="button" wire:click="close" class="text-2xl text-gray-500 hover:text-gray-700">×</button>
            </div>

            {{-- 検索 --}}
            <div class="mb-2">
                <input type="text"
                    placeholder="検索..."
                    wire:model.live.debounce.300ms="keyword"
                    class="w-full h-9.5 px-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-(--color-500)">
            </div>

            {{-- ユーザー一覧 --}}
            <div class="flex-1 min-h-0 flex flex-col space-y-1">
                <div class="grid grid-cols-[48px_1fr_2fr] items-center gap-4 px-2 min-h-8.75 text-sm text-gray-700">
                    <div></div>
                    <div>名前</div>
                    @if ($showEmail)
                        <div>メール</div>
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto">
                    @foreach($items as $item)
                        <div wire:click="toggleActivate({{ $item->id }})"
                            class="grid grid-cols-[48px_1fr_2fr] items-center gap-4 px-2 min-h-15 cursor-pointer
                                {{ (!$hideActiveOnUi && in_array($item->id, $activeIds, true)) 
                                    ? 'bg-(--color-500) text-white' 
                                    : 'hover:bg-(--color-50)' }}">
                            <x-user-icon :icon-path="$item->icon_path ?? null" size="w-12 h-12" />
                            <div>{{ $item->{$displayFieldName} }}</div>
                            @if ($showEmail)
                                <div>{{ $item->{$emailFieldName} ?? '' }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ページング --}}
            <div class="pt-3">
                {{ $items->links('livewire::page') }}
            </div>


            {{-- フッター --}}
            @if($showFooter)
                <div class="mt-4 pt-4 flex justify-end gap-3 border-t border-(--color-500)">
                    <button type="button" wire:click="close"
                            class="h-8.75 px-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                        キャンセル
                    </button>

                    <button type="button" wire:click="executeNext"
                            class="h-8.75 px-3 rounded-lg bg-(--color-500) text-white hover:bg-(--color-600)">
                        {{$nextButtonLabel ?? '次の処理'}}
                    </button>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
