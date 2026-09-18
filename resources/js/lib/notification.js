import axios from 'axios';

export async function requestNotificationPermission() {
    if (!('serviceWorker' in navigator && 'PushManager' in window)) {
        return false;
    }

    if (!window.Notification) {
        return false;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission != 'granted') {
            console.error('Akses notifikasi ditolak');
            return false;
        }
        
        const registration = await navigator.serviceWorker.register('/sw.js');
        await navigator.serviceWorker.ready;

        const existingSubscription = await registration.pushManager.getSubscription();
        if (existingSubscription) {
            // kalau sudah subcribe ya nggak subcribe lagi
            return true;
        }
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: import.meta.env.VITE_VAPID_PUBLIC_KEY,
        });

        await axios.post('/notifications/subscribe', subscription);
        return true;
    } catch (e) {
        console.error(e);
        return false;
    }
}
