// SSC Student PWA — Service Worker
const CACHE_NAME = 'ssc-student-v1';

// Assets to cache immediately on install
const PRECACHE_URLS = [
    '/m/student/proposals',
    '/assets/css/mobile-student.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap',
];

// ── Install: pre-cache critical assets ──
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// ── Activate: clear old caches ──
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

// ── Fetch: Network-first for API/form, Cache-first for assets ──
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Don't cache POST requests or non-GET
    if (event.request.method !== 'GET') return;

    // Network-first for HTML pages (always fresh content)
    if (event.request.mode === 'navigate' || event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(event.request)
                .catch(() => caches.match('/m/student/proposals') || offlinePage())
        );
        return;
    }

    // Cache-first for static assets (CSS, fonts, icons)
    if (url.origin === location.origin && (
        url.pathname.startsWith('/assets/') ||
        url.pathname.startsWith('/storage/')
    )) {
        event.respondWith(
            caches.open(CACHE_NAME).then(cache =>
                cache.match(event.request).then(cached => {
                    if (cached) return cached;
                    return fetch(event.request).then(response => {
                        cache.put(event.request, response.clone());
                        return response;
                    });
                })
            )
        );
        return;
    }

    // Stale-while-revalidate for CDN resources (fonts, icons)
    if (url.origin.includes('jsdelivr.net') || url.origin.includes('googleapis.com') || url.origin.includes('gstatic.com')) {
        event.respondWith(
            caches.open(CACHE_NAME).then(cache =>
                cache.match(event.request).then(cached => {
                    const fetchPromise = fetch(event.request).then(response => {
                        cache.put(event.request, response.clone());
                        return response;
                    }).catch(() => cached);
                    return cached || fetchPromise;
                })
            )
        );
    }
});

function offlinePage() {
    return new Response(
        `<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>SSC — Offline</title>
            <style>
                body { font-family: system-ui, sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f8fafc; margin:0; }
                .card { text-align:center; padding:40px; background:#fff; border-radius:20px; box-shadow:0 4px 20px rgba(0,0,0,.1); max-width:300px; }
                h1 { font-size:1.2rem; color:#0f172a; margin-bottom:8px; }
                p  { font-size:.875rem; color:#64748b; }
                button { margin-top:20px; padding:12px 24px; background:#4f46e5; color:#fff; border:none; border-radius:12px; font-size:.9rem; font-weight:600; cursor:pointer; }
            </style>
        </head>
        <body>
            <div class="card">
                <div style="font-size:3rem;">📡</div>
                <h1>You're Offline</h1>
                <p>Connect to the internet to use the SSC Student app.</p>
                <button onclick="location.reload()">Try Again</button>
            </div>
        </body>
        </html>`,
        { headers: { 'Content-Type': 'text/html' } }
    );
}
