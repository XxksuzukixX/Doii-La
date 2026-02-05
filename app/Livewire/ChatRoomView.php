<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

use App\Models\User;
use App\Models\Chat;
use App\Models\ChatRoom;
use App\Models\ChatRoomUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use App\Events\MessageRead;
use App\Events\MessageReceived;
use App\Events\RemoveUsers;



class ChatRoomView extends Component
{
    public ChatRoom $chat_room;

    public $chat_messages;
    public $chat_user_ids;
    public $other_member_user_ids; //自分以外の参加メンバー 
    public $non_member_user_ids; //チャットルームにいないユーザー

    public int $member_count = 0;
    public array $user_ids = [];

    public string $input_message = '';
    public bool $confirming = false; // チャット送信時の警告モーダル
    public string $highlightedMessage = ''; //入力した文字列の個人情報を強調表示

    public bool $no_more_messages = false; //最も古いメッセージに到達したときture
    public int $loaded_count = 0; //無限スクロール更新カウント
    public int $limit = 15;

    protected function getListeners(): array
    {
        return [
            "echo-private:chat.room.{$this->chat_room->id},MessageSent" => 'receiveMessage',
            "echo-private:chat.room.{$this->chat_room->id},RemoveUsers" => 'leftRoom',
            // "echo-private:chat.room.{$this->chat_room->id},MessageRead" => '$refresh',
            "echo-private:chat.room.{$this->chat_room->id},MessageRead",
            // "echo-private:chat.room.{$this->chat_room->id},MessageRead" => 'handle_message_read',
            'addGroupMember' => 'addGroupMember',
            'removeGroupMember' => 'removeGroupMember',
        ];
    }

    public function mount(int $room_id): void
    {
        $this->chat_room = ChatRoom::with('members')->findOrFail($room_id);
        $this->chat_messages = collect(); // メッセージ初期化

        $activeMembers = $this->chat_room->activeMembers;

        // 現在参加中のユーザーID
        $this->chat_user_ids = $activeMembers
            ->pluck('id')
            ->toArray();

        // 自分以外の参加メンバーID
        $this->other_member_user_ids = $activeMembers
            ->where('id', '!=', auth()->id())
            ->pluck('id')
            ->toArray();

        // 参加していないユーザーID
        $this->non_member_user_ids = User::whereNotIn('id', $this->chat_user_ids)
            ->pluck('id')
            ->toArray();
        $this->loadChatMessages();
        $this->markAsRead();

        //メッセージ最下部にスクロール
        $this->dispatch('scroll-to-bottom');
    }

    public function loadChatMessages(): ?int
    {
        // これ以上読み込むものがない場合は何もしない
        if ($this->no_more_messages) {
            return null;
        }

        // 参加日時（これ以前のメッセージは見せない）
        $joinedAt = $this->chat_room
            ->users()
            ->where('user_id', auth()->id())
            ->value('joined_at');

        // 現在表示されている件数
        $loadedCount = $this->chat_messages?->count() ?? 0;

        // 読み込み前の「一番上のメッセージID（スクロール補正用）」
        $firstIdBefore = $this->chat_messages?->first()?->id;

        // 過去メッセージ取得（DB上は新→古）
        $olderMessages = Chat::where('room_id', $this->chat_room->id)
            ->where('created_at', '>=', $joinedAt)
            ->orderByDesc('created_at')
            ->skip($loadedCount)
            ->take($this->limit)
            ->get();

        // もう取得できない場合
        if ($olderMessages->isEmpty()) {
            $this->no_more_messages = true;
            return $firstIdBefore;
        }

        // 表示順を古→新に並び替え
        $olderMessages = $olderMessages->sortBy('created_at');

        // 既存メッセージの前に結合
        $this->chat_messages = $olderMessages->concat(
            $this->chat_messages ?? collect()
        );

        // 読み込み件数更新
        $this->loaded_count += $olderMessages->count();

        return $firstIdBefore;
    }

    //メンバー追加
    public function addGroupMember(array $activeIds){
        if(!$this->chat_room){
            return;
        }

        $my_user = Auth::user();
        // 管理者権限確認
        if(!$my_user->isAdmin()){
            return;
        }

        DB::transaction(function () use ($activeIds, $my_user) {

            // チャットルームにユーザーを追加
            foreach ($activeIds as $userId) {
                ChatRoomUser::updateOrCreate(
                    [
                        'room_id' => $this->chat_room->id,
                        'user_id' => $userId,
                    ],
                    [
                        'joined_at' => now(),
                        'left_at'   => null,
                    ]
                );
            }

            // 追加メンバー名取得
            $add_members = User::whereIn('id', $activeIds)
                ->pluck('name');

            $system_message = $add_members->implode('、') . 'が参加しました。';

            // システムメッセージ送信
            $chat = Chat::create([
                'room_id'     => $this->chat_room->id,
                'type'        => 'system',
                'sender_id'   => $my_user->id,
                'receiver_id' => $my_user->id, // ダミー（暫定）
                'message'     => $system_message,
                'read_flg'    => 0,
            ]);
            // ルームに通知
            broadcast(new MessageSent($chat))->toOthers();
            // 自分以外のグループメンバーに通知
            foreach ($this->chat_room->members as $member) {
                if ($member->id === Auth::id()) continue;
                broadcast(new MessageReceived($chat, $member->id));
            }
        });
        // リダイレクト
        return redirect()->route('chat.room', [
            'room_id' => $this->chat_room->id,
        ]);
    }
    //メンバー削除
    public function removeGroupMember(array $activeIds){
        if(!$this->chat_room){
            return;
        }

        $my_user = Auth::user();
        // 管理者権限確認
        if(!$my_user->isAdmin()){
            return;
        }

        DB::transaction(function () use ($activeIds, $my_user) {

            // チャットルームからユーザーを削除
            $chat_room_user = ChatRoomUser::where('room_id', $this->chat_room->id)
                ->whereIn('user_id', $activeIds)
                ->whereNull('left_at')
                ->update([
                    'left_at' => now(),
                ]);

            // 削除メンバー取得
            $remove_members = User::whereIn('id', $activeIds)->get();
            $remove_members_name = $remove_members->pluck('name');

            $system_message = $remove_members_name->implode('、') . 'が退室しました。';

            // システムメッセージ送信
            $chat = Chat::create([
                'room_id'     => $this->chat_room->id,
                'type'        => 'system',
                'sender_id'   => $my_user->id,
                'receiver_id' => $my_user->id, // ダミー（暫定）
                'message'     => $system_message,
                'read_flg'    => 0,
            ]);
            // ルームに通知
            broadcast(new MessageSent($chat))->toOthers();
            broadcast(new RemoveUsers($remove_members->toArray(), $chat))->toOthers();
            // 自分以外のグループメンバーに通知
            foreach ($this->chat_room->members as $member) {
                if ($member->id === Auth::id()) continue;
                broadcast(new MessageReceived($chat, $member->id));
            }
        });
        
        // リダイレクト
        return redirect()->route('chat.room', [
            'room_id' => $this->chat_room->id,
        ]);
    }
    public function requestSend(): void
    {
        // $this->validate([
        //     'input_message' => ['required', 'string'],
        // ]);

        $validator = Validator::make(
            ['input_message' => $this->input_message],
            ['input_message' => ['required', 'string', 'max:5000']],
            [
                'input_message.required' => 'メッセージを入力してください。',
                'input_message.max'      => 'メッセージは5000文字以内で入力してください。',
            ]
        );
        if ($validator->fails()) {
            // エラーメッセージを取得（最初の1件）
            $message = $validator->errors()->first('input_message');

            // Alpine に渡す
            $this->dispatch('show-error-toast', message: $message);
            return;
        }

        $highlighted = $this->detectAndHighlightSensitiveInfo($this->input_message);

        if ($highlighted !== null) {
            $this->highlightedMessage = $highlighted;
            $this->confirming = true;
            return;
        }

        // 問題なければ即送信
        $this->send();
    }

    public function send(): void
    {
        // 参加していないユーザーは送信不可
        if (! in_array(Auth::id(), $this->chat_user_ids, true)) {
            return;
        }

        // メッセージ送信
        $chat = Chat::create([
            'room_id'   => $this->chat_room->id,
            'sender_id' => Auth::id(),
            'receiver_id'=> Auth::id(), // ← ダミー（暫定）
            'message'   => $this->input_message,
            'read_flg'  => 0,
        ]);
        // ルームに通知
        broadcast(new MessageSent($chat))->toOthers();
        // 自分以外のグループメンバーに通知
        foreach ($this->chat_room->members as $member) {
            if ($member->id === Auth::id()) continue;
            broadcast(new MessageReceived($chat, $member->id));
        }
        // モーダルを閉じる
        $this->confirming = false;

        $this->chat_messages->push($chat);

        $this->input_message = '';

        $this->markAsRead();

        //メッセージ最下部にスクロール
        $this->dispatch('scroll-to-bottom');
    }

    public function detectAndHighlightSensitiveInfo(string $text): ?string
    {

        // $pref = '(東京都|北海道|青森県|岩手県|宮城県|秋田県|山形県|福島県|茨城県|栃木県|群馬県|埼玉県|千葉県|神奈川県|新潟県|富山県|石川県|福井県|山梨県|長野県|岐阜県|静岡県|愛知県|三重県|滋賀県|京都府|大阪府|兵庫県|奈良県|和歌山県|鳥取県|島根県|岡山県|広島県|山口県|徳島県|香川県|愛媛県|高知県|福岡県|佐賀県|長崎県|熊本県|大分県|宮崎県|鹿児島県|沖縄県)';

        $patterns = [
            // メールアドレス
            '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',

            // 電話番号（日本想定）
            '/(\+81[-\s]?)?0\d{1,4}[-\s]?\d{1,4}[-\s]?\d{4}/',

            // 住所系（都道府県 + 市区町村優先判定）
            // 1) 市の例外リスト（必要なら追加）
            '/(.{2,3}?[都道府県])((?:旭川|伊達|石狩|盛岡|奥州|田村|南相馬|那須塩原|東村山|武蔵村山|羽村|十日町|上越|富山|野々市|大町|蒲郡|四日市|姫路|大和郡山|廿日市|下松|岩国|田川|大村)市)/u',

            // 2) 郡 + 町/村
            '/(.{2,3}?[都道府県])(.+?郡.+?[町村])/u',

            // 3) 市 + 区（政令市形式）
            '/(.{2,3}?[都道府県])(.+?市.+?区)/u',

            // 4) 一般的な市区町村
            '/(.{2,3}?[都道府県])(.+?[市区町村])/u',

            // 市から始まる住所
            '/([\p{Han}\p{Hiragana}\p{Katakana}]+市.+?[区町村])/u',
            
            // 郡 + 町/村
            '/([\p{Han}\p{Hiragana}\p{Katakana}]{2,3}?.+?郡.+?[町村])/u',
        ];

        $found = [];
        $normalized = mb_convert_kana($text, 'asrn');
        
        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $normalized, $matches)) {
                foreach ($matches[0] as $match) {
                    $found[] = $match;
                }
            }
        }

        if(!$found){
            return null;
        }

        $highlighted = e($normalized); // HTMLエスケープ

        foreach ($found as $word) {
            // すでにタグがついていない部分だけ置換
            $highlighted = preg_replace(
                '/(?<!>)(?<!<span class="text-red-600 font-semibold bg-red-50 px-0.5 rounded">)' . preg_quote($word, '/') . '(?!<\/span>)/u',
                '<span class="text-red-600 font-semibold bg-red-50 px-0.5 rounded">' . $word . '</span>',
                $highlighted
            );
        }

        return nl2br($highlighted, false);
    }

    public function leftRoom(array $payload)
    {
        $removed_users = $payload['removed_users'];
        foreach($removed_users as $removed_user){

            // 自分のidが削除対象に含まれている
            if($removed_user['id'] == Auth::id()){
                //チャット一覧へリダイレクト
                return redirect()->route('chat.list');
            }
        }

    }
    

    public function receiveMessage(array $payload): void
    {
        if ($payload['chat']['sender_id'] === Auth::id()) {
            return;
        }

        $this->chat_messages->push(
            Chat::find($payload['chat']['id'])
        );

        $this->markAsRead();

        //メッセージ最下部にスクロール
        $this->dispatch('scroll-to-bottom');
    }
    public function leftChatRoom()
    {
        $my_user = Auth::user();
        $user_id = $my_user->id;

        $room_id = $this->chat_room->id;

        if(!$user_id || !$room_id){
            return;
        }
        DB::transaction(function () use ($user_id, $my_user, $room_id) {
            // left_atに打刻
            ChatRoomUser::where('room_id', $room_id)
                ->where('user_id', $user_id)
                ->update([
                    'left_at' => now(),
                ]);

            $system_message = $my_user->name . 'が退室しました。';
            // システムメッセージ送信
            $chat = Chat::create([
                'room_id'     => $this->chat_room->id,
                'type'        => 'system',
                'sender_id'   => $my_user->id,
                'receiver_id' => $my_user->id, // ダミー（暫定）
                'message'     => $system_message,
                'read_flg'    => 0,
            ]);
            // ルームに通知
            broadcast(new MessageSent($chat))->toOthers();
            // 自分以外のグループメンバーに通知
            foreach ($this->chat_room->members as $member) {
                if ($member->id === Auth::id()) continue;
                broadcast(new MessageReceived($chat, $member->id));
            }
        });

        //チャット一覧へリダイレクト
        return redirect()->route('chat.list');
        
    }

    public function markAsRead(): void
    {
        $user_id = Auth::id();
        $room_id = $this->chat_room->id;

        // 未読メッセージを既読にする
        $this->incrementReadCount();

        // 最終既読メッセージIDを更新
        $last_chat = Chat::where('room_id', $room_id)
            ->orderByDesc('id')
            ->first();

        if ($last_chat) {
            ChatRoomUser::where('room_id', $room_id)
                ->where('user_id', $user_id)
                ->update([
                    'last_read_chat_id' => $last_chat->id,
                    'last_read_at'      => now(),
                ]);
        }
    }

    protected function incrementReadCount(): void
    {
        $user_id = Auth::id();
        $room_id = $this->chat_room->id;

        $my_member = ChatRoomUser::where('room_id', $room_id)
            ->where('user_id', $user_id);

        $joined_at = $my_member->value('joined_at');

        $last_read_id = $my_member->value('last_read_chat_id') ?? 0;

        $unread_chats = Chat::where('room_id', $room_id)
            ->where('created_at', '>=', $joined_at) // ユーザーが追加された
            ->where('id', '>', $last_read_id)   // このユーザーがまだ読んでいないチャット
            ->where('sender_id', '!=', $user_id)
            ->get();

        foreach ($unread_chats as $chat) {
            // 既読カウントをインクリメント
            $chat->increment('read_count');

            // Livewire が保持しているコレクションも更新
            if ($this->chat_messages) {
                $this->chat_messages
                    ->where('id', $chat->id)
                    ->first()
                    ?->setAttribute(
                        'read_count',
                        $chat->read_count + 1
                    );
            }
        }
        // 既読をリスナーに通知
        if ($unread_chats->isNotEmpty()) {
            broadcast(new MessageRead(
                $this->chat_room->id,
                $unread_chats->pluck('id')->all()
            ))->toOthers();
        }

        $last_chat = $unread_chats->last();
        if ($last_chat) {
            ChatRoomUser::where('room_id', $room_id)
                ->where('user_id', $user_id)
                ->update([
                    'last_read_chat_id' => $last_chat->id,
                    'last_read_at'      => now(),
                ]);
        }
    }

    public function render()
    {

        $my_member = $this->chat_room->users->firstWhere('id', auth()->id());

        $room_members = $this->chat_room->room_users->whereNull('left_at');
        $this->user_ids = $room_members 
            ->pluck('user_id')
            ->all();
        //現在参加中のメンバーをカウント
        $this->member_count = $room_members->count();
        $last_read_chat_id = $my_member?->pivot->last_read_chat_id ?? 0; //最終既読メッセージのid
        $last_read_chat_ids = [];

        foreach ($room_members as $member) {
            // ルームメンバー全員の最終既読メッセージid
            $last_read_chat_ids[] = $member->pivot->last_read_chat_id ?? 0;
        }

        // dd($last_read_chat_ids);
        // dd($this->chat_messages->pluck('id'));

        return view('livewire.chat-room-view', [
            'last_read_chat_id' => $last_read_chat_id,
        ]);
    }
}
