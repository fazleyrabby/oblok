
import Alpine from 'alpinejs';

import { getEcho, showToast } from './realtime';

window.Alpine = Alpine;
window.showRealtimeToast = showToast;

// Reusable live-stream poller: toggles a pulsing Live badge and emits a
// `live-tick` event every N ms while connected. Dashboards listen for the
// event to refetch their data without a full page reload.
Alpine.data('liveStream', (options = {}) => ({
    connected: true,
    timer: null,
    refreshMs: options.refreshMs ?? 5000,
    onTick: options.onTick ?? null,

    init() {
        this.timer = setInterval(() => this.tick(), this.refreshMs);
    },

    tick() {
        if (!this.connected) {
            return;
        }

        if (typeof this.onTick === 'function') {
            this.onTick();
        }
    },

    toggle() {
        this.connected = !this.connected;
    },
}));

// Subscribe to realtime events for the current project, if any.
// The body carries the active project id (see layouts/app.blade.php).
const projectId = document.body.dataset.projectId;

if (projectId && window.EchoDisabled !== true) {
    const echo = getEcho();

    echo.private(`projects.${projectId}`)
        .listen('.service.health.changed', (event) => {
            showToast(
                `[${event.status.toUpperCase()}] ${event.service_name} is ${event.status}`,
                event.status === 'healthy' ? 'success' : 'error',
            );
        })
        .listen('.alert.triggered', (event) => {
            showToast(`Alert triggered: ${event.subject}`, 'warning');
        })
        .listen('.deployment.status.changed', (event) => {
            showToast(
                `Deployment ${event.status}: ${event.commit_message ?? event.commit_hash ?? event.deployment_id}`,
                event.status === 'successful' ? 'success' : 'warning',
            );
        });
}

Alpine.start();
