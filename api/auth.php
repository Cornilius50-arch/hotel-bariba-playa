<?php
// ── /api/auth.php ─────────────────────────────────────
// Handles login and logout for the admin panel.
// ──────────────────────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

session_start();

$action = $_GET['action'] ?? 'login';

// ── Logout ─────────────────────────────────────────────
if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ../admin/login.php');
    exit;
}

// ── Login ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$username = sanitize($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['success' => false, 'error' => 'Identifiants requis.']);
    exit;
}

try {
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Update last login
        $db->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?')
           ->execute([$user['id']]);

        $_SESSION['admin_id']   = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        $_SESSION['admin_role'] = $user['role'];

        echo json_encode(['success' => true, 'redirect' => 'dashboard.php']);
    } else {
        // Slow down brute force
        sleep(1);
        echo json_encode(['success' => false, 'error' => 'Identifiant ou mot de passe incorrect.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur.']);
    error_log('[Bariba Playa] Auth Error: ' . $e->getMessage());
}
