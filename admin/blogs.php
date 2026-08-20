<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'blogs';
$admin_title = 'Blog Posts';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$blogs = $pdo->query("SELECT b.*, bc.name AS category_name FROM blogs b LEFT JOIN blog_categories bc ON b.category_id = bc.id ORDER BY b.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM blog_categories WHERE status='active' ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Vedic Wisdom &amp; Blog Posts (<?= count($blogs) ?>)</h5>
    <p class="text-muted small mb-0">Manage sanctuary articles, A2 milk knowledge, and educational stories.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#blogModal" onclick="resetBlogForm()">
    <i class="bi bi-plus-lg me-1"></i> Create Blog Post
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Cover</th>
        <th>Article Title</th>
        <th>Category</th>
        <th>Author</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($blogs as $b): ?>
      <tr>
        <td>
          <img src="<?= e($b['featured_image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=150&q=80') ?>" class="table-thumb" alt="<?= e($b['title']) ?>">
        </td>
        <td>
          <div class="fw-semibold fs-6"><?= e(truncate($b['title'], 45)) ?></div>
          <small class="text-muted"><?= e(truncate($b['excerpt'] ?? '', 40)) ?></small>
        </td>
        <td><span class="badge bg-light text-dark border"><?= e($b['category_name'] ?: 'Vedic') ?></span></td>
        <td><small class="fw-semibold text-muted"><?= e($b['author'] ?: 'Kamadhenu Team') ?></small></td>
        <td>
          <span class="badge <?= $b['status']==='Published'?'badge-active':'badge-pending' ?>">
            <?= e($b['status']) ?>
          </span>
        </td>
        <td><small class="text-muted"><?= formatDate($b['published_at'] ?? $b['created_at']) ?></small></td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-blog-btn" data-blog='<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Post">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-blog-btn" data-id="<?= $b['id'] ?>" data-title="<?= e($b['title']) ?>" title="Delete Post">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($blogs)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No articles found. Click "Create Blog Post" to publish one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Blog Modal -->
<div class="modal fade" id="blogModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="blogModalTitle">Create Blog Post</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="blogForm">
          <input type="hidden" name="entity" value="blogs">
          <input type="hidden" name="id" id="blogId">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Article Title *</label>
              <input type="text" name="title" id="blogTitle" class="form-control" placeholder="e.g. The Magnificent Significance of Indigenous Desi Cow Breeds" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category</label>
              <select name="category_id" id="blogCategory" class="form-select">
                <option value="">Select category</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Author Name</label>
              <input type="text" name="author" id="blogAuthor" class="form-control" value="Acharya Vidyadhar">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Publish Date</label>
              <input type="date" name="published_at" id="blogDate" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="blogStatus" class="form-select">
                <option value="Published">Published</option>
                <option value="Draft">Draft</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Cover Image URL</label>
              <input type="url" name="featured_image" id="blogImage" class="form-control" placeholder="https://images.unsplash.com/...">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Short Summary / Excerpt</label>
              <textarea name="excerpt" id="blogExcerpt" rows="2" class="form-control" placeholder="Brief preview paragraph shown on cards..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Full HTML Article Content</label>
              <textarea name="content" id="blogContent" rows="6" class="form-control font-monospace" placeholder="<h2>Introduction</h2><p>Full article text goes here...</p>"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveBlogBtn">Publish Article</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetBlogForm() {
  document.getElementById("blogForm").reset();
  document.getElementById("blogId").value = "";
  document.getElementById("blogDate").value = new Date().toISOString().split("T")[0];
  document.getElementById("blogModalTitle").textContent = "Create Blog Post";
}

document.querySelectorAll(".edit-blog-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const b = JSON.parse(this.dataset.blog);
    document.getElementById("blogModalTitle").textContent = "Edit Article: " + b.title;
    document.getElementById("blogId").value = b.id;
    document.getElementById("blogTitle").value = b.title || "";
    document.getElementById("blogCategory").value = b.category_id || "";
    document.getElementById("blogAuthor").value = b.author || "Kamadhenu Team";
    document.getElementById("blogDate").value = b.published_at || "";
    document.getElementById("blogStatus").value = b.status || "Published";
    document.getElementById("blogImage").value = b.featured_image || "";
    document.getElementById("blogExcerpt").value = b.excerpt || "";
    document.getElementById("blogContent").value = b.content || "";
    new bootstrap.Modal(document.getElementById("blogModal")).show();
  });
});

document.getElementById("saveBlogBtn").addEventListener("click", async () => {
  const form = document.getElementById("blogForm");
  if (!form.checkValidity()) { form.reportValidity(); return; }
  const fd = new FormData(form);
  const data = Object.fromEntries(fd.entries());
  try {
    const res = await fetch(BASE_URL + "/admin/api/crud_api.php", {
      method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      showAdminToast(result.message, "success");
      setTimeout(() => location.reload(), 600);
    } else {
      showAdminToast(result.message || "Failed to save blog post.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-blog-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Are you sure you want to delete \'" + this.dataset.title + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=blogs&id=" + this.dataset.id);
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
