<x-app title="ログイン">
    <x-header_sidebar>
        <div class="max-w-200 w-[90%] mx-auto pt-10">

            <h1 class="text-2xl font-semibold pb-3 text-left">
                ログイン
            </h1>

            <form method="POST" action="{{ route('login') }}" class="px-5">
                @csrf

                <x-input_unit
                    label="メールアドレス"
                    type="email"
                    name="email"
                    value=""
                />

                <x-input_unit
                    label="パスワード"
                    type="password"
                    name="password"
                    value=""
                />

                <x-button_unit>
                    ログイン
                </x-button_unit>
                
            </form>

            <p class="text-center text-lg pt-2">
                アカウント新規登録は
                <a href="/register" class="text-indigo-700 hover:underline">
                    こちら
                </a>
            </p>

        </div>
    </x-header_sidebar>
</x-app>