/**
 * Kamadhenu Goushala — 100% In-Site Full-Page Bilingual Translator (English ⇄ Kannada)
 * Translates EVERY single text node, paragraph, heading, card, button, and form across all pages.
 */

(function () {
  'use strict';

  const LANG_KEY = 'kamadhenu_site_lang';
  const CACHE_KEY = 'kamadhenu_kn_cache_v2';
  const SUPPORTED = ['en', 'kn'];

  // 1. In-Memory and LocalStorage Translation Cache
  let transCache = {};
  try {
    const raw = localStorage.getItem(CACHE_KEY);
    if (raw) transCache = JSON.parse(raw);
  } catch (e) {
    transCache = {};
  }

  function saveCache() {
    try {
      localStorage.setItem(CACHE_KEY, JSON.stringify(transCache));
    } catch (e) {}
  }

  // 2. Pre-baked Instant Dictionary for zero-latency core UI
  const INSTANT_DICT = {
    // Navigation & Actions
    'HOME': 'ಮುಖಪುಟ',
    'ABOUT': 'ನಮ್ಮ ಬಗ್ಗೆ',
    'OUR COWS': 'ನಮ್ಮ ಗೋವುಗಳು',
    'BREEDS': 'ತಳಿಗಳು',
    'GAU SEVA': 'ಗೋ ಸೇವೆ',
    'GALLERY': 'ಗ್ಯಾಲರಿ',
    'PRODUCTS': 'ಉತ್ಪನ್ನಗಳು',
    'BLOG': 'ಬ್ಲಾಗ್',
    'EVENTS': 'ಕಾರ್ಯಕ್ರಮಗಳು',
    'CONTACT': 'ಸಂಪರ್ಕಿಸಿ',
    'DONATE': 'ದೇಣಿಗೆ',
    'DONATE NOW': 'ಈಗಲೇ ದೇಣಿಗೆ ನೀಡಿ',
    'SUPPORT GAU SEVA': 'ಗೋ ಸೇವೆಗೆ ಬೆಂಬಲ ನೀಡಿ',
    'WhatsApp': 'ವಾಟ್ಸಾಪ್',
    'Language / ಭಾಷೆ': 'ಭಾಷೆ / Language',
    'Select Language / ಭಾಷೆ': 'ಭಾಷೆ ಆಯ್ಕೆಮಾಡಿ',

    // Core Brand
    'KAMADHENU': 'ಕಾಮಧೇನು',
    'GOUSHALA': 'ಗೋಶಾಲೆ',
    'Kamadhenu Goushala': 'ಕಾಮಧೇನು ಗೋಶಾಲೆ',
    'Kamadhenu Goushala Trust': 'ಕಾಮಧೇನು ಗೋಶಾಲೆ ಟ್ರಸ್ಟ್',
    'Serving Gau Mata With Pure Devotion & Vedic Care': 'ಪೂರ್ಣ ಭಕ್ತಿಯೊಂದಿಗೆ ಗೋಮಾತೆಯ ಸೇವೆ ಮತ್ತು ವೈದಿಕ ರಕ್ಷಣೆ',

    // Common Links & Breadcrumbs
    'Home': 'ಮುಖಪುಟ',
    'About': 'ನಮ್ಮ ಬಗ್ಗೆ',
    'About Us': 'ನಮ್ಮ ಬಗ್ಗೆ',
    'Our Cows': 'ನಮ್ಮ ಗೋವುಗಳು',
    'Breeds': 'ತಳಿಗಳು',
    'Cow Breeds': 'ಗೋ ತಳಿಗಳು',
    'Gau Seva': 'ಗೋ ಸೇವೆ',
    'Donate': 'ದೇಣಿಗೆ ನೀಡಿ',
    'Adopt a Cow': 'ಗೋ ದತ್ತು ಪಡೆಯಿರಿ',
    'Adopt / Sponsor a Cow': 'ಗೋವನ್ನು ದತ್ತು ಪಡೆಯಿರಿ / ಪ್ರಾಯೋಜಿಸಿ',
    'Organic Store': 'ಸಾವಯವ ಮಳಿಗೆ',
    'Photo Gallery': 'ಫೋಟೋ ಗ್ಯಾಲರಿ',
    'Vedic Blog': 'ವೈದಿಕ ಬ್ಲಾಗ್',
    'Vedic Articles': 'ವೈದಿಕ ಲೇಖನಗಳು',
    'Upcoming Events': 'ಮುಂಬರುವ ಕಾರ್ಯಕ್ರಮಗಳು',
    'Contact Us': 'ಸಂಪರ್ಕಿಸಿ',
    'Privacy Policy': 'ಗೌಪ್ಯತಾ ನೀತಿ',
    'Terms & Conditions': 'ನಿಯಮಗಳು ಮತ್ತು ಷರತ್ತುಗಳು',

    // Badges & Buttons
    '🐄 Indigenous Breeds': '🐄 ದೇಶಿ ತಳಿಗಳು',
    '🌿 Organic Farm': '🌿 ಸಾವಯವ ತೋಟ',
    '🏥 Vet Hospital': '🏥 ಪಶು ಆಸ್ಪತ್ರೆ',
    '80G Tax Exemption': '80G ತೆರಿಗೆ ವಿನಾಯಿತಿ',
    'Quick Links': 'ತ್ವರಿತ ಲಿಂಕ್‌ಗಳು',
    'Resources': 'ಸಂಪನ್ಮೂಲಗಳು',
    'Newsletter & Direct UPI': 'ಸುದ್ದಿಪತ್ರ & ನೇರ UPI',
    'Quick UPI ID': 'ತ್ವರಿತ UPI ಐಡಿ',
    'View Details': 'ವಿವರಗಳನ್ನು ನೋಡಿ',
    'Read More': 'ಮತ್ತಷ್ಟು ಓದಿ',
    'Add to Cart': 'ಕಾರ್ಟ್‌ಗೆ ಸೇರಿಸಿ',
    'Checkout': 'ಚೆಕ್‌ಔಟ್',
    'CONTACT US': 'ಸಂಪರ್ಕಿಸಿ',
    'Submit': 'ಸಲ್ಲಿಸಿ',
    'Send Message': 'ಸಂದೇಶ ಕಳುಹಿಸಿ',
    'Subscribe': 'ಚಂದಾದಾರರಾಗಿ',
    'Join': 'ಸೇರಿ',
    'All': 'ಎಲ್ಲ',
    'All Breeds': 'ಎಲ್ಲ ತಳಿಗಳು',
    'All Photos': 'ಎಲ್ಲ ಫೋಟೋಗಳು',
    'All Products': 'ಎಲ್ಲ ಉತ್ಪನ್ನಗಳು',
    'All Categories': 'ಎಲ್ಲ ವಿಭಾಗಗಳು',
    'Your Organic Cart': 'ನಿಮ್ಮ ಸಾವಯವ ಕಾರ್ಟ್',
    'Subtotal:': 'ಒಟ್ಟು ಮೊತ್ತ:',
    'Your cart is empty.': 'ನಿಮ್ಮ ಕಾರ್ಟ್ ಖಾಲಿಯಾಗಿದೆ.'
  };

  // Populate cache from instant dict
  for (const [k, v] of Object.entries(INSTANT_DICT)) {
    transCache[k.trim()] = v;
  }

  // 3. Get Active Language
  function getActiveLang() {
    const urlParams = new URLSearchParams(window.location.search);
    const urlLang = urlParams.get('lang');
    if (urlLang && SUPPORTED.includes(urlLang)) {
      localStorage.setItem(LANG_KEY, urlLang);
      document.cookie = `site_lang=${urlLang}; path=/; max-age=31536000;`;
      return urlLang;
    }

    const saved = localStorage.getItem(LANG_KEY);
    if (saved && SUPPORTED.includes(saved)) {
      return saved;
    }

    const match = document.cookie.match(/(?:^|;\s*)site_lang=([^;]*)/);
    if (match && SUPPORTED.includes(match[1])) {
      return match[1];
    }

    return 'en';
  }

  // 4. Translate a single string or query headless endpoint if needed
  async function fetchTranslation(text) {
    const trimmed = text.trim();
    if (!trimmed || /^\d+$/.test(trimmed) || trimmed.length <= 1) return text;
    if (transCache[trimmed]) return transCache[trimmed];

    try {
      const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=kn&dt=t&q=${encodeURIComponent(trimmed)}`;
      const res = await fetch(url);
      if (res.ok) {
        const data = await res.json();
        if (data && data[0]) {
          let translated = '';
          for (let i = 0; i < data[0].length; i++) {
            if (data[0][i][0]) translated += data[0][i][0];
          }
          if (translated) {
            transCache[trimmed] = translated;
            saveCache();
            return translated;
          }
        }
      }
    } catch (e) {
      console.warn('Translation fetch note:', trimmed, e);
    }
    return text;
  }

  // 5. Collect all eligible text nodes on the page
  function collectTextNodes(root = document.body) {
    const walker = document.createTreeWalker(
      root,
      NodeFilter.SHOW_TEXT,
      {
        acceptNode: function (node) {
          if (!node || !node.nodeValue) return NodeFilter.FILTER_REJECT;
          const parent = node.parentElement;
          if (!parent) return NodeFilter.FILTER_REJECT;

          const tag = parent.tagName.toLowerCase();
          if (['script', 'style', 'noscript', 'code', 'pre'].includes(tag)) {
            return NodeFilter.FILTER_REJECT;
          }
          if (parent.closest('.site-phone, .site-email, .site-upi, .site-address, .lang-switcher-pill, .lang-header-dropdown, .notranslate')) {
            return NodeFilter.FILTER_REJECT;
          }
          const val = node.nodeValue.trim();
          if (val.length === 0 || /^[0-9+@#%:.,\-_/\\|()]+$/.test(val)) {
            return NodeFilter.FILTER_REJECT;
          }
          return NodeFilter.FILTER_ACCEPT;
        }
      }
    );

    const nodes = [];
    while (walker.nextNode()) {
      nodes.push(walker.currentNode);
    }
    return nodes;
  }

  // 6. Translate the Entire DOM into Kannada
  let isTranslating = false;
  async function translateWholePageToKannada() {
    if (isTranslating) return;
    isTranslating = true;

    const nodes = collectTextNodes();
    const uncachedTexts = new Set();

    // First pass: Save original text and apply any cached translations immediately
    nodes.forEach(node => {
      if (typeof node._origEnText === 'undefined') {
        node._origEnText = node.nodeValue;
      }
      const orig = node._origEnText.trim();
      if (transCache[orig]) {
        node.nodeValue = node._origEnText.replace(orig, transCache[orig]);
      } else {
        uncachedTexts.add(orig);
      }
    });

    // Translate placeholders
    document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
      if (!el.dataset.origPlaceholder) {
        el.dataset.origPlaceholder = el.getAttribute('placeholder') || '';
      }
      const orig = el.dataset.origPlaceholder.trim();
      if (transCache[orig]) {
        el.setAttribute('placeholder', transCache[orig]);
      } else if (orig) {
        uncachedTexts.add(orig);
      }
    });

    // Second pass: Batch fetch any uncached phrases in parallel chunks
    if (uncachedTexts.size > 0) {
      const batchList = Array.from(uncachedTexts);
      const chunkSize = 12;
      for (let i = 0; i < batchList.length; i += chunkSize) {
        const chunk = batchList.slice(i, i + chunkSize);
        await Promise.all(chunk.map(txt => fetchTranslation(txt)));
      }

      // Apply newly fetched translations to all nodes
      nodes.forEach(node => {
        const orig = (node._origEnText || node.nodeValue).trim();
        if (transCache[orig]) {
          node.nodeValue = (node._origEnText || node.nodeValue).replace(orig, transCache[orig]);
        }
      });

      // Apply newly fetched placeholders
      document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
        const orig = (el.dataset.origPlaceholder || '').trim();
        if (transCache[orig]) {
          el.setAttribute('placeholder', transCache[orig]);
        }
      });
    }

    isTranslating = false;
  }

  // 7. Revert the Entire DOM back to Original English
  function revertWholePageToEnglish() {
    const nodes = collectTextNodes();
    nodes.forEach(node => {
      if (typeof node._origEnText !== 'undefined') {
        node.nodeValue = node._origEnText;
      }
    });

    document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach(el => {
      if (el.dataset.origPlaceholder) {
        el.setAttribute('placeholder', el.dataset.origPlaceholder);
      }
    });
  }

  // 8. Master Switch Function
  window.setWebsiteLanguage = function (lang, updateUrl = true) {
    if (!SUPPORTED.includes(lang)) return;

    localStorage.setItem(LANG_KEY, lang);
    document.cookie = `site_lang=${lang}; path=/; max-age=31536000;`;

    if (lang === 'kn') {
      document.documentElement.lang = 'kn';
      document.body.classList.add('lang-kn');
      translateWholePageToKannada();
    } else {
      document.documentElement.lang = 'en';
      document.body.classList.remove('lang-kn');
      revertWholePageToEnglish();
    }

    updateHeaderSwitcherUI(lang);

    if (updateUrl && window.history && window.history.replaceState) {
      const url = new URL(window.location);
      url.searchParams.set('lang', lang);
      window.history.replaceState({}, '', url);
    }
  };

  // 9. Update UI Switcher Controls
  function updateHeaderSwitcherUI(lang) {
    // Top-infobar pills
    document.querySelectorAll('.lang-option-btn').forEach(btn => {
      if (btn.dataset.lang === lang) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    // Dropdown labels
    document.querySelectorAll('.lang-current-label').forEach(label => {
      label.textContent = (lang === 'kn') ? 'ಕನ್ನಡ' : 'English';
    });

    document.querySelectorAll('.lang-select-item').forEach(item => {
      if (item.dataset.lang === lang) {
        item.classList.add('active');
      } else {
        item.classList.remove('active');
      }
    });

    // Mobile quick toggle
    document.querySelectorAll('.lang-quick-label').forEach(label => {
      label.textContent = (lang === 'kn') ? 'English' : 'ಕನ್ನಡ';
    });

    // Drawer buttons
    document.querySelectorAll('.lang-drawer-btn').forEach(btn => {
      if (btn.dataset.lang === lang) {
        btn.classList.add('btn-gold', 'active');
        btn.classList.remove('btn-outline-forest');
      } else {
        btn.classList.remove('btn-gold', 'active');
        btn.classList.add('btn-outline-forest');
      }
    });
  }

  // 10. Attach Click Handlers
  function attachLanguageEvents() {
    document.querySelectorAll('[data-lang]').forEach(el => {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        const targetLang = this.getAttribute('data-lang');
        if (targetLang && targetLang !== getActiveLang()) {
          window.setWebsiteLanguage(targetLang, true);
        }
      });
    });

    document.querySelectorAll('.lang-quick-toggle-btn').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const current = getActiveLang();
        const next = (current === 'kn') ? 'en' : 'kn';
        window.setWebsiteLanguage(next, true);
      });
    });
  }

  // 11. Run on DOM load
  document.addEventListener('DOMContentLoaded', () => {
    const active = getActiveLang();
    updateHeaderSwitcherUI(active);
    attachLanguageEvents();

    if (active === 'kn') {
      document.documentElement.lang = 'kn';
      document.body.classList.add('lang-kn');
      translateWholePageToKannada();
    }
  });
})();
