const CACHE_NAME = 'lab-explora-v1';
const urlsToCache = [
    '/lab2/',
    '/lab2/pagina-principal.php',
    '/lab2/assets/css/main.css',
    '/lab2/assets/vendor/bootstrap/css/bootstrap.min.css',
    '/lab2/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/lab2/assets/img/logo/logobrayan2.ico'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Cache hit - return response
                if (response) {
                    return response;
                }
                return fetch(event.request);
            })
    );
});
