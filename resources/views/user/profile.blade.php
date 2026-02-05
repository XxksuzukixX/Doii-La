<x-app title="ユーザー情報">
    <x-header_sidebar>

        <div class="max-w-200 w-[90%] mx-auto py-10">

            <!-- タイトル -->
            <h1 class="text-2xl font-semibold mb-6">
                ユーザー情報
            </h1>

            <!-- 情報カード -->
            <div class="
                content_frame
                bg-white shadow-md 
                rounded-xl px-8 py-6 space-y-8
                border border-(--color-50)/50"
            ">

                <!-- ユーザーアイコン＋基本情報 -->
                <div class="flex items-center gap-6">
                    <!-- アイコン -->
                    <x-user-icon :icon-path="Auth::user()->icon_path" />

                    <!-- 名前・権限 -->
                    <div>
                        <div class="text-xl font-medium">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            {{ Auth::user()->admin_flg ? '管理者' : '一般ユーザー' }}
                        </div>
                    </div>
                </div>

                <!-- メールアドレス -->
                <div>
                    <label class="block text-sm text-gray-800 mb-1">
                        メールアドレス
                    </label>
                    <span class="text-lg ml-2">
                        {{ Auth::user()->email }}
                    </span>
                    @if (!Auth::user()->hasVerifiedEmail())
                        <div class="text-red-600 text-sm pl-2">
                            *メールアドレスが未承認のため使用できる機能を制限しています。
                            <a href="{{ route('verification.notice') }}" class=" underline">こちら</a>
                            からメール認証を完了してください。
                        </div>
                    @endif
                </div>

                <!-- テーマカラー -->
                <div>
                    <label class="block text-sm text-gray-800 mb-2">
                        テーマカラー
                    </label>

                    <div class="flex items-center gap-4 ml-2">
                        <div
                            class="w-8 h-8 rounded-full
                                   bg-(--color-500)"
                        ></div>
                        <span class="text-lg">
                            {{ Auth::user()->theme }}
                        </span>
                    </div>
                </div>

                <!-- 編集リンク -->
                <div class="pt-4">
                    <a href="/user/edit"
                       class="text-(--color-600)
                              hover:underline text-sm">
                        ユーザー情報を変更する
                    </a>
                </div>

            </div>
        </div>

    </x-header_sidebar>
</x-app>