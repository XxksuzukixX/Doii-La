{{-- // resources/views/livewire/icon-cropper.blade.php --}}

<div wire:ignore>
    <div
        x-data="iconCropper()"
    >
        <!-- ユーザーアイコン -->
        <div>
            <label class="block text-sm text-gray-800 mb-2">
                {{ $label }}
            </label>

            <div class="flex items-center gap-6 flex-wrap">
                <!-- 現在のアイコン -->
                <div x-show="!preview">
                    <x-user-icon :icon-path="$iconPath" />
                </div>

                <!-- 新アイコンプレビュー -->
                <div>
                    <img
                        x-show="preview"
                        :src="preview"
                        class="w-20 h-20 rounded-full object-cover"
                    >
                </div>

                <!-- ファイル選択 -->
                <label
                    class="cursor-pointer inline-flex items-center px-4 py-2
                        bg-(--color-500) text-white rounded-lg h-8.75
                        hover:bg-(--color-600)"
                >
                    画像を変更
                    <input
                        type="file"
                        accept="image/*"
                        class="hidden"
                        x-on:change="load"
                    >
                </label>
            </div>
        </div>

        <!-- トリミングモーダル -->
        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50
                flex items-center justify-center
                bg-black/50"
        >
            <div class="bg-white rounded-xl shadow-lg w-[90vw] max-w-105 flex flex-col">
                <div class="px-4 py-3 border-b text-lg font-semibold">
                    画像のトリミング
                </div>

                <div class="flex-1 p-4 flex items-center justify-center">
                    <div class="w-full aspect-square max-h-[60vh]">
                        <img x-ref="image" class="block max-w-full max-h-full">
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-(--color-500) flex justify-end gap-2">
                    <button
                        type="button"
                        x-on:click="cancel"
                        class="h-8.75 px-3 rounded-lg
                            border border-gray-300
                            text-gray-700
                            hover:bg-gray-100"
                    >
                        キャンセル
                    </button>

                    <button
                        type="button"
                        x-on:click="crop"
                        class="h-8.75 px-3 rounded-lg
                            bg-(--color-500) text-white
                            hover:bg-(--color-600)"
                    >
                        決定
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
