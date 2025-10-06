<div id="toast-root" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>
<script>
(function() {
  if (window.showToast) return;

  const typeStyles = {
    success: 'bg-green-600',
    error: 'bg-red-600',
    warning: 'bg-yellow-600',
    info: 'bg-blue-600'
  };

  window.showToast = function(message, type = 'info', duration = 3000) {
    try {
      const root = document.getElementById('toast-root');
      if (!root) return;

      const wrapper = document.createElement('div');
      wrapper.className = 'pointer-events-auto rounded-md px-4 py-3 shadow-lg text-white flex items-start gap-2 transition-all duration-300 transform';
      wrapper.classList.add(typeStyles[type] || typeStyles.info);

      const icon = document.createElement('span');
      icon.className = 'inline-flex items-center justify-center h-5 w-5 rounded-full bg-white/20 mr-1';
      icon.innerHTML = '&#9888;';

      const textWrap = document.createElement('div');

      const title = document.createElement('div');
      title.className = 'text-sm font-semibold capitalize';
      title.textContent = type;

      const body = document.createElement('div');
      body.className = 'text-sm';
      body.textContent = message;

      textWrap.appendChild(title);
      textWrap.appendChild(body);

      wrapper.appendChild(icon);
      wrapper.appendChild(textWrap);

      root.appendChild(wrapper);

      // Auto remove after duration
      setTimeout(() => {
        wrapper.classList.add('opacity-0', 'translate-y-1');
        wrapper.addEventListener('transitionend', () => wrapper.remove(), { once: true });
      }, duration);
    } catch (e) {
      // Fail silently if something goes wrong
      console && console.warn && console.warn('Toast error', e);
    }
  };
})();
</script>
