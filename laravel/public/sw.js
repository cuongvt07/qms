/* QMS Forms service worker — cho phép cài PWA + tải shell nhanh.
   Chiến lược an toàn: network-first, chỉ fallback cache khi mất mạng.
   KHÔNG cache các POST/Livewire để tránh dữ liệu cũ. */
const CACHE = 'qms-shell-v1';
const SHELL = ['/icon-192.png', '/icon-512.png', '/manifest.json'];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(SHELL)).catch(() => {}));
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
  );
  self.clients.claim();
});

self.addEventListener('fetch', (e) => {
  const req = e.request;
  if (req.method !== 'GET') return;                       // bỏ qua POST/Livewire
  const url = new URL(req.url);
  if (url.pathname.startsWith('/livewire')) return;       // không đụng Livewire
  // Tài nguyên tĩnh: cache-first
  if (/\.(png|jpg|jpeg|svg|css|js|woff2?|ico)$/.test(url.pathname) || url.pathname.startsWith('/build')) {
    e.respondWith(caches.match(req).then((hit) => hit || fetch(req).then((res) => {
      const copy = res.clone();
      caches.open(CACHE).then((c) => c.put(req, copy)).catch(() => {});
      return res;
    })));
    return;
  }
  // Trang: network-first, fallback cache khi offline
  e.respondWith(fetch(req).catch(() => caches.match(req)));
});
