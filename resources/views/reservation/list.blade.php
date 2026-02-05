<x-app title="募集一覧">
    <x-header_sidebar>
        @if(Auth::user()->isAdmin())
            <x-admin.panel title="募集管理">
                <x-slot:actions>
                    <a
                        href="{{ route('reservation.create') }}"
                        class="
                            h-9 px-5 rounded-lg bg-(--color-500) hover:bg-(--color-600) text-white text-md
                            flex items-center justify-center
                    ">
                        新規作成
                    </a>
                </x-slot:actions>
            </x-admin-panel>
        @endif

        <div class="max-w-200 w-[90%] mx-auto py-10">

            <!-- タイトル行 -->
            <div class="flex justify-between items-end pb-3">
                <h1 class="text-2xl font-semibold text-left">
                    募集一覧
                </h1>

            </div>

            <!-- 一覧 -->
            <div class="p-5 space-y-4">
                <livewire:reservation-search />
            </div>

        </div>

    </x-header_sidebar>
</x-app>