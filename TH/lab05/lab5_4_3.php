<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng ký thành viên</title>
    <style>
    fieldset {
        width: 50%;
        margin: 40px auto;
        padding: 20px;
    }

    label {
        font-weight: bold;
    }

    .result {
        width: 50%;
        margin: 20px auto;
        padding: 15px;
        background: #f0f0f0;
        border-radius: 8px;
    }

    .error {
        color: red;
        font-weight: bold;
    }
    </style>
</head>

<body>

    <fieldset>
        <legend>Form thông tin thành viên</legend>
        <form action="" method="post" enctype="multipart/form-data">
            <label>Tên đăng nhập (*)</label><br>
            <input type="text" name="account" required> <br><br>

            <label>Mật khẩu (*)</label><br>
            <input type="password" name="pwd" required> <br><br>

            <label>Nhập lại mật khẩu (*)</label><br>
            <input type="password" name="repwd" required> <br><br>

            <label>Giới tính (*)</label><br>
            <input type="radio" value="Nam" name="gt" required> Nam
            <input type="radio" value="Nu" name="gt"> Nữ<br><br>

            <label>Sở thích</label><br>
            <input type="text" name="st"><br><br>

            <label>Hình ảnh (tùy chọn)</label><br>
            <input type="file" name="img" accept=".jpg,.jpeg,.png,.gif,.bmp"> <br><br>

            <label>Tỉnh (*)</label><br>
            <select name="tinh" required>
                <option value="">-- Chọn tỉnh --</option>
                <option value="Ha Noi">Hà Nội</option>
                <option value="Ho Chi Minh">Hồ Chí Minh</option>
                <option value="An Giang">An Giang</option>
                <option value="Tien Giang">Tiền Giang</option>
                <option value="Thanh Hoa">Thanh Hóa</option>
            </select><br><br>

            <input type="submit" name="Gui" value="Đăng ký">
            <input type="reset" name="Xoa" value="Xóa">
        </form>
    </fieldset>

    <?php
        if (isset($_POST['Gui'])) {
            $account = $_POST['account'] ?? '';
            $pwd     = $_POST['pwd'] ?? '';
            $repwd   = $_POST['repwd'] ?? '';
            $gt      = $_POST['gt'] ?? '';
            $st      = $_POST['st'] ?? '';
            $tinh    = $_POST['tinh'] ?? '';

            $errors = [];

            if ($account == '') {
                $errors[] = "Phải nhập tên đăng nhập.";
            }

            if ($pwd == '') {
                $errors[] = "Phải nhập mật khẩu.";
            }

            if ($repwd == '') {
                $errors[] = "Phải nhập lại mật khẩu.";
            }

            if ($pwd != $repwd) {
                $errors[] = "Mật khẩu nhập lại không trùng khớp.";
            }

            if ($gt == '') {
                $errors[] = "Phải chọn giới tính.";
            }

            if ($tinh == '') {
                $errors[] = "Phải chọn tỉnh.";
            }

            $uploadedFile = null;
            if (! empty($_FILES['img']['name'])) {
                $allowed = ["image/jpeg", "image/png", "image/gif", "image/bmp"];
                if ($_FILES['img']['error'] == 0) {
                    if (! in_array($_FILES['img']['type'], $allowed)) {
                        $errors[] = "File không phải hình hợp lệ.";
                    } else {
                        $name = basename($_FILES['img']['name']);
                        if (! is_dir("image")) {
                            mkdir("image");
                        }

                        if (move_uploaded_file($_FILES['img']['tmp_name'], "image/" . $name)) {
                            $uploadedFile = $name;
                        } else {
                            $errors[] = "Không thể lưu file ảnh.";
                        }
                    }
                } else {
                    $errors[] = "Lỗi upload hình.";
                }
            }

            echo '<div class="result">';
            if (! empty($errors)) {
                echo '<div class="error">Có lỗi xảy ra:<br>';
                foreach ($errors as $e) {
                    echo "- $e<br>";
                }
                echo '</div>';
            } else {
                echo "<h3>Thông tin đăng ký:</h3>";
                echo "Tên đăng nhập: <strong>$account</strong><br>";
                echo "Giới tính: <strong>$gt</strong><br>";
                echo "Sở thích: <strong>$st</strong><br>";
                echo "Tỉnh: <strong>$tinh</strong><br>";
                if ($uploadedFile) {
                    echo "Ảnh:<br><img src='image/" . htmlspecialchars($uploadedFile) . "' style='max-width:200px'>";
                }
            }
            echo '</div>';
        }
    ?>
</body>

</html>