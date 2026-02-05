<x-app title="募集情報編集">
    <x-header_sidebar>
        <div class="max-w-200 w-[90%] mx-auto py-10">
            
            <h1 class="text-2xl font-semibold pb-3 text-left">
                募集情報編集
            </h1>

            {{-- 編集 --}}
            <livewire:reservation-form :reservation="$reservation" />

        </div>
    </x-header_sidebar>
</x-app>