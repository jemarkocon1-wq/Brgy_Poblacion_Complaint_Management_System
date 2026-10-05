import Alpine from 'alpinejs';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Alpine = Alpine;
window.Pusher = Pusher;

Alpine.start();

window.toggleAppearance = async () => {
    const appearance = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    document.documentElement.classList.toggle('dark', appearance === 'dark');
    localStorage.setItem('appearance', appearance);

    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    if (token && app.dataset.role) {
        try {
            const response = await fetch('/appearance', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({ appearance, guard: app.dataset.role }),
            });

            if (!response.ok) {
                throw new Error('Unable to save your appearance preference.');
            }
        } catch {
            const feedback = document.querySelector('[data-appearance-error]');

            if (feedback) {
                feedback.textContent = 'This theme is saved on this device but could not be saved to your account.';
                feedback.classList.remove('hidden');
            }
        }
    }
};

const app = document.body;
const role = app.dataset.role;
const userId = Number(app.dataset.userId);

if (role && window.Echo === undefined && import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

function statusBucket(status) {
    return status === 'approved' ? 'in_progress' : status;
}

function changeCounter(name, amount) {
    document.querySelectorAll(`[data-live-count="${name}"]`).forEach((element) => {
        const current = Number(element.textContent || '0');
        element.textContent = String(Math.max(0, current + amount));
    });
}

function receiveStatusChange(event) {
    if (role === 'resident' && event.resident_id !== userId) {
        return;
    }

    document.querySelectorAll(`[data-complaint-status="${event.complaint_id}"]`).forEach((badge) => {
        badge.textContent = event.status_label;
        badge.dataset.status = event.status;
        badge.className = `inline-flex rounded-full px-3 py-1 text-xs font-semibold ${statusClasses[event.status]}`;
    });

    const previous = statusBucket(event.previous_status);
    const next = statusBucket(event.status);

    if (previous && previous !== next) {
        changeCounter(previous, -1);
        changeCounter(next, 1);
    }

    if (role === 'staff' && event.assigned_staff_id === userId) {
        if (event.status === 'approved' && previous !== 'in_progress') {
            changeCounter('assigned', 1);
        } else if (event.status === 'resolved' && previous === 'in_progress') {
            changeCounter('assigned', -1);
        }
    }

    incrementNotifications();
    window.dispatchEvent(new CustomEvent('complaint-updated', { detail: event }));
}

const statusClasses = {
    pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
    approved: 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
    rejected: 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
    resolved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
};

function incrementNotifications() {
    document.querySelectorAll('[data-notification-count]').forEach((element) => {
        const next = Number(element.textContent || '0') + 1;
        element.textContent = String(next);
        element.classList.remove('hidden');
    });
}

if (window.Echo && role) {
    const channels = role === 'resident'
        ? [`complaints.resident.${userId}`]
        : role === 'staff'
            ? ['complaints.staff']
            : ['complaints.admin'];

    channels.forEach((channelName) => {
        window.Echo.private(channelName)
            .listen('.complaint.status-changed', receiveStatusChange)
            .listen('.complaint.submitted', (event) => {
                if (role === 'resident' && event.resident_id !== userId) {
                    return;
                }

                changeCounter('pending', 1);
                changeCounter('total', 1);
                incrementNotifications();
                window.dispatchEvent(new CustomEvent('complaint-submitted', { detail: event }));
            });
    });
}
