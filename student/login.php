<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    $stmt = $mysqli->prepare("SELECT id, password, name FROM students WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r && password_verify($password, $r['password'])) {
        $_SESSION['student_id'] = $r['id'];
        $_SESSION['student_name'] = $r['name'];
        header('Location: index.php');
        exit;
    } else {
        $err = 'Email hoặc mật khẩu sai';
    }
}
$registered = isset($_GET['registered']) ? true : false;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Student Login</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    html,body { height: 100%; margin: 0; }
    body {
      
      background-image: url('../img/svienbg.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;

      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    /* lớp nền mờ để chữ nổi bật */
    .bg-overlay {
      position: absolute;
      inset: 0;
      background: rgba(255,255,255,0.65);
      backdrop-filter: blur(3px);
      z-index: 0;
    }

    .login-wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 520px;
    }

    .card.login-card {
      background: rgba(255,255,255,0.96);
      border-radius: 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    @media (max-width: 576px) {
      .login-wrap { padding: 0 12px; }
    }
  </style>
</head>
<body>
  <div class="bg-overlay" aria-hidden="true"></div>

  <div class="login-wrap">
    <div class="card login-card p-3">
      <div class="card-body">
        <h4 class="mb-3">Student Login</h4>

        <?php if($registered): ?>
          <div class="alert alert-success">Đăng ký thành công. Bạn có thể đăng nhập.</div>
        <?php endif; ?>

        <?php if($err): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control" required>
          </div>

          <div class="d-flex align-items-center">
            <button class="btn btn-primary me-3">Login</button>
            
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
