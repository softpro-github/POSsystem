// Global ⌘K / Ctrl+K shortcut — dispatches the same open-modal event the
// x-modal component already listens for, so command-palette.blade.php reuses
// its existing overlay/focus-trap/Escape-to-close logic rather than duplicating it.
document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'command-palette' }));
    }
});
