/**
 * Kamadhenu Goushala — Dark & Light Theme Controller
 * Switches theme seamlessly across all pages and persists user preference.
 */

(function () {
  'use strict';

  const THEME_KEY = 'kamadhenu_theme';

  // Get current active theme
  function getCurrentTheme() {
    const saved = localStorage.getItem(THEME_KEY);
    if (saved === 'dark' || saved === 'light') {
      return saved;
    }
    const match = document.cookie.match(/(?:^|;\s*)kamadhenu_theme=([^;]*)/);
    if (match && (match[1] === 'dark' || match[1] === 'light')) {
      return match[1];
    }
    return 'light';
  }

  // Apply theme to DOM and update toggle icons
  function applyTheme(theme) {
    const isDark = (theme === 'dark');

    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    if (isDark) {
      document.documentElement.classList.add('dark-theme');
      if (document.body) document.body.classList.add('dark-theme');
    } else {
      document.documentElement.classList.remove('dark-theme');
      if (document.body) document.body.classList.remove('dark-theme');
    }

    localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    document.cookie = `kamadhenu_theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000;`;

    // Update toggle icons on all theme buttons
    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      const moonIcon = btn.querySelector('.theme-icon-moon');
      const sunIcon  = btn.querySelector('.theme-icon-sun');

      if (moonIcon && sunIcon) {
        if (isDark) {
          moonIcon.classList.add('d-none');
          sunIcon.classList.remove('d-none');
          btn.setAttribute('title', 'Switch to Light Theme');
        } else {
          sunIcon.classList.add('d-none');
          moonIcon.classList.remove('d-none');
          btn.setAttribute('title', 'Switch to Dark Theme');
        }
      }
    });
  }

  // Toggle between dark and light
  window.toggleTheme = function () {
    const current = getCurrentTheme();
    const next = (current === 'dark') ? 'light' : 'dark';
    applyTheme(next);
  };

  // Attach event handlers
  function initThemeControls() {
    const active = getCurrentTheme();
    applyTheme(active);

    document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
      btn.removeEventListener('click', window.toggleTheme);
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        window.toggleTheme();
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeControls);
  } else {
    initThemeControls();
  }
})();
