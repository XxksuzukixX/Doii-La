<x-app title="パスワード再設定">
    <x-header_sidebar>
        <div class="max-w-200 w-[90%] mx-auto pt-10">

            <h1 class="text-2xl font-semibold pb-3 text-left">
                パスワード再設定
            </h1>

            <form method="POST" action="{{ route('password.update') }}" class="px-5">
                @csrf

                <x-input_unit
                    label="メールアドレス"
                    type="email"
                    name="email"
                    id="email"
                    value="{{ $request->email }}"
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

                {{-- <input type="hidden" name="token" > --}}
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <x-button_unit>
                    再設定
                </x-button_unit>
            </form>

        </div>
    </x-header_sidebar>
</x-app>