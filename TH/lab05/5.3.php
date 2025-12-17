<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <title>Lab5_4_3 - Đăng ký thành viên</title>
    <style>
    fieldset {
        width: 50%;
        margin: 20px auto;
    }

    .error {
        color: red;
    }
    </style>
    <script>
    // Kiểm tra form bằng JavaScript
    function validateForm() {
        let f = document.forms["regForm"];
        let account = f["account"].value.trim();
        let pwd = f["pwd"].value;
        let repwd = f["repwd"].value;
        let gt = f["gt"].value;
        let tinh = f["tinh"].value;
        let img = f["img"].value;

        let errs = [];

        if (account === "") errs.push("Phải nhập tên đăng nhập");
        if (pwd === "") errs.push("Phải nhập mật khẩu");
        if (repwd === "") errs.push("Phải nhập lại mật khẩu");
        if (pwd !== "" && repwd !== "" && pwd !== repwd) errs.push("Mật khẩu nhập lại không trùng");
        if (gt === "") errs.push("Phải chọn giới tính");
        if (tinh === "") errs.push("Phải chọn tỉnh");
        if (img) {
            let okExt = [".jpg", ".jpeg", ".png", ".gif", ".bmp"];
            let lower = img.toLowerCase();
            if (!okExt.some(ext => lower.endsWith(ext))) {
                errs.push("Ảnh phải là file hình (.jpg, .png, .gif, .bmp)");
            }
        }

        if (errs.length > 0) {
            alert("Có lỗi:\n- " + errs.join("\n- "));
            return false; // chặn submit
        }
        return true;
    }
    </script>
</head>

<body>
    <fieldset>
        <legend>Form thông tin thành viên</legend>
        <form name="regForm" action="" method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <label>Tên đăng nhập (*)</label><br>
            <input type="text" name="account"
                value="<?php echo isset($_POST['account']) ? htmlspecialchars($_POST['account']) : ''; ?>"> <br>

            <label>Mật khẩu (*)</label><br>
            <input type="password" name="pwd"> <br>

            <label>Nhập lại mật khẩu (*)</label><br>
            <input type="password" name="repwd"> <br>

            <label>Giới tính (*)</label><br>
            <input type="radio" value="Nam" name="gt"
                <?php if (isset($_POST['gt']) && $_POST['gt'] == 'Nam') {
                        echo 'checked';
                }
                ?>>Nam
            <input type="radio" value="Nu" name="gt"
                <?php if (isset($_POST['gt']) && $_POST['gt'] == 'Nu') {
                        echo 'checked';
                }
                ?>>Nữ<br>

            <label>Sở thích</label><br>
            <input type="text" name="st"
                value="<?php echo isset($_POST['st']) ? htmlspecialchars($_POST['st']) : ''; ?>"><br>

            <label>Hình ảnh (tùy chọn)</label><br>
            <input type="file" name="img" accept=".jpg,.jpeg,.png,.gif,.bmp"> <br>

            <label>Tỉnh (*)</label><br>
            <select name="tinh">
                <option value="">--Chọn tỉnh--</option>
                <?php
                    $provinces = ["Ha Noi", "Ho Chi Minh", "An Giang", "Tien Giang", "Thanh Hoa"];
                    $oldTinh   = $_POST['tinh'] ?? '';
                    foreach ($provinces as $p) {
                        $sel = ($p == $oldTinh) ? 'selected' : '';
                        echo "<option value=\"$p\" $sel>$p</option>";
                    }
                ?>
            </select><br><br>

            <input type="submit" name="Gui" value="Đăng ký">
            <input type="reset" name="Xoa" value="Xóa">
        </form>
    </fieldset>

    <?php
        // Kiểm tra trên server
        if (isset($_POST['Gui'])) {
            $err     = "";
            $account = trim($_POST['account']);
            $pwd     = $_POST['pwd'];
            $repwd   = $_POST['repwd'];
            $gt      = $_POST['gt'] ?? '';
            $tinh    = $_POST['tinh'] ?? '';
            $st      = $_POST['st'] ?? '';

            if ($account == "") {
                $err .= "Phải nhập tên đăng nhập<br>";
            }

            if ($pwd == "") {
                $err .= "Phải nhập mật khẩu<br>";
            }

            if ($repwd == "") {
                $err .= "Phải nhập lại mật khẩu<br>";
            }

            if ($pwd != $repwd) {
                $err .= "Mật khẩu không trùng<br>";
            }

            if ($gt == "") {
                $err .= "Phải chọn giới tính<br>";
            }

            if ($tinh == "") {
                $err .= "Phải chọn tỉnh<br>";
            }

            // kiểm tra file hình nếu có
            if (! empty($_FILES['img']['name'])) {
                $allowed = ["image/jpeg", "image/png", "image/gif", "image/bmp"];
                if ($_FILES['img']['error'] == 0) {
                    if (! in_array($_FILES['img']['type'], $allowed)) {
                        $err .= "File không phải hình hợp lệ<br>";
                    }
                } else {
                    $err .= "Lỗi upload hình<br>";
                }
            }

            if ($err != "") {
                echo "<div class='error'>$err</div>";
            } else {
                echo "<h3>Thông tin hợp lệ:</h3>";
                echo "Tên đăng nhập: " . htmlspecialchars($account) . "<br>";
                echo "Giới tính: " . htmlspecialchars($gt) . "<br>";
                echo "Tỉnh: " . htmlspecialchars($tinh) . "<br>";
                echo "Sở thích: " . htmlspecialchars($st) . "<br>";
                if (! empty($_FILES['img']['name'])) {
                    $name = basename($_FILES['img']['name']);
                    move_uploaded_file($_FILES['img']['tmp_name'], "image/" . $name);
                    echo "Ảnh:<br><img src='image/" . htmlspecialchars($name) . "' style='max-width:200px'>";
                }
            }
        }
    ?>
</body>

</html>