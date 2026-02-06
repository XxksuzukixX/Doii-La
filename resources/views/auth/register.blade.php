<x-app title="新規登録">
    <x-header_sidebar>
        <div class="max-w-200 w-[90%] mx-auto pt-10">

            <h1 class="text-2xl font-semibold pb-3 text-left">
                新規登録
            </h1>

            <form method="POST" action="{{ route('register.show') }}" class="px-5">
                @csrf

                <x-input_unit
                    label="メールアドレス"
                    type="email"
                    name="email"
                    id="email"
                    value=""
                />

                <x-input_unit
                    label="パスワード"
                    type="password"
                    name="password"
                    id="password"
                    value=""
                />

                <x-input_unit
                    label="パスワード確認"
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    value=""
                />

                <x-input_unit
                    label="ユーザー名"
                    type="text"
                    name="name"
                    id="name"
                    value=""
                />

                <x-button_unit>
                    登録
                </x-button_unit>
            </form>

            <p class="text-center text-lg pt-2">
                パスワードを忘れてしまった場合は
                <a href="/forget" class="text-indigo-700 hover:underline">
                    こちら
                </a>
            </p>

        </div>
    </x-header_sidebar>
</x-app>