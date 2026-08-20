<?php
$page_title = 'Events';
$page_desc  = 'Upcoming festivals, workshops, health camps, and celebrations at Kamadhenu Goushala.';
$active_nav = 'events';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

$base     = BASE_URL;
$events   = getEvents();
$upcoming = array_filter($events, fn($e) => $e['status'] === 'Upcoming');
$past     = array_filter($events, fn($e) => $e['status'] === 'Completed');
?>

  <section class="page-hero">
    <div class="container">
      <div class="hero-badge"><i class="bi bi-calendar-event"></i> Community Events</div>
      <h1 class="hero-title">Events &amp; <span>Celebrations</span></h1>
      <p class="hero-subtitle">Join us for sacred festivals, health camps, workshops, and community service events at Kamadhenu Goushala.</p>
    </div>
  </section>

  <section class="section-padding">
    <div class="container">
      <?php if (!empty($upcoming)): ?>
      <h3 class="section-title mb-4"><i class="bi bi-calendar-check text-warning me-2"></i> Upcoming Events &amp; Festivals</h3>
      <div class="row g-4 mb-5">
        <?php foreach ($upcoming as $ev): 
          $imgUrl = !empty($ev['image']) ? getImageUrl($ev['image']) : '';
        ?>
        <div class="col-lg-6">
          <div class="p-4 bg-white rounded-4 shadow-sm border h-100 d-flex flex-column">
            <?php if ($imgUrl): ?>
              <div class="event-img-container mb-3 rounded-3 overflow-hidden position-relative" style="background:#fdfcf9; border: 1px solid #ede8de; text-align:center;">
                <img src="<?= e($imgUrl) ?>" 
                     alt="<?= e($ev['title']) ?>" 
                     class="img-fluid rounded-3" 
                     style="width: 100%; max-height: 520px; object-fit: contain; display: block; margin: 0 auto;"
                     loading="lazy">
                <a href="<?= e($imgUrl) ?>" target="_blank" class="btn btn-sm btn-light border position-absolute bottom-0 end-0 m-2 shadow-sm" title="View Full Image" style="font-size:0.75rem; background:rgba(255,255,255,0.9);">
                  <i class="bi bi-arrows-fullscreen me-1"></i> Full Poster
                </a>
              </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-success px-3 py-2">Upcoming Event</span>
              <span class="text-forest fw-bold small"><i class="bi bi-calendar3 me-1"></i> <?= formatDate($ev['event_date'], 'D, d M Y') ?></span>
            </div>
            <h4 class="fw-bold text-forest mb-2"><?= e($ev['title']) ?></h4>
            <div class="d-flex flex-wrap gap-3 mb-3 text-muted small pb-2 border-bottom">
              <span><i class="bi bi-clock me-1 text-warning"></i> <?= date('h:i A', strtotime($ev['start_time'])) ?> – <?= date('h:i A', strtotime($ev['end_time'])) ?></span>
              <span><i class="bi bi-geo-alt me-1 text-danger"></i> <?= e($ev['location'] ?? 'Sanctuary Main Grounds') ?></span>
            </div>
            <p class="text-muted fs-6 mb-4" style="white-space: pre-line; line-height: 1.7;"><?= e($ev['description'] ?? '') ?></p>
            <div class="mt-auto pt-2">
              <a href="<?= $base ?>/contact.php" class="btn btn-gold w-100 py-2 fw-bold">
                <i class="bi bi-person-check-fill me-1"></i> Inquire / Attend Event
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($past)): ?>
      <h3 class="section-title mb-4"><i class="bi bi-check-circle text-muted me-2"></i> Past Events</h3>
      <div class="row g-4">
        <?php foreach ($past as $ev): 
          $pastImgUrl = !empty($ev['image']) ? getImageUrl($ev['image']) : '';
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="p-4 bg-light rounded-4 border h-100 d-flex flex-column" style="opacity:.92;">
            <?php if ($pastImgUrl): ?>
              <div class="mb-3 rounded-3 overflow-hidden text-center bg-white border" style="max-height: 260px;">
                <img src="<?= e($pastImgUrl) ?>" alt="<?= e($ev['title']) ?>" class="img-fluid" style="max-height: 260px; object-fit: contain;">
              </div>
            <?php endif; ?>
            <span class="badge bg-secondary mb-2 align-self-start">Completed</span>
            <h5 class="fw-bold mb-1"><?= e($ev['title']) ?></h5>
            <span class="small text-muted mb-2"><i class="bi bi-calendar3 me-1"></i> <?= formatDate($ev['event_date'], 'D, d M Y') ?></span>
            <p class="text-muted small mt-2 mb-0"><?= e(truncate($ev['description'] ?? '', 120)) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (empty($events)): ?>
        <div class="text-center py-5">
          <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
          <p class="text-muted fs-5">No events scheduled at this time. Please check back soon!</p>
        </div>
      <?php endif; ?>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
