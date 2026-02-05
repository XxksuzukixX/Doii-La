<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class UserSearchModal extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    protected $updatesQueryString = [];

    public string $keyword = '';
    public array $selected_user_ids = [];
    public array $exclude_user_ids = [];

    //初期設定
    public function mount(array $excludeUserIds = []): void
    {
        $this->exclude_user_ids = $excludeUserIds;
    }

    /**
     * 検索語変更時にページを戻す
     */
    public function updatingKeyword(): void
    {
        $this->resetPage();
    }

    /**
     * チェックON/OFF
     */
    public function toggleUser(int $user_id): void
    {
        if (in_array($user_id, $this->selected_user_ids, true)) {
            $this->selected_user_ids = array_values(
                array_diff($this->selected_user_ids, [$user_id])
            );
        } else {
            $this->selected_user_ids[] = $user_id;
        }
    }

    /**
     * 親へ選択ユーザーを送信
     */
    public function addUsers(): void
    {
        $users = User::whereIn('id', $this->selected_user_ids)->get();

        foreach ($users as $user) {
            $this->dispatch('userSelected', [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ]);
        }

        $this->selected_user_ids = [];
        // モーダルを閉じる
        $this->dispatch('closeUserModal');
    }

    public function render()
    {
        $users = User::query()
            ->when(!empty($this->exclude_user_ids), function ($q) {
                $q->whereNotIn('id', $this->exclude_user_ids);
            })
            ->when($this->keyword !== '', function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->keyword . '%')
                    ->orWhere('email', 'like', '%' . $this->keyword . '%');
                });
            })
            ->paginate(10);

        return view('livewire.user-search-modal', compact('users'));
    }
}



