<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$newCount  = $db->query("SELECT COUNT(*) FROM reservations WHERE status='nouveau'")->fetchColumn();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$catFilter = $_GET['cat'] ?? 'all';

$where  = $catFilter !== 'all' ? 'WHERE category = ?' : '';
$params = $catFilter !== 'all' ? [$catFilter] : [];
$stmt   = $db->prepare("SELECT * FROM gallery_photos $where ORDER BY sort_order ASC, created_at DESC");
$stmt->execute($params);
$photos = $stmt->fetchAll();

$catCounts = [];
$allCount  = (int)$db->query("SELECT COUNT(*) FROM gallery_photos")->fetchColumn();
foreach (['hotel','chambres','restaurant','plage','services'] as $c) {
    $s = $db->prepare("SELECT COUNT(*) FROM gallery_photos WHERE category=?");
    $s->execute([$c]); $catCounts[$c] = (int)$s->fetchColumn();
}

$uploadDir = __DIR__ . '/../uploads/gallery/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Galerie — Admin Bariba Playa</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="css/admin.css"/>
</head>
<body>

<aside class="admin-sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-main">Bariba Playa</div>
    <span class="logo-sub">Administration</span>
    <span class="logo-badge"><i class="fa-solid fa-shield-halved"></i> &nbsp;Panel Admin</span>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Navigation</div>
    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge"></i> Tableau de Bord</a>
    <a href="reservations.php" class="sidebar-link">
      <i class="fa-solid fa-calendar-check"></i> Réservations
      <?php if ($newCount > 0): ?><span class="sidebar-badge"><?= $newCount ?></span><?php endif; ?>
    </a>
    <a href="gallery.php" class="sidebar-link active"><i class="fa-solid fa-images"></i> Galerie Photos</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Liens Rapides</div>
    <a href="../index.html" target="_blank" class="sidebar-link"><i class="fa-solid fa-globe"></i> Voir le Site</a>
    <a href="../galerie.html" target="_blank" class="sidebar-link"><i class="fa-solid fa-images"></i> Galerie Publique</a>
  </div>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($adminName,0,1)) ?></div>
      <div><div class="uname"><?= htmlspecialchars($adminName) ?></div><div class="urole">Admin</div></div>
      <a href="../api/auth.php?action=logout" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
  </div>
</aside>

<div class="admin-main">
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="mobile-sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
      <div class="topbar-title">Galerie Photos</div>
    </div>
    <div class="topbar-actions">
      <button class="topbar-btn topbar-btn-primary" onclick="openUploadModal()">
        <i class="fa-solid fa-cloud-arrow-up"></i> Ajouter des Photos
      </button>
    </div>
  </header>

  <div class="admin-content">

    <!-- Stats -->
    <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:14px; margin-bottom:24px;">
      <?php
        $allCats = [
          'all'        => ['Toutes',     '#1a0b06', $allCount],
          'hotel'      => ['Hôtel',      '#4a2010', $catCounts['hotel']],
          'chambres'   => ['Chambres',   '#2d7a5f', $catCounts['chambres']],
          'restaurant' => ['Restaurant', '#c82018', $catCounts['restaurant']],
          'plage'      => ['Plage',      '#1a9bd7', $catCounts['plage']],
          'services'   => ['Services',   '#e67e22', $catCounts['services']],
        ];
        foreach ($allCats as $k => [$lbl, $col, $cnt]):
      ?>
      <a href="?cat=<?= $k ?>" style="background:var(--white); border-radius:10px; padding:14px 10px; text-align:center; box-shadow:var(--shadow); border:2px solid <?= $catFilter===$k ? $col : 'transparent' ?>; transition:.2s; text-decoration:none; display:block;">
        <div style="font-family:'Playfair Display',serif; font-size:1.4rem; font-weight:700; color:<?= $col ?>;"><?= $cnt ?></div>
        <div style="font-size:.72rem; color:#6b7a8a; margin-top:3px;"><?= $lbl ?></div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Photos grid -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">
          <i class="fa-solid fa-images" style="color:#c82018; margin-right:8px;"></i>
          <?= $catFilter === 'all' ? 'Toutes les photos' : ucfirst($catFilter) ?>
          <span style="font-size:.78rem; color:#6b7a8a; font-weight:400; margin-left:8px;">(<?= count($photos) ?> photo<?= count($photos)>1?'s':'' ?>)</span>
        </div>
        <?php if ($catFilter !== 'all'): ?>
        <a href="gallery.php" style="font-size:.8rem; color:#c82018;">Voir toutes →</a>
        <?php endif; ?>
      </div>
      <div class="panel-body">
        <?php if ($photos): ?>
        <div class="gallery-admin-grid" id="photoGrid">
          <?php foreach ($photos as $p):
            // Check if it's an uploaded file or still a URL placeholder
            $isUploaded = !str_starts_with($p['filename'], 'http');
            $src = $isUploaded ? '../uploads/gallery/' . htmlspecialchars($p['filename']) : htmlspecialchars($p['filename']);
          ?>
          <div class="photo-card" id="photo-<?= $p['id'] ?>">
            <img src="<?= $src ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22140%22%3E%3Crect fill=%22%23f0f0f0%22 width=%22200%22 height=%22140%22/%3E%3Ctext fill=%22%23999%22 font-size=%2212%22 x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22%3EImage%3C/text%3E%3C/svg%3E'" />
            <div class="photo-card-actions">
              <button class="photo-action-btn photo-action-del" title="Supprimer" onclick="deletePhoto(<?= $p['id'] ?>)">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
            <div class="photo-card-body">
              <div class="photo-card-title"><?= htmlspecialchars($p['title'] ?: 'Sans titre') ?></div>
              <div class="photo-card-cat">
                <span style="display:inline-block; background:rgba(200,32,24,.12); color:#c82018; font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:50px; text-transform:uppercase; letter-spacing:.08em;">
                  <?= htmlspecialchars($p['category']) ?>
                </span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-images"></i>
          <p>Aucune photo dans cette catégorie.</p>
          <button class="btn btn-primary" style="margin-top:16px;" onclick="openUploadModal()">
            <i class="fa-solid fa-cloud-arrow-up"></i> Ajouter des Photos
          </button>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- Upload Modal -->
<div class="modal-overlay" id="uploadModal">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <div class="modal-title"><i class="fa-solid fa-cloud-arrow-up" style="color:#c82018; margin-right:8px;"></i>Ajouter des Photos</div>
      <button class="modal-close" onclick="closeUpload()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body">

      <!-- Drop zone -->
      <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <p><strong>Glissez vos photos ici</strong> ou cliquez pour sélectionner</p>
        <p style="font-size:.78rem; margin-top:6px; color:#9aa5af;">JPEG, PNG, WebP — Max 5 Mo par photo</p>
        <input type="file" id="fileInput" multiple accept="image/jpeg,image/png,image/webp" />
      </div>

      <!-- Preview list -->
      <div id="previewList" style="display:flex; flex-direction:column; gap:10px; margin-bottom:20px;"></div>

      <!-- Metadata -->
      <div id="metaForm" style="display:none;">
        <div class="form-group">
          <label>Titre de la photo</label>
          <input class="form-control" type="text" id="photoTitle" placeholder="Ex: Vue lagune depuis la terrasse" />
        </div>
        <div class="form-group">
          <label>Catégorie</label>
          <select class="form-control" id="photoCat">
            <option value="hotel">Hôtel</option>
            <option value="chambres">Chambres</option>
            <option value="restaurant">Restaurant</option>
            <option value="plage">Plage &amp; Lagune</option>
            <option value="services">Services</option>
          </select>
        </div>
      </div>

      <!-- Progress -->
      <div id="uploadProgress" style="display:none;">
        <div style="background:#f0f2f5; border-radius:6px; height:8px; overflow:hidden; margin-bottom:8px;">
          <div id="progressBar" style="background:linear-gradient(90deg,#c82018,#e87018); height:100%; width:0%; transition:.3s; border-radius:6px;"></div>
        </div>
        <div id="progressText" style="font-size:.8rem; color:#6b7a8a; text-align:center;"></div>
      </div>

    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeUpload()">Annuler</button>
      <button class="btn btn-primary" id="uploadBtn" onclick="startUpload()" disabled>
        <i class="fa-solid fa-cloud-arrow-up"></i> Téléverser
      </button>
    </div>
  </div>
</div>

<div id="toast"></div>

<script>
  let selectedFiles = [];

  function toast(msg, type='success') {
    const t = document.getElementById('toast');
    const el = document.createElement('div');
    el.className = `toast-item ${type}`;
    el.innerHTML = `<i class="fa-solid ${type==='success'?'fa-check-circle':'fa-circle-exclamation'}"></i> ${msg}`;
    t.appendChild(el);
    setTimeout(() => el.remove(), 3500);
  }

  // Upload modal
  function openUploadModal() { document.getElementById('uploadModal').classList.add('open'); }
  function closeUpload() {
    document.getElementById('uploadModal').classList.remove('open');
    resetUploadForm();
  }
  function resetUploadForm() {
    selectedFiles = [];
    document.getElementById('previewList').innerHTML = '';
    document.getElementById('metaForm').style.display = 'none';
    document.getElementById('uploadBtn').disabled = true;
    document.getElementById('uploadProgress').style.display = 'none';
    document.getElementById('fileInput').value = '';
    document.getElementById('progressBar').style.width = '0%';
  }

  // File input
  document.getElementById('fileInput').addEventListener('change', function() {
    handleFiles(this.files);
  });

  // Drag & drop
  const dropZone = document.getElementById('dropZone');
  ['dragenter','dragover'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.classList.add('drag-over'); }));
  ['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => { ev.preventDefault(); dropZone.classList.remove('drag-over'); }));
  dropZone.addEventListener('drop', ev => handleFiles(ev.dataTransfer.files));

  function handleFiles(files) {
    selectedFiles = Array.from(files).filter(f => f.type.startsWith('image/') && f.size <= 5*1024*1024);
    if (!selectedFiles.length) { toast('Aucun fichier valide sélectionné (max 5 Mo, images uniquement).', 'error'); return; }

    const list = document.getElementById('previewList');
    list.innerHTML = '';
    selectedFiles.forEach((f, i) => {
      const reader = new FileReader();
      reader.onload = e => {
        const item = document.createElement('div');
        item.style.cssText = 'display:flex; align-items:center; gap:12px; padding:10px; background:#faf7f2; border-radius:8px; border:1px solid #e8e0d5;';
        item.innerHTML = `
          <img src="${e.target.result}" style="width:56px; height:42px; object-fit:cover; border-radius:6px;" />
          <div style="flex:1;">
            <div style="font-size:.84rem; font-weight:600; color:#1a0b06;">${f.name}</div>
            <div style="font-size:.74rem; color:#6b7a8a;">${(f.size/1024).toFixed(0)} Ko</div>
          </div>
          <i class="fa-solid fa-check-circle" style="color:#2d7a5f;"></i>
        `;
        list.appendChild(item);
      };
      reader.readAsDataURL(f);
    });

    document.getElementById('metaForm').style.display = 'block';
    document.getElementById('uploadBtn').disabled = false;
  }

  async function startUpload() {
    if (!selectedFiles.length) return;
    const btn = document.getElementById('uploadBtn');
    const progress = document.getElementById('uploadProgress');
    const bar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const title    = document.getElementById('photoTitle').value.trim();
    const category = document.getElementById('photoCat').value;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Upload...';
    progress.style.display = 'block';

    let done = 0;
    const errors = [];

    for (const file of selectedFiles) {
      const fd = new FormData();
      fd.append('photo', file);
      fd.append('title', title || file.name.replace(/\.[^.]+$/, ''));
      fd.append('category', category);

      try {
        const res  = await fetch('../api/gallery.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
          done++;
        } else {
          errors.push(`${file.name}: ${json.error}`);
        }
      } catch {
        errors.push(`${file.name}: erreur réseau`);
      }

      const pct = Math.round((done / selectedFiles.length) * 100);
      bar.style.width = pct + '%';
      progressText.textContent = `${done} / ${selectedFiles.length} fichier(s) uploadé(s)`;
    }

    if (errors.length) {
      errors.forEach(e => toast(e, 'error'));
    }
    if (done > 0) {
      toast(`${done} photo(s) ajoutée(s) avec succès !`);
      setTimeout(() => { closeUpload(); location.reload(); }, 1200);
    } else {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Réessayer';
    }
  }

  async function deletePhoto(id) {
    if (!confirm('Supprimer cette photo définitivement ?')) return;
    const res  = await fetch('../api/gallery.php?id=' + id, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      document.getElementById('photo-' + id)?.remove();
      toast('Photo supprimée.');
    } else {
      toast(data.error || 'Erreur.', 'error');
    }
  }

  // Close modal on overlay click
  document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) closeUpload();
  });
</script>
</body>
</html>
