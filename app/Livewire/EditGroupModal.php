<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Closure;

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditGroupModal extends Component
{
    use WithPagination;

    // ----- Props -----
    public ChatRoom $chat_room;
    public int $roomId ;          // ルームID
    public bool $showModal = false;          // モーダル表示フラグ
    public string $nextButtonLabel; //ボタンラベル
    public string $title;
    public string $openEvent = 'openUserSelectModal';

    public string $group_name = '';
    public ?string $icon_path;
    public ?string $icon_cropped = null;

    protected function getListeners(): array
    {
        return [
            $this->openEvent => 'open',
            'image-cropped'  => 'onImageCropped'
        ];
    }
    public function mount()
    {
        $this->chat_room = ChatRoom::findOrFail($this->roomId);
        if($this->chat_room){
            $this->group_name = $this->chat_room->name;
            $this->icon_path = $this->chat_room->icon_path;
        }
    }
    public function open($userIds = [])
    {
        $this->showModal = true;
        $this->resetPage();
    }

    public function close(): void
    {
        $this->showModal = false;
        $this->resetPage();
    }

    public function onImageCropped(string $image): void
    {
        $this->icon_cropped = $image;
    }

    public function save(): void
    {
        $my_user_id = auth()->user()->id;

        if (!auth()->user()->isAdmin()) {
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

        /** ルーム情報更新 */
        $this->chat_room->update([
            'name'      => $this->group_name,
            'icon_path' => $this->icon_path,
        ]);

        /** モーダルを閉じる */
        $this->reset([
            'showModal',
            'group_name',
            'icon_cropped',
            'icon_path',
        ]);

        $this->close();

        redirect()->route('chat.room', [
            'room_id' => $this->chat_room->id,
        ]);
    }

    public function render()
    {
        return view('livewire.edit-group-modal');
    }
}
