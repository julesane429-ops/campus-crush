// ── Campus Crush Service Worker v3 ────────────────────────────────────────
// Stratégie par type de ressource :
//   • Assets statiques (images, fonts, icons) → Cache-First (7 jours)
//   • Pages HTML, JS, CSS                     → Network-First avec fallback
//   • API / temps réel / broadcast            → Network-Only (jamais en cache)

const CACHE_STATIC  = 'cc-static-v3';
const CACHE_PAGES   = 'cc-pages-v3';
const OFFLINE_URL   = '/offline.html';

// Ressources critiques à pré-cacher lors de l'installation
const PRECACHE_ASSETS = [
    '/offline.html',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
];

// TTL en secondes pour chaque type de cache
const TTL = {
    static: 7 * 24 * 3600,   // 7 jours (images, fonts, avatars)
    pages:  5 * 60,           // 5 minutes (HTML rendu côté serveur)
};

// Routes à ne JAMAIS mettre en cache (temps-réel, auth, mutations)
const NEVER_CACHE = [
    '/api/',
    '/broadcasting/',
    '/load-profiles',
    '/nav-counts',
    '/notifications',
    '/like/',
    '/pass/',
    '/messages/',      // POST — jamais GET sera déjà exclu par method check
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

// Extensions "assets statiques"
const STATIC_EXTENSIONS = ['.jpg', '.jpeg', '.png', '.webp', '.gif', '.svg',
                            '.woff', '.woff2', '.ttf', '.ico'];

// ── INSTALL ──────────────────────────────────────────────────────────────────
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_STATIC)
            .then(cache => cache.addAll(PRECACHE_ASSETS).catch(() => {}))
            .then(() => self.skipWaiting())
    );
});

// ── ACTIVATE (nettoyage des anciens caches) ───────────────────────────────────
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(names =>
            Promise.all(
                names
                    .filter(n => n !== CACHE_STATIC && n !== CACHE_PAGES)
                    .map(n => caches.delete(n))
            )
        ).then(() => self.clients.claim())
    );
});

// ── FETCH ─────────────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // 1. Ignorer les méthodes non-GET
    if (request.method !== 'GET') return;

    // 2. Ignorer les protocoles non-HTTP
    if (!request.url.startsWith('http')) return;

    // 3. Routes à ne jamais mettre en cache
    if (NEVER_CACHE.some(path => url.pathname.startsWith(path))) return;

    // 4. Choix de stratégie selon le type de ressource
    const ext = url.pathname.slice(url.pathname.lastIndexOf('.'));

    if (STATIC_EXTENSIONS.includes(ext.toLowerCase())) {
        // ── CACHE-FIRST pour les images et fonts ──────────────────────────
        event.respondWith(cacheFirstStrategy(request, CACHE_STATIC, TTL.static));
    } else if (url.hostname !== self.location.hostname) {
        // ── Ressources CDN (fonts Google, Tailwind CDN) → Cache-First ────
        event.respondWith(cacheFirstStrategy(request, CACHE_STATIC, TTL.static));
    } else {
        // ── NETWORK-FIRST pour les pages HTML et scripts Laravel ──────────
        event.respondWith(networkFirstStrategy(request));
    }
});

// ── STRATÉGIES ───────────────────────────────────────────────────────────────

/**
 * Cache-First : cherche en cache d'abord, réseau si absent.
 * Idéal pour les ressources qui ne changent pas souvent.
 */
async function cacheFirstStrategy(request, cacheName, maxAge) {
    const cache    = await caches.open(cacheName);
    const cached   = await cache.match(request);

    if (cached) {
        // Vérifier si le cache est encore frais (via Date header ou timestamp)
        const cachedDate = cached.headers.get('sw-cached-at');
        if (cachedDate && (Date.now() - parseInt(cachedDate)) < maxAge * 1000) {
            return cached;
        }
        // Expiré → retourner le cache périmé ET re-fetcher en arrière-plan
        fetchAndCache(request, cacheName).catch(() => {});
        return cached;
    }

    // Pas en cache → réseau
    return fetchAndCache(request, cacheName).catch(() => cached || Response.error());
}

/**
 * Network-First : réseau d'abord, cache en fallback si offline.
 * Idéal pour les pages HTML avec données dynamiques.
 */
async function networkFirstStrategy(request) {
    const cache = await caches.open(CACHE_PAGES);

    try {
        const response = await fetch(request, { signal: AbortSignal.timeout(8000) });

        if (response.ok && response.type === 'basic') {
            // Stocker en cache (avec timestamp)
            const toCache = response.clone();
            const headers = new Headers(toCache.headers);
            headers.append('sw-cached-at', Date.now().toString());
            const body = await toCache.blob();
            cache.put(request, new Response(body, {
                status: toCache.status,
                headers: headers,
            })).catch(() => {});
        }

        return response;
    } catch {
        // Offline → fallback sur le cache
        const cached = await cache.match(request);
        if (cached) return cached;

        // Si c'est une page HTML, afficher la page offline
        if (request.headers.get('accept')?.includes('text/html')) {
            return cache.match(OFFLINE_URL) || Response.error();
        }

        return Response.error();
    }
}

/**
 * Fetch et stocker en cache.
 */
async function fetchAndCache(request, cacheName) {
    const response = await fetch(request);

    if (response.ok && (response.type === 'basic' || response.type === 'cors')) {
        const cache   = await caches.open(cacheName);
        const headers = new Headers(response.headers);
        headers.append('sw-cached-at', Date.now().toString());
        const body = await response.clone().blob();
        cache.put(request, new Response(body, {
            status:  response.status,
            headers: headers,
        })).catch(() => {});
    }

    return response;
}

// ── PUSH NOTIFICATIONS ────────────────────────────────────────────────────────
self.addEventListener('push', event => {
    if (!event.data) return;

    const data = event.data.json();
    event.waitUntil(
        self.registration.showNotification(data.title || 'Campus Crush', {
            body:    data.body || 'Nouvelle notification',
            icon:    '/images/icons/icon-192x192.png',
            badge:   '/images/icons/icon-72x72.png',
            vibrate: [100, 50, 100],
            data:    { url: data.url || '/' },
            actions: [
                { action: 'open',  title: 'Ouvrir' },
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
