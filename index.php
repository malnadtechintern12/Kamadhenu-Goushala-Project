<?php
// ============================================================
// Kamadhenu Goushala — Homepage (index.php)
// ============================================================
$page_title = 'Serving Gau Mata With Devotion';
$page_desc  = 'Kamadhenu Goushala is dedicated to the ethical protection, preservation, and healthcare of indigenous Indian cow breeds (Desi Cows). Support Gau Seva today.';
$active_nav = 'home';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

// Fetch data server-side
$cows          = getActiveCows(6);
$breeds        = getActiveBreeds(4);
$sevaPackages  = getActiveSeva(3);
$testimonials  = getTestimonials(3);
$blogs         = getPublishedBlogs(3);
$galleryPhotos = getGalleryPhotos(6);
$stats         = getDonationStats();

// Timeline
try {
    $timeline = $pdo->query("SELECT * FROM timeline WHERE status='active' ORDER BY display_order ASC")->fetchAll();
} catch (Exception $e) {
    $timeline = [];
}

$base = BASE_URL;
$stat_cows   = getSetting('stat_cows_served', $stats['cow_count'].'+ Cows');
$stat_donors = getSetting('stat_donors', $stats['donor_count'].'+');
$stat_years  = getSetting('stat_years_seva', '25+');
$stat_breeds = getSetting('stat_breeds', $stats['breed_count'].'+');
$phone       = getSetting('phone_primary', SITE_PHONE);
$email_addr  = getSetting('email_primary', SITE_EMAIL);
$address     = getSetting('address', 'Bengaluru, Karnataka');

$banner = getPageBanner('home');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>

  <!-- Hero Section -->
  <section class="hero-section position-relative overflow-hidden">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'Kamadhenu Goushala') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <div class="hero-badge">
            <i class="bi bi-stars"></i> <?= e($banner['badge_text'] ?? 'ॐ Sri Kamadhenave Namaha') ?>
          </div>
          <h1 class="hero-title">
            <?= $banner['title'] ?? 'Serving <span>Gau Mata</span><br>With Pure Devotion' ?>
          </h1>
          <p class="hero-subtitle">
            <?= e($banner['subtitle'] ?? "Protect • Preserve • Serve • Nurture — Dedicated to providing lifelong shelter, organic nutrition, and compassionate veterinary healthcare for India's sacred indigenous cows.") ?>
          </p>
          <div class="d-flex flex-wrap gap-3">
            <a href="<?= $base ?>/donation.php" class="btn btn-gold py-3 px-4 fs-6">
              <i class="bi bi-heart-fill"></i> SUPPORT GAU SEVA
            </a>
            <a href="<?= $base ?>/cows.php" class="btn btn-outline-light rounded-pill py-3 px-4 fs-6 fw-bold">
              <i class="bi bi-arrow-right-circle"></i> MEET OUR COWS
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Bar -->
  <section class="stats-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-6 col-md-3">
          <div class="stat-box">
            <div class="stat-number"><?= e($stat_cows) ?></div>
            <div class="stat-label">Cows Served</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-box">
            <div class="stat-number"><?= e($stat_donors) ?></div>
            <div class="stat-label">Dedicated Donors</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-box">
            <div class="stat-number"><?= e($stat_years) ?></div>
            <div class="stat-label">Years of Seva</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-box">
            <div class="stat-number"><?= e($stat_breeds) ?></div>
            <div class="stat-label">Indigenous Breeds</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="section-padding">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <div class="position-relative">
            <img src="https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80" alt="Kamadhenu Cow Shelter" class="rounded-4 shadow-lg w-100" style="height: 480px; object-fit: cover;">
            <div class="position-absolute bottom-0 start-0 m-4 p-3 rounded-3 text-white" style="background: rgba(23, 59, 42, 0.9); backdrop-filter: blur(8px); max-width: 280px; border-left: 4px solid var(--sacred-gold);">
              <div class="fw-bold fs-5">100% Non-Profit</div>
              <small class="text-white-50">Every rupee contributed goes directly towards green fodder, shelter, and medical care.</small>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <span class="section-tag">About Our Sanctuary</span>
          <h2 class="section-title">A Safe &amp; Sacred Home for Every Gau Mata</h2>
          <div class="title-ornament justify-content-start"></div>
          <p class="text-muted mb-4 fs-6">
            Kamadhenu Goushala is an authentic spiritual sanctuary established with the sole mission of protecting, nurturing, and conserving India's noble indigenous cow breeds. We provide a peaceful, loving lifelong haven where abandoned, injured, and aging cows receive dedicated veterinary medical care, clean shelter, and nutritious organic green fodder.
          </p>
          <div class="row g-3 mb-4">
            <div class="col-sm-6">
              <div class="p-3 bg-white rounded-3 shadow-sm border-start border-4 border-success">
                <h6 class="fw-bold mb-1 text-forest"><i class="bi bi-shield-check me-2"></i> Our Mission</h6>
                <p class="small text-muted mb-0">Rescue distressed cattle and establish sustainable sanctuaries for indigenous breeds.</p>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="p-3 bg-white rounded-3 shadow-sm border-start border-4 border-warning">
                <h6 class="fw-bold mb-1 text-forest"><i class="bi bi-eye me-2"></i> Our Vision</h6>
                <p class="small text-muted mb-0">A society where Gau Mata is revered, and zero-budget organic cow farming thrives.</p>
              </div>
            </div>
          </div>
          <div class="d-flex gap-3">
            <a href="<?= $base ?>/about.php" class="btn btn-forest px-4 py-2">
              LEARN MORE <i class="bi bi-arrow-right"></i>
            </a>
            <a href="<?= $base ?>/cows.php" class="btn btn-outline-gold px-4 py-2">
              VIEW SANCTUARY
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Our Cows Showcase -->
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Divine Residents</span>
        <h2 class="section-title">Meet Our Rescued Gau Mata</h2>
        <div class="title-ornament"></div>
        <p class="section-subtitle">Every cow at Kamadhenu Goushala has a unique name, story, and personality. Explore our beloved cows available for sponsorship and adoption.</p>
      </div>

      <div class="row g-4" id="homeCowsContainer">
        <?php if (empty($cows)): ?>
          <div class="col-12 text-center py-4">
            <p class="text-muted">No cows found. Please check back soon.</p>
          </div>
        <?php else: ?>
          <?php foreach ($cows as $cow): ?>
            <div class="col-md-6 col-lg-4">
              <div class="cow-card">
                <div class="cow-image-wrapper">
                  <img src="<?= e($cow['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($cow['name']) ?>">
                  <span class="cow-badge badge-healthy"><?= e($cow['health_status']) ?></span>
                  <span class="cow-tag-badge"><?= e($cow['tag_number']) ?></span>
                </div>
                <div class="cow-content">
                  <h4 class="cow-name"><?= e($cow['name']) ?></h4>
                  <div class="cow-breed-text"><?= e($cow['breed_name'] ?? 'Desi Breed') ?></div>
                  <div class="cow-meta-grid">
                    <div class="cow-meta-item"><i class="bi bi-gender-ambiguous text-warning"></i> <?= e($cow['gender']) ?></div>
                    <div class="cow-meta-item"><i class="bi bi-shield-check text-success"></i> <?= e($cow['status']) ?></div>
                  </div>
                  <p class="small text-muted mb-4" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= e($cow['story'] ?: 'A gentle resident cow enjoying healthy green pasture and daily love at Kamadhenu Goushala.') ?>
                  </p>
                  <div class="mt-auto">
                    <a href="<?= $base ?>/cow-details.php?id=<?= $cow['id'] ?>" class="btn btn-outline-gold w-100 py-2">
                      VIEW DETAILS &amp; ADOPT
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="text-center mt-5">
        <a href="<?= $base ?>/cows.php" class="btn btn-forest px-4 py-3">
          VIEW ALL COWS <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- Indigenous Breeds Showcase -->
  <section class="section-padding" style="background-color: #F6F2E8;">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Pure Heritage</span>
        <h2 class="section-title">Conserving Indigenous Desi Breeds</h2>
        <div class="title-ornament"></div>
        <p class="section-subtitle">Indian native Zebu cattle possess unique genetic resistance, high A2 medicinal milk quality, and sacred Vedic heritage.</p>
      </div>

      <div class="row g-4" id="homeBreedsContainer">
        <?php if (empty($breeds)): ?>
          <div class="col-12 text-center py-4"><p class="text-muted">No breeds found.</p></div>
        <?php else: ?>
          <?php foreach ($breeds as $b): ?>
            <div class="col-md-6 col-lg-3">
              <div class="breed-card h-100">
                <div class="breed-img-box">
                  <img src="<?= e($b['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=600&q=80') ?>" alt="<?= e($b['name']) ?>">
                </div>
                <div class="breed-body d-flex flex-column">
                  <span class="breed-origin-badge"><?= e($b['origin'] ?: 'India') ?></span>
                  <h5 class="fw-bold mb-1"><?= e($b['name']) ?></h5>
                  <p class="small text-muted mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= e($b['description'] ?: 'Indigenous heritage breed known for rich A2 milk.') ?>
                  </p>
                  <div class="mt-auto">
                    <a href="<?= $base ?>/breeds.php" class="btn btn-sm btn-outline-success w-100">Learn Characteristics</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="text-center mt-5">
        <a href="<?= $base ?>/breeds.php" class="btn btn-outline-gold px-4 py-3">
          DISCOVER ALL BREEDS <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- Gau Seva Packages -->
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Sacred Service</span>
        <h2 class="section-title">Ways to Support Gau Seva</h2>
        <div class="title-ornament"></div>
        <p class="section-subtitle">Choose a seva that resonates with your heart. Your noble contributions directly provide nutrition, medical treatment, and shelter.</p>
      </div>

      <div class="row g-4" id="homeSevaContainer">
        <?php if (empty($sevaPackages)): ?>
          <div class="col-12 text-center py-4"><p class="text-muted">No seva packages found.</p></div>
        <?php else: ?>
          <?php foreach ($sevaPackages as $s): ?>
            <div class="col-md-4">
              <div class="seva-card">
                <div class="seva-icon"><i class="bi <?= e($s['icon'] ?: 'bi-heart-fill') ?>"></i></div>
                <h4 class="seva-title"><?= e($s['title']) ?></h4>
                <div class="seva-amount">₹<?= number_format((float)$s['suggested_amount']) ?></div>
                <p class="seva-desc"><?= e($s['short_desc'] ?: $s['full_desc']) ?></p>
                <a href="<?= $base ?>/donation.php?seva_id=<?= $s['id'] ?>&amount=<?= $s['suggested_amount'] ?>" class="btn btn-gold w-100 py-2">
                  OFFER THIS SEVA
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Why Support Us -->
  <section class="section-padding" style="background: #FAF7EF;">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Ethical Excellence</span>
        <h2 class="section-title">Why Support Kamadhenu Goushala?</h2>
        <div class="title-ornament"></div>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="p-4 bg-white rounded-4 shadow-sm h-100 border text-center">
            <div class="seva-icon mb-3"><i class="bi bi-heart-pulse"></i></div>
            <h5 class="fw-bold mb-2">24x7 Veterinary Care</h5>
            <p class="text-muted small mb-0">Full-time veterinary hospital on campus with round-the-clock doctors, wound treatment, and orthopedic surgical support.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-4 bg-white rounded-4 shadow-sm h-100 border text-center">
            <div class="seva-icon mb-3"><i class="bi bi-flower2"></i></div>
            <h5 class="fw-bold mb-2">Indigenous Breed Conservation</h5>
            <p class="text-muted small mb-0">Active preservation of pure Gir, Hallikar, and Malenadu Gidda bloodlines to ensure biodiversity for future generations.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-4 bg-white rounded-4 shadow-sm h-100 border text-center">
            <div class="seva-icon mb-3"><i class="bi bi-tree"></i></div>
            <h5 class="fw-bold mb-2">100% Organic Nutrition</h5>
            <p class="text-muted small mb-0">Cultivating 15+ acres of organic multi-cut Napier grass, Lucerne, jaggery, and mineral supplements free of chemicals.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-4 bg-white rounded-4 shadow-sm h-100 border text-center">
            <div class="seva-icon mb-3"><i class="bi bi-house-heart"></i></div>
            <h5 class="fw-bold mb-2">Hygienic Clean Shelter</h5>
            <p class="text-muted small mb-0">Modern eco-flooring sheds with natural ventilation, solar lighting, automated water troughs, and daily sanitation.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-4 bg-white rounded-4 shadow-sm h-100 border text-center">
            <div class="seva-icon mb-3"><i class="bi bi-recycle"></i></div>
            <h5 class="fw-bold mb-2">Sustainable Circular Economy</h5>
            <p class="text-muted small mb-0">Producing zero-waste vermicompost, Panchagavya bio-fertilizers, and bio-gas energy benefiting organic farmers.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-4 bg-white rounded-4 shadow-sm h-100 border text-center">
            <div class="seva-icon mb-3"><i class="bi bi-patch-check"></i></div>
            <h5 class="fw-bold mb-2">80G Tax Exemption</h5>
            <p class="text-muted small mb-0">All voluntary donations made to Kamadhenu Goushala Trust are eligible for 50% tax exemption under Section 80G.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Journey & Timeline -->
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Our Milestones</span>
        <h2 class="section-title">The Sacred Journey</h2>
        <div class="title-ornament"></div>
        <p class="section-subtitle">From a humble shelter of 12 rescued cattle to a premier indigenous preservation sanctuary.</p>
      </div>

      <div class="timeline-container" id="homeTimelineContainer">
        <?php foreach ($timeline as $idx => $t): ?>
          <div class="timeline-item <?= $idx % 2 === 0 ? 'left' : 'right' ?>">
            <div class="timeline-dot"></div>
            <div class="timeline-box">
              <div class="timeline-year"><?= e($t['year']) ?></div>
              <h5 class="fw-bold mb-2"><?= e($t['title']) ?></h5>
              <p class="text-muted small mb-0"><?= e($t['description']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Gallery Preview -->
  <section class="section-padding" style="background-color: #F8F5EC;">
    <div class="container">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
        <div>
          <span class="section-tag">Sanctuary Moments</span>
          <h2 class="section-title mb-0">Glimpses of Gau Seva</h2>
        </div>
        <a href="<?= $base ?>/gallery.php" class="btn btn-outline-gold mt-3 mt-md-0">
          VIEW FULL GALLERY <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>

      <div class="row g-3" id="homeGalleryContainer">
        <?php foreach ($galleryPhotos as $g): ?>
          <div class="col-6 col-md-4">
            <div class="gallery-card" onclick="window.location.href='<?= $base ?>/gallery.php'">
              <img src="<?= e($g['image_url']) ?>" alt="<?= e($g['title']) ?>">
              <div class="gallery-overlay">
                <div class="fw-bold small"><?= e($g['title']) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Devotee &amp; Donor Words</span>
        <h2 class="section-title">Voices of Love &amp; Support</h2>
        <div class="title-ornament"></div>
      </div>

      <div class="row g-4" id="homeTestimonialsContainer">
        <?php foreach ($testimonials as $t): ?>
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="rating-stars"><?= str_repeat('★', (int)($t['rating'] ?? 5)) ?></div>
              <p class="testimonial-text">"<?= e($t['message']) ?>"</p>
              <div class="testimonial-author mt-auto">
                <img src="<?= e($t['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= e($t['author_name']) ?>" class="author-avatar">
                <div>
                  <h6 class="fw-bold mb-0"><?= e($t['author_name']) ?></h6>
                  <small class="text-muted"><?= e($t['designation'] ?? 'Devotee / Donor') ?></small>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Blog Preview -->
  <section class="section-padding" style="background-color: #FAF6ED;">
    <div class="container">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
        <div>
          <span class="section-tag">Vedic Knowledge</span>
          <h2 class="section-title mb-0">Latest Articles &amp; Insights</h2>
        </div>
        <a href="<?= $base ?>/blog.php" class="btn btn-forest mt-3 mt-md-0">
          READ ALL BLOGS <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>

      <div class="row g-4" id="homeBlogContainer">
        <?php foreach ($blogs as $b): ?>
          <div class="col-md-4">
            <div class="blog-card">
              <div class="blog-img-box">
                <img src="<?= e($b['featured_image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=600&q=80') ?>" alt="<?= e($b['title']) ?>">
              </div>
              <div class="blog-body">
                <div class="blog-date"><i class="bi bi-calendar3 me-1"></i> <?= formatDate($b['published_at'] ?? $b['created_at']) ?></div>
                <h4 class="blog-title"><?= e($b['title']) ?></h4>
                <p class="text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= e($b['excerpt'] ?? '') ?>
                </p>
                <div class="mt-auto">
                  <a href="<?= $base ?>/blog-details.php?slug=<?= e($b['slug']) ?>" class="fw-bold text-forest text-decoration-none">
                    Read Full Article <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Donation CTA Banner -->
  <section class="container my-5">
    <div class="donation-cta-banner shadow-lg">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <span class="badge bg-warning text-dark px-3 py-2 fw-bold mb-3 fs-6">
            <i class="bi bi-heart-fill text-danger me-1"></i> Make an Eternal Impact
          </span>
          <h2 class="display-6 fw-bold text-white mb-3">Your Support Gives a Cow Food, Shelter &amp; Lifelong Care.</h2>
          <p class="text-white-50 fs-6 mb-4">
            Even a small contribution of ₹501 feeds a cow wholesome nutrition for an entire day. Join thousands of devotees in honoring Gau Mata.
          </p>
          <div class="d-flex flex-wrap gap-3">
            <a href="<?= $base ?>/donation.php" class="btn btn-gold py-3 px-4 fs-6">
              DONATE NOW <i class="bi bi-heart-fill ms-2"></i>
            </a>
            <a href="<?= $base ?>/adopt.php" class="btn btn-outline-light rounded-pill py-3 px-4 fs-6 fw-bold">
              SPONSOR / ADOPT A COW
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="section-padding bg-white" id="contactSection">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-5">
          <span class="section-tag">Get in Touch</span>
          <h2 class="section-title">Visit or Contact Kamadhenu Goushala</h2>
          <div class="title-ornament justify-content-start"></div>
          <p class="text-muted mb-4">
            We warmly welcome visitors, families, and school groups for Gau Seva, morning aarti, and weekend volunteering.
          </p>
          <div class="d-flex flex-column gap-3 mb-4">
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-geo-alt-fill"></i></div>
              <div>
                <h6 class="fw-bold mb-0">Sanctuary Address</h6>
                <p class="small text-muted mb-0"><?= e($address) ?></p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-telephone-fill"></i></div>
              <div>
                <h6 class="fw-bold mb-0">Helpline / WhatsApp</h6>
                <p class="small text-muted mb-0"><?= e($phone) ?></p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-envelope-fill"></i></div>
              <div>
                <h6 class="fw-bold mb-0">Email Correspondence</h6>
                <p class="small text-muted mb-0"><?= e($email_addr) ?></p>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-clock-fill"></i></div>
              <div>
                <h6 class="fw-bold mb-0">Visiting Hours</h6>
                <p class="small text-muted mb-0">Daily: 07:00 AM – 12:30 PM &amp; 03:30 PM – 06:30 PM</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="p-4 p-md-5 rounded-4 shadow-sm bg-light border">
            <h4 class="fw-bold mb-3 text-forest">Send Us a Message</h4>
            <form id="mainContactForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Your Name *</label>
                  <input type="text" name="name" class="form-control py-2" placeholder="e.g. Ramesh Kumar" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email Address *</label>
                  <input type="email" name="email" class="form-control py-2" placeholder="name@example.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Phone Number</label>
                  <input type="tel" name="phone" class="form-control py-2" placeholder="+91 98765 43210">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Subject</label>
                  <input type="text" name="subject" class="form-control py-2" placeholder="Seva / Visit / General Inquiry">
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Message *</label>
                  <textarea name="message" rows="4" class="form-control" placeholder="Write your message or inquiry here..." required></textarea>
                </div>
                <div class="col-12 mt-4">
                  <button type="submit" class="btn btn-gold py-3 px-5 w-100">
                    <i class="bi bi-send-fill me-2"></i> Send Message
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php
$extra_js = '
<script>
// Contact form handler
document.getElementById("mainContactForm")?.addEventListener("submit", async function(e) {
  e.preventDefault();
  const form = e.target;
  const btn = form.querySelector("button[type=submit]");
  btn.disabled = true;
  btn.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span> Sending...\';
  try {
    const fd = new FormData(form);
    const data = Object.fromEntries(fd.entries());
    const res = await fetch(BASE_URL + "/api/submit_contact.php", {
      method: "POST",
      headers: {"Content-Type": "application/json"},
      body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      showToast("Message sent successfully! We will respond soon.", "success");
      form.reset();
    } else {
      showToast(result.message || "Failed to send message.", "error");
    }
  } catch {
    showToast("Network error. Please try again.", "error");
  }
  btn.disabled = false;
  btn.innerHTML = \'<i class="bi bi-send-fill me-2"></i> Send Message\';
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
