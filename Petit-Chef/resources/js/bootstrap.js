import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.Pusher = Pusher;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialiser Echo après que le DOM soit prêt
document.addEventListener('DOMContentLoaded', () => {
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY;

    if (!pusherKey) {
        console.warn('[PetitChef] VITE_PUSHER_APP_KEY manquant — temps réel désactivé.');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'eu',
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        },
    });

    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        console.log('[PetitChef Echo]', states.previous, '→', states.current);
    });

    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('[PetitChef Echo] ✅ Connecté à Pusher');
        // Déclencher l'init realtime une fois connecté
        window.dispatchEvent(new Event('echo:ready'));
    });

    window.Echo.connector.pusher.connection.bind('error', (err) => {
        console.error('[PetitChef Echo] ❌ Erreur:', err);
    });
});
