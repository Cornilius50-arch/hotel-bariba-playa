<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../config/database.php';

$db       = getDB();
$newCount = $db->query("SELECT COUNT(*) FROM reservations WHERE status='nouveau'")->fetchColumn();
$adminName = $_SESSION['admin_name'] ?? 'Admin';

$statusFilter = $_GET['status'] ?? 'all';
$search       = trim($_GET['search'] ?? '');
$where = []; $params = [];
if ($statusFilter !== 'all') { $where[] = 'status = ?'; $params[] = $statusFilter; }
if ($search) {
    $where[] = '(prenom LIKE ? OR nom LIKE ? OR email LIKE ? OR telephone LIKE ?)';
    $like = "%$search%"; array_push($params, $like, $like, $like, $like);
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$stmt = $db->prepare("SELECT * FROM reservations $whereSQL ORDER BY created_at DESC");
$stmt->execute($params);
$reservations = $stmt->fetchAll();

$counts = [];
foreach (['all','nouveau','lu','confirme','annule'] as $s) {
    $q = $s === 'all' ? $db->query("SELECT COUNT(*) FROM reservations") : $db->prepare("SELECT COUNT(*) FROM reservations WHERE status=?");
    if ($s !== 'all') $q->execute([$s]); else $q->execute();
    $counts[$s] = (int)$q->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Réservations — Admin Bariba Playa</title>
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
    <a href="reservations.php" class="sidebar-link active">
      <i class="fa-solid fa-calendar-check"></i> Réservations
      <?php if ($newCount > 0): ?><span class="sidebar-badge"><?= $newCount ?></span><?php endif; ?>
    </a>
    <a href="gallery.php" class="sidebar-link"><i class="fa-solid fa-images"></i> Galerie Photos</a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-section-label">Liens Rapides</div>
    <a href="../index.html" target="_blank" class="sidebar-link"><i class="fa-solid fa-globe"></i> Voir le Site</a>
  </div>
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="avatar"><?= strtoupper(substr($adminName,0,1)) ?></div>
      <div><div class="uname"><?= htmlspecialchars($adminName) ?></div><div class="urole">Admin</div></div>
      <a href="../api/auth.php?action=logout" class="logout-btn" title="Déconnexion"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
  </div>
</aside>

<div class="admin-main">
  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="mobile-sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fa-solid fa-bars"></i></button>
      <div class="topbar-title">Réservations</div>
    </div>
    <div class="topbar-actions">
      <button class="topbar-btn topbar-btn-ghost" onclick="exportCSV()"><i class="fa-solid fa-download"></i> Exporter CSV</button>
    </div>
  </header>

  <div class="admin-content">

    <!-- Stat mini-cards -->
    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:24px;">
      <?php
        $statCards = [
          'all'     => ['Toutes', 'fa-list', '#4a2010'],
          'nouveau' => ['Nouvelles', 'fa-bell', '#c82018'],
          'lu'      => ['Lues', 'fa-envelope-open', '#e67e22'],
          'confirme'=> ['Confirmées', 'fa-circle-check', '#2d7a5f'],
          'annule'  => ['Annulées', 'fa-xmark-circle', '#c0392b'],
        ];
        foreach ($statCards as $s => [$lbl, $ico, $col]):
      ?>
      <a href="?status=<?= $s ?>" style="background:var(--white); border-radius:10px; padding:16px; display:flex; flex-direction:column; align-items:center; text-align:center; box-shadow:var(--shadow); border:2px solid <?= $statusFilter===$s ? $col : 'transparent' ?>; transition:.2s; text-decoration:none;">
        <i class="fa-solid <?= $ico ?>" style="font-size:1.3rem; color:<?= $col ?>; margin-bottom:8px;"></i>
        <div style="font-family:'Playfair Display',serif; font-size:1.5rem; font-weight:700; color:#1a0b06;"><?= $counts[$s] ?></div>
        <div style="font-size:.72rem; color:#6b7a8a; margin-top:2px;"><?= $lbl ?></div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Table panel -->
    <div class="panel">
      <div class="filter-bar">
        <form method="GET" style="display:contents;">
          <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>" />
          <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher..." />
          </div>
          <button type="submit" class="btn btn-ghost btn-sm" style="margin-left:8px;"><i class="fa-solid fa-search"></i> Rechercher</button>
          <?php if ($search): ?><a href="?status=<?= $statusFilter ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-xmark"></i> Effacer</a><?php endif; ?>
        </form>
      </div>

      <?php if ($reservations): ?>
      <div class="table-wrap">
        <table id="resTable">
          <thead>
            <tr>
              <th>#</th><th>Client</th><th>Contact</th><th>Type</th>
              <th>Dates / Détails</th><th>Statut</th><th>Reçu le</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservations as $r): ?>
            <tr id="row-<?= $r['id'] ?>">
              <td style="color:#6b7a8a; font-size:.8rem;">#<?= $r['id'] ?></td>
              <td>
                <div style="font-weight:600; color:#1a0b06;"><?= htmlspecialchars($r['prenom'].' '.$r['nom']) ?></div>
              </td>
              <td>
                <div style="font-size:.8rem;"><a href="mailto:<?= htmlspecialchars($r['email']) ?>" style="color:#4a2010;"><?= htmlspecialchars($r['email']) ?></a></div>
                <?php if ($r['telephone']): ?><div style="font-size:.78rem; color:#6b7a8a;"><?= htmlspecialchars($r['telephone']) ?></div><?php endif; ?>
              </td>
              <td><span class="badge badge-<?= $r['type'] ?>"><?= ucfirst($r['type']) ?></span></td>
              <td style="font-size:.82rem;">
                <?php if ($r['type']==='chambre'): ?>
                  <?= $r['checkin'] ? date('d/m/Y',$r['checkin'] ? strtotime($r['checkin']) : 0).' → '.date('d/m/Y',strtotime($r['checkout'])) : '—' ?><br>
                  <span style="color:#6b7a8a;"><?= htmlspecialchars($r['room_type'] ?: '—') ?></span>
                <?php elseif ($r['type']==='table'): ?>
                  <?= $r['rest_date'] ? date('d/m/Y',strtotime($r['rest_date'])) : '—' ?> à <?= htmlspecialchars($r['rest_time'] ?: '—') ?><br>
                  <span style="color:#6b7a8a;"><?= htmlspecialchars($r['covers'] ?: '') ?></span>
                <?php else: ?>
                  <?= $r['event_date'] ? date('d/m/Y',strtotime($r['event_date'])) : '—' ?><br>
                  <span style="color:#6b7a8a;"><?= htmlspecialchars($r['event_type'] ?: '') ?></span>
                <?php endif; ?>
              </td>
              <td>
                <select class="form-control" style="padding:5px 8px; font-size:.78rem; border-radius:6px;"
                  onchange="updateStatus(<?= $r['id'] ?>, this.value)">
                  <option value="nouveau"  <?= $r['status']==='nouveau'  ? 'selected':'' ?>>🔵 Nouveau</option>
                  <option value="lu"       <?= $r['status']==='lu'       ? 'selected':'' ?>>🟡 Lu</option>
                  <option value="confirme" <?= $r['status']==='confirme' ? 'selected':'' ?>>🟢 Confirmé</option>
                  <option value="annule"   <?= $r['status']==='annule'   ? 'selected':'' ?>>🔴 Annulé</option>
                </select>
              </td>
              <td style="font-size:.78rem; color:#6b7a8a; white-space:nowrap;"><?= date('d/m/Y H:i',strtotime($r['created_at'])) ?></td>
              <td>
                <div style="display:flex; gap:6px;">
                  <button class="btn btn-ghost btn-icon btn-sm" title="Voir détails" onclick="showDetail(<?= $r['id'] ?>)"><i class="fa-solid fa-eye"></i></button>
                  <button class="btn btn-danger btn-icon btn-sm" title="Supprimer" onclick="deleteRes(<?= $r['id'] ?>)"><i class="fa-solid fa-trash"></i></button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php else: ?>
      <div class="empty-state"><i class="fa-solid fa-inbox"></i><p>Aucune réservation trouvée.</p></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <div class="modal-title" id="modalTitle">Détails de la Réservation</div>
      <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="modal-body" id="modalBody">Chargement...</div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal()">Fermer</button>
      <button class="btn btn-success" id="confirmBtn" onclick="confirmFromModal()"><i class="fa-solid fa-check"></i> Confirmer</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<?php
// Embed reservations as JSON for JS
$resJson = json_encode($reservations, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
?>
<script>
  const ALL_RESERVATIONS = <?= $resJson ?>;

  function toast(msg, type='success') {
    const t = document.getElementById('toast');
    const el = document.createElement('div');
    el.className = `toast-item ${type}`;
    el.innerHTML = `<i class="fa-solid ${type==='success'?'fa-check-circle':'fa-circle-exclamation'}"></i> ${msg}`;
    t.appendChild(el);
    setTimeout(() => el.remove(), 3500);
  }

  async function updateStatus(id, status) {
    const res = await fetch('../api/reservations.php', {
      method: 'PATCH',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({id, status})
    });
    const data = await res.json();
    if (data.success) toast('Statut mis à jour !');
    else toast('Erreur lors de la mise à jour.', 'error');
  }

  async function deleteRes(id) {
    if (!confirm('Supprimer cette réservation ? Cette action est irréversible.')) return;
    const res = await fetch('../api/reservations.php?id=' + id, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) {
      document.getElementById('row-' + id)?.remove();
      toast('Réservation supprimée.');
    } else toast('Erreur.', 'error');
  }

  let currentModalId = null;
  function showDetail(id) {
    currentModalId = id;
    const r = ALL_RESERVATIONS.find(x => x.id == id);
    if (!r) return;
    document.getElementById('modalTitle').textContent = `Réservation #${r.id} — ${r.prenom} ${r.nom}`;
    const typeLabel = {chambre:'Chambre', table:'Restaurant', event:'Événement'}[r.type] || r.type;
    let details = '';
    if (r.type === 'chambre') {
      details = `<div class="form-row">
        <div><strong>Arrivée</strong><br>${r.checkin||'—'}</div>
        <div><strong>Départ</strong><br>${r.checkout||'—'}</div>
      </div>
      <p><strong>Chambre :</strong> ${r.room_type||'—'}</p>
      <p><strong>Voyageurs :</strong> ${r.guests||'—'}</p>`;
    } else if (r.type === 'table') {
      details = `<p><strong>Date :</strong> ${r.rest_date||'—'}</p>
      <p><strong>Heure :</strong> ${r.rest_time||'—'}</p>
      <p><strong>Couverts :</strong> ${r.covers||'—'}</p>`;
    } else {
      details = `<p><strong>Type d'événement :</strong> ${r.event_type||'—'}</p>
      <p><strong>Date :</strong> ${r.event_date||'—'}</p>
      <p><strong>Participants :</strong> ${r.participants||'—'}</p>`;
    }
    document.getElementById('modalBody').innerHTML = `
      <div style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
        <span class="badge badge-${r.type}">${typeLabel}</span>
        <span class="badge badge-${r.status}">${r.status}</span>
      </div>
      <div style="background:#faf7f2; border-radius:10px; padding:18px; margin-bottom:18px;">
        <h4 style="font-family:'Playfair Display',serif; color:#1a0b06; margin-bottom:12px;">Client</h4>
        <p><strong>Nom :</strong> ${r.prenom} ${r.nom}</p>
        <p><strong>Email :</strong> <a href="mailto:${r.email}" style="color:#4a2010;">${r.email}</a></p>
        <p><strong>Téléphone :</strong> ${r.telephone||'—'}</p>
      </div>
      <div style="background:#faf7f2; border-radius:10px; padding:18px; margin-bottom:18px;">
        <h4 style="font-family:'Playfair Display',serif; color:#1a0b06; margin-bottom:12px;">Détails</h4>
        ${details}
      </div>
      ${r.message ? `<div style="background:#faf7f2; border-radius:10px; padding:18px;"><h4 style="font-family:'Playfair Display',serif; color:#1a0b06; margin-bottom:8px;">Message</h4><p style="font-size:.9rem; color:#6b7a8a; line-height:1.7;">${r.message}</p></div>` : ''}
      <p style="font-size:.78rem; color:#6b7a8a; margin-top:14px;">Reçu le ${new Date(r.created_at).toLocaleString('fr-FR')}</p>
    `;
    document.getElementById('detailModal').classList.add('open');
  }

  function closeModal() { document.getElementById('detailModal').classList.remove('open'); }
  document.getElementById('detailModal').addEventListener('click', e => { if (e.target === e.currentTarget) closeModal(); });

  async function confirmFromModal() {
    if (!currentModalId) return;
    await updateStatus(currentModalId, 'confirme');
    closeModal();
    // Reload to reflect changes
    setTimeout(() => location.reload(), 800);
  }

  function exportCSV() {
    const rows = [['ID','Prénom','Nom','Email','Téléphone','Type','Statut','Arrivée','Départ','Chambre','Message','Date']];
    ALL_RESERVATIONS.forEach(r => {
      rows.push([r.id, r.prenom, r.nom, r.email, r.telephone||'', r.type, r.status,
        r.checkin||'', r.checkout||'', r.room_type||'', (r.message||'').replace(/,/g,' '), r.created_at]);
    });
    const csv = rows.map(r => r.map(v => `"${v}"`).join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csv);
    a.download = `reservations-bariba-playa-${Date.now()}.csv`;
    a.click();
  }
</script>
</body>
</html>
