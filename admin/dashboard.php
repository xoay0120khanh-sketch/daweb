<?php
session_start();
require_once __DIR__ . '/../config/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Count stats
$stats = [];
$res = $mysqli->query("SELECT COUNT(*) as c FROM students");
$stats['students'] = $res->fetch_assoc()['c'];
$res = $mysqli->query("SELECT COUNT(*) as c FROM courses");
$stats['courses'] = $res->fetch_assoc()['c'];
$res = $mysqli->query("SELECT COUNT(*) as c FROM registrations");
$stats['registrations'] = $res->fetch_assoc()['c'];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --card-bg: #ffffff;
      --muted: #6c757d;
      --accent: #0d6efd;
      --surface: #f6f7f9;
    }
    body {
      background: linear-gradient(180deg, #f8fafc 0%, #eef2f6 100%);
      min-height: 100vh;
      margin: 0;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      color: #111827;
    }
    .topbar {
      background: #fff;
      border-bottom: 1px solid rgba(0,0,0,0.06);
      box-shadow: 0 2px 6px rgba(16,24,40,0.03);
    }
    .container-sm {
      max-width: 1100px;
    }
    .dashboard-wrap {
      padding: 28px 16px;
    }
    .header-row {
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:18px;
    }
    .header-title h1 {
      font-size:20px;
      margin:0;
      font-weight:600;
    }
    .header-sub { color:var(--muted); font-size:13px; margin-top:4px; }

    /* Cards grid */
    .grid {
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
    }
    @media (max-width: 900px) { .grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }

    .stat-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 6px 18px rgba(13, 27, 62, 0.06);
      display:flex;
      align-items:center;
      gap:14px;
      border: 1px solid rgba(13,27,62,0.04);
    }
    .stat-icon {
      width:58px; height:58px;
      border-radius:10px;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:20px;
      color:#fff;
    }
    .icon-students { background: linear-gradient(135deg,#06b6d4,#0ea5a7); }
    .icon-courses  { background: linear-gradient(135deg,#7c3aed,#6d28d9); }
    .icon-reg      { background: linear-gradient(135deg,#f97316,#fb923c); }

    .stat-body { flex:1; min-width:0; }
    .stat-label { font-size:13px; color:var(--muted); margin:0 0 6px 0; }
    .stat-value { font-size:28px; font-weight:700; margin:0; }

    .stat-cta { display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }
    .stat-cta a { font-size:13px; }

    /* compact table preview */
    .mini-table {
      margin-top: 18px;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid rgba(0,0,0,0.04);
    }
    .mini-table thead { background: #fbfbfd; }
    .mini-table td, .mini-table th { padding:10px 12px; font-size:13px; vertical-align:middle; }

  </style>
</head>
<body>
  <!-- top nav -->
  <header class="topbar">
    <div class="container container-sm d-flex align-items-center justify-content-between py-2">
      <div class="d-flex align-items-center gap-3">
        <a href="../index.php" class="text-decoration-none"><strong style="color:var(--accent);">STU</strong></a>
        <nav class="d-none d-md-block" aria-label="breadcrumb">
          <ol class="breadcrumb mb-0" style="background:transparent; --bs-breadcrumb-divider: '›';">
            <li class="breadcrumb-item"><a href="#" class="text-muted text-decoration-none">Admin</a></li>
            <li class="breadcrumb-item active text-muted" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="text-end me-2" style="font-size:13px;">
          <div style="font-weight:600;"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
          <div style="color:var(--muted); font-size:12px;">Giảng viên</div>
        </div>
        <a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>
      </div>
    </div>
  </header>

  <!-- main -->
  <main class="dashboard-wrap d-flex justify-content-center">
    <div class="container container-sm">

      <div class="header-row">
        <div class="header-title">
          <h1>Hệ thống quản lý</h1>
          
        </div>

        <div class="d-flex gap-2">
          <a href="./students_add.php" class="btn btn-sm btn-primary">+ Thêm sinh viên</a>
          <a href="./courses.php" class="btn btn-sm btn-outline-primary">Quản lý môn</a>
        </div>
      </div>

      <div class="grid">
        <!-- Students -->
        <div class="stat-card">
          <div class="stat-icon icon-students" aria-hidden="true">👩‍🎓</div>
          <div class="stat-body">
            <div class="stat-label">Sinh viên</div>
            <div class="stat-value"><?= intval($stats['students']) ?></div>
            <div class="stat-cta">
              <a href="./students_add.php" class="btn btn-sm btn-link">Quản lý</a>
              <a href="./students_add.php" class="btn btn-sm btn-link text-muted">Xuất CSV</a>
            </div>
          </div>
        </div>

        <!-- Courses -->
        <div class="stat-card">
          <div class="stat-icon icon-courses" aria-hidden="true">📚</div>
          <div class="stat-body">
            <div class="stat-label">Môn học</div>
            <div class="stat-value"><?= intval($stats['courses']) ?></div>
            <div class="stat-cta">
              <a href="./courses.php" class="btn btn-sm btn-link">Quản lý</a>
              <a href="./courses.php" class="btn btn-sm btn-link text-muted">Thêm nhanh</a>
            </div>
          </div>
        </div>

        <!-- Registrations -->
        <div class="stat-card">
          <div class="stat-icon icon-reg" aria-hidden="true">📝</div>
          <div class="stat-body">
            <div class="stat-label">Đăng ký</div>
            <div class="stat-value"><?= intval($stats['registrations']) ?></div>
            <div class="stat-cta">
              <a href="./registrations.php" class="btn btn-sm btn-link">Duyệt</a>
              <a href="./registrations.php" class="btn btn-sm btn-link text-muted">Lịch sử</a>
            </div>
          </div>
        </div>
      </div>

      <!-- optional quick preview area -->
      <div class="row mt-4 gx-3">
        <div class="col-lg-7 mb-3">
          <div class="card">
            <div class="card-body p-3">
              <h6 class="mb-3">Các thao tác nhanh</h6>
              <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary btn-sm" href="./students_add.php">Thêm SV</a>
                <a class="btn btn-outline-primary btn-sm" href="./courses.php">Thêm môn</a>
                <a class="btn btn-outline-warning btn-sm" href="./registrations.php">Duyệt đăng ký</a>
                
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5 mb-3">
          <div class="card">
            <div class="card-body p-3">
              <h6 class="mb-3">Tổng quan ngắn</h6>
              <table class="table table-borderless table-sm mini-table mb-0">
                <thead><tr><th>Tên</th><th class="text-end">Số lượng</th></tr></thead>
                <tbody>
                  <tr><td>Sinh viên</td><td class="text-end"><?= intval($stats['students']) ?></td></tr>
                  <tr><td>Môn học</td><td class="text-end"><?= intval($stats['courses']) ?></td></tr>
                  <tr><td>Đăng ký</td><td class="text-end"><?= intval($stats['registrations']) ?></td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

</body>
</html>
