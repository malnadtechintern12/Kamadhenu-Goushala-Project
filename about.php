<?php
// ============================================================
// Kamadhenu Goushala — About Us Page
// ============================================================
$page_title = 'About Us';
$page_desc  = 'Learn about Kamadhenu Goushala — our mission, vision, journey, and commitment to protecting indigenous Indian cow breeds.';
$active_nav = 'about';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

$base = BASE_URL;
try {
    $timeline = $pdo->query("SELECT * FROM timeline WHERE status='active' ORDER BY display_order ASC")->fetchAll();
} catch (Exception $e) { $timeline = []; }
$testimonials = getTestimonials(3);

$banner = getPageBanner('about');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>

  <!-- Page Hero -->
  <section class="page-hero">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'About Us') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="hero-badge"><i class="bi bi-info-circle"></i> <?= e($banner['badge_text'] ?? 'Our Story') ?></div>
      <h1 class="hero-title"><?= $banner['title'] ?? 'About <span>Kamadhenu Goushala</span>' ?></h1>
      <p class="hero-subtitle"><?= e($banner['subtitle'] ?? 'A Non-Profit Spiritual Sanctuary Dedicated to the Ethical Protection, Preservation, and Natural Healthcare of India\'s Noble Indigenous Cow Breeds.') ?></p>
    </div>
  </section>

  <!-- About Content -->
  <section class="section-padding">
    <div class="container">
      <div class="row align-items-center g-5">
        <div class="col-lg-6">
          <img src="https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?auto=format&fit=crop&w=800&q=80" alt="Sanctuary" class="rounded-4 shadow-lg w-100" style="height:480px;object-fit:cover;">
        </div>
        <div class="col-lg-6">
          <span class="section-tag">Our Sanctuary</span>
          <h2 class="section-title">A Sacred Haven for Gau Mata</h2>
          <div class="title-ornament justify-content-start"></div>
          <p class="mb-3" style="font-size: 1.05rem; line-height: 1.75;">Kamadhenu Goushala is an authentic spiritual sanctuary established with the sole mission of protecting, nurturing, and conserving India's noble indigenous cow breeds. We provide a peaceful, loving lifelong haven where abandoned, injured, and aging cows receive dedicated veterinary medical care, clean shelter, and nutritious organic green fodder.</p>
          <p class="mb-4" style="font-size: 1.05rem; line-height: 1.75;">Our sanctuary is spread across 25+ acres of lush green farmland with modern eco-sheds, a dedicated veterinary hospital, organic fodder cultivation, and a Panchagavya production center.</p>
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
        </div>
      </div>
    </div>
  </section>

  <!-- Values -->
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Core Values</span>
        <h2 class="section-title">What We Stand For</h2>
        <div class="title-ornament"></div>
      </div>
      <div class="row g-4">
        <?php
        $values = [
          ['icon'=>'bi-heart-pulse','title'=>'Compassionate Care','desc'=>'Every cow receives personalized attention, love, and 24/7 veterinary support.'],
          ['icon'=>'bi-flower2','title'=>'Breed Conservation','desc'=>'Preserving pure indigenous bloodlines of Gir, Hallikar, Ongole, and more.'],
          ['icon'=>'bi-tree','title'=>'Organic Sustainability','desc'=>'100% organic fodder farming and zero-waste Panchagavya production.'],
          ['icon'=>'bi-transparency','title'=>'Transparency','desc'=>'Full financial accountability with 80G certificates and donor reports.'],
          ['icon'=>'bi-people','title'=>'Community Seva','desc'=>'Engaging volunteers, schools, and families in sacred Gau Seva activities.'],
          ['icon'=>'bi-lightning','title'=>'Solar-Powered Campus','desc'=>'100% solar-powered infrastructure for sustainable environmental impact.'],
        ];
        foreach ($values as $v): ?>
          <div class="col-md-4">
            <div class="p-4 bg-light rounded-4 shadow-sm h-100 border text-center">
              <div class="seva-icon mb-3"><i class="bi <?= $v['icon'] ?>"></i></div>
              <h5 class="fw-bold mb-2"><?= e($v['title']) ?></h5>
              <p class="text-muted small mb-0"><?= e($v['desc']) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Timeline -->
  <?php if (!empty($timeline)): ?>
  <section class="section-padding" style="background:#F6F2E8;">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">Our Journey</span>
        <h2 class="section-title">Sacred Milestones</h2>
        <div class="title-ornament"></div>
      </div>
      <div class="timeline-container">
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
  <?php endif; ?>

  <!-- Testimonials -->
  <?php if (!empty($testimonials)): ?>
  <section class="section-padding bg-white">
    <div class="container">
      <div class="text-center mb-5">
        <span class="section-tag">What People Say</span>
        <h2 class="section-title">Voices of Love &amp; Support</h2>
        <div class="title-ornament"></div>
      </div>
      <div class="row g-4">
        <?php foreach ($testimonials as $t): ?>
          <div class="col-md-4">
            <div class="testimonial-card">
              <div class="rating-stars"><?= str_repeat('★', (int)($t['rating'] ?? 5)) ?></div>
              <p class="testimonial-text">"<?= e($t['message']) ?>"</p>
              <div class="testimonial-author mt-auto">
                <img src="<?= e($t['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80') ?>" alt="<?= e($t['author_name']) ?>" class="author-avatar">
                <div>
                  <h6 class="fw-bold mb-0"><?= e($t['author_name']) ?></h6>
                  <small class="text-muted"><?= e($t['designation'] ?? 'Devotee') ?></small>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA -->
  <section class="container my-5">
    <div class="donation-cta-banner shadow-lg">
      <div class="row align-items-center">
        <div class="col-lg-8">
          <h2 class="display-6 fw-bold text-white mb-3">Join Us in This Sacred Mission</h2>
          <p class="text-white-50 fs-6 mb-4">Whether through donations, sponsorship, or volunteering — every act of kindness strengthens our mission.</p>
          <div class="d-flex flex-wrap gap-3">
            <a href="<?= $base ?>/donation.php" class="btn btn-gold py-3 px-4 fs-6"><i class="bi bi-heart-fill"></i> DONATE NOW</a>
            <a href="<?= $base ?>/contact.php" class="btn btn-outline-light rounded-pill py-3 px-4 fs-6 fw-bold">CONTACT US</a>
          </div>
        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
