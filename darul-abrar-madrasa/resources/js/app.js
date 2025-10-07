import './bootstrap';

// CSRF helper
function getCsrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
}

// Global AJAX helper using Fetch API
window.ajax = async (url, options = {}) => {
    const {
        method = 'GET',
        headers = {},
        body = null,
        json = null,
        signal,
    } = options;

    const finalHeaders = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
        ...headers,
    };

    let fetchBody = body;
    if (json !== null && json !== undefined) {
        finalHeaders['Content-Type'] = 'application/json';
        fetchBody = JSON.stringify(json);
    }

    try {
        if (window.Alpine) Alpine.store('ui')?.setLoading(true);
        const res = await fetch(url, { method, headers: finalHeaders, body: fetchBody, signal });
        const contentType = res.headers.get('content-type') || '';
        let data;
        if (contentType.includes('application/json')) {
            data = await res.json();
        } else {
            data = await res.text();
        }
        if (!res.ok) {
            const message = (data && data.message) ? data.message : `Request failed with status ${res.status}`;
            throw new Error(message);
        }
        return data;
    } catch (err) {
        console.error('ajax error:', err);
        if (window.Alpine) Alpine.store('ui')?.addToast(err.message || 'Request failed', 'error');
        throw err;
    } finally {
        if (window.Alpine) Alpine.store('ui')?.setLoading(false);
    }
};

// Debounce utility
window.debounce = (fn, delay = 300) => {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(null, args), delay);
    };
};

// Clipboard utility
window.copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        if (window.Alpine) Alpine.store('ui')?.addToast('Copied to clipboard', 'success');
    } catch (e) {
        console.warn('Clipboard copy failed', e);
        if (window.Alpine) Alpine.store('ui')?.addToast('Copy failed', 'error');
    }
};

// Print helper for a section
window.printSection = (selector) => {
    const node = document.querySelector(selector);
    if (!node) return window.print();
    const w = window.open('', '_blank');
    if (!w) return;
    w.document.write('<html><head><title>Print</title>');
    document.querySelectorAll('link[rel="stylesheet"], style').forEach(el => w.document.write(el.outerHTML));
    w.document.write('</head><body>');
    w.document.write(node.outerHTML);
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    w.print();
    w.close();
};

// Promise-based confirmation helper (fallbacks to native confirm)
window.confirmAction = (message = 'Are you sure?') => {
    // If a custom modal system exists, integrate here. Fallback:
    return Promise.resolve(window.confirm(message));
};

// Alpine global UI store
document.addEventListener('alpine:init', () => {
    const genId = () => Math.random().toString(36).slice(2);

    Alpine.store('ui', {
        loading: false,
        toasts: [],
        setLoading(val) {
            this.loading = !!val;
        },
        addToast(message, type = 'info', duration = 5000) {
            const id = genId();
            this.toasts.push({ id, message, type });
            // Also call window.showToast if available (component container)
            if (typeof window.showToast === 'function') {
                window.showToast(message, type, duration);
            }
            setTimeout(() => this.removeToast(id), duration + 300);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    });
});

// Global unhandled error handler
window.addEventListener('error', (e) => {
    if (window.Alpine) Alpine.store('ui')?.addToast('An unexpected error occurred', 'error');
    console.error('Unhandled error:', e.error || e.message || e);
});
window.addEventListener('unhandledrejection', (e) => {
    if (window.Alpine) Alpine.store('ui')?.addToast('A request failed', 'error');
    console.error('Unhandled rejection:', e.reason || e);
});
