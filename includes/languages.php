<?php
// ============================================================
// Kamadhenu Goushala — Bilingual Localization Engine (English ⇄ Kannada)
// Native implementation without external dependencies.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Determine and return current active language ('en' or 'kn').
 */
function getCurrentLang(): string {
    // 1. URL Query parameter (?lang=kn or ?lang=en)
    if (isset($_GET['lang']) && in_array(strtolower($_GET['lang']), ['en', 'kn'])) {
        $lang = strtolower($_GET['lang']);
        $_SESSION['site_lang'] = $lang;
        setcookie('site_lang', $lang, time() + (86400 * 365), '/');
        return $lang;
    }

    // 2. Session storage
    if (!empty($_SESSION['site_lang']) && in_array($_SESSION['site_lang'], ['en', 'kn'])) {
        return $_SESSION['site_lang'];
    }

    // 3. Cookie storage
    if (!empty($_COOKIE['site_lang']) && in_array($_COOKIE['site_lang'], ['en', 'kn'])) {
        $_SESSION['site_lang'] = $_COOKIE['site_lang'];
        return $_COOKIE['site_lang'];
    }

    // Default language is English
    return 'en';
}

/**
 * Global bilingual translations dictionary.
 */
function getTranslations(): array {
    static $dict = null;
    if ($dict !== null) {
        return $dict;
    }

    $dict = [
        'en' => [
            // Navigation
            'nav_home'        => 'HOME',
            'nav_about'       => 'ABOUT',
            'nav_cows'        => 'OUR COWS',
            'nav_breeds'      => 'BREEDS',
            'nav_seva'        => 'GAU SEVA',
            'nav_gallery'     => 'GALLERY',
            'nav_products'    => 'PRODUCTS',
            'nav_blog'        => 'BLOG',
            'nav_events'      => 'EVENTS',
            'nav_contact'     => 'CONTACT',
            'nav_donate'      => 'DONATE',
            'nav_donate_now'  => 'DONATE NOW',
            'nav_whatsapp'    => 'WhatsApp',

            // Brand
            'brand_title'     => 'KAMADHENU',
            'brand_subtitle'  => 'GOUSHALA',
            'site_name'       => 'Kamadhenu Goushala',
            'site_tagline'    => 'Serving Gau Mata With Pure Devotion & Vedic Care',

            // Badges & Actions
            'badge_breeds'    => '🐄 Indigenous Breeds',
            'badge_farm'      => '🌿 Organic Farm',
            'badge_hospital'  => '🏥 Vet Hospital',
            'badge_80g'       => '80G Tax Exemption',
            'btn_adopt'       => 'Adopt a Cow',
            'btn_sponsor'     => 'Sponsor Seva',
            'btn_view_more'   => 'View Details',
            'btn_read_more'   => 'Read More',
            'btn_add_cart'    => 'Add to Cart',
            'btn_checkout'    => 'Checkout',
            'btn_contact_us'  => 'CONTACT US',
            'btn_submit'      => 'Submit',
            'btn_donate'      => 'Donate Now',

            // Footer
            'footer_quick_links' => 'Quick Links',
            'footer_resources'   => 'Resources',
            'footer_newsletter'  => 'Newsletter & Direct UPI',
            'footer_upi_title'   => 'Quick UPI ID',
            'footer_subscribe_desc' => 'Subscribe to receive monthly sanctuary updates and Gopashtami festival announcements.',
            'footer_copyright'   => '© 2026 Kamadhenu Goushala Trust. All rights reserved. Registered Public Charitable Trust. Protecting Mother Cow With Pure Devotion.',
            'footer_80g_text'    => 'Donations are eligible for 50% tax exemption under Section 80G of the Income Tax Act (Reg. No: AAATK1234PF20214).',

            // Cart
            'cart_title'         => 'Your Organic Cart',
            'cart_subtotal'      => 'Subtotal:',
            'cart_empty'         => 'Your cart is currently empty.',

            // Common Pages
            'page_home'          => 'Home',
            'page_about'         => 'About Us',
            'page_cows'          => 'Our Cows',
            'page_breeds'        => 'Cow Breeds',
            'page_seva'          => 'Gau Seva',
            'page_donation'      => 'Donate',
            'page_adopt'         => 'Adopt a Cow',
            'page_products'      => 'Organic Store',
            'page_gallery'       => 'Photo Gallery',
            'page_blog'          => 'Vedic Articles',
            'page_events'        => 'Upcoming Events',
            'page_contact'       => 'Contact Us',
            'page_privacy'       => 'Privacy Policy',
            'page_terms'         => 'Terms & Conditions',

            // Hero Subtitles
            'hero_about_sub'     => 'Preserving India\'s Sacred Bovine Heritage with Vedic compassion and modern veterinary healthcare.',
            'hero_cows_sub'      => 'Meet the divine resident cows happily living in our peaceful sanctuary.',
            'hero_breeds_sub'    => 'Explore the magnificent native Desi breeds protected at Kamadhenu Goushala.',
            'hero_seva_sub'      => 'Participate in sacred Gau Seva and receive immense spiritual blessings.',
            'hero_donation_sub'  => 'Your generous contribution helps feed, shelter, and provide healthcare for hundreds of rescued Desi cows.',
            'hero_adopt_sub'     => 'Experience the divine joy of adopting a sacred Gau Mata. Receive regular health updates, photos, and perform personalized pujas.',
            'hero_products_sub'  => '100% Pure, Ethically Sourced A2 Desi Cow Products from our organic farm.',
            'hero_gallery_sub'   => 'Visual glimpse into the joyful daily life at Kamadhenu Goushala sanctuary.',
            'hero_blog_sub'      => 'Enlightening insights on indigenous cow breeds, Panchagavya, and ethical cow protection.',
            'hero_events_sub'    => 'Join us for sacred festivals, Gopashtami celebrations, and sanctuary volunteer seva days.',
            'hero_contact_sub'   => 'We warmly welcome devotees, visitors, and volunteers to visit Kamadhenu Goushala.',
        ],

        'kn' => [
            // Navigation
            'nav_home'        => 'ಮುಖಪುಟ',
            'nav_about'       => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'nav_cows'        => 'ನಮ್ಮ ಗೋವುಗಳು',
            'nav_breeds'      => 'ತಳಿಗಳು',
            'nav_seva'        => 'ಗೋ ಸೇವೆ',
            'nav_gallery'     => 'ಗ್ಯಾಲರಿ',
            'nav_products'    => 'ಉತ್ಪನ್ನಗಳು',
            'nav_blog'        => 'ಬ್ಲಾಗ್',
            'nav_events'      => 'ಕಾರ್ಯಕ್ರಮಗಳು',
            'nav_contact'     => 'ಸಂಪರ್ಕಿಸಿ',
            'nav_donate'      => 'ದೇಣಿಗೆ',
            'nav_donate_now'  => 'ಈಗಲೇ ದೇಣಿಗೆ ನೀಡಿ',
            'nav_whatsapp'    => 'ವಾಟ್ಸಾಪ್',

            // Brand
            'brand_title'     => 'ಕಾಮಧೇನು',
            'brand_subtitle'  => 'ಗೋಶಾಲೆ',
            'site_name'       => 'ಕಾಮಧೇನು ಗೋಶಾಲೆ',
            'site_tagline'    => 'ಪೂರ್ಣ ಭಕ್ತಿಯೊಂದಿಗೆ ಗೋಮಾತೆಯ ಸೇವೆ ಮತ್ತು ವೈದಿಕ ರಕ್ಷಣೆ',

            // Badges & Actions
            'badge_breeds'    => '🐄 ದೇಶಿ ತಳಿಗಳು',
            'badge_farm'      => '🌿 ಸಾವಯವ ತೋಟ',
            'badge_hospital'  => '🏥 ಪಶು ಆಸ್ಪತ್ರೆ',
            'badge_80g'       => '80G ತೆರಿಗೆ ವಿನಾಯಿತಿ',
            'btn_adopt'       => 'ಗೋವನ್ನು ದತ್ತು ಪಡೆಯಿರಿ',
            'btn_sponsor'     => 'ಸೇವೆ ಪ್ರಾಯೋಜಿಸಿ',
            'btn_view_more'   => 'ವಿವರಗಳನ್ನು ನೋಡಿ',
            'btn_read_more'   => 'ಮತ್ತಷ್ಟು ಓದಿ',
            'btn_add_cart'    => 'ಕಾರ್ಟ್‌ಗೆ ಸೇರಿಸಿ',
            'btn_checkout'    => 'ಚೆಕ್‌ಔಟ್',
            'btn_contact_us'  => 'ಸಂಪರ್ಕಿಸಿ',
            'btn_submit'      => 'ಸಲ್ಲಿಸಿ',
            'btn_donate'      => 'ದೇಣಿಗೆ ನೀಡಿ',

            // Footer
            'footer_quick_links' => 'ತ್ವರಿತ ಲಿಂಕ್‌ಗಳು',
            'footer_resources'   => 'ಸಂಪನ್ಮೂಲಗಳು',
            'footer_newsletter'  => 'ಸುದ್ದಿಪತ್ರ & ನೇರ UPI',
            'footer_upi_title'   => 'ತ್ವರಿತ UPI ಐಡಿ',
            'footer_subscribe_desc' => 'ಮಾಸಿಕ ನವೀಕರಣಗಳು ಮತ್ತು ಗೋಪಾಷ್ಟಮಿ ಹಬ್ಬದ ಪ್ರಕಟಣೆಗಳನ್ನು ಪಡೆಯಲು ಚಂದಾದಾರರಾಗಿ.',
            'footer_copyright'   => '© 2026 ಕಾಮಧೇನು ಗೋಶಾಲೆ ಟ್ರಸ್ಟ್. ಎಲ್ಲ ಹಕ್ಕುಗಳನ್ನು ಕಾಯ್ದಿರಿಸಲಾಗಿದೆ. ನೋಂದಾಯಿತ ಸಾರ್ವಜನಿಕ ಧರ್ಮಾರ್ಥ ಟ್ರಸ್ಟ್. ಪೂರ್ಣ ಭಕ್ತಿಯೊಂದಿಗೆ ಗೋಮಾತೆಯ ರಕ್ಷಣೆ.',
            'footer_80g_text'    => 'ಆದಾಯ ತೆರಿಗೆ ಕಾಯ್ದೆಯ ಸೆಕ್ಷನ್ 80G ಅಡಿಯಲ್ಲಿ ದೇಣಿಗೆಗಳಿಗೆ 50% ತೆರಿಗೆ ವಿನಾಯಿತಿ ಲಭ್ಯವಿದೆ (ನೋಂದಣಿ ಸಂಖ್ಯೆ: AAATK1234PF20214).',

            // Cart
            'cart_title'         => 'ನಿಮ್ಮ ಸಾವಯವ ಕಾರ್ಟ್',
            'cart_subtotal'      => 'ಒಟ್ಟು ಮೊತ್ತ:',
            'cart_empty'         => 'ನಿಮ್ಮ ಕಾರ್ಟ್ ಪ್ರಸ್ತುತ ಖಾಲಿಯಾಗಿದೆ.',

            // Common Pages
            'page_home'          => 'ಮುಖಪುಟ',
            'page_about'         => 'ನಮ್ಮ ಬಗ್ಗೆ',
            'page_cows'          => 'ನಮ್ಮ ಗೋವುಗಳು',
            'page_breeds'        => 'ಗೋ ತಳಿಗಳು',
            'page_seva'          => 'ಗೋ ಸೇವೆ',
            'page_donation'      => 'ದೇಣಿಗೆ ನೀಡಿ',
            'page_adopt'         => 'ಗೋ ದತ್ತು ಪಡೆಯಿರಿ',
            'page_products'      => 'ಸಾವಯವ ಮಳಿಗೆ',
            'page_gallery'       => 'ಫೋಟೋ ಗ್ಯಾಲರಿ',
            'page_blog'          => 'ವೈದಿಕ ಲೇಖನಗಳು',
            'page_events'        => 'ಕಾರ್ಯಕ್ರಮಗಳು',
            'page_contact'       => 'ಸಂಪರ್ಕಿಸಿ',
            'page_privacy'       => 'ಗೌಪ್ಯತಾ ನೀತಿ',
            'page_terms'         => 'ನಿಯಮಗಳು ಮತ್ತು ಷರತ್ತುಗಳು',

            // Hero Subtitles
            'hero_about_sub'     => 'ವೈದಿಕ ಕರುಣೆ ಮತ್ತು ಆಧುನಿಕ ಪಶು ವೈದ್ಯಕೀಯ ಆರೈಕೆಯೊಂದಿಗೆ ಭಾರತದ ಪವಿತ್ರ ಗೋ ಪರಂಪರೆಯ ಸಂರಕ್ಷಣೆ.',
            'hero_cows_sub'      => 'ನಮ್ಮ ಪ್ರಶಾಂತ ಆಶ್ರಮದಲ್ಲಿ ಸಂತೋಷದಿಂದ ವಾಸಿಸುತ್ತಿರುವ ಪವಿತ್ರ ಗೋವುಗಳನ್ನು ಭೇಟಿ ಮಾಡಿ.',
            'hero_breeds_sub'    => 'ಕಾಮಧೇನು ಗೋಶಾಲೆಯಲ್ಲಿ ರಕ್ಷಿಸಲ್ಪಟ್ಟಿರುವ ಅದ್ಭುತ ಭಾರತೀಯ ದೇಶಿ ಗೋ ತಳಿಗಳನ್ನು ಅನ್ವೇಷಿಸಿ.',
            'hero_seva_sub'      => 'ಪವಿತ್ರ ಗೋ ಸೇವೆಯಲ್ಲಿ ಭಾಗವಹಿಸಿ ಮತ್ತು ಅಪಾರ ಆಧ್ಯಾತ್ಮಿಕ ಪುಣ್ಯ ಹಾಗೂ ಆಶೀರ್ವಾದಗಳನ್ನು ಪಡೆಯಿರಿ.',
            'hero_donation_sub'  => 'ನಿಮ್ಮ ಉದಾರ ದೇಣಿಗೆಯು ನೂರಾರು ರಕ್ಷಿತ ದೇಶಿ ಹಸುಗಳಿಗೆ ಆಹಾರ, ಆಶ್ರಯ ಮತ್ತು ವೈದ್ಯಕೀಯ ಚಿಕಿತ್ಸೆ ನೀಡಲು ನೆರವಾಗುತ್ತದೆ.',
            'hero_adopt_sub'     => 'ಪವಿತ್ರ ಗೋಮಾತೆಯನ್ನು ದತ್ತು ಪಡೆಯುವ ದೈವಿಕ ಆನಂದವನ್ನು ಅನುಭವಿಸಿ. ನಿಯಮಿತ ಆರೋಗ್ಯ ವರದಿಗಳು, ಫೋಟೋಗಳನ್ನು ಪಡೆಯಿರಿ.',
            'hero_products_sub'  => 'ನಮ್ಮ ಸಾವಯವ ತೋಟದಿಂದ 100% ಶುದ್ಧ ಮತ್ತು ನೈಸರ್ಗಿಕ A2 ದೇಶಿ ಹಸುವಿನ ಉತ್ಪನ್ನಗಳು.',
            'hero_gallery_sub'   => 'ಕಾಮಧೇನು ಗೋಶಾಲೆಯ ಆನಂದಮಯ ದೈನಂದಿನ ಜೀವನದ ಸುಂದರ ಕ್ಷಣಗಳು.',
            'hero_blog_sub'      => 'ದೇಶಿ ಗೋ ತಳಿಗಳು, ಪಂಚಗವ್ಯ ಮತ್ತು ಗೋ ರಕ್ಷಣೆಯ ಮಹತ್ವದ ಬಗೆಗಿನ ವೈದಿಕ ಲೇಖನಗಳು.',
            'hero_events_sub'    => 'ಪವಿತ್ರ ಉತ್ಸವಗಳು, ಗೋಪಾಷ್ಟಮಿ ಆಚರಣೆಗಳು ಮತ್ತು ಗೋಶಾಲೆ ಸ್ವಯಂಸೇವಾ ದಿನಗಳಲ್ಲಿ ನಮ್ಮೊಂದಿಗೆ ಕೈಜೋಡಿಸಿ.',
            'hero_contact_sub'   => 'ಕಾಮಧೇನು ಗೋಶಾಲೆಗೆ ಭೇಟಿ ನೀಡಲು ಭಕ್ತರು ಮತ್ತು ಸಾರ್ವಜನಿಕರನ್ನು ಹೃತ್ಪೂರ್ವಕವಾಗಿ ಸ್ವಾಗತಿಸುತ್ತೇವೆ.',
        ]
    ];

    return $dict;
}

/**
 * Translate a key or text into active language.
 */
function __t(string $key, string $default = ''): string {
    $lang = getCurrentLang();
    $dict = getTranslations();

    if (isset($dict[$lang][$key])) {
        return $dict[$lang][$key];
    }

    if ($default !== '') {
        return $default;
    }

    if (isset($dict['en'][$key])) {
        return $dict['en'][$key];
    }

    return $key;
}
