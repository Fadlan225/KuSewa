import { ref } from 'vue';
import axios from 'axios';

const isSupported = ref(false);
const permission = ref('default'); // 'default' | 'granted' | 'denied'
const isSubscribed = ref(false);

const VAPID_PUBLIC_KEY = import.meta.env.VITE_VAPID_PUBLIC_KEY;

/**
 * Konversi base64url ke Uint8Array (dibutuhkan oleh PushManager)
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}

export function usePushNotifications() {
    /**
     * Cek dukungan browser dan inisialisasi state
     */
    const init = async () => {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            isSupported.value = false;
            return;
        }

        isSupported.value = true;
        permission.value = Notification.permission;

        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            isSubscribed.value = !!subscription;
        } catch (e) {
            isSubscribed.value = false;
        }
    };

    /**
     * Daftarkan service worker dan minta izin notifikasi
     */
    const subscribe = async () => {
        if (!isSupported.value) return false;

        try {
            // Daftarkan Service Worker
            const registration = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;

            // Minta izin dari browser
            const perm = await Notification.requestPermission();
            permission.value = perm;

            if (perm !== 'granted') return false;

            // Buat push subscription
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
            });

            // Kirim subscription ke server Laravel
            const subscriptionJson = subscription.toJSON();
            await axios.post('/api/push-subscriptions', {
                endpoint: subscriptionJson.endpoint,
                keys: subscriptionJson.keys,
            });

            isSubscribed.value = true;
            return true;
        } catch (e) {
            console.error('Gagal subscribe push notification:', e);
            return false;
        }
    };

    /**
     * Batalkan push subscription
     */
    const unsubscribe = async () => {
        try {
            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();
            if (!subscription) return;

            await axios.delete('/api/push-subscriptions', {
                data: { endpoint: subscription.endpoint }
            });

            await subscription.unsubscribe();
            isSubscribed.value = false;
        } catch (e) {
            console.error('Gagal unsubscribe push notification:', e);
        }
    };

    return {
        isSupported,
        permission,
        isSubscribed,
        init,
        subscribe,
        unsubscribe,
    };
}
