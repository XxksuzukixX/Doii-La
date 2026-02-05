<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Closure;

class UserSelectModal extends Component
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
    public array $activeIds = [];

    protected $updatesQueryString = ['keyword'];

    // protected $listeners = ['openUserSelectModal' => 'open'];

    protected function getListeners(): array
    {
        return [
            $this->openEvent => 'open',
            'set-user-ids'   => 'setUserIds',
            'reset-user-ids' => 'resetUserIds', 
        ];
    }
    public function mount(array $userIds = [])
    {
        // $this->userIds = $userIds;
        // $this->activeIds = $userIds; // 初期状態としてコピー
        // dd($this->userIds );
    }

    // public function open()
    // {
    //     $this->showModal = true;
    //     $this->resetPage();
    // }
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
            ->when(
                is_array($this->userIds) && count($this->userIds) > 0,
                fn ($q) => $q->whereIn('id', $this->userIds),
                fn ($q) => $q->whereRaw('1 = 0') // 常に0件
            )
            ->orderBy($this->displayFieldName)
            ->paginate(10);

        return view('livewire.user-select-modal', [
            'items' => $items,
        ]);
    }
}
