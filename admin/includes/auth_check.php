<?php
session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: ' . (defined('ADMIN_ROOT') ? ADMIN_ROOT : '') . 'login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
