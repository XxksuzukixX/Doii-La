<div class="min-h-screen bg-gray-200 flex flex-col">

    {{-- Header --}}
    <header
        class="
            fixed sm:static top-0 inset-x-0 z-50
            h-16 sm:h-25
            bg-neutral-900 text-white
            flex items-center
            px-4 sm:px-6 lg:px-10
        "
    >
        {{-- 左（sm未満では空） --}}
        <div class="flex-1 sm:flex-none"></div>

        {{-- ロゴ（中央） --}}
        <div class="absolute sm:static left-1/2 sm:left-auto -translate-x-1/2 sm:translate-x-0">
            <a href="/home" class="text-xl sm:text-4xl font-bold">
                Simple Reservation
            </a>
        </div>

        {{-- 右 --}}
        <div class="flex-1 flex justify-end items-center gap-3">

            {{-- ハンバーガー（sm未満のみ） --}}
            <button
                wire:click="toggleSidebar"
                class="sm:hidden p-2"
                aria-label="menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- ログアウト（sm以上） --}}
            @auth
                <form method="POST" action="{{ route('logout') }}"
                      class="hidden sm:flex items-center gap-3">
                    @csrf
                    <span class="font-bold">{{ Auth::user()->name }}</span>
                    <button
                        type="submit"
                        class="py-1 px-4 rounded-lg bg-indigo-600 hover:bg-indigo-700"
                    >
                        ログアウト
                    </button>
                </form>
            @endauth
        </div>
    </header>

    {{-- Overlay（sm未満のみ） --}}
    @if($sidebarOpen)
        <div
            wire:click="closeSidebar"
            class="fixed inset-0 bg-black/50 z-40 sm:hidden"
        ></div>
    @endif

    {{-- Body --}}
    <div class="flex flex-1 pt-16 sm:pt-0">

        {{-- Sidebar --}}
        <aside
            class="
                fixed sm:static top-0 left-0 z-50
                h-full w-64 sm:w-52
                bg-neutral-700 text-white
                px-4 py-5
                transform transition-transform
                {{ $sidebarOpen ? 'translate-x-0' : '-translate-x-full' }}
                sm:translate-x-0
            "
        >
            <nav class="space-y-4 text-lg">
                @auth
                    <a href="/home" class="block">トップページ</a>
                    <a href="/reservation/list" class="block">予約</a>
                    <a href="/user/profile" class="block">ユーザー情報</a>
                    <a href="/chat/list" class="block">チャット</a>
                @endauth

                @guest
                    <a href="/login" class="block">ログイン</a>
                    <a href="/register" class="block">新規登録</a>
                @endguest
            </nav>
        </aside>

        {{-- Main --}}
        <main class="flex-1 bg-neutral-50 flex justify-center">
            <div class="flex-1 max-w-200 w-full px-4 sm:px-0">
                {{-- {{ $slot }} --}}
            </div>
        </main>

    </div>
</div>
