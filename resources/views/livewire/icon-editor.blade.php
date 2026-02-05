<div>
    <!-- アイコン表示 -->
    <div class="flex items-center gap-6 flex-wrap">
        <div id="currentIcon">
            <x-user-icon :icon-path="$croppedIcon ?? $icon" />
        </div>

        <!-- 新アイコンプレビュー（JS用） -->
        <div>
            <img id="preview" class="w-20 h-20 rounded-full object-cover hidden">
        </div>

        <!-- ファイル選択 -->
        <label for="iconInput"
            class="cursor-pointer inline-flex items-center px-4 py-2 
                   bg-(--color-500) text-white rounded-lg h-8.75 
                   hover:bg-(--color-600)"
        >
            画像を変更
        </label>
        <input
            type="file"
            id="iconInput"
            accept="image/*"
            class="hidden"
            wire:change="$emit('openCropModal')"
        >

        <!-- トリミング後画像を送るため -->
        <input type="hidden" name="{{ $inputName }}" wire:model="croppedIcon">
    </div>

    <!-- トリミングモーダル -->
    @if ($showCropModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    >
        <div
            class="bg-white rounded-xl shadow-lg w-[90vw] max-w-105 max-h-[90vh] flex flex-col"
        >
            <div class="px-4 py-3 border-b border-(--color-500) text-lg font-semibold text-gray-800">
                画像のトリミング
            </div>

            <div class="flex-1 p-4 overflow-hidden flex items-center justify-center">
                <div class="w-full aspect-square max-h-[60vh]">
                    <img id="cropTarget" class="block max-w-full max-h-full">
                </div>
            </div>

            <div class="px-4 py-3 border-t border-(--color-500) flex justify-end gap-2">
                <button type="button" wire:click="closeCropModal"
                    class="h-8.75 px-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                    キャンセル
                </button>
                <button type="button" id="cropConfirm"
                    class="h-8.75 px-3 rounded-lg bg-(--color-700) text-white hover:bg-(--color-800)">
                    決定
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('livewire:load', function () {
    let cropper;
    const input = document.getElementById('iconInput');
    const cropTarget = document.getElementById('cropTarget');

    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(event) {
            cropTarget.src = event.target.result;

            if(cropper) cropper.destroy();
            cropper = new Cropper(cropTarget, { aspectRatio: 1 });
        };
        reader.readAsDataURL(file);

        @this.openCropModal();
    });

    document.getElementById('cropConfirm').addEventListener('click', () => {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width: 200, height: 200 });
        @this.set('croppedIcon', canvas.toDataURL());
        @this.closeCropModal();
    });
});
</script>
@endpush
