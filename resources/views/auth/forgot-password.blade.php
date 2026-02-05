<x-app title="パスワードの変更">
    <x-header_sidebar>
        <div class="max-w-200 w-[90%] mx-auto pt-10">

            <h1 class="text-2xl font-semibold pb-3 text-left">
                パスワードの変更
            </h1>

            {{-- <form method="POST" action="#" class="px-5"> --}}
            <form method="POST" action="{{ route('password.email') }}" class="px-5">
                @csrf

                <x-input_unit
                    label="本サービスでお使いのメールアドレス"
                    type="email"
                    name="email"
                    id="email"
                    value=""
                />
                @if (session('status'))
                    <p class="text-md text-indigo-700">
                        *受信したメールのURLからパスワード変更手続きを行ってください。
                    </p>
                @endif

                <x-button_unit>
                    送信
                </x-button_unit>
            </form>


        </div>
    </x-header_sidebar>
</x-app>
