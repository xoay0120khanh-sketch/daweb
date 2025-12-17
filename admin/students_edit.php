<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: students_add.php'); exit; }

$stmt = $mysqli->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
if (!$student) { echo "Không tìm thấy"; exit; }

$err = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email) {
        $err = 'Vui lòng nhập tên & email';
    } else {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("UPDATE students SET name=?, email=?, password=? WHERE id=?");
            $stmt->bind_param('sssi', $name, $email, $hash, $id);
        } else {
            $stmt = $mysqli->prepare("UPDATE students SET name=?, email=? WHERE id=?");
            $stmt->bind_param('ssi', $name, $email, $id);
        }
        if ($stmt->execute()) {
            $msg = 'Cập nhật thành công';
            // reload student data
            $stmt2 = $mysqli->prepare("SELECT * FROM students WHERE id = ?");
            $stmt2->bind_param('i', $id);
            $stmt2->execute();
            $student = $stmt2->get_result()->fetch_assoc();
        } else {
            $err = 'Lỗi: '.$mysqli->error;
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Student #<?= htmlspecialchars($student['id']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{ --muted:#6c757d; --accent:#0d6efd; }
    body {
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background: linear-gradient(180deg,#f7f9fb,#eef2f6);
      margin:0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      padding:24px;
    }
    .card-edit {
      width:100%;
      max-width:720px;
      border-radius:10px;
      box-shadow:0 10px 30px rgba(15,23,42,0.08);
      overflow:hidden;
    }
    .card-body { padding:22px; }
    .form-actions { display:flex; gap:10px; align-items:center; margin-top:12px; }
    .meta { color:var(--muted); font-size:13px; margin-top:6px; }
    .topbar {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:10px;
      margin-bottom:10px;
    }
    .title { font-weight:700; font-size:18px; margin:0; }
    .small-note { font-size:13px; color:var(--muted); }
    .input-group .btn { border-top-left-radius:0; border-bottom-left-radius:0; }
    @media (max-width:480px){
      .form-actions { flex-direction:column; align-items:stretch; }
    }
  </style>
</head>
<body>
  <div class="card card-edit">
    <div class="card-body">
      <div class="topbar">
        <div>
          <h2 class="title">Edit Student #<?= htmlspecialchars($student['id']) ?></h2>
          <div class="meta">Chỉnh sửa thông tin sinh viên</div>
        </div>
        <div class="text-end">
          <a href="students_add.php" class="btn btn-outline-secondary btn-sm">← Back</a>
        </div>
      </div>

      <?php if($err): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>
      <?php if($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label small">Họ & tên</label>
          <input name="name" class="form-control" required value="<?= htmlspecialchars($student['name']) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label small">Email</label>
          <input name="email" type="email" class="form-control" required value="<?= htmlspecialchars($student['email']) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label small">Mật khẩu mới (để trống nếu giữ nguyên)</label>
          <div class="input-group">
            <input id="pwd" name="password" type="password" class="form-control" placeholder="Mật khẩu mới (nếu muốn)">
            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()" aria-label="Show password">👁</button>
          </div>
          <div class="small-note">Để trống nếu không muốn thay đổi mật khẩu.</div>
        </div>

        <div class="form-actions">
          <button class="btn btn-primary">Lưu thay đổi</button>
          <a href="students_add.php" class="btn btn-outline-secondary">Hủy</a>
          <div class="ms-auto small-note">ID: <?= htmlspecialchars($student['id']) ?> · Tạo: <?= htmlspecialchars($student['created_at'] ?? '—') ?></div>
        </div>
      </form>
    </div>
  </div>

<script>
function togglePwd(){
  const e = document.getElementById('pwd');
  e.type = (e.type === 'password') ? 'text' : 'password';
}
</script>
</body>
</html>
