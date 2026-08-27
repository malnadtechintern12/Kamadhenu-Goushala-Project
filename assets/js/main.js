// Global Frontend Script for Kamadhenu Goushala

document.addEventListener('DOMContentLoaded', async () => {
  // 1. Highlight Active Nav Item
  highlightActiveNav();

  // 2. Fetch and apply global website settings
  await loadGlobalSettings();

  // 3. Ensure Floating WhatsApp Button is present on every page
  ensureFloatingWhatsAppButton();

  // 4. Setup Newsletter Subscription
  setupNewsletterForm();

  // 5. Setup Global Contact Form if present
  setupContactForm();

  // 6. Initialize Live Counters Animation
  initStatCounters();
});

function highlightActiveNav() {
  const currentPath = window.location.pathname.toLowerCase();
  const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (!href) return;

    const cleanHref = href.toLowerCase();
    if (currentPath === '/' && (cleanHref === '/' || cleanHref === '/index.html' || cleanHref === 'index.html')) {
      link.classList.add('active');
    } else if (cleanHref !== '/' && cleanHref !== 'index.html' && currentPath.includes(cleanHref.replace('/', ''))) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
}

async function loadGlobalSettings() {
  try {
    const res = await API.get('/settings');
    if (res && res.success && res.data) {
      window.GOUSHALA_SETTINGS = res.data;
      const s = res.data;

      // Update phone numbers
      document.querySelectorAll('.site-phone').forEach(el => {
        el.textContent = s.phone_primary || '+91 98450 88990';
        if (el.tagName === 'A') el.href = `tel:${(s.phone_primary || '').replace(/[^0-9+]/g, '')}`;
      });

      // Update email addresses
      document.querySelectorAll('.site-email').forEach(el => {
        el.textContent = s.email_primary || 'info@kamadhenugoushala.org';
        if (el.tagName === 'A') el.href = `mailto:${s.email_primary || 'info@kamadhenugoushala.org'}`;
      });

      // Update address
      document.querySelectorAll('.site-address').forEach(el => {
        el.textContent = s.address || 'Bengaluru, Karnataka, India';
      });

      // Update WhatsApp links
      document.querySelectorAll('.whatsapp-link, a[href*="wa.me"], a[href*="whatsapp.com"]').forEach(el => {
        if (el.getAttribute('onclick') || el.classList.contains('no-auto-wa') || el.closest('#shareWa')) return;
        const num = (s.whatsapp_number || '919845088990').replace(/[^0-9]/g, '');
        const sName = s.site_name || (typeof SITE_NAME !== 'undefined' ? SITE_NAME : 'Kamadhenu Goushala');
        const sTag = s.site_tagline || (typeof SITE_TAGLINE !== 'undefined' ? SITE_TAGLINE : 'Serving Gau Mata With Pure Devotion');
        const bUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : (window.location.origin + (window.location.pathname.startsWith('/kamadhenu-goushala') ? '/kamadhenu-goushala' : ''));
        
        let msg = "🌸 *" + sName.toUpperCase() + "*\n";
        msg += "🌿 _" + sTag + "_\n";
        msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
        msg += "ℹ️ *About Us:* Dedicated to ethical protection, Vedic healthcare & preservation of indigenous Desi cows.\n\n";
        msg += "💬 *Message:* \n";
        msg += "Namaste Admin! I am reaching out through your official website. I would like to know more about Gau Seva, cow adoption, sanctuary visits, and daily activities. 🙏\n\n";
        msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
        msg += "🌐 *Website:* " + bUrl + "/index.php";

        el.href = `https://wa.me/${num}?text=${encodeURIComponent(msg)}`;
      });

      // Update Social Media Links
      if (s.facebook_url) document.querySelectorAll('.social-fb').forEach(el => el.href = s.facebook_url);
      if (s.instagram_url) document.querySelectorAll('.social-insta').forEach(el => el.href = s.instagram_url);
      if (s.youtube_url) document.querySelectorAll('.social-yt').forEach(el => el.href = s.youtube_url);

      // Update UPI ID
      document.querySelectorAll('.site-upi').forEach(el => {
        el.textContent = s.donation_upi_id || 'kamadhenu@sbi';
      });

      // Update Live Stat Numbers if present
      if (s.stat_cows_served) document.querySelectorAll('.stat-val-cows').forEach(el => el.textContent = s.stat_cows_served);
      if (s.stat_donors) document.querySelectorAll('.stat-val-donors').forEach(el => el.textContent = s.stat_donors);
      if (s.stat_years_seva) document.querySelectorAll('.stat-val-years').forEach(el => el.textContent = s.stat_years_seva);
      if (s.stat_breeds) document.querySelectorAll('.stat-val-breeds').forEach(el => el.textContent = s.stat_breeds);
    }
  } catch (err) {
    console.warn('Could not load dynamic settings:', err.message);
  }
}

function setupNewsletterForm() {
  const forms = document.querySelectorAll('.newsletter-form');
  forms.forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const input = form.querySelector('input[type="email"]');
      const submitBtn = form.querySelector('button[type="submit"]');

      if (!input || !input.value.trim()) return;

      try {
        if (submitBtn) submitBtn.disabled = true;
        const res = await API.post('/newsletter', { email: input.value.trim() });
        showToast(res.message || 'Subscribed successfully!');
        input.value = '';
      } catch (err) {
        showToast(err.message || 'Subscription failed.', 'error');
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  });
}

function setupContactForm() {
  const contactForm = document.getElementById('mainContactForm');
  if (!contactForm) return;

  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = contactForm.querySelector('button[type="submit"]');

    const payload = {
      name: contactForm.name ? contactForm.name.value.trim() : '',
      email: contactForm.email ? contactForm.email.value.trim() : '',
      phone: contactForm.phone ? contactForm.phone.value.trim() : '',
      subject: contactForm.subject ? contactForm.subject.value.trim() : '',
      message: contactForm.message ? contactForm.message.value.trim() : ''
    };

    if (!payload.name || !payload.email || !payload.message) {
      showToast('Please fill in your name, email, and message.', 'error');
      return;
    }

    try {
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
      }

      const res = await API.post('/contact', payload);
      showToast(res.message || 'Message sent successfully!');
      contactForm.reset();
    } catch (err) {
      showToast(err.message || 'Failed to send message.', 'error');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill me-2"></i>Send Message';
      }
    }
  });
}

function initStatCounters() {
  const counters = document.querySelectorAll('.stat-number');
  if (counters.length === 0) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const rawText = el.innerText;
        const targetNum = parseInt(rawText.replace(/[^0-9]/g, ''), 10);

        if (!isNaN(targetNum) && targetNum > 0) {
          let count = 0;
          const step = Math.ceil(targetNum / 35);
          const interval = setInterval(() => {
            count += step;
            if (count >= targetNum) {
              el.innerText = targetNum + (rawText.includes('+') ? '+' : '');
              clearInterval(interval);
            } else {
              el.innerText = count + (rawText.includes('+') ? '+' : '');
            }
          }, 35);
        }
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.5 });

  counters.forEach(c => observer.observe(c));
}

// Global Product Card Quantity & WhatsApp Order Handlers
function changeCardQty(prodId, delta) {
  const input = document.getElementById('qty_' + prodId);
  if (!input) return;
  let val = parseInt(input.value, 10) || 1;
  val = Math.max(1, val + delta);
  input.value = val;
}

function orderProductWhatsApp(id, name, price, customWp = '', image = '') {
  const qtyInput = document.getElementById('qty_' + id);
  const quantity = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
  const lineTotal = (parseFloat(price) || 0) * quantity;
  let targetWp = customWp;
  if (!targetWp) {
    targetWp = (typeof ORDER_WHATSAPP_NUMBER !== 'undefined' && ORDER_WHATSAPP_NUMBER) 
      ? ORDER_WHATSAPP_NUMBER 
      : (window.GOUSHALA_SETTINGS?.whatsapp_number || '919845088990');
  }
  
  const siteName = (typeof SITE_NAME !== 'undefined' && SITE_NAME) ? SITE_NAME : (window.GOUSHALA_SETTINGS?.site_name || 'Kamadhenu Goushala');
  const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : (window.location.origin + (window.location.pathname.startsWith('/kamadhenu-goushala') ? '/kamadhenu-goushala' : ''));
  
  let msg = "🌸 *" + siteName.toUpperCase() + "*\n";
  msg += "🌿 _Vedic Organic Store & Cow Sanctuary_\n";
  msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
  msg += "🛒 *PRODUCT ORDER ENQUIRY*\n\n";
  msg += "📦 *Product Details:*\n";
  msg += "• *Product:* " + name + "\n";
  msg += "• *Quantity:* " + quantity + "\n";
  msg += "• *Unit Price:* ₹" + parseFloat(price).toFixed(2) + " each\n";
  msg += "• *Total Amount:* ₹" + lineTotal.toFixed(2) + "\n\n";
  msg += "💬 *Message:*\n";
  msg += "Namaste! I would like to order / enquire about this organic product from " + siteName + ". Please share availability, delivery options, and payment details. 🙏\n\n";
  msg += "🛍️ *Store Page:*\n";
  msg += baseUrl + "/products.php\n";
  msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
  msg += "🌐 *Official Website:* " + baseUrl + "/index.php";

  const cleanNumber = targetWp.toString().replace(/[^0-9]/g, '');
  const url = "https://wa.me/" + cleanNumber + "?text=" + encodeURIComponent(msg);
  window.open(url, "_blank");
}

function getSevaWhatsAppUrl(seva, customWp = '') {
  let targetWp = customWp || (seva && seva.whatsapp_number) || '';
  if (!targetWp) {
    targetWp = (window.GOUSHALA_SETTINGS?.whatsapp_number || '919845088990');
  }
  const cleanNumber = targetWp.toString().replace(/[^0-9]/g, '') || '919845088990';
  const siteName = (typeof SITE_NAME !== 'undefined' && SITE_NAME) ? SITE_NAME : (window.GOUSHALA_SETTINGS?.site_name || 'Kamadhenu Goushala');
  const title = (seva && seva.title) || 'Gau Seva';
  const amount = (seva && seva.suggested_amount) ? '₹' + parseFloat(seva.suggested_amount).toLocaleString('en-IN') : '';
  const desc = (seva && (seva.short_desc || seva.full_desc)) || '';
  const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : (window.location.origin + (window.location.pathname.startsWith('/kamadhenu-goushala') ? '/kamadhenu-goushala' : ''));

  let msg = "🌸 *" + siteName.toUpperCase() + "*\n";
  msg += "🌿 _Dedicated to Gau Seva & Cow Protection_\n";
  msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
  msg += "🌾 *GAU SEVA & FEED NOW INQUIRY*\n\n";
  msg += "📋 *Seva Package Details:*\n";
  msg += "• *Seva Name:* " + title + "\n";
  if (amount) msg += "• *Suggested Amount:* " + amount + "\n";
  if (desc) msg += "• *Description:* " + desc.replace(/<[^>]*>?/gm, '') + "\n";
  msg += "\n💬 *Message:*\n";
  msg += "Namaste! I would like to feed the cows and offer this sacred seva (*" + title + (amount ? " - " + amount : "") + "*) at " + siteName + ". Please share payment/UPI details and guidance for this seva. 🙏\n\n";
  msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
  msg += "🌐 *Gau Seva Packages:* " + baseUrl + "/seva.php\n";
  msg += "🏡 *Sanctuary Website:* " + baseUrl + "/index.php";

  return "https://wa.me/" + cleanNumber + "?text=" + encodeURIComponent(msg);
}

function feedSevaWhatsApp(title, amount, desc, customWp = '') {
  const url = getSevaWhatsAppUrl({ title, suggested_amount: amount, short_desc: desc }, customWp);
  window.open(url, "_blank");
}

function ensureFloatingWhatsAppButton() {
  const existing = document.getElementById('floatingWhatsAppBtn');
  const wpNum = (window.GOUSHALA_SETTINGS?.whatsapp_number || (typeof ORDER_WHATSAPP_NUMBER !== 'undefined' && ORDER_WHATSAPP_NUMBER ? ORDER_WHATSAPP_NUMBER : '919845088990')).toString().replace(/[^0-9]/g, '') || '919845088990';
  const siteName = (window.GOUSHALA_SETTINGS?.site_name || (typeof SITE_NAME !== 'undefined' ? SITE_NAME : 'Kamadhenu Goushala'));
  const siteTag = (window.GOUSHALA_SETTINGS?.site_tagline || (typeof SITE_TAGLINE !== 'undefined' ? SITE_TAGLINE : 'Serving Gau Mata With Pure Devotion'));
  const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : (window.location.origin + (window.location.pathname.startsWith('/kamadhenu-goushala') ? '/kamadhenu-goushala' : ''));

  let msg = "🌸 *" + siteName.toUpperCase() + "*\n";
  msg += "🌿 _" + siteTag + "_\n";
  msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
  msg += "ℹ️ *About Us:* Dedicated to ethical protection, Vedic healthcare & preservation of indigenous Desi cows.\n\n";
  msg += "💬 *Message:* \n";
  msg += "Namaste Admin! I am reaching out through your official website. I would like to know more about Gau Seva, cow adoption, sanctuary visits, and daily activities. 🙏\n\n";
  msg += "━━━━━━━━━━━━━━━━━━━━━━━━\n";
  msg += "🌐 *Official Website:* " + baseUrl + "/index.php";

  const targetUrl = `https://wa.me/${wpNum}?text=${encodeURIComponent(msg)}`;

  if (existing) {
    existing.href = targetUrl;
    return;
  }

  const btn = document.createElement('a');
  btn.id = 'floatingWhatsAppBtn';
  btn.className = 'floating-whatsapp-btn';
  btn.href = targetUrl;
  btn.target = '_blank';
  btn.rel = 'noopener';
  btn.setAttribute('aria-label', 'WhatsApp Helpline');
  btn.innerHTML = '<i class="bi bi-whatsapp"></i>';
  document.body.appendChild(btn);
}


