<?php
require_once __DIR__ . '/../includes/functions.php';
global $pdo;

$sql = "
CREATE TABLE IF NOT EXISTS page_banners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  page_key VARCHAR(50) NOT NULL UNIQUE,
  page_name VARCHAR(100) NOT NULL,
  banner_image VARCHAR(500) DEFAULT NULL,
  badge_text VARCHAR(150) DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  subtitle TEXT DEFAULT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_page_key (page_key),
  INDEX idx_banner_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);

$banners = [
  ['home', 'Home Page', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1920&q=80', 'Dedicated to Gau Seva & Protection', 'Sacred Sanctuary For Desi Gau Mata', 'Dedicated to the protection, ethical care, and preservation of India\'s indigenous cow breeds.'],
  ['about', 'About Us', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1600&q=80', 'Vedic Heritage & Gau Seva', 'Preserving India\'s <span>Sacred Bovine Heritage</span>', 'Founded with a divine mission to rescue, nurture, and ethically protect indigenous Desi cows through Vedic principles, modern veterinary care, and organic sustenance.'],
  ['cows', 'Our Cows', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=1600&q=80', 'Protected In Sanctuary', 'Meet Our <span>Sacred Residents</span>', 'Every cow at Kamadhenu Goushala is treated as family with love, holistic veterinary care, and peaceful lifelong shelter.'],
  ['cow-details', 'Cow Details', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1600&q=80', 'Sanctuary Resident', 'Sacred Cow <span>Profile & Story</span>', 'Discover the life story, breed heritage, and adoption status of our sacred cows.'],
  ['breeds', 'Indigenous Breeds', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1600&q=80', 'Heritage Conservation', 'Indigenous <span>Desi Cow Breeds</span>', 'Discover the sacred heritage, unique characteristics, and medicinal A2 milk qualities of India\'s native Zebu cattle.'],
  ['seva', 'Gau Seva Packages', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1600&q=80', 'Spiritual Seva', 'Sacred <span>Gau Seva Opportunities</span>', 'Earn spiritual merit by sponsoring daily fodder, medical treatment, shelter, or adopting a cow for life.'],
  ['donation', 'Donations & 80G', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1600&q=80', '80G Tax Exemption Eligible', 'Support Sacred <span>Gau Seva</span>', 'Your generous contribution directly funds green fodder, medical supplies, shelter construction, and lifelong care for rescued cows. 50% Tax Exemption under 80G.'],
  ['adopt', 'Adopt a Cow', 'https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=1600&q=80', 'Lifelong Guardian', 'Adopt a <span>Sacred Cow</span>', 'Become a guardian angel by sponsoring lifelong monthly or annual care for an indigenous cow.'],
  ['products', 'Organic Store', 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=1600&q=80', 'Natural & Organic', 'Organic <span>Products</span>', '100% natural products crafted from sacred cow resources — vermicompost, Panchagavya, diyas, and herbal formulations.'],
  ['gallery', 'Photo Gallery', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1600&q=80', 'Sacred Moments', 'Sanctuary <span>Photo Gallery</span>', 'Witness the divine joy, daily care, Gopashtami celebrations, and peaceful life of our sacred cows.'],
  ['blog', 'Vedic Blog', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1600&q=80', 'Knowledge & Insights', 'Vedic Wisdom &amp; <span>Gau Vignana</span>', 'Explore ancient scriptures, scientific benefits of A2 milk, Panchagavya formulations, and cow protection.'],
  ['blog-details', 'Blog Details', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1600&q=80', 'Vedic Article', 'Article <span>Details</span>', 'Insights and sacred wisdom from Kamadhenu Goushala.'],
  ['events', 'Events & Yagnas', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1600&q=80', 'Join Our Celebrations', 'Sanctuary <span>Events &amp; Yagnas</span>', 'Join us for upcoming Gopashtami festivals, monthly Gau Pujas, organic farming workshops, and spiritual retreats.'],
  ['contact', 'Contact Us', 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=1600&q=80', 'Connect With Us', 'Visit &amp; <span>Contact Us</span>', 'We warmly welcome devotees, volunteers, and well-wishers to visit our sanctuary. Plan your visit or send us a message.'],
  ['terms', 'Terms & Conditions', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1600&q=80', 'Legal & Policies', 'Terms &amp; <span>Conditions</span>', 'Rules and guidelines for website use, donations, and organic store purchases.'],
  ['privacy', 'Privacy Policy', 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1600&q=80', 'Data Privacy', 'Privacy <span>Policy</span>', 'How we handle, protect, and respect your personal information.']
];

$stmt = $pdo->prepare('INSERT INTO page_banners (page_key, page_name, banner_image, badge_text, title, subtitle) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE page_name = VALUES(page_name)');

foreach ($banners as $b) {
  $stmt->execute($b);
}

echo "Page banners table created and seeded successfully! Total rows: " . $pdo->query('SELECT COUNT(*) FROM page_banners')->fetchColumn() . PHP_EOL;
