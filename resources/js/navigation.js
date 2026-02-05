function initMenu(force = false) {
    const menuButton = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    if (!menuButton || !sidebar || !overlay) return;

    //BFCache 対策：強制再バインド
    if (force) {
        menuButton.replaceWith(menuButton.cloneNode(true));
        overlay.replaceWith(overlay.cloneNode(true));
    }

    const newMenuButton = document.getElementById('menuButton');
    const newOverlay = document.getElementById('overlay');

    newMenuButton.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        newOverlay.classList.toggle('hidden');
    });

    newOverlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        newOverlay.classList.add('hidden');
    });
}

/* 通常ロード */
document.addEventListener('DOMContentLoaded', () => initMenu());

/* Livewire */
document.addEventListener('livewire:load', () => initMenu());
document.addEventListener('livewire:update', () => initMenu());
document.addEventListener('livewire:navigated', () => initMenu(true));

/*ブラウザバック*/
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        initMenu(true);
    }
});
