const CACHE_NAME = 'wantrace-static-v2';

// Hanya aset statis aman yang di-cache (tidak pernah cache HTML dinamis, data privat, atau API)
const PRECACHE_URLS = [
    '/offline.html',
    '/manifest.webmanifest',
    '/images/wi-logo.png',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                PRECACHE_URLS.map((url) => cache.add(url))
            );
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // 0. Abaikan request only-if-cached lintas mode agar prefetch/history Chrome tidak memicu TypeError
    if (request.cache === 'only-if-cached' && request.mode !== 'same-origin') {
        return;
    }

    // 1. Transaksi tulis (POST, PUT, PATCH, DELETE) wajib langsung ke server (tidak ada offline queue)
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // 2. Abaikan request lintas origin (seperti CDN Font/Tailwind) agar tidak merusak CSP/CORS
    if (url.origin !== self.location.origin) {
        return;
    }

    // 3. File privat / media terotorisasi tidak boleh pernah di-cache oleh Service Worker
    if (url.pathname.startsWith('/media/')) {
        return;
    }

    // 4. Navigasi halaman HTML: selalu ambil dari server (network-first); jika gagal tampilkan offline.html
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async (error) => {
                console.error('[SW] Navigation fetch failed:', request.url, error);
                const offlineResponse = await caches.match('/offline.html');
                return offlineResponse || Response.error();
            })
        );
        return;
    }

    // 5. Aset statis dalam daftar aman: cache-first dengan fallback network
    if (PRECACHE_URLS.includes(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                return cachedResponse || fetch(request);
            })
        );
    }
});