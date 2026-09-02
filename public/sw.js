const CACHE_NAME = 'alihsan-cms-shell-v1';

const PRECACHE_URLS = [
    '/css/admin-theme.css',
    '/js/sweetalert-bridge.js',
    '/images/logo.png',
    '/images/icons/icon-192.png',
    '/images/icons/icon-512.png',
    '/manifest.json',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

/**
 * Only the static app-shell assets above are cached. Admin pages are
 * Livewire-driven with CSRF tokens and live data, so everything else
 * (HTML, API/Livewire calls) always goes straight to the network —
 * caching those would risk serving stale forms or broken CSRF state.
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin || !PRECACHE_URLS.includes(url.pathname)) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request)),
    );
});
