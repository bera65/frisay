/* sw.js */
self.addEventListener("install", (event) => {
  self.skipWaiting();

  // offline ekranı ve temel dosyaları cache'le
  event.waitUntil(
    caches.open("app-v1").then((cache) =>
      cache.addAll([
        "/fshop/offline.html",
        "/fshop/templates/fshop/img/favicon.png"
      ])
    )
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(self.clients.claim());
});

// PWA açık mı bilgisini SW içinde tutacağız
let HAS_PWA = false;
let HAS_PWA_TS = 0;

// PWA kapandıysa sonsuza kadar true kalmasın diye (2 dk timeout)
function isPwaAlive() {
  return HAS_PWA && Date.now() - HAS_PWA_TS < 120000;
}

self.addEventListener("message", (event) => {
  const data = event.data || {};

  // PWA kendini işaretler (heartbeat gibi düşün)
  if (data.type === "I_AM_PWA") {
    HAS_PWA = true;
    HAS_PWA_TS = Date.now();
    return;
  }

  // Sekme sorar: PWA açık mı?
  if (data.type === "HAS_PWA_CLIENT") {
    event.ports?.[0]?.postMessage({ hasPwa: isPwaAlive() });
    return;
  }

  // Bildirim göster
  if (data.type === "SHOW_NOTIFICATION") {
    const title = data.title || "Bildirim";
    const options = {
      body: data.body || "",
      icon: data.icon || "/fshop/templates/fshop/img/favicon.png",
      badge: data.badge || "/fshop/templates/fshop/img/favicon.png",
      data: data.data || {},
    };
    self.registration.showNotification(title, options);
    return;
  }
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const url = event.notification.data?.url || "/";
  event.waitUntil(clients.openWindow(url));
});

/**
 * OFFLINE yakalama:
 * - internet yoksa navigate (sayfa) isteklerinde offline.html döndürür
 * - statik dosyalarda cache varsa onu döndürür
 
self.addEventListener("fetch", (event) => {
  const req = event.request;

  event.respondWith(
    caches.match(req).then((cached) => {
      if (cached) return cached;

      return fetch(req).then((res) => {
        return caches.open("app-v1").then((cache) => {
          cache.put(req, res.clone());
          return res;
        });
      });
    }).catch(async () => {
      if (req.mode === "navigate") {
        const cache = await caches.open("app-v1");
        return await cache.match("/fshop/offline.html");
      }
    })
  );
});
*/
self.addEventListener("fetch", (event) => {
  event.respondWith(fetch(event.request));
});