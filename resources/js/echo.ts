import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/*
|--------------------------------------------------------------------------
| Laravel Echo — Reverb WebSocket Client
|--------------------------------------------------------------------------
|
| Echo is configured to connect to our self-hosted Reverb WebSocket server.
| Pusher-js is used as the transport library (Reverb is Pusher-protocol
| compatible). All channel credentials are read from Vite env vars so
| no secrets are hardcoded in the frontend bundle.
|
*/
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
