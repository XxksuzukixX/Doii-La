<?php

namespace App\Http\Controllers;
use App\Models\Chat;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;


class ChatController extends Controller
{
    //
    public function list()
    {
        $user_id = Auth::id();
        $chats = Chat::with('sender')
        ->where('receiver_id', $user_id)
        ->latest()
        ->get()
        ->unique('sender_id'); // 送信者ごとに1件

        return view('chat.list', compact('chats'));
    }
    public function room(int $room_id)
    {
        $chat_room = ChatRoom::with('members')->findOrFail($room_id);


        // $user_id = auth()->id();

        // $messages = Chat::with('sender')
        // ->where(function ($query) use ($user_id, $partner_id) {
        //     $query->where('sender_id', $partner_id)
        //         ->where('receiver_id', $user_id);
        // })
        // ->orWhere(function ($query) use ($user_id, $partner_id) {
        //     $query->where('sender_id', $user_id)
        //         ->where('receiver_id', $partner_id);
        // })
        // ->orderBy('created_at')
        // ->get();
        // $chat_partner = User::find($partner_id);

        // return view('chat.room', compact('messages', 'chat_room'));
        return view('chat.room', compact('chat_room'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'sender_id'   => ['required', 'exists:users,id'],
            'receiver_id' => ['required', 'exists:users,id'],
            'message'     => ['required', 'string'],
        ]);

        DB::transaction(function () use ($request) {
            $chat = Chat::create([
                'sender_id'   => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'message'     => $request->message,
            ]);

            broadcast(new MessageSent($chat))->toOthers();
        });

        return response()->noContent();
    }
}
