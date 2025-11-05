<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !in_array($action, ['enable', 'disable'])) {
    die("Invalid request");
}

$is_locked = ($action === 'disable') ? 1 : 0;

$stmt = $pdo->prepare("UPDATE users SET is_locked = ? WHERE id = ?");
$stmt->execute([$is_locked, $id]);

header("Location: admin_dashboard.php");
exit;
