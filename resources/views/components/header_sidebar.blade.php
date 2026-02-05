<div class="min-h-screen max-w-5xl mx-auto flex flex-col shadow-xl">

    {{-- Header --}}
    <header
        class="
            fixed inset-x-0 top-0 z-50
            sm:static
            h-25
            flex items-center
            bg-neutral-900  text-white
            px-6 sm:px-8
        "
    >
        {{-- Logo --}}
        <div class="flex items-center gap-2 p-2">
            <a 
                href="/home" 
                class="
                    w-14 h-14 p-0.5
                    rounded-xl
                    bg-green-100

                    border border-white/20
                    shadow-[0_4px_12px_rgba(0,0,0,0.4)]
                    overflow-hidden
                    flex items-center justify-center
            ">
                <img
                    src="{{ asset('img/Doii-La.png') }}"
                    alt="{{config('app.name')}}"
                    class="w-full h-full object-contain"
                >
            </a>

            <a href="/home" class="text-4xl font-bold tracking-tight text-green-100 border-b-3  py-1">
                {{config('app.name')}}
            </a>
        </div>
        

        {{-- Right --}}
        <div class="flex-1 flex justify-end items-center gap-3">
            {{-- Hamburger --}}
            <button
                id="menuButton"
                class="p-2 md:hidden"
                aria-label="menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            @auth
                <div class="hidden md:flex items-center gap-4">
                    <a
                        href="/user/profile"
                        class="px-4 py-1 rounded-lg bg-(--color-600) hover:bg-(--color-700)"
                    >
                        {{ Auth::user()->name }}
                    </a>
                    <span>ログイン中</span>
                </div>
            @endauth
        </div>
    </header>

    {{-- Overlay --}}
    <div
        id="overlay"
        class="fixed inset-0 z-40 bg-black/50 hidden sm:hidden"
    ></div>

    {{-- Body --}}
    <div class="flex flex-1 pt-25 sm:pt-0">

        {{-- Sidebar --}}
        <aside
            id="sidebar"
            class="
                fixed left-0 top-25 z-40
                h-screen w-62
                px-8 py-8
                bg-neutral-700 text-white text-lg font-semibold
                transform transition-transform
                -translate-x-full
                md:static md:h-auto md:translate-x-0
            "
        >
            <nav class="flex flex-col gap-4 sm:gap-5">

                @auth
                    @if(Auth::user()->hasVerifiedEmail())
                        <x-nav.link route="dashboard">トップページ</x-nav.link>
                        <x-nav.link route="reservation.list">募集一覧</x-nav.link>
                        <x-nav.link route="chat.list">チャット</x-nav.link>
                    @else
                        <x-nav.link route="verification.notice">メール認証</x-nav.link>
                    @endif

                    <x-nav.link route="user.profile">ユーザー情報</x-nav.link>
                @endauth

                @guest
                    <x-nav.link route="login">ログイン</x-nav.link>
                    <x-nav.link route="register.show">新規登録</x-nav.link>
                    <x-nav.link route="forgot.show">パスワードの変更</x-nav.link>
                @endguest

                @auth
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="mt-auto pt-6 border-t border-white/20"
                    >
                        @csrf
                        <button
                            type="submit"
                            class="w-full text-left text-red-300 hover:text-red-400"
                        >
                            ログアウト
                        </button>
                    </form>
                @endauth
            </nav>
        </aside>

        {{-- Main --}}
        <main 
            class="
                flex-1 flex justify-center bg-white bg-pattern-dots
            " >
            <div class="w-full max-w-200">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

{{-- 
<script>
document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    if (!menuButton || !sidebar || !overlay) return;

    menuButton.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
});
</script> --}}