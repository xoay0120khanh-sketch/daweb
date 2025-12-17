<?php
// student/register_action.php
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$name || !$email || !$password) {
    echo "Vui lòng điền đầy đủ. <a href='login.php'>Back</a>";
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare("INSERT INTO students (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $name, $email, $hash);
if ($stmt->execute()) {
    header('Location: login.php?registered=1');
    exit;
} else {
    echo "Lỗi: " . $mysqli->error . " <a href='login.php'>Back</a>";
}
