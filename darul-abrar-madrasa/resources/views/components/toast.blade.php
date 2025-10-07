<div id="toast-root" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>
<script>
(function() {
  if (window.showToast) return;

  const variants = {
    success: {
      bg: 'bg-green-600',
      icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
      aria: 'polite'
    },
    error: {
      bg: 'bg-red-600',
      icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
      aria: 'assertive'
    },
    warning: {
      bg: 'bg-yellow-600',
      icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>',
      aria: 'polite'
    },
    info: {
      bg: 'bg-blue-600',
      icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01"/></svg>',
      aria: 'polite'
    }
  };

  function clampVisible(root, limit = 3) {
    const items = Array.from(root.children);
    if (items.length > limit) {
      const excess = items.length - limit;
      for (let i = 0; i < excess; i++) {
        const n = items[i];
        n.classList.add('opacity-0', 'translate-y-1');
        n.addEventListener('transitionend', () => n.remove(), { once: true });
      }
    }
  }

  window.showToast = function(message, type = 'info', duration = 5000) {
    try {
      const root = document.getElementById('toast-root');
      if (!root) return;

      const v = variants[type] || variants.info;

      const wrapper = document.createElement('div');
      wrapper.setAttribute('role', 'alert');
      wrapper.setAttribute('aria-live', v.aria);
      wrapper.className = `pointer-events-auto rounded-md px-4 py-3 shadow-lg text-white flex items-start gap-2 transition-all duration-300 transform ${v.bg}`;

      const closeBtn = document.createElement('button');
      closeBtn.className = 'ml-2 text-white/80 hover:text-white focus:outline-none';
      closeBtn.setAttribute('aria-label', 'Dismiss notification');
      closeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';

      const progress = document.createElement('div');
      progress.className = 'absolute left-0 bottom-0 h-1 bg-white/40';
      progress.style.width = '100%';
      progress.style.transition = `width ${duration}ms linear`;

      const icon = document.createElement('div');
      icon.className = 'inline-flex items-center justify-center h-5 w-5 rounded-full bg-white/20 mr-1';
      icon.innerHTML = v.icon;

      const textWrap = document.createElement('div');
      const body = document.createElement('div');
      body.className = 'text-sm';
      body.textContent = message;

      textWrap.appendChild(body);

      const inner = document.createElement('div');
      inner.className = 'relative flex items-start';
      inner.appendChild(icon);
      inner.appendChild(textWrap);
      inner.appendChild(closeBtn);

      wrapper.appendChild(inner);
      wrapper.appendChild(progress);

      // Insert and animate
      root.appendChild(wrapper);
      clampVisible(root, 3);
      setTimeout(() => { progress.style.width = '0%'; }, 10);

      // Close handlers
      const remove = () => {
        wrapper.classList.add('opacity-0', 'translate-y-1');
        wrapper.addEventListener('transitionend', () => wrapper.remove(), { once: true });
      };
      closeBtn.addEventListener('click', remove);

      // Auto remove after duration
      setTimeout(remove, duration);
    } catch (e) {
      console && console.warn && console.warn('Toast error', e);
    }
  };
})();
</script>
