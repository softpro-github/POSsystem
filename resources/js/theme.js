const STORAGE_KEY = 'gadgetstore-theme';

function systemPrefersDark() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function resolve(mode) {
    return mode === 'auto' ? (systemPrefersDark() ? 'dark' : 'light') : mode;
}

function apply(mode) {
    const resolved = resolve(mode);
    document.documentElement.setAttribute('data-theme', resolved);
    document.documentElement.setAttribute('data-theme-mode', mode);
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { mode, resolved } }));
}

export function getThemeMode() {
    return localStorage.getItem(STORAGE_KEY) || 'dark';
}

export function setTheme(mode) {
    localStorage.setItem(STORAGE_KEY, mode);
    apply(mode);
}

window.setTheme = setTheme;
window.getThemeMode = getThemeMode;

// Live-update while in "auto" mode when the OS preference changes.
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getThemeMode() === 'auto') apply('auto');
    });
}
