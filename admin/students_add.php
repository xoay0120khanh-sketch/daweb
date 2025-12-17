<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

/* ---------- xử lý POST: thêm sinh viên (và redirect để tránh resubmit) ---------- */
$err = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
        $err = 'Vui lòng điền đầy đủ';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO students (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $email, $hash);
        if ($stmt->execute()) {
            
            header('Location: students_add.php?page=1&added=1');
            exit;
        } else {
            $err = 'Lỗi: ' . $mysqli->error;
        }
    }
}

$perPage = 10; // số bản ghi mỗi trang 
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

// tổng số bản ghi
$resTotal = $mysqli->query("SELECT COUNT(*) AS total FROM students");
$total = ($resTotal && $row = $resTotal->fetch_assoc()) ? intval($row['total']) : 0;
$totalPages = ($total === 0) ? 1 : (int)ceil($total / $perPage);
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

// lấy dssv
$stmt = $mysqli->prepare("SELECT id, name, email FROM students ORDER BY id ASC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $perPage, $offset);
$stmt->execute();

$res = $stmt->get_result();
$students = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// ktra add
$added = isset($_GET['added']) ? true : false;
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Quản lý Sinh viên</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --muted:#6c757d;
      --card-bg: #fff;
      --accent: #0d6efd;
    }
    body {
      background: #f5f7fb;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      margin:0;
      padding:24px;
    }
    .wrap {
      max-width:1200px;
      margin:0 auto;
    }
    .top-row {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:18px;
    }
    .page-title { font-weight:700; font-size:20px; }
    .sub { color:var(--muted); font-size:13px; }

    .card-form {
      border-radius:10px;
      box-shadow:0 6px 18px rgba(15,23,42,0.06);
    }
    .card-list {
      border-radius:10px;
      box-shadow:0 6px 18px rgba(15,23,42,0.06);
    }

    /* responsive two-column layout */
    .grid {
      display:grid;
      grid-template-columns: 360px 1fr;
      gap:18px;
    }
    @media (max-width: 900px){
      .grid { grid-template-columns: 1fr; }
    }

    .small-meta { font-size:13px; color:var(--muted); }
    .table td, .table th { vertical-align: middle; }
    .actions .btn { margin-right:6px; }

    .no-data { color:var(--muted); text-align:center; padding:20px; }

    /* compact controls */
    .controls { display:flex; gap:8px; align-items:center; }
    .controls .btn { white-space:nowrap; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top-row">
      <div>
        <div class="page-title">Quản lý Sinh viên</div>
        <div class="sub">Thêm, chỉnh sửa và xoá sinh viên</div>
      </div>
      <div class="controls">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Về Dashboard</a>
        <a href="students_add.php" class="btn btn-primary btn-sm">⟳ Làm mới</a>
      </div>
    </div>

    <?php if($err): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <?php if($added): ?>
      <div class="alert alert-success">Thêm sinh viên thành công.</div>
    <?php endif; ?>

    <div class="grid">
      <!-- FORM -->
      <div class="card card-form p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="mb-0">Thêm sinh viên</h5>
          <div class="small-meta">Tạo tài khoản mới cho sinh viên</div>
        </div>

        <form method="post" class="mt-2">
          <div class="mb-3">
            <label class="form-label small">Họ & tên</label>
            <input name="name" class="form-control" placeholder="Nguyễn Văn A" required>
          </div>

          <div class="mb-3">
            <label class="form-label small">Email</label>
            <input name="email" type="email" class="form-control" placeholder="abc@domain.com" required>
          </div>

          <div class="mb-3">
            <label class="form-label small">Mật khẩu</label>

            <div class="input-group">
                <input id="passwordInput" name="password" type="password" class="form-control" placeholder="Mật khẩu tạm thời" required>
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">👁</button>
            </div>

            <div class="form-text small">Gợi ý: đặt mật khẩu tạm thời để sinh viên đổi sau.</div>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-success">Thêm</button>
            <button type="reset" class="btn btn-outline-secondary">Xóa thông tin</button>
          </div>
        </form>

        <div class="mt-3 small-meta">
          <strong>Lưu ý:</strong> Email phải là duy nhất. 
        </div>
      </div>

      <!-- LIST -->
      <div class="card card-list p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="mb-0">Danh sách sinh viên</h5>
          <div class="small-meta"><?= $total ?> kết quả</div>
        </div>

        <?php if($total === 0): ?>
          <div class="no-data">Chưa có sinh viên nào.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width:64px">#</th>
                  <th>Tên</th>
                  <th>Email</th>
                  <th style="width:170px" class="text-end">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($students as $r): ?>
                  <tr>
                    <td><?= intval($r['id']) ?></td>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td class="text-end actions">
                      <a class="btn btn-sm btn-outline-warning" href="students_edit.php?id=<?= intval($r['id']) ?>">Edit</a>
                      <a class="btn btn-sm btn-outline-danger" href="students_delete.php?id=<?= intval($r['id']) ?>&page=<?= $page ?>" onclick="return confirm('Xác nhận xoá sinh viên này?')">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- pagination -->
          <div class="mt-3 d-flex justify-content-between align-items-center">
            <div class="small-meta">Hiển thị trang <?= $page ?> / <?= $totalPages ?> — <?= $perPage ?> Sinh viên/trang</div>
            <nav>
              <ul class="pagination mb-0">
                <!-- previous -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=1">««</a>
                </li>
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= max(1, $page-1) ?>">«</a>
                </li>

                <?php
                // show window of pages around current
                $window = 3;
                $start = max(1, $page - $window);
                $end = min($totalPages, $page + $window);
                for ($p = $start; $p <= $end; $p++): ?>
                  <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                  </li>
                <?php endfor; ?>

                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= min($totalPages, $page+1) ?>">»</a>
                </li>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?page=<?= $totalPages ?>">»»</a>
                </li>
              </ul>
            </nav>
          </div>

        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
  function togglePassword() {
      const input = document.getElementById("passwordInput");
      input.type = input.type === "password" ? "text" : "password";
  }
  </script>
</body>
</html>
