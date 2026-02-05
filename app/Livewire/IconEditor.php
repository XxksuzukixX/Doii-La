<?php

namespace App\Livewire;

use Livewire\Component;

class IconEditor extends Component
{
    public ?string $icon;           // 初期アイコンパス
    public ?string $croppedIcon = null;  // トリミング後 Base64
    public bool $showCropModal = false;  // モーダル表示フラグ
    public string $inputName;       // hidden input name 属性

    protected $listeners = [
        'openCropModal' => 'openCropModal',
    ];

    public function mount(?string $icon = null, string $inputName = 'icon_cropped')
    {
        $this->icon = $icon;
        $this->inputName = $inputName;
    }

    public function openCropModal()
    {
        $this->showCropModal = true;
    }

    public function closeCropModal()
    {
        $this->showCropModal = false;
    }

    public function render()
    {
        return view('livewire.icon-editor');
    }
}
