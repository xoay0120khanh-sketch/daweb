<?php
session_start();

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Login cố định
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_id'] = 1; 
        $_SESSION['admin_name'] = 'Administrator';
        header('Location: dashboard.php');
        exit;
    } else {
        $err = 'Sai tài khoản hoặc mật khẩu';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
      body {
          height: 100vh;
          margin: 0;
          display: flex;
          justify-content: center;
          align-items: center;
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;

          background-image: url('../img/background.webp'); 
      }

      

      

      .login-card {
          width: 380px;
          background: rgba(255, 255, 255, 0.92);
          backdrop-filter: blur(6px);
          border-radius: 10px;
      }
  </style>
</head>

<body>

<div class="card shadow login-card p-4">
    <h3 class="text-center mb-3">Admin Login</h3>

    <?php if($err): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <button class="btn btn-primary w-100">Login</button>
    </form>
</div>

</body>
</html>
