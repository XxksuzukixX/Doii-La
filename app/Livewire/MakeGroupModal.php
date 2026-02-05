<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Closure;

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MakeGroupModal extends Component
{
    use WithPagination;

    // ----- Props -----
    public bool $showModal = false;          // モーダル表示フラグ
    public string $modelClass = '';               // 検索対象のモデルクラス
    public string $displayFieldName = 'name';// 表示名フィールド
    public string $emailFieldName = 'email'; // メールなど追加情報フィールド
    public string $nextMethod = '';               // 選択ID配列を渡す Livewire メソッド名
    public array $userIds = [];
    public bool $showEmail = false; //メールアドレスの表示設定
    public bool $showFooter = false; //フッターの表示設定
    public bool $enableToggle = false; //トグルの有効化設定
    public bool $singleSelect = false; //単一選択モード
    public bool $resetOnClose = false; // close時のみリセットするか
    public bool $hideActiveOnUi = false; // true にすると activeIds は保持するが見た目に反映されない
    public string $nextButtonLabel; //ボタンラベル
    public string $title;
    public string $openEvent = 'openUserSelectModal';

    // ----- 状態 -----
    public string $keyword = '';
    protected $updatesQueryString = ['keyword'];

    public array $activeIds = []; // 選択されたユーザーID
    public string $group_name = '';
    public string $icon_path = '';
    public ?string $icon_cropped = null;



    protected function getListeners(): array
    {
        return [
            $this->openEvent => 'open',
            'set-user-ids'   => 'setUserIds',
            'reset-user-ids' => 'resetUserIds', 
            'image-cropped'  => 'onImageCropped'
        ];
    }
    public function mount(array $userIds = [])
    {
        $this->userIds = $userIds;
        // $this->activeIds = $userIds; // 初期状態としてコピー
    }


    public function open($userIds = [])
    {
        // Livewire 内部イベントでは配列の最初の値に userIds が入ってきます
        $userIds = $userIds['userIds'] ?? [];

        $this->showModal = true;
        if (!empty($userIds)) {
            $this->userIds = $userIds;
            $this->activeIds = $userIds;
        }
        $this->resetPage();
    }

    public function close(): void
    {
        $this->showModal = false;

        $this->activeIds = [];
        $this->resetPage();
    }

    public function toggleActivate(int $id)
    {
        // 選択無効モード
        if(!$this->enableToggle){
            return;
        }

        // 単一選択モード
        if ($this->singleSelect) {
            // 同じものを再クリック → 解除したい場合
            if (count($this->activeIds) === 1 && $this->activeIds[0] === $id) {
                $this->activeIds = [];
            } else {
                $this->activeIds = [$id];
            }
            return;
        }

        // 複数選択モード
        if (in_array($id, $this->activeIds, true)) {
            $this->activeIds = array_filter($this->activeIds, fn($i) => $i !== $id);
        } else {
            $this->activeIds[] = $id;
        }

    }
    public function executeNext()
    {
        if ($this->nextMethod) {
            // Livewire 3 では dispatch を使う
            $this->dispatch($this->nextMethod, activeIds: $this->activeIds);
            // dd($this->nextMethod);
        }
        $this->close();

    }

    public function onImageCropped(string $image): void
    {
        $this->icon_cropped = $image;
    }

    public function save(): void
    {
        $my_user_id = auth()->user()->id;

        if (!auth()->user()->isAdmin() || empty($this->activeIds)) {
            return;
        }

        $this->validate([
            'group_name' => ['required', 'string', 'max:255'],
        ], [
            'group_name.required' => 'グループ名は必須です。',
            'group_name.max'      => 'グループ名は255文字以内で入力してください。',
        ]);

        /** アイコン保存（サーバー側リサイズなし） */
        if (!empty($this->icon_cropped) && str_contains($this->icon_cropped, ',')) {
            [, $base64] = explode(',', $this->icon_cropped);
            $binary = base64_decode($base64);

            // 既存アイコンがあれば削除
            if (!empty($this->icon_path)
                && Storage::disk('public')->exists($this->icon_path)
            ) {
                Storage::disk('public')->delete($this->icon_path);
            }

            // 新しいパスを生成
            $this->icon_path  = 'group_icons/' . uniqid('', true) . '.png';
            Storage::disk('public')->put(
                $this->icon_path,
                $binary
            );

        }

        /** ルーム作成 */
        $chat_room = ChatRoom::create([
            'type'       => 'group',
            'name'       => $this->group_name,
            'icon_path'  => $this->icon_path,
            'created_by' => $my_user_id,
        ]);

        /** 作成者を owner として追加 */
        $chat_room->members()->attach($my_user_id, [
            'role' => 'owner',
        ]);

        /** メンバー追加（重複防止） */
        $users_to_add = array_unique($this->activeIds);

        foreach ($users_to_add as $user_id) {
            if ($user_id !== null && $user_id != $my_user_id) {
                $chat_room->members()->syncWithoutDetaching([
                    $user_id => ['role' => 'member'],
                ]);
            }
        }

        /** モーダルを閉じる */
        $this->reset([
            'showModal',
            'group_name',
            'icon_cropped',
            'icon_path',
            'activeIds',
        ]);

        $this->close();

        redirect()->route('chat.room', [
            'room_id' => $chat_room->id,
        ]);
    }

    public function render()
    {
        $modelClass = $this->modelClass;

        $items = $modelClass::query()
            ->when($this->userIds, fn ($q) =>
                $q->whereIn('id', $this->userIds)
            )
            ->when($this->keyword, function ($q) {
                $q->where(function ($q) {
                    $q->where(
                        $this->displayFieldName,
                        'like',
                        "%{$this->keyword}%"
                    );

                    if ($this->showEmail) {
                        $q->orWhere(
                            $this->emailFieldName,
                            'like',
                            "%{$this->keyword}%"
                        );
                    }
                });
            })
            ->orderBy($this->displayFieldName)
            ->paginate(10);

        return view('livewire.make-group-modal', [
            'items' => $items,
        ]);
    }
}
