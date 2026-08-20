/**
 * Service Worker untuk KitaSewa Web Push Notifications
 * File ini harus berada di root /public agar memiliki scope seluruh domain.
 */

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

/**
 * Handle notifikasi push yang masuk dari server.
 */
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let data = {};
    try {
        data = event.data.json();
    } catch (e) {
        data = { title: 'KitaSewa', body: event.data.text() };
    }

    const title = data.title || 'KitaSewa';
    const options = {
        body: data.message || data.body || '',
        icon: data.icon || '/kitasewa-logo.png',
        badge: '/kitasewa-logo.png',
        data: {
            url: data.action_url || '/',
            ...data,
        },
        actions: data.action_url
            ? [{ action: 'open', title: 'Buka' }]
            : [],
        requireInteraction: false,
        silent: false,
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

/**
 * Handle klik pada notifikasi: arahkan pengguna ke URL tujuan.
 */
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Jika tab KitaSewa sudah terbuka, fokus ke tab itu
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            // Jika tidak ada tab yang terbuka, buka tab baru
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
