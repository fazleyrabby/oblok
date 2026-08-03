import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1';
const reverbPort = import.meta.env.VITE_REVERB_PORT ?? 8080;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

let echo = null;

export function getEcho() {
    if (echo) {
        return echo;
    }

    echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo = echo;

    return echo;
}

/**
 * Show a small toast notification for a realtime event.
 */
export function showToast(message, type = 'info') {
    const palette = {
        success: 'border-emerald-500/50 text-emerald-300',
        error: 'border-red-500/50 text-red-300',
        warning: 'border-amber-500/50 text-amber-300',
        info: 'border-indigo-500/50 text-indigo-300',
    };

    const container = document.querySelector('#realtime-toasts') ?? createToastContainer();

    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg bg-gray-900 border ${palette[type] ?? palette.info} shadow-lg text-sm font-medium max-w-sm animate-fade-in`;
    toast.textContent = message;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 300ms ease';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

function createToastContainer() {
    const el = document.createElement('div');
    el.id = 'realtime-toasts';
    el.className = 'fixed bottom-4 right-4 z-50 space-y-2 flex flex-col items-end';
    document.body.appendChild(el);

    return el;
}
