<?php
// ── /api/reservations.php ─────────────────────────────
// Admin-only CRUD for reservations.
// ──────────────────────────────────────────────────────
require_once __DIR__ . '/../config/database.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

// ── GET — list all reservations ───────────────────────
if ($method === 'GET') {
    $status = $_GET['status'] ?? 'all';
    $search = trim($_GET['search'] ?? '');
    $limit  = min(intval($_GET['limit']  ?? 50), 200);
    $offset = max(intval($_GET['offset'] ?? 0),  0);

    $where  = [];
    $params = [];

    if ($status !== 'all') {
        $where[]  = 'status = ?';
        $params[] = $status;
    }
    if ($search) {
        $where[]  = '(prenom LIKE ? OR nom LIKE ? OR email LIKE ? OR telephone LIKE ?)';
        $like = "%$search%";
        array_push($params, $like, $like, $like, $like);
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $rows = $db->prepare("
        SELECT * FROM reservations $whereSQL
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $rows->execute($params);

    $countStmt = $db->prepare("SELECT COUNT(*) FROM reservations $whereSQL");
    $countStmt->execute($params);

    jsonResponse([
        'data'  => $rows->fetchAll(),
        'total' => (int) $countStmt->fetchColumn(),
    ]);
}

// ── PATCH — update status or add note ─────────────────
if ($method === 'PATCH' || $method === 'POST') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $id   = intval($data['id'] ?? $_GET['id'] ?? 0);

    if (!$id) jsonResponse(['error' => 'ID manquant'], 400);

    $allowed_statuses = ['nouveau','lu','confirme','annule'];

    if (isset($data['status'])) {
        if (!in_array($data['status'], $allowed_statuses)) {
            jsonResponse(['error' => 'Statut invalide'], 400);
        }
        $db->prepare('UPDATE reservations SET status = ? WHERE id = ?')
           ->execute([$data['status'], $id]);
    }

    if (isset($data['notes'])) {
        $db->prepare('UPDATE reservations SET notes = ? WHERE id = ?')
           ->execute([sanitize($data['notes']), $id]);
    }

    jsonResponse(['success' => true]);
}

// ── DELETE ────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID manquant'], 400);
    $db->prepare('DELETE FROM reservations WHERE id = ?')->execute([$id]);
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Méthode non supportée'], 405);
