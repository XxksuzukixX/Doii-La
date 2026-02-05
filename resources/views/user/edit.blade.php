<x-app title="ユーザー情報編集">
    <x-header_sidebar>

        <div class="max-w-200 w-[90%] mx-auto py-10">

            <!-- タイトル -->
            <h1 class="text-2xl font-semibold mb-6">
                ユーザー情報編集
            </h1>
            <div id="jsDebug" class="fixed top-2 left-2 bg-yellow-200 text-black p-2 rounded z-50 hidden"></div>

            <!-- フォームカード -->

            <form
                action="/user/edit"
                method="POST"
                enctype="multipart/form-data"
                class="content_frame
                       bg-white shadow-md rounded-xl
                       px-8 py-6 space-y-6 max-w-full"
            >
                @csrf

                
                <!-- ユーザーアイコン -->
                <div>
                    <label class="block text-sm text-gray-800 mb-2">
                        ユーザーアイコン
                    </label>

                    <div class="flex items-center gap-6 flex-wrap">
                        <!-- 現在のアイコン -->
                        <div id="currentIcon">
                            <x-user-icon :icon-path="Auth::user()->icon_path" />
                        </div>

                        <!-- 新アイコンプレビュー -->
                        <div>
                            <img id="preview" class="w-20 h-20 rounded-full object-cover hidden">
                        </div>

                        <!-- ファイル選択 -->
                        <label for="iconInput"
                            class="cursor-pointer inline-flex items-center px-4 py-2 
                                    bg-(--color-500) text-white rounded-lg h-8.75 
                                    hover:bg-(--color-600)"
                        >
                            画像を変更
                        </label>
                        <input
                            type="file"
                            id="iconInput"
                            accept="image/*"
                            class="hidden"
                        >
        
                        <!-- トリミング後画像を送るため -->
                        <input type="hidden" name="icon_cropped" id="iconCropped">
                    </div>
                    
                </div>
                {{-- トリミング画面用モーダル --}}
                <div
                    id="cropModal"
                    class="fixed inset-0 z-50 hidden
                        flex items-center justify-center
                        bg-black/50"
                >
                    <div
                        class="bg-white rounded-xl shadow-lg
                            w-[90vw] max-w-105
                            max-h-[90vh]
                            flex flex-col"
                    >
                        {{-- ヘッダー --}}
                        <div class="px-4 py-3 border-b border-(--color-500) text-lg font-semibold text-gray-800">
                            画像のトリミング
                        </div>

                        {{-- Cropper 表示領域 --}}
                        <div
                            class="flex-1 p-4
                                overflow-hidden
                                flex items-center justify-center"
                        >
                            <div
                                class="w-full aspect-square
                                    max-h-[60vh]"
                            >
                                <img
                                    id="cropTarget"
                                    class="block max-w-full max-h-full"
                                >
                            </div>
                        </div>

                        {{-- フッター --}}
                        <div class="px-4 py-3 border-t border-(--color-500) flex justify-end gap-2">
                            <button
                                type="button"
                                id="cropCancel"
                                class="h-8.75 px-3 rounded-lg
                                    border border-gray-300
                                    text-gray-700
                                    hover:bg-gray-100"
                            >
                                キャンセル
                            </button>

                            <button
                                type="button"
                                id="cropConfirm"
                                class="h-8.75 px-3 rounded-lg
                                    bg-(--color-700) text-white
                                    hover:bg-(--color-800)"
                            >
                                決定
                            </button>
                        </div>
                    </div>
                </div>
                {{-- <livewire:icon-editor 
                    :icon="Auth::user()->icon_path" 
                    input-name="icon_cropped" 
                /> --}}
                

                <!-- ユーザー名 -->
                <x-input_unit
                    label="ユーザー名"
                    type="text"
                    name="name"
                    value="{{ Auth::user()->name }}"
                />

                <!-- メールアドレス -->
                <x-input_unit
                    label="メールアドレス"
                    type="email"
                    name="email"
                    value="{{ Auth::user()->email }}"
                />

                <!-- テーマカラー -->
                <div>
                    <label class="block text-sm text-gray-800 mb-2">
                        テーマカラー
                    </label>

                    <div class="flex items-center gap-4">
                        <select
                            name="theme"
                            class="rounded-lg border border-gray-300
                                   px-3 text-lg h-8.75
                                   focus:outline-none focus:ring-2
                                   focus:ring-(--color-500)"
                        >
                            @foreach (['red','amber','lime','emerald','cyan','blue','indigo','purple','pink','slate','zinc'] as $color)
                                <option
                                    value="{{ $color }}"
                                    data-theme="{{ $color }}"
                                    class="text-(--color-600)"
                                    @selected(Auth::user()->theme === $color)
                                >
                                    {{ $color }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 更新ボタン -->
                <x-button_unit>
                    更新
                </x-button_unit>
            </form>
        </div>

    </x-header_sidebar>
</x-app>



