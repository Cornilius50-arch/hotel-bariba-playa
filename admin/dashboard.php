<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Stats
$totalRes  = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$newRes    = $db->query("SELECT COUNT(*) FROM reservations WHERE status='nouveau'")->fetchColumn();
$totalPics = $db->query("SELECT COUNT(*) FROM gallery_photos")->fetchColumn();
$confirmed = $db->query("SELECT COUNT(*) FROM reservations WHERE status='confirme'")->fetchColumn();

// Recent reservations
$recent = $db->query("SELECT * FROM reservations ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Reservations by type
$byType = $db->query("SELECT type, COUNT(*) as cnt FROM reservations GROUP BY type")->fetchAll(PDO::FETCH_KEY_PAIR);

$adminName = $_SESSION['admin_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tableau de Bord — Admin Bariba Playa</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<body>

<!-- ─── Sidebar ─────────────────────────────────────────── -->
<aside class="admin-sidebar" id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-main">Bariba Playa</div>
    <span class="logo-sub">Administration</span>
    <span class="logo-badge"><i class="fa-solid fa-shield-halved"></i> &nbsp;Panel Admin</span>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Navigation</div>
    <a href="dashboard.php" class="sidebar-link active"><i class="fa-solid fa-gauge"></i> Tableau de Bord</a>
    <a href="reservations.php" class="sidebar-link">
      <i class="fa-solid fa-calendar-check"></i> Réservations
      <?php if ($newRes > 0): ?>
        <span class="sidebar-badge"><?= $newRes ?></span>
      <?php endif; ?>
    </a>
    <a href="gallery.php" class="sidebar-link"><i class="fa-solid fa-images"></i> Galerie Photos</a>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-label">Liens Rapides</div>
    <a href="../index.html" target="_blank" class="sidebar-link"><i class="fa-solid fa-globe"></i> Voir le Site</a>
    <a href="../contact.html" target="_blank" class="sidebar-link"><i class="fa-solid fa-paper-plane"></i> Page Contact</a>
  </div>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($adminName, 0, 1)) ?></div>
      <div>
        <div class="uname"><?= htmlspecialchars($adminName) ?></div>
        <div class="urole"><?= $_SESSION['admin_role'] === 'super' ? 'Super Admin' : 'Staff' ?></div>
      </div>
      <a href="../api/auth.php?action=logout" class="logout-btn" title="Déconnexion"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
  </div>
</aside>

<!-- ─── Main ─────────────────────────────────────────────── -->
<div class="admin-main">
  <header class="admin-topbar">
    <div style="display:flex; align-items:center; gap:12px;">
      <button class="mobile-sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="topbar-title">Tableau de Bord</div>
    </div>
    <div class="topbar-actions">
      <span style="font-size:.82rem; color:#6b7a8a;"><?= date('l d F Y') ?></span>
      <a href="reservations.php" class="topbar-btn topbar-btn-primary">
        <i class="fa-solid fa-calendar-check"></i> Réservations
        <?php if ($newRes > 0): ?><span style="background:rgba(255,255,255,.3); padding:1px 7px; border-radius:50px; font-size:.7rem;"><?= $newRes ?></span><?php endif; ?>
      </a>
    </div>
  </header>

  <div class="admin-content">

    <!-- Welcome -->
    <div style="margin-bottom:28px;">
      <h1 style="font-family:'Playfair Display',serif; font-size:1.6rem; color:#1a0b06; margin-bottom:4px;">
        Bonjour, <?= htmlspecialchars($adminName) ?> 👋
      </h1>
      <p style="color:#6b7a8a; font-size:.9rem;">Voici un résumé de l'activité de l'Hôtel Bariba Playa.</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card gold">
        <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
        <div><div class="stat-num"><?= $totalRes ?></div><div class="stat-lbl">Total Réservations</div></div>
      </div>
      <div class="stat-card blue">
        <div class="stat-icon"><i class="fa-solid fa-bell"></i></div>
        <div><div class="stat-num"><?= $newRes ?></div><div class="stat-lbl">Nouvelles (non lues)</div></div>
      </div>
      <div class="stat-card green">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div><div class="stat-num"><?= $confirmed ?></div><div class="stat-lbl">Confirmées</div></div>
      </div>
      <div class="stat-card orange">
        <div class="stat-icon"><i class="fa-solid fa-images"></i></div>
        <div><div class="stat-num"><?= $totalPics ?></div><div class="stat-lbl">Photos en Galerie</div></div>
      </div>
    </div>

    <div style="display:grid; grid-template-columns:1.6fr 1fr; gap:20px;">

      <!-- Recent reservations -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title"><i class="fa-solid fa-clock" style="color:#c82018; margin-right:8px;"></i>Dernières Réservations</div>
          <a href="reservations.php" style="font-size:.8rem; color:#c82018; font-weight:600;">Voir tout →</a>
        </div>
        <?php if ($recent): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Client</th><th>Type</th><th>Date</th><th>Statut</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $r): ?>
              <tr>
                <td>
                  <div style="font-weight:600; color:#1a0b06;"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></div>
                  <div style="font-size:.75rem; color:#6b7a8a;"><?= htmlspecialchars($r['email']) ?></div>
                </td>
                <td><span class="badge badge-<?= $r['type'] ?>"><?= ucfirst($r['type']) ?></span></td>
                <td style="font-size:.8rem; color:#6b7a8a;"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                <td><a href="reservations.php?id=<?= $r['id'] ?>" style="color:#c82018; font-size:.78rem;">Voir</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <div class="empty-state"><i class="fa-solid fa-inbox"></i><p>Aucune réservation pour le moment.</p></div>
        <?php endif; ?>
      </div>

      <!-- Quick actions + Summary -->
      <div style="display:flex; flex-direction:column; gap:20px;">

        <!-- Quick actions -->
        <div class="panel">
          <div class="panel-header"><div class="panel-title">Actions Rapides</div></div>
          <div class="panel-body" style="display:flex; flex-direction:column; gap:10px;">
            <a href="reservations.php?status=nouveau" style="display:flex; align-items:center; gap:10px; padding:12px; border-radius:8px; background:#faf7f2; border:1px solid #e8e0d5; font-size:.88rem; color:#1a0b06; font-weight:500; transition:.2s;" onmouseover="this.style.borderColor='#c82018'" onmouseout="this.style.borderColor='#e8e0d5'">
              <i class="fa-solid fa-envelope" style="color:#c82018; width:16px;"></i>
              Traiter les nouvelles demandes
              <?php if ($newRes > 0): ?><span style="margin-left:auto; background:#c82018; color:#fff; font-size:.65rem; font-weight:700; padding:2px 8px; border-radius:50px;"><?= $newRes ?></span><?php endif; ?>
            </a>
            <a href="gallery.php" style="display:flex; align-items:center; gap:10px; padding:12px; border-radius:8px; background:#faf7f2; border:1px solid #e8e0d5; font-size:.88rem; color:#1a0b06; font-weight:500; transition:.2s;" onmouseover="this.style.borderColor='#c82018'" onmouseout="this.style.borderColor='#e8e0d5'">
              <i class="fa-solid fa-cloud-arrow-up" style="color:#c82018; width:16px;"></i>
              Ajouter des photos à la galerie
            </a>
            <a href="../contact.html" target="_blank" style="display:flex; align-items:center; gap:10px; padding:12px; border-radius:8px; background:#faf7f2; border:1px solid #e8e0d5; font-size:.88rem; color:#1a0b06; font-weight:500; transition:.2s;" onmouseover="this.style.borderColor='#c82018'" onmouseout="this.style.borderColor='#e8e0d5'">
              <i class="fa-solid fa-globe" style="color:#c82018; width:16px;"></i>
              Voir le site web
            </a>
          </div>
        </div>

        <!-- Reservations by type -->
        <div class="panel">
          <div class="panel-header"><div class="panel-title">Réservations par Type</div></div>
          <div class="panel-body">
            <?php
              $types = ['chambre' => ['Chambres', 'fa-bed', '#4a2010'], 'table' => ['Restaurant', 'fa-utensils', '#2d7a5f'], 'event' => ['Événements', 'fa-glass-cheers', '#c82018']];
              foreach ($types as $key => [$label, $icon, $color]): $cnt = $byType[$key] ?? 0;
            ?>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
              <i class="fa-solid <?= $icon ?>" style="color:<?= $color ?>; width:16px;"></i>
              <span style="font-size:.88rem; color:#1a0b06; flex:1;"><?= $label ?></span>
              <span style="font-family:'Playfair Display',serif; font-size:1.2rem; font-weight:700; color:<?= $color ?>;"><?= $cnt ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Hotel contact info -->
        <div class="panel" style="background:linear-gradient(135deg,#1a0b06,#4a2010); border:none;">
          <div class="panel-body">
            <div style="font-family:'Playfair Display',serif; font-size:1rem; color:#fff; margin-bottom:12px;">Contact Hôtel</div>
            <div style="font-size:.82rem; color:rgba(255,255,255,.6); line-height:2;">
              <div><i class="fa-solid fa-phone" style="color:#e87018; margin-right:8px;"></i><a href="tel:+22997856500" style="color:rgba(255,255,255,.7);">+229 97 85 65 00</a></div>
              <div><i class="fa-solid fa-envelope" style="color:#e87018; margin-right:8px;"></i><a href="mailto:hotelbaribaplaya.28@gmail.com" style="color:rgba(255,255,255,.7);">hotelbaribaplaya.28@gmail.com</a></div>
              <div><i class="fa-solid fa-location-dot" style="color:#e87018; margin-right:8px;"></i><span style="color:rgba(255,255,255,.7);">Fidjrossè, Cotonou, Bénin</span></div>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div><!-- /admin-content -->
</div><!-- /admin-main -->

<div id="toast"></div>
</body>
</html>
