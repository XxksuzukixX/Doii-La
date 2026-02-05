import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

window.Cropper = Cropper

window.iconCropper = function () {
    return {
        open: false,
        cropper: null,
        preview: null,

        load(e) {
            const file = e.target.files[0]
            if (!file) return

            const reader = new FileReader()
            reader.onload = () => {
                this.open = true
                this.$nextTick(() => {
                    this.$refs.image.src = reader.result
                    this.cropper = new Cropper(this.$refs.image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        guides: false,
                        background: false,
                    })
                })
            }
            reader.readAsDataURL(file)
        },

        cancel() {
            this.cropper?.destroy()
            this.cropper = null
            this.open = false
        },

        crop() {
            if (!this.cropper) return

            const canvas = this.cropper.getCroppedCanvas({
                width: 256,
                height: 256,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            })

            const dataUrl = canvas.toDataURL('image/png')

            // プレビュー用
            this.preview = dataUrl

            // Livewire に値を渡す
            this.$wire.set('croppedImage', dataUrl)
            // this.$wire.set('icon_cropped', dataUrl)
            // this.$wire.icon_cropped = dataUrl
            this.$wire.confirm()

            this.cropper.destroy()
            this.cropper = null
            this.open = false
        }

    }
}



document.addEventListener('DOMContentLoaded', () => {

    const input = document.getElementById('iconInput');
    const modal = document.getElementById('cropModal');
    const target = document.getElementById('cropTarget');
    const preview = document.getElementById('preview');
    const hiddenInput = document.getElementById('iconCropped');
    const cancelBtn = document.getElementById('cropCancel');
    const confirmBtn = document.getElementById('cropConfirm');
    const userIcon = document.querySelector('x-user-icon');

    // 必須要素がなければ何もしない
    if (!input || !modal || !target || !preview || !hiddenInput || !cancelBtn || !confirmBtn) {
        return;
    }

    let cropper = null;
    let imageDataUrl = null;

    // デバッグ
    const debug = document.getElementById('jsDebug');
    debug.innerText = 'JS loaded';

    // 初期状態でプレビュー非表示
    preview.classList.add('hidden');

    // ファイル選択時に即モーダルを開く
    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        // if (!file) return;

        const reader = new FileReader();
        reader.onload = () => {
            imageDataUrl = reader.result;
            target.src = imageDataUrl;


            // モーダルを開く
            modal.classList.remove('hidden');

            // Cropper 初期化
            cropper = new Cropper(target, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                cropBoxMovable: false,
                cropBoxResizable: false,
                autoCropArea: 1,
                guides: false,
                center: false,
                highlight: false,
                background: false,
            });

            debug.innerText = 'Crop modal opened';
        };
        reader.readAsDataURL(file);
    });

    // キャンセル
    cancelBtn.addEventListener('click', () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        modal.classList.add('hidden');
        debug.innerText = 'Crop cancelled';
    });

    // 確定
    confirmBtn.addEventListener('click', () => {
        if (!cropper) return;

        const canvas = cropper.getCroppedCanvas({
            width: 256,
            height: 256,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        const dataUrl = canvas.toDataURL('image/png');

        // プレビューに反映
        preview.src = dataUrl;
        preview.classList.remove('hidden');

        // フォーム送信用
        hiddenInput.value = dataUrl;

        // 既存アイコンを隠す
        if (currentIcon) currentIcon.style.display = 'none';
        
        cropper.destroy();
        cropper = null;
        modal.classList.add('hidden');

        debug.innerText = 'Crop confirmed';
    });

});
