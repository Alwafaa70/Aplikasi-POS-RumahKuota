const CACHE_NAME = 'pos-cache-v1';
const urlsToCache = [
  '/',
  '/kasir/pos',
  '/manifest.json',
  '/js/pwa.js',
  'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  // Hanya intercept GET request (Network First, falling back to cache)
  if (event.request.method === 'GET') {
    event.respondWith(
      fetch(event.request).catch(() => {
        return caches.match(event.request);
      })
    );
  }
});
