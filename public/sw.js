// Campus Crush Service Worker
// - Images: cache-first, 30 days, bounded cache
// - Static assets: cache-first, 7 days
// - HTML pages: network-first, offline fallback
// - API/auth/realtime/payment routes: never cached

const CACHE_STATIC = 'cc-static-v5';
const CACHE_PAGES = 'cc-pages-v5';
const CACHE_IMAGES = 'cc-images-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    '/offline.html',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
];

const TTL = {
    images: 30 * 24 * 3600,
    static: 7 * 24 * 3600,
    pages: 5 * 60,
};

const MAX_ENTRIES = {
    images: 180,
    static: 120,
    pages: 30,
};

const NEVER_CACHE = [
    '/api/',
    '/broadcasting/',
    '/load-profiles',
    '/nav-counts',
    '/notifications',
    '/like/',
    '/pass/',
    '/messages/',
    '/typing/',
    '/logout',
    '/login',
    '/register',
    '/ai/chat/',
    '/ai/pay',
    '/subscription/',
    '/payment/',
    '/boost/pay',
    '/crush/send',
];

const IMAGE_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.webp', '.gif'];
const STATIC_EXTENSIONS = ['.svg', '.woff', '.woff2', '.ttf', '.ico', '.css', '.js'];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_STATIC)
            .then(cache => cache.addAll(PRECACHE_ASSETS).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(names =>
            Promise.all(
                names
                    .filter(name => ![CACHE_STATIC, CACHE_PAGES, CACHE_IMAGES].includes(name))
                    .map(name => caches.delete(name))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const { request } = event;

    if (request.method !== 'GET') return;
    if (!request.url.startsWith('http')) return;

    const url = new URL(request.url);
    if (NEVER_CACHE.some(path => url.pathname.startsWith(path))) return;

    const ext = getExtension(url.pathname);

    if (IMAGE_EXTENSIONS.includes(ext)) {
        event.respondWith(cacheFirst(request, CACHE_IMAGES, TTL.images, MAX_ENTRIES.images));
        return;
    }

    if (STATIC_EXTENSIONS.includes(ext) || url.hostname !== self.location.hostname) {
        event.respondWith(cacheFirst(request, CACHE_STATIC, TTL.static, MAX_ENTRIES.static));
        return;
    }

    event.respondWith(networkFirst(request));
});

function getExtension(pathname) {
    const index = pathname.lastIndexOf('.');
    return index >= 0 ? pathname.slice(index).toLowerCase() : '';
}

async function cacheFirst(request, cacheName, maxAge, maxEntries) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) {
        const cachedAt = parseInt(cached.headers.get('sw-cached-at') || '0', 10);
        if (cachedAt && Date.now() - cachedAt < maxAge * 1000) return cached;

        fetchAndCache(request, cacheName, maxEntries).catch(() => {});
        return cached;
    }

    return fetchAndCache(request, cacheName, maxEntries).catch(() => Response.error());
}

async function networkFirst(request) {
    const cache = await caches.open(CACHE_PAGES);

    try {
        const response = await fetch(request, { signal: AbortSignal.timeout(8000) });

        if (shouldCache(request, response)) {
            await putWithTimestamp(cache, request, response, MAX_ENTRIES.pages);
        }

        return response;
    } catch {
        const cached = await cache.match(request);
        if (cached) return cached;

        if ((request.headers.get('accept') || '').includes('text/html')) {
            return cache.match(OFFLINE_URL) || Response.error();
        }

        return Response.error();
    }
}

async function fetchAndCache(request, cacheName, maxEntries) {
    const response = await fetch(request);
    if (shouldCache(request, response, true)) {
        const cache = await caches.open(cacheName);
        await putWithTimestamp(cache, request, response, maxEntries);
    }

    return response;
}

async function putWithTimestamp(cache, request, response, maxEntries) {
    const headers = new Headers(response.headers);
    headers.set('sw-cached-at', Date.now().toString());
    const body = await response.clone().blob();

    await cache.put(request, new Response(body, {
        status: response.status,
        statusText: response.statusText,
        headers,
    }));

    await trimCache(cache, maxEntries);
}

async function trimCache(cache, maxEntries) {
    const keys = await cache.keys();
    if (keys.length <= maxEntries) return;

    await Promise.all(keys.slice(0, keys.length - maxEntries).map(key => cache.delete(key)));
}

function shouldCache(request, response, allowCors = false) {
    if (!response.ok) return false;
    if (response.type !== 'basic' && (!allowCors || response.type !== 'cors')) return false;

    const cacheControl = response.headers.get('cache-control') || '';
    if (/no-store|no-cache|private/i.test(cacheControl)) return false;

    const accept = request.headers.get('accept') || '';
    if (accept.includes('text/html')) return false;

    return true;
}

self.addEventListener('push', event => {
    if (!event.data) return;

    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title || 'Campus Crush', {
            body: data.body || 'Nouvelle notification',
            icon: '/images/icons/icon-192x192.png',
            badge: '/images/icons/icon-72x72.png',
            vibrate: [100, 50, 100],
            data: { url: data.url || '/' },
            actions: [
                { action: 'open', title: 'Ouvrir' },
                { action: 'close', title: 'Fermer' },
            ],
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(clientList => {
            for (const client of clientList) {
                if (client.url.includes(url) && 'focus' in client) return client.focus();
            }
            return clients.openWindow(url);
        })
    );
});
