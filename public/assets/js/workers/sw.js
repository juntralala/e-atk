self.addEventListener('install', () => {
//   console.log('SW: install');
  self.skipWaiting(); // langsung aktif tanpa tunggu tab lama tutup
});

self.addEventListener('activate', (event) => {
  console.log('SW: aktif');
  event.waitUntil(clients.claim()); // ambil alih semua tab
});

self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : { title: 'Notifikasi baru!', body: 'Buka aplikasi segera.' };

  console.log("push:");
  console.log(event);
  console.log(data);

  const options = {
    body: data.body,
    icon: data?.icon || '/icon.png',
    // badge: '/badge.png',
    data: { url: data.data.url }
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

// Menangani klik pada notifikasi
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  console.log(clients);
  console.log(event);
  event.waitUntil(
    clients.openWindow(event.notification.data?.url || '/') // Buka link tujuan
  );
});
