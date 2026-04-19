/**
 * Service Worker — Comercial San Francisco
 *
 * Responsabilidades:
 *  1. Recibir eventos push y mostrar notificaciones del OS
 *  2. Manejar clicks en notificaciones (abrir/focalizar la app)
 *
 * Este SW no maneja caché ni offline mode — solo push notifications.
 * El alcance (scope) es '/' porque el archivo está en public/sw.js.
 */

'use strict';

// ─── Push: recibe el mensaje del servidor y muestra la notificación ───────────

self.addEventListener('push', (event) => {
    if (!event.data) return;

    let payload;
    try {
        payload = event.data.json();
    } catch {
        payload = { title: 'Comercial San Francisco', body: event.data.text() };
    }

    const title = payload.title ?? 'Comercial San Francisco';

    const options = {
        body: payload.body ?? '',
        icon: payload.icon ?? '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        tag: payload.tag ?? 'csf-notification',
        data: {
            url: payload.data?.url ?? '/dashboard',
        },
        // Notificaciones críticas requieren interacción (no se auto-cierran)
        requireInteraction: payload.requireInteraction ?? false,
        // Vibración en móvil: patrón [ms encendido, ms apagado, ...]
        vibrate: [200, 100, 200],
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ─── Click en notificación: abre/focaliza la ventana de la app ───────────────

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url ?? '/dashboard';

    event.waitUntil(
        clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Si ya hay una ventana abierta con esa URL, la enfocamos
                for (const client of clientList) {
                    if (client.url.includes(targetUrl) && 'focus' in client) {
                        return client.focus();
                    }
                }

                // Si hay cualquier ventana abierta de la app, la enfocamos y navegamos
                for (const client of clientList) {
                    if ('focus' in client && 'navigate' in client) {
                        client.focus();
                        return client.navigate(targetUrl);
                    }
                }

                // Si no hay ninguna ventana, abrimos una nueva
                if (clients.openWindow) {
                    return clients.openWindow(targetUrl);
                }
            })
    );
});

// ─── Instalación y activación: limpiar cachés antiguas si las hubiera ─────────

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});
