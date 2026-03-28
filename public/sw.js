const CACHE_NAME = 'campuscrush-v2';
const OFFLINE_URL = '/offline.html';

// Fichiers à mettre en cache au premier chargement
const PRECACHE_URLS = [
    '/',
    '/offline.html',
    '/images/icons/icon-192x192.png',
    '/images/icons/icon-512x512.png',
];;

// Installation - mise en cache des ressources essentielles
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('🔥 Campus Crush SW: Cache ouvert');
            return cache.addAll(PRECACHE_URLS).catch(() => {
                // Ignorer les erreurs de cache pour les CDN
                console.log('⚠️ Certaines ressources non mises en cache');
            });
        })
    );
    self.skipWaiting();
});

// Activation - nettoyage des anciens caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.filter((name) => name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        })
    );
    self.clients.claim();
});

// Fetch - stratégie Network First avec fallback cache
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Ignorer les requêtes non-GET
    if (request.method !== 'GET') return;

    // Ignorer les requêtes API/AJAX
    if (request.url.includes('/api/') ||
        request.url.includes('/broadcasting/') ||
        request.url.includes('/load-profiles') ||
        request.url.includes('/nav-counts') ||
        request.url.includes('/notifications')) {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                // Mettre en cache les réponses réussies
                if (response.ok && response.type === 'basic') {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                // Fallback sur le cache
                return caches.match(request).then((cachedResponse) => {
                    if (cachedResponse) return cachedResponse;

                    // Si c'est une page HTML, montrer la page offline
                    if (request.headers.get('accept')?.includes('text/html')) {
                        return caches.match(OFFLINE_URL);
                    }
                });
            })
    );
});

// Push notifications (prêt pour plus tard)
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body || 'Nouvelle notification',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-72x72.png',
        vibrate: [100, 50, 100],
        data: { url: data.url || '/' },
        actions: [
            { action: 'open', title: 'Ouvrir' },
            { action: 'close', title: 'Fermer' },
        ],
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Campus Crush', options)
    );
});

// Clic sur notification
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window' }).then((clientList) => {
            for (const client of clientList) {
                if (client.url.includes(url) && 'focus' in client) {
                    return client.focus();
                }
            }
            return clients.openWindow(url);
        })
    );
});
