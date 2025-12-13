const CACHE_NAME = 'lab-explora-v1';
const urlsToCache = [
    '/lab2/',
    '/lab2/pagina-principal.php',
    '/lab2/assets/css/main.css',
    '/lab2/assets/css-admins/admin.css',
    '/lab2/assets/vendor/bootstrap/css/bootstrap.min.css',
    '/lab2/assets/vendor/bootstrap/js/bootstrap.bundle.min.js',
    '/lab2/assets/vendor/aos/aos.js',
    '/lab2/assets/js/main.js',
    '/lab2/assets/img/logo/logo-labexplora.png',
    '/lab2/assets/img/fondo-inicio-registro/registro-inicio.png'
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
