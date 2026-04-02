<?php
// ── /api/gallery.php ──────────────────────────────────
// Admin-only: upload / delete / reorder gallery photos.
// Public GET (no auth) returns the photo list for galerie.html
// ──────────────────────────────────────────────────────
require_once __DIR__ . '/../config/database.php';

header('Access-Control-Allow-Origin: *');
$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

// ── GET — public, returns all photos ──────────────────
if ($method === 'GET') {
    $category = $_GET['category'] ?? 'all';
    $where  = $category !== 'all' ? 'WHERE category = ?' : '';
    $params = $category !== 'all' ? [$category] : [];

    $stmt = $db->prepare("
        SELECT id, filename, title, category, sort_order, created_at
        FROM gallery_photos
        $where
        ORDER BY sort_order ASC, created_at DESC
    ");
    $stmt->execute($params);
    jsonResponse(['data' => $stmt->fetchAll()]);
}

// ── All other methods require admin auth ───────────────
requireAuth();

// ── POST — upload new photo ───────────────────────────
if ($method === 'POST') {
    if (empty($_FILES['photo'])) {
        jsonResponse(['error' => 'Aucun fichier reçu.'], 400);
    }

    $file     = $_FILES['photo'];
    $title    = sanitize($_POST['title']    ?? '');
    $category = $_POST['category'] ?? 'hotel';
    $validCats = ['hotel','chambres','restaurant','plage','services'];

    if (!in_array($category, $validCats)) {
        jsonResponse(['error' => 'Catégorie invalide.'], 400);
    }

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonResponse(['error' => 'Erreur lors de l\'upload.'], 400);
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        jsonResponse(['error' => 'Fichier trop volumineux (max 5 Mo).'], 400);
    }

    // Detect real MIME type
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_TYPES)) {
        jsonResponse(['error' => 'Type de fichier non autorisé (JPEG, PNG, WebP uniquement).'], 400);
    }

    // Generate unique filename
    $ext      = match($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg',
    };
    $filename = uniqid('photo_', true) . '.' . $ext;
    $dest     = UPLOAD_DIR . $filename;

    // Ensure upload directory exists
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        jsonResponse(['error' => 'Impossible de sauvegarder le fichier.'], 500);
    }

    // Get max sort_order
    $maxOrder = (int) $db->query('SELECT MAX(sort_order) FROM gallery_photos')->fetchColumn();

    $stmt = $db->prepare("
        INSERT INTO gallery_photos (filename, title, category, sort_order)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$filename, $title, $category, $maxOrder + 1]);

    jsonResponse([
        'success'  => true,
        'id'       => (int) $db->lastInsertId(),
        'filename' => $filename,
        'url'      => UPLOAD_URL . $filename,
    ]);
}

// ── PATCH — update title / category ───────────────────
if ($method === 'PATCH') {
    $raw  = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = intval($raw['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID manquant'], 400);

    if (isset($raw['title']))    $db->prepare('UPDATE gallery_photos SET title = ? WHERE id = ?')->execute([sanitize($raw['title']), $id]);
    if (isset($raw['category'])) $db->prepare('UPDATE gallery_photos SET category = ? WHERE id = ?')->execute([$raw['category'], $id]);
    if (isset($raw['sort_order'])) $db->prepare('UPDATE gallery_photos SET sort_order = ? WHERE id = ?')->execute([intval($raw['sort_order']), $id]);

    jsonResponse(['success' => true]);
}

// ── DELETE — remove photo ──────────────────────────────
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID manquant'], 400);

    $stmt = $db->prepare('SELECT filename FROM gallery_photos WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();

    if (!$row) jsonResponse(['error' => 'Photo introuvable'], 404);

    // Delete file from disk
    $filepath = UPLOAD_DIR . $row['filename'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    $db->prepare('DELETE FROM gallery_photos WHERE id = ?')->execute([$id]);
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Méthode non supportée'], 405);
