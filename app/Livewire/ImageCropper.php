<?php

namespace App\Livewire;

use Livewire\Component;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCropper extends Component
{
    public string $label = 'ユーザーアイコン';
    public string $name = 'icon_cropped'; // name属性
    public ?string $iconPath = null;

    /** Cropper.js から受け取る Base64 */
    public string $croppedImage = '';

    public function confirm(): void
    {
        if ($this->croppedImage === '') {
            return;
        }

        // Base64 解析
        if (!str_contains($this->croppedImage, ',')) {
            return;
        }

        [, $data] = explode(',', $this->croppedImage);
        $binary = base64_decode($data);

        $manager = new ImageManager(new Driver());
        $image = $manager->read($binary);

        // 正方形にトリミングしてリサイズ
        $image->cover(256, 256);

        // PNG に変換して Base64 に戻す
        $pngBinary = (string) $image->toPng();
        $base64 = 'data:image/png;base64,' . base64_encode($pngBinary);

        // 親へ通知（保存は親の責務）
        $this->dispatch(
            'image-cropped',
            image: $base64
        );

        // 自身の状態はリセット
        $this->reset('croppedImage');
    }

    public function render()
    {
        return view('livewire.image-cropper');
    }
}
