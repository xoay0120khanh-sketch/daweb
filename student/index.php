<?php
session_start();
require_once __DIR__ . '/../config/db.php';
//ktra
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
$student_id = $_SESSION['student_id'];

// đki môn 
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);

    $chk = $mysqli->prepare("SELECT id, status FROM registrations WHERE student_id = ? AND course_id = ?");
    $chk->bind_param('ii', $student_id, $course_id);
    $chk->execute();
    $resChk = $chk->get_result()->fetch_assoc();
    if ($resChk) {
        $msg = "Bạn đã đăng ký môn này rồi. Trạng thái: " . ($resChk['status']==0 ? 'Chờ duyệt' : ($resChk['status']==1 ? 'Đã duyệt' : 'Từ chối'));
    } else {
        $ins = $mysqli->prepare("INSERT INTO registrations (student_id, course_id, status, created_at) VALUES (?, ?, 0, NOW())");
        $ins->bind_param('ii', $student_id, $course_id);
        if ($ins->execute()) $msg = "Đăng ký thành công — chờ admin duyệt.";
        else $err = "Lỗi khi đăng ký: " . $mysqli->error;
    }
}


$allowedPer = [5,10,20,50]; //option
$defaultPer = 10; //mặc định
$per = isset($_GET['per']) ? intval($_GET['per']) : $defaultPer;
if (!in_array($per, $allowedPer)) $per = $defaultPer;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;


$resTotal = $mysqli->query("SELECT COUNT(*) AS total FROM courses");
$total = ($resTotal && $row = $resTotal->fetch_assoc()) ? intval($row['total']) : 0;
$totalPages = $total === 0 ? 1 : (int)ceil($total / $per);
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $per;


$stmt = $mysqli->prepare("SELECT id, code, title, credits FROM courses ORDER BY id ASC LIMIT ? OFFSET ?");
$stmt->bind_param('ii', $per, $offset);
$stmt->execute();
$coursesRes = $stmt->get_result();
$courses = $coursesRes ? $coursesRes->fetch_all(MYSQLI_ASSOC) : [];



//môn đã đki 
$regsStmt = $mysqli->prepare("SELECT r.id, r.course_id, r.status, r.created_at, c.code, c.title FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.student_id = ?");
$regsStmt->bind_param('i', $student_id);
$regsStmt->execute();
$regsRes = $regsStmt->get_result();
$regs = [];
while ($row = $regsRes->fetch_assoc()) {
    $regs[intval($row['course_id'])] = $row;
}

/* helpers */
function status_badge_label($s) {
    if ($s == 0) return '<span class="badge bg-secondary">Chờ duyệt</span>';
    if ($s == 1) return '<span class="badge bg-success">Đã duyệt</span>';
    return '<span class="badge bg-danger">Từ chối</span>';
}
function qp($params = []) {
    $qs = $_GET;
    foreach ($params as $k => $v) $qs[$k] = $v;
    return http_build_query($qs);
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Student Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{ --muted:#6c757d; --accent:#0d6efd; }
    body {
      min-height:100vh;
      margin:0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: linear-gradient(180deg,#f5f7fb,#eef2f6);
    }
    .container-sm { max-width:1100px; }
    .topbar {
      background: #fff;
      border-bottom: 1px solid rgba(0,0,0,0.05);
      box-shadow: 0 2px 6px rgba(10,20,40,0.03);
    }
    .dashboard { padding: 26px 16px; }
    .card { border-radius:10px; box-shadow:0 8px 24px rgba(12,24,48,0.06); }
    .muted { color:var(--muted); font-size:13px; }
    .table thead th { font-weight:600; }
    .action-btn { min-width:110px; }
    .badge-ghost { background: #f8f9fa; color: #6c757d; border: 1px solid rgba(0,0,0,0.04); }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="container container-sm d-flex align-items-center justify-content-between py-2">
      <div>
        <strong style="color:var(--accent)">Hệ thống quản lý môn học</strong>
        <div class="muted">Xin chào, <?= htmlspecialchars($_SESSION['student_name'] ?? 'Sinh viên') ?></div>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
      </div>
    </div>
  </header>

  <main class="dashboard">
    <div class="container container-sm">
      <?php if ($err): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
      <?php elseif ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-12 col-md-7">
          <div class="card p-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <h5 class="mb-0">Các môn học có sẵn</h5>
                <div class="muted">Chọn môn và nhấn <strong>Register</strong> để đăng ký</div>
              </div>

              <div class="d-flex align-items-center gap-2">
                <div class="muted small">Tổng: <?= $total ?> môn</div>
                <form method="get" class="d-flex align-items-center" style="gap:8px;">
                  <label class="muted small mb-0">Hiển thị:</label>
                  <select name="per" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ([5,10,20,50] as $opt): ?>
                      <option value="<?= $opt ?>" <?= $opt == $per ? 'selected' : '' ?>><?= $opt ?> /trg</option>
                    <?php endforeach; ?>
                  </select>
                  <input type="hidden" name="page" value="<?= $page ?>">
                </form>
              </div>
            </div>

            <?php if (empty($courses)): ?>
              <div class="p-4 muted text-center">Hiện không có môn học nào trong hệ thống.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th style="width:110px">Mã</th>
                      <th>Tên môn</th>
                      <th style="width:100px">TC</th>
                      <th style="width:170px" class="text-end">Hành động</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($courses as $c):
                      $cid = intval($c['id']);
                      $registered = isset($regs[$cid]);
                      $reg = $registered ? $regs[$cid] : null;
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($c['code']) ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><?= intval($c['credits']) ?></td>
                        <td class="text-end">
                          <?php if ($registered): ?>
                            <span class="me-2"><?= $reg ? status_badge_label(intval($reg['status'])) : '' ?></span>
                            <button class="btn btn-sm btn-outline-secondary action-btn" disabled>
                              <?= intval($reg['status'])===0 ? 'Pending' : (intval($reg['status'])===1 ? 'Enrolled' : 'Rejected') ?>
                            </button>
                          <?php else: ?>
                            <form method="post" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn đăng ký môn này?')">
                              <input type="hidden" name="course_id" value="<?= $cid ?>">
                              <button class="btn btn-sm btn-primary action-btn">Đăng Ký</button>
                            </form>
                          <?php endif; ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- pagination -->
              <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="muted small">Hiển thị trang <?= $page ?> / <?= $totalPages ?> — <?= $per ?> môn/trang</div>

                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                      <a class="page-link" href="?<?= qp(['page'=>1,'per'=>$per]) ?>">««</a>
                    </li>
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                      <a class="page-link" href="?<?= qp(['page'=>max(1,$page-1),'per'=>$per]) ?>">«</a>
                    </li>

                    <?php
                    $window = 2;
                    $start = max(1, $page - $window);
                    $end = min($totalPages, $page + $window);
                    if ($start > 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    for ($p = $start; $p <= $end; $p++): ?>
                      <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= qp(['page'=>$p,'per'=>$per]) ?>"><?= $p ?></a>
                      </li>
                    <?php endfor;
                    if ($end < $totalPages) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    ?>

                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                      <a class="page-link" href="?<?= qp(['page'=>min($totalPages,$page+1),'per'=>$per]) ?>">»</a>
                    </li>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                      <a class="page-link" href="?<?= qp(['page'=>$totalPages,'per'=>$per]) ?>">»»</a>
                    </li>
                  </ul>
                </nav>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12 col-md-5">
          <div class="card p-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div>
                <h5 class="mb-0">Đăng ký của bạn</h5>
                <div class="muted">Trạng thái đăng ký các môn</div>
              </div>
              <div class="muted small"><?= count($regs) ?> đăng ký</div>
            </div>

            <?php if (empty($regs)): ?>
              <div class="p-4 muted text-center">Bạn chưa đăng ký môn nào.</div>
            <?php else: ?>
              <div class="list-group">
                <?php foreach ($regs as $r): ?>
                  <div class="list-group-item d-flex align-items-center justify-content-between">
                    <div>
                      <div><strong><?= htmlspecialchars($r['code']) ?></strong> — <?= htmlspecialchars($r['title']) ?></div>
                      <div class="muted small">Ngày: <?= htmlspecialchars($r['created_at']) ?></div>
                    </div>
                    <div class="text-end">
                      <div class="mb-2"><?= status_badge_label(intval($r['status'])) ?></div>
                      <?php if (intval($r['status']) === 2): ?>
                        <div class="muted small">Bạn có thể thử đăng ký lại.</div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
