// Full-page navigations (a plain <a> click, a <form> submit) give zero visual
// feedback while the request is in flight — no browser tab spinner exists at
// all in an installed PWA's standalone window, so a slow response looks like
// nothing happened, and users click again. This shows a thin top progress bar
// immediately on a qualifying click/submit and disables the trigger so repeat
// clicks don't fire duplicate navigations/submissions while one is in flight.

let navStarted = false;

function showBar() {
    const bar = document.getElementById('nav-loading-bar');
    if (bar) bar.classList.add('nav-loading-bar--visible');
}

function startNavigation() {
    if (navStarted) return;
    navStarted = true;
    // Show immediately, synchronously — NOT after a delay. For a real
    // cross-document navigation, Chromium can tear down this page's JS
    // execution context within milliseconds of the click (well before any
    // network response, sometimes before a 150ms setTimeout even fires), so
    // a "wait a bit before showing" delay can lose the race entirely and
    // never show at all for exactly the slow-navigation case this exists for.
    // Fast navigations get a brief flash — an accepted, standard tradeoff
    // (the same one NProgress-style bars make) rather than risking silence.
    showBar();
}

function isModifiedClick(e) {
    return e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey;
}

document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href]');
    if (!link || isModifiedClick(e)) return;
    if (link.target === '_blank' || link.hasAttribute('download') || link.dataset.noNavLoading !== undefined) return;

    const url = new URL(link.href, window.location.href);
    if (url.origin !== window.location.origin) return;
    if (url.pathname === window.location.pathname && url.hash) return; // in-page anchor

    startNavigation();
});

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.dataset.noNavLoading !== undefined || form.target === '_blank') return;
    startNavigation();
});

// A page that's about to be replaced is exactly when the bar should still be
// visible — no explicit "hide" is needed for real navigations, since the whole
// DOM (including the bar) is discarded. If navigation is cancelled (e.g. the
// user hits back, or a validation error re-renders the same page instantly),
// pageshow fires and resets state so the bar/guard don't get stuck forever.
window.addEventListener('pageshow', () => {
    navStarted = false;
    const bar = document.getElementById('nav-loading-bar');
    if (bar) bar.classList.remove('nav-loading-bar--visible');
});
