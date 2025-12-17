<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

/* ---------- Handle actions (POST) ---------- */
$err = $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['rid'])) {
    $action = $_POST['action'];
    $rid = intval($_POST['rid']);
    if ($action === 'approve') {
        $stmt = $mysqli->prepare("UPDATE registrations SET status = 1 WHERE id = ?");
        $stmt->bind_param('i', $rid);
        $ok = $stmt->execute();
        if ($ok) $msg = "Đã duyệt đăng ký #{$rid}.";
        else $err = "Lỗi khi duyệt: " . $mysqli->error;
    } elseif ($action === 'reject') {
        $stmt = $mysqli->prepare("UPDATE registrations SET status = 2 WHERE id = ?");
        $stmt->bind_param('i', $rid);
        $ok = $stmt->execute();
        if ($ok) $msg = "Đã từ chối đăng ký #{$rid}.";
        else $err = "Lỗi khi từ chối: " . $mysqli->error;
    }
    // redirect to avoid form resubmission and keep query params
    $qs = $_GET ? ('?' . http_build_query($_GET)) : '';
    header("Location: registrations.php{$qs}");
    exit;
}

/* ---------- Filters/Search/Pagination ---------- */
$perPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$statusFilter = isset($_GET['status']) ? intval($_GET['status']) : -1; // -1 = all, 0 pending,1 aproved,2 rejected
$q = trim($_GET['q'] ?? '');

$where = [];
$params = [];
$types = '';

// status filter
if ($statusFilter >= 0 && in_array($statusFilter, [0,1,2], true)) {
    $where[] = "r.status = ?";
    $types .= 'i';
    $params[] = $statusFilter;
}

// search by student name or course code/title
if ($q !== '') {
    $where[] = "(s.name LIKE ? OR c.code LIKE ? OR c.title LIKE ?)";
    $types .= 'sss';
    $like = "%{$q}%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// count total
$count_sql = "
    SELECT COUNT(*) as total
    FROM registrations r
    JOIN students s ON r.student_id = s.id
    JOIN courses c ON r.course_id = c.id
    {$where_sql}
";
$countStmt = $mysqli->prepare($count_sql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRow = $countStmt->get_result()->fetch_assoc();
$total = intval($totalRow['total'] ?? 0);
$totalPages = $total === 0 ? 1 : (int)ceil($total / $perPage);
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

/* ---------- fetch paginated registrations ---------- */
$sql = "
    SELECT r.*, s.name as student_name, s.email as student_email, c.title as course_title, c.code as course_code
    FROM registrations r
    JOIN students s ON r.student_id = s.id
    JOIN courses c ON r.course_id = c.id
    {$where_sql}
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $mysqli->prepare($sql);
if ($types) {
    // bind dynamic params + limit/offset
    // build types string
    $bind_types = $types . 'ii';
    $bind_params = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bind_types, ...$bind_params);
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$res = $stmt->get_result();

/* helper to keep query params in links */
function keep_qs($more = []) {
    $qs = $_GET;
    foreach ($more as $k => $v) $qs[$k] = $v;
    return $qs ? ('?' . http_build_query($qs)) : '';
}

/* status label */
function status_badge($s) {
    if ($s == 0) return '<span class="badge bg-secondary">Chờ duyệt</span>';
    if ($s == 1) return '<span class="badge bg-success">Đã duyệt</span>';
    return '<span class="badge bg-danger">Từ chối</span>';
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Quản lý Đăng ký</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f6f8fb; font-family:system-ui, -apple-system, "Segoe UI", Roboto; margin:0; padding:20px; }
    .wrap { max-width:1100px; margin:0 auto; }
    .card { border-radius:10px; box-shadow:0 6px 18px rgba(15,23,42,0.06); }
    .filters { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .muted { color:#6c757d; }
    .table td, .table th { vertical-align: middle; }
    .small-note { font-size:13px; color:#6c757d; }
    .actions .btn { margin-right:6px; }
    .empty { text-align:center; padding:30px; color:#6c757d; }
    .search-input { min-width:220px; }
    .topbar { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <div>
        <h4 class="mb-0">Đăng ký môn học</h4>
        <div class="muted small">Duyệt hoặc từ chối các đăng ký sinh viên</div>
      </div>
      <div class="d-flex gap-2">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
        <a href="registrations.php" class="btn btn-sm btn-primary">⟳ Làm mới</a>
      </div>
    </div>

    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="card p-3 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="filters">
          <form method="get" class="d-flex align-items-center" style="gap:8px;">
            <input class="form-control form-control-sm search-input" name="q" placeholder="Tìm tên sinh viên / mã môn / tên môn" value="<?= htmlspecialchars($q) ?>">
            <select name="status" class="form-select form-select-sm" style="width:auto;">
              <option value="-1" <?= $statusFilter === -1 ? 'selected' : '' ?>>Tất cả</option>
              <option value="0" <?= $statusFilter === 0 ? 'selected' : '' ?>>Chờ duyệt</option>
              <option value="1" <?= $statusFilter === 1 ? 'selected' : '' ?>>Đã duyệt</option>
              <option value="2" <?= $statusFilter === 2 ? 'selected' : '' ?>>Từ chối</option>
            </select>
            <button class="btn btn-sm btn-outline-primary">Tìm</button>
          </form>

          <div class="ms-3 small-note">Tổng: <?= $total ?> kết quả</div>
        </div>

        <div class="small-note">Hiển thị trang <?= $page ?> / <?= $totalPages ?> — <?= $perPage ?> bản/trang</div>
      </div>

      <?php if ($total === 0): ?>
        <div class="empty">Không có đăng ký nào.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:60px">#</th>
                <th>Student</th>
                <th>Course</th>
                <th style="width:120px">Ngày đăng ký</th>
                <th style="width:120px">Status</th>
                <th style="width:210px" class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($r = $res->fetch_assoc()): ?>
                <tr>
                  <td><?= intval($r['id']) ?></td>
                  <td>
                    <div><?= htmlspecialchars($r['student_name']) ?></div>
                    <div class="muted small"><?= htmlspecialchars($r['student_email']) ?></div>
                  </td>
                  <td>
                    <div><?= htmlspecialchars($r['course_code'] . ' — ' . $r['course_title']) ?></div>
                  </td>
                  <td class="muted small"><?= htmlspecialchars($r['created_at']) ?></td>
                  <td><?= status_badge($r['status']) ?></td>
                  <td class="text-end actions">
                    <?php if ($r['status'] == 0): ?>
                      <form method="post" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn duyệt đăng ký #<?= $r['id'] ?>?')">
                        <input type="hidden" name="rid" value="<?= $r['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button class="btn btn-sm btn-success">Chấp nhận</button>
                      </form>
                      <form method="post" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn từ chối đăng ký #<?= $r['id'] ?>?')">
                        <input type="hidden" name="rid" value="<?= $r['id'] ?>">
                        <input type="hidden" name="action" value="reject">
                        <button class="btn btn-sm btn-danger">Từ chối</button>
                      </form>
                    <?php else: ?>
                      <span class="muted small">Không có</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <!-- pagination (simple) -->
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="muted small">Hiển thị trang <?= $page ?> / <?= $totalPages ?> — <?= $perPage ?> bản/trang</div>

          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= keep_qs(['page'=>1]) ?>">««</a>
              </li>
              <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= keep_qs(['page'=>max(1,$page-1)]) ?>">«</a>
              </li>

              <?php
              $window = 2;
              $start = max(1, $page - $window);
              $end = min($totalPages, $page + $window);
              if ($start > 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
              for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                  <a class="page-link" href="<?= keep_qs(['page'=>$p]) ?>"><?= $p ?></a>
                </li>
              <?php endfor;
              if ($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
              ?>

              <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= keep_qs(['page'=>min($totalPages,$page+1)]) ?>">»</a>
              </li>
              <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= keep_qs(['page'=>$totalPages]) ?>">»»</a>
              </li>
            </ul>
          </nav>
        </div>

      <?php endif; ?>
    </div>
  </div>
</body>
</html>
