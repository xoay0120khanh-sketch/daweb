<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if (!$id) { header('Location: courses.php'); exit; }

$stmt = $mysqli->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
if (!$course) { echo "Not found"; exit; }

$err = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $credits = intval($_POST['credits'] ?? 0);

    if ($code === '' || $title === '') {
        $err = 'Vui lòng điền đầy đủ mã và tiêu đề môn học.';
    } else {
        $stmt = $mysqli->prepare("UPDATE courses SET code=?, title=?, credits=? WHERE id=?");
        $stmt->bind_param('ssii', $code, $title, $credits, $id);
        if ($stmt->execute()) {
            // redirect về danh sách để tránh resubmit và thông báo
            header('Location: courses.php?updated=1');
            exit;
        } else {
            $err = 'Lỗi: ' . $mysqli->error;
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Chỉnh sửa môn học #<?= htmlspecialchars($course['id']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height:100vh;
      margin:0;
      display:flex;
      align-items:center;
      justify-content:center;
      background: linear-gradient(180deg,#f7f9fb,#eef2f6);
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      padding:24px;
    }
    .card-edit {
      width:100%;
      max-width:900px;
      border-radius:12px;
      box-shadow:0 12px 36px rgba(12,24,48,0.08);
      overflow:hidden;
      background:#fff;
    }
    .card-body { padding:20px; }
    .top {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:8px;
    }
    .title { font-weight:700; font-size:18px; margin:0; }
    .meta { color:#6c757d; font-size:13px; }
    .form-row { gap:12px; display:flex; align-items:center; }
    .form-row > * { flex:1; }
    .form-row .col-code { flex:0 0 160px; max-width:160px; }
    .form-row .col-credits { flex:0 0 110px; max-width:110px; }
    @media (max-width:720px){
      .form-row { flex-direction:column; align-items:stretch; }
      .form-row .col-code, .form-row .col-credits { max-width:100%; flex:auto; }
    }
    .actions { display:flex; gap:10px; margin-top:14px; align-items:center; }
    .muted { color:#6c757d; font-size:13px; }
  </style>
</head>
<body>
  <div class="card card-edit">
    <div class="card-body">
      <div class="top">
        <div>
          <h2 class="title">Chỉnh sửa môn học #<?= htmlspecialchars($course['id']) ?></h2>
          <div class="meta">Cập nhật mã, tên và số tín chỉ</div>
        </div>
        <div>
          <a href="courses.php" class="btn btn-outline-secondary btn-sm">← Quay lại</a>
        </div>
      </div>

      <?php if ($err): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>
      <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <div class="form-row">
          <div class="col-code">
            <label class="form-label small">Mã môn</label>
            <input name="code" class="form-control" value="<?= htmlspecialchars($course['code']) ?>" required>
          </div>

          <div class="">
            <label class="form-label small">Tên môn</label>
            <input name="title" class="form-control" value="<?= htmlspecialchars($course['title']) ?>" required>
          </div>

          <div class="col-credits">
            <label class="form-label small">Tín chỉ</label>
            <input name="credits" type="number" min="0" class="form-control" value="<?= intval($course['credits']) ?>">
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-primary">Lưu thay đổi</button>
          <a href="courses.php" class="btn btn-outline-secondary">Hủy</a>
          <div class="ms-auto muted">ID: <?= htmlspecialchars($course['id']) ?> · Created: <?= htmlspecialchars($course['created_at'] ?? '—') ?></div>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
