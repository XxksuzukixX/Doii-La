<x-app title="チャット">
    <x-header_sidebar>

        {{-- <livewire:chat-room :partner="$chat_partner" /> --}}

        <livewire:chat-room-view :room_id="$chat_room->id" />

    </x-header_sidebar>
</x-app>