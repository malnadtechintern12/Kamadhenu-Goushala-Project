<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'events';
$admin_title = 'Events';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Sanctuary Events &amp; Festivals (<?= count($events) ?>)</h5>
    <p class="text-muted small mb-0">Manage Gopashtami festivals, veterinary camps, and community workshops.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#eventModal" onclick="resetEventForm()">
    <i class="bi bi-plus-lg me-1"></i> Create Event
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Poster</th>
        <th>Event Title</th>
        <th>Date &amp; Time</th>
        <th>Location</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($events as $ev): 
        $thumb = !empty($ev['image']) ? getImageUrl($ev['image']) : 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=150&q=80';
      ?>
      <tr>
        <td>
          <img src="<?= e($thumb) ?>" class="table-thumb" alt="<?= e($ev['title']) ?>" style="object-fit:cover;">
        </td>
        <td>
          <div class="fw-semibold fs-6"><?= e($ev['title']) ?></div>
          <small class="text-muted"><?= e(truncate($ev['description'] ?? '', 45)) ?></small>
        </td>
        <td>
          <div class="fw-semibold"><i class="bi bi-calendar3 me-1 text-warning"></i> <?= formatDate($ev['event_date'], 'D, d M Y') ?></div>
          <small class="text-muted"><?= date('h:i A', strtotime($ev['start_time'])) ?> – <?= date('h:i A', strtotime($ev['end_time'])) ?></small>
        </td>
        <td><small class="text-muted"><i class="bi bi-geo-alt me-1 text-danger"></i> <?= e(truncate($ev['location'] ?? 'Sanctuary Main Grounds', 35)) ?></small></td>
        <td>
          <?php if ($ev['status'] === 'Upcoming'): ?>
            <span class="badge bg-success">Upcoming</span>
          <?php elseif ($ev['status'] === 'Completed'): ?>
            <span class="badge bg-secondary">Completed</span>
          <?php else: ?>
            <span class="badge bg-danger">Cancelled</span>
          <?php endif; ?>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-event-btn" data-event='<?= json_encode($ev, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Event">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-event-btn" data-id="<?= $ev['id'] ?>" data-title="<?= e($ev['title']) ?>" title="Delete Event">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($events)): ?>
        <tr><td colspan="6" class="text-center py-4 text-muted">No events found. Click "Create Event" to schedule one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventModalTitle">Schedule Event</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="eventForm" enctype="multipart/form-data">
          <input type="hidden" name="entity" value="events">
          <input type="hidden" name="id" id="eventId">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Event Title *</label>
              <input type="text" name="title" id="eventTitle" class="form-control" placeholder="e.g. Annual Gopashtami Mahotsav & Gau Aarti" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="eventStatus" class="form-select">
                <option value="Upcoming">Upcoming</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Event Date *</label>
              <input type="date" name="event_date" id="eventDate" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Start Time</label>
              <input type="time" name="start_time" id="eventStartTime" class="form-control" value="09:00">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">End Time</label>
              <input type="time" name="end_time" id="eventEndTime" class="form-control" value="17:00">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold">Location / Venue</label>
              <input type="text" name="location" id="eventLocation" class="form-control" placeholder="Kamadhenu Goushala Main Grounds, Bengaluru">
            </div>

            <!-- Upload / Image Section -->
            <div class="col-12">
              <div class="p-3 bg-light rounded-3 border">
                <label class="form-label fw-bold text-forest mb-2"><i class="bi bi-image me-1 text-warning"></i> Event Poster / Full Banner Image</label>
                <div class="row g-3 align-items-center">
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Upload Image from Computer</label>
                    <input type="file" name="image_file" id="eventImageFile" class="form-control" accept="image/*">
                    <small class="text-muted">Supports JPG, PNG, WEBP, GIF (Full poster loaded without cropping)</small>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold">Or Image URL</label>
                    <input type="text" name="image" id="eventImage" class="form-control" placeholder="assets/uploads/events/... or https://...">
                  </div>
                  <div class="col-12" id="eventImgPreviewContainer" style="display:none;">
                    <label class="form-label small fw-semibold text-success">Image Preview:</label>
                    <div class="p-2 bg-white rounded border text-center" style="max-height: 240px; overflow: hidden;">
                      <img id="eventImgPreview" src="" alt="Preview" style="max-height: 220px; max-width: 100%; object-fit: contain;">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Event Description &amp; Highlights</label>
              <textarea name="description" id="eventDescription" rows="4" class="form-control" placeholder="Grand celebration featuring havan, cow alankaram, and mahaprasadam..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveEventBtn">Save Event</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetEventForm() {
  document.getElementById("eventForm").reset();
  document.getElementById("eventId").value = "";
  document.getElementById("eventDate").value = new Date().toISOString().split("T")[0];
  document.getElementById("eventModalTitle").textContent = "Schedule Event";
  document.getElementById("eventImgPreviewContainer").style.display = "none";
}

// Live Image Preview for URL input
document.getElementById("eventImage")?.addEventListener("input", function() {
  const url = this.value.trim();
  if (url) {
    document.getElementById("eventImgPreview").src = url.startsWith("http") ? url : (BASE_URL + "/" + url.replace(/^\//, ""));
    document.getElementById("eventImgPreviewContainer").style.display = "block";
  } else {
    document.getElementById("eventImgPreviewContainer").style.display = "none";
  }
});

// Live Image Preview for File input
document.getElementById("eventImageFile")?.addEventListener("change", function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(evt) {
      document.getElementById("eventImgPreview").src = evt.target.result;
      document.getElementById("eventImgPreviewContainer").style.display = "block";
    };
    reader.readAsDataURL(file);
  }
});

document.querySelectorAll(".edit-event-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const ev = JSON.parse(this.dataset.event);
    document.getElementById("eventModalTitle").textContent = "Edit Event: " + ev.title;
    document.getElementById("eventId").value = ev.id;
    document.getElementById("eventTitle").value = ev.title || "";
    document.getElementById("eventStatus").value = ev.status || "Upcoming";
    document.getElementById("eventDate").value = ev.event_date || "";
    document.getElementById("eventStartTime").value = ev.start_time || "09:00";
    document.getElementById("eventEndTime").value = ev.end_time || "17:00";
    document.getElementById("eventLocation").value = ev.location || "";
    document.getElementById("eventImage").value = ev.image || "";
    document.getElementById("eventDescription").value = ev.description || "";
    
    if (ev.image) {
      const fullUrl = ev.image.startsWith("http") ? ev.image : (BASE_URL + "/" + ev.image.replace(/^\//, ""));
      document.getElementById("eventImgPreview").src = fullUrl;
      document.getElementById("eventImgPreviewContainer").style.display = "block";
    } else {
      document.getElementById("eventImgPreviewContainer").style.display = "none";
    }
    
    new bootstrap.Modal(document.getElementById("eventModal")).show();
  });
});

document.getElementById("saveEventBtn").addEventListener("click", async () => {
  const form = document.getElementById("eventForm");
  if (!form.checkValidity()) { form.reportValidity(); return; }
  const btn = document.getElementById("saveEventBtn");
  btn.disabled = true;
  btn.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>Saving...\';

  const fd = new FormData(form);
  try {
    const res = await fetch(BASE_URL + "/admin/api/crud_api.php", {
      method: "POST",
      body: fd // Sends multipart form data directly
    });
    const result = await res.json();
    if (result.success) {
      showAdminToast(result.message, "success");
      setTimeout(() => location.reload(), 600);
    } else {
      showAdminToast(result.message || "Failed to save event.", "error");
      btn.disabled = false;
      btn.innerHTML = "Save Event";
    }
  } catch {
    showAdminToast("Network error occurred.", "error");
    btn.disabled = false;
    btn.innerHTML = "Save Event";
  }
});

document.querySelectorAll(".delete-event-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete event \'" + this.dataset.title + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=events&id=" + this.dataset.id);
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      } else {
        showAdminToast(result.message || "Delete failed.", "error");
      }
    } catch { showAdminToast("Network error.", "error"); }
  });
});
</script>';
include __DIR__ . '/includes/admin_footer.php';
?>
