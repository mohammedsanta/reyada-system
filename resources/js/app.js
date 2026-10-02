// ADD to the end of resources/js/app.js
// 1) any element with data-open-modal="ID" opens <dialog id="ID">
// 2) any element with data-close-modal closes its dialog; clicking the dark backdrop closes it too
// 3) clicking outside an open <details data-menu> (row menus, user menu) closes it
document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-open-modal]');
    if (opener) {
        document.getElementById(opener.dataset.openModal)?.showModal();
        return;
    }

    if (event.target.closest('[data-close-modal]')) {
        event.target.closest('dialog')?.close();
        return;
    }

    if (event.target.tagName === 'DIALOG') {
        event.target.close();
    }

    document.querySelectorAll('details[data-menu][open]').forEach((menu) => {
        if (!menu.contains(event.target)) menu.removeAttribute('open');
    });
});

// select-all helper: <input type="checkbox" data-check-all=".group-a"> toggles every checkbox matching the selector
document.addEventListener('change', (event) => {
    const master = event.target.closest('[data-check-all]');
    if (!master) return;

    document.querySelectorAll(master.dataset.checkAll).forEach((box) => (box.checked = master.checked));
});