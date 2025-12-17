<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $stmt = $mysqli->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
}
header('Location: students_add.php');
exit;
