import { clientsClaim } from 'workbox-core';
import { precacheAndRoute, cleanupOutdatedCaches, matchPrecache } from 'workbox-precaching';
import { registerRoute, setCatchHandler } from 'workbox-routing';
import { NetworkFirst } from 'workbox-strategies';
import { ExpirationPlugin } from 'workbox-expiration';

self.skipWaiting();
clientsClaim();

precacheAndRoute(self.__WB_MANIFEST);
cleanupOutdatedCaches();

// The last successful load of the POS screen — including the embedded product
// catalog baked into the HTML — is kept as the offline fallback. Matched
// narrowly (no query string) so ?resume=... and /pos/held are never served
// from a stale cache.
registerRoute(
    ({ request, url }) => request.mode === 'navigate' && url.pathname === '/pos' && url.search === '',
    new NetworkFirst({
        cacheName: 'pos-page',
        networkTimeoutSeconds: 4,
        plugins: [new ExpirationPlugin({ maxEntries: 1 })],
    }),
);

// Only engages when a navigation has no network AND nothing cached for it —
// browsing while online, or to already-cached pages, is unaffected.
setCatchHandler(async ({ event }) => {
    if (event.request.mode === 'navigate') {
        return matchPrecache('/offline.html');
    }
    return Response.error();
});
