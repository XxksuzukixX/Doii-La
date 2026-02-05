<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Chat;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Collection;

class ChatList extends Component
{
    use WithPagination;
    public Collection  $chat_rooms;
    protected $queryString = [];
    public Collection $room_member_counts;

    // 表示件数
    public int $limit = 10;
    // 検索キーワード
    public string $keyword = '';
    // 選択ユーザー
    public int $active_user_id = 0;
    public array $active_user_ids = [];
    // 個人チャット可能なユーザーID
    public array $chat_user_ids = [];
    // ユーザーID
    public array $user_ids = [];
    // グループ名
    public string $group_name = '';
    // トリミング画像
    public string $icon_cropped = '';

    public function mount()
    {
        
    }
    //ブロードキャスト購読
    protected function getListeners()
    {
        $user_id = Auth::id();

        return [
            "echo-private:user.{$user_id},MessageReceived" => 'receiveMessage', //個人チャンネルを聴講
            'makeChatRoom' => 'makeChatRoom', // UserSelectModal から呼び出されるメソッド名
            'makeGroupChatRoom' => 'makeGroupChatRoom', // UserSelectModal から呼び出されるメソッド名
            'openMakeGroup' => 'openMakeGroup', // UserSelectModal から呼び出されるメソッド名
        ];
    }

    public function receiveMessage(array $payload)
    {
        // メッセージを Livewire コレクションに追加
        $this->chat_rooms->push(ChatRoom::find($payload['chat']['room_id']));
    }

    // 追加読み込み
    public function loadMore()
    {
        $this->limit += 10;
    }

    public function activateUser(int $selected_user_id): void
    {
        $this->active_user_id = $selected_user_id;
    }
    public function toggleActivateUser(int $selected_user_id): void
    {
        if (($key = array_search($selected_user_id, $this->active_user_ids, true)) !== false) {
            // 削除
            unset($this->active_user_ids[$key]);
            // インデックスを詰める
            $this->active_user_ids = array_values($this->active_user_ids);
        } else {
            // 追加
            $this->active_user_ids[] = $selected_user_id;
        }
    }
    
    public function enterChatRoom(int $active_user_id)
    {
        if($active_user_id == null){
            return;
        }
        return redirect()->route('chat.room', ['partnerId' => $active_user_id]);
    }

    #[On('makeChatRoom')]
    public function makeChatRoom(array $activeIds)
    {
        $my_user_id = Auth::id();
        
        // [] または 2件以上なら return
        if (count($activeIds) !== 1) {
            return;
        }

        $active_user_id = $activeIds[0] ?? 0;

        if($my_user_id == $active_user_id ){
            return;
        }

        /**
         * 既存の 1対1 private ルームを探す
         */
        $existing_room = ChatRoom::where('type', 'private')
            ->whereHas('members', function ($q) use ($my_user_id) {
                $q->where('users.id', $my_user_id);
            })
            ->whereHas('members', function ($q) use ($active_user_id) {
                $q->where('users.id', $active_user_id);
            })
            ->withCount('members')
            ->having('members_count', 2)
            ->first();

        if ($existing_room) {
            return redirect()->route('chat.room', [
                'room_id' => $existing_room->id,
            ]);
        }
        //ルーム作成
        $chat_room = ChatRoom::create([
            'type'        => 'private',
            'created_by'  => Auth::id(),
        ]);
  
        $chat_room->members()->attach($my_user_id, [
            'role' => 'member',
        ]);

        $chat_room->members()->attach($active_user_id, [
            'role' => 'member',
        ]);

        // return redirect()->route('chat.room', ['partnerId' => $active_user_id]);
        return redirect()->route('chat.room', [
            'room_id' => $chat_room->id,
        ]);
    }

    #[On('openMakeGroup')]
    public function openMakeGroup(array $activeIds)
    {
        $user = auth()->user();
        //管理者権限確認
        if (!$user->isAdmin() || empty($activeIds)) {
            return;
        }

        $user_id = $user->id;
        //自身のidが含まれていなければ追加
        if(!in_array($user_id,  $activeIds)){
            $activeIds[] = $user_id;
        }

        // 選択されたユーザーを保持
        $this->active_user_ids = $activeIds;


        
        // グループチャット作成モーダルを開く
        // $this->dispatch('open-make-group-modal');
        $this->dispatch('open-make-group-modal', ['userIds' => $this->active_user_ids]);
        
    }

    #[On('makeGroupChatRoom')]
    public function makeGroupChatRoom(array $activeIds)
    {
        // dd($activeIds);
        $my_user_id = Auth::id();
        if(!auth()->user()->isAdmin() || empty($activeIds)){
            return;
        }
        $this->validate([
            'group_name' => ['required', 'string', 'max:255'],
        ],[
            'group_name.required' => 'グループ名は必須です。',
            'group_name.max'      => 'グループ名は255文字以内で入力してください。',
        ]);

        //ルーム作成
        $chat_room = ChatRoom::create([
            'type'        => 'group',
            'name'        => 'テストグループ',
            // 'name'        => $this->group_name,
            'created_by'  => $my_user_id,
        ]);
        //作成者をオーナーとして追加
        $chat_room->members()->attach($my_user_id, [
            'role' => 'owner',
        ]);

        // ユーザーを追加（重複を避ける）
        $users_to_add = array_unique($activeIds);
        foreach ($users_to_add as $user_id) {
            if ($user_id !== null && $user_id != $my_user_id) {
                $chat_room->members()->syncWithoutDetaching([
                    $user_id => ['role' => 'member']
                ]);
            }
        }

        return redirect()->route('chat.room', [
            'room_id' => $chat_room->id,
        ]);

    }

    public function render()
    {
        $my_user_id = Auth::id();
   
        // チャットルーム一覧
        $this->chat_rooms = ChatRoom::with('latestChat')
            ->withCount('activeMembers')
            ->whereHas('room_users', fn ($q) =>
                $q->where('user_id', $my_user_id)
                ->whereNull('left_at')
            )
            ->where(fn ($q) => $q
                ->whereHas('latestChat')
                ->orWhere('chat_rooms.type', 'group')
            )
            // ->select('chat_rooms.*')
            ->addSelect([
                // 未読数
                'unread_count' => function ($query) use ($my_user_id) {
                    $query->from('chat_room_users')
                        ->join('chats', 'chats.room_id', '=', 'chat_room_users.room_id')
                        ->whereColumn('chat_room_users.room_id', 'chat_rooms.id')
                        ->where('chat_room_users.user_id', $my_user_id)
                        // ->where('chats.type', 'message')
                        ->where(function ($q) {
                            $q->whereColumn('chats.id', '>', 'chat_room_users.last_read_chat_id')
                            ->orWhereNull('chat_room_users.last_read_chat_id');
                        })
                        ->whereColumn('chats.created_at', '>', 'chat_room_users.joined_at')
                        ->selectRaw('COUNT(chats.id)');
                },
                // ソート用日時
                'sort_datetime' => function ($query) {
                    $query->from('chats')
                        ->whereColumn('chats.room_id', 'chat_rooms.id')
                        ->selectRaw('MAX(chats.created_at)');
                },
            ])
            ->orderByRaw('COALESCE(sort_datetime, chat_rooms.created_at) DESC')
            ->get();

        // $this->room_member_counts = ChatRoom::withCount('activeMembers')->get();
        // dd( $this->chat_rooms);

        // ユーザー検索（ベースクエリ）
        $chat_list_users = User::query()
            ->when(!auth()->user()->isAdmin(), function ($q) {
                $q->where('admin_flg', 1);
            })
            ->when($this->keyword !== '', function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->keyword . '%')
                    ->orWhere('email', 'like', '%' . $this->keyword . '%');
                });
            })
            ->whereNot('id', $my_user_id);

        // ページネーション（clone）
        $searchUsers = (clone $chat_list_users)
            ->paginate(10, pageName: 'searchUsersPage');


        // 全ユーザーID取得（clone）
        $this->chat_user_ids = (clone $chat_list_users)
            ->pluck('id')
            ->all();


        // グループチャット用ユーザー
        $activeUsers = User::whereIn('id', $this->active_user_ids)
            ->orWhere('id', $my_user_id)
            ->paginate(10, pageName: 'groupChatPage');

        // デバッグ
        // dd($this->chat_user_ids);

        return view('livewire.chat-list', [
            'search_users' => $searchUsers,
            'active_users' => $activeUsers,
            'chat_rooms'   => $this->chat_rooms,
        ]);
    }

}
