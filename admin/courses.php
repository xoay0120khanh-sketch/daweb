<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

/* ---------- Handle add course (POST -> redirect to avoid resubmit) ---------- */
$err = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $credits = intval($_POST['credits'] ?? 0);
    if (!$code || !$title) {
        $err = 'Vui lòng điền đầy đủ mã và tiêu đề môn học.';
    } else {
        $stmt = $mysqli->prepare("INSERT INTO courses (code, title, credits) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $code, $title, $credits);
        if ($stmt->execute()) {
            header('Location: courses.php?added=1');
            exit;
        } else {
            $err = 'Lỗi khi thêm môn: ' . $mysqli->error;
        }
    }
}

/* ---------- Search & pagination params ---------- */
$allowedPerPage = [5,10,20,50];
$defaultPerPage = 10;

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = intval($_GET['per'] ?? $defaultPerPage);
if (!in_array($perPage, $allowedPerPage)) $perPage = $defaultPerPage;

$q = trim($_GET['q'] ?? '');
$q_param = "%{$q}%";

/* ---------- Total count ---------- */
if ($q !== '') {
    $countStmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM courses WHERE code LIKE ? OR title LIKE ?");
    $countStmt->bind_param('ss', $q_param, $q_param);
    $countStmt->execute();
    $resTotal = $countStmt->get_result()->fetch_assoc();
    $total = intval($resTotal['total'] ?? 0);
} else {
    $resTotal = $mysqli->query("SELECT COUNT(*) AS total FROM courses");
    $total = intval($resTotal->fetch_assoc()['total'] ?? 0);
}

$totalPages = ($total === 0) ? 1 : (int)ceil($total / $perPage);
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/* ---------- Fetch page data (secure prepared statements) ---------- */
if ($q !== '') {
    $stmt = $mysqli->prepare("SELECT * FROM courses WHERE code LIKE ? OR title LIKE ? ORDER BY id ASC LIMIT ? OFFSET ?");
    $stmt->bind_param('ssii', $q_param, $q_param, $perPage, $offset);
    $stmt->execute();
    $courses = $stmt->get_result();
} else {
    $stmt = $mysqli->prepare("SELECT * FROM courses ORDER BY id ASC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $courses = $stmt->get_result();
}

$added = isset($_GET['added']);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Quản lý Môn học</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f5f7fb; font-family:system-ui, -apple-system, "Segoe UI", Roboto; margin:0; padding:20px; }
    .wrap { max-width:1200px; margin:0 auto; }
    .grid { display:grid; grid-template-columns: 360px 1fr; gap:18px; }
    @media (max-width:900px){ .grid{ grid-template-columns:1fr } }
    .card { border-radius:10px; box-shadow:0 6px 18px rgba(15,23,42,0.06); }
    .muted { color:#6c757d; font-size:13px; }
    .table td, .table th { vertical-align: middle; }
    .pagination .page-link { min-width:40px; text-align:center; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-0">Quản lý môn học</h4>
        <div class="muted">Thêm / sửa / xóa môn học</div>
      </div>
      <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
        <a href="courses.php" class="btn btn-primary btn-sm">⟳ Làm mới</a>
      </div>
    </div>

    <?php if($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if($added): ?><div class="alert alert-success">Thêm môn thành công.</div><?php endif; ?>

    <div class="grid">
      <!-- FORM -->
      <div class="card p-3">
        <h5 class="mb-3">Thêm môn học</h5>
        <form method="post" class="mb-2">
          <div class="mb-2">
            <label class="form-label small">Mã môn</label>
            <input name="code" class="form-control" placeholder="VD: IT101" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">Tên môn</label>
            <input name="title" class="form-control" placeholder="Nhập tên môn" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">Số tín chỉ</label>
            <input name="credits" type="number" class="form-control" value="3" min="0">
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-success">Thêm</button>
            <button type="reset" class="btn btn-outline-secondary">Xóa</button>
          </div>
        </form>

        <hr class="my-3">
        <div class="muted small">Gợi ý: dùng mã ngắn gọn, dễ nhớ. </div>
      </div>

      <!-- LIST -->
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <strong>Danh sách môn</strong>
            <div class="muted small">Tổng: <?= $total ?> kết quả</div>
          </div>

          <form method="get" class="d-flex align-items-center" role="search">
            <input name="q" value="<?= htmlspecialchars($q) ?>" class="form-control form-control-sm me-2" placeholder="Tìm mã hoặc tên..." />
            <select name="per" class="form-select form-select-sm me-2" style="width:auto;">
                <?php foreach($allowedPerPage as $opt): ?>
                  <option value="<?= $opt ?>" <?= $opt === $perPage ? 'selected' : '' ?>><?= $opt ?> /trg</option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-outline-primary btn-sm">Tìm</button>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:70px">#</th>
                <th style="width:120px">Mã</th>
                <th>Tên môn</th>
                <th style="width:90px">TC</th>
                <th style="width:160px" class="text-end">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($courses && $courses->num_rows): ?>
                <?php while($c = $courses->fetch_assoc()): ?>
                  <tr>
                    <td><?= intval($c['id']) ?></td>
                    <td><?= htmlspecialchars($c['code']) ?></td>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td><?= intval($c['credits']) ?></td>
                    <td class="text-end">
                      <a href="courses_edit.php?id=<?= intval($c['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                      <a href="courses_delete.php?id=<?= intval($c['id']) ?>&page=<?= $page ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Xác nhận xoá môn này?')">Delete</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center muted">Không có môn học.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- pagination -->
        <?php if ($totalPages > 1): ?>
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="muted small">Hiển thị trang <?= $page ?> / <?= $totalPages ?> — <?= $perPage ?> kết quả/trang</div>
            <nav>
              <ul class="pagination pagination-sm mb-0">

                <!-- First / Prev -->
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= $q ? 'q='.urlencode($q).'&' : '' ?>per=<?= $perPage ?>&page=1">««</a>
                </li>
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= $q ? 'q='.urlencode($q).'&' : '' ?>per=<?= $perPage ?>&page=<?= max(1,$page-1) ?>">«</a>
                </li>

                <!-- page window -->
                <?php
                $window = 3;
                $start = max(1, $page - $window);
                $end = min($totalPages, $page + $window);
                if ($start > 1): ?>
                  <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif;
                for ($p = $start; $p <= $end; $p++): ?>
                  <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= $q ? 'q='.urlencode($q).'&' : '' ?>per=<?= $perPage ?>&page=<?= $p ?>"><?= $p ?></a>
                  </li>
                <?php endfor;
                if ($end < $totalPages): ?>
                  <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>

                <!-- Next / Last -->
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= $q ? 'q='.urlencode($q).'&' : '' ?>per=<?= $perPage ?>&page=<?= min($totalPages,$page+1) ?>">»</a>
                </li>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                  <a class="page-link" href="?<?= $q ? 'q='.urlencode($q).'&' : '' ?>per=<?= $perPage ?>&page=<?= $totalPages ?>">»»</a>
                </li>
              </ul>
            </nav>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</body>
</html>
