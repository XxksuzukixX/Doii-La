<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">

            {{-- モーダル本体 --}}
            <div class="w-full max-w-3xl max-h-[90vh] bg-white rounded-lg shadow-lg p-5 flex flex-col"
                wire:keydown.enter.prevent>
                <form wire:submit.prevent="save" clsaa="w-full">
                    @csrf

                    {{-- ヘッダー --}}
                    <div class="flex justify-between items-center pb-2 ">
                        <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
                        <button type="button" wire:click="close" class="text-2xl text-gray-500 hover:text-gray-700">×</button>
                    </div>
                    <div class="space-y-2 pb-4">
                        <x-input_unit
                            label="グループ名"
                            type="text"
                            name="group_name"
                            wire:model.live="group_name"
                        />
                        <livewire:image-cropper
                            label="グループアイコン"
                            name="icon_cropped"
                            :icon-path="$icon_path"
                        />
                    </div>

                    {{-- フッター --}}
                    {{-- @if($showFooter) --}}
                        <div class="mt-4 pt-4 flex justify-end gap-3 border-t border-(--color-500)">
                            <button type="button" wire:click="close"
                                    class="h-8.75 px-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                キャンセル
                            </button>

                            <button type="submit"
                            {{-- <button type="button" wire:click="executeNext" --}}
                                    class="h-8.75 px-3 rounded-lg bg-(--color-500) text-white hover:bg-(--color-600)">
                                変更
                            </button>
                        </div>
                    {{-- @endif --}}
                </form>
            </div>

        </div>
    @endif
</div>
