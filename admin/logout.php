<?php
session_start();
session_unset();
session_destroy();

header("Location: ../index.php");  // quay về trang chọn người dùng
exit;
