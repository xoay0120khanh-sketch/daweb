<?php
    function postIndex($index, $value = "")
    {
        if (! isset($_POST[$index])) {
            return $value;
        }

        return $_POST[$index];
    }

    $sm     = postIndex("submit");
    $ten    = postIndex("ten");
    $gt     = postIndex("gt");
    $arrImg = ["image/png", "image/jpeg", "image/bmp", "image/gif"];

    if ($sm == "") {
        header("location:1.php");exit; // quay về 1.php
    }

    $err = "";
    if ($ten == "") {
        $err .= "Phải nhập tên <br>";
    }

    if ($gt == "") {
        $err .= "Phải chọn giới tính <br>";
    }

    $uploadedFiles = [];

    if (isset($_FILES["hinh"])) {
        // duyệt qua tất cả file
        foreach ($_FILES["hinh"]["error"] as $i => $errFile) {
            if ($errFile > 0) {
                $err .= "Lỗi file hình thứ " . ($i + 1) . "<br>";
            } else {
                $type = $_FILES["hinh"]["type"][$i];
                if (! in_array($type, $arrImg)) {
                    $err .= "File thứ " . ($i + 1) . " không phải file hình <br>";
                } else {
                    $temp = $_FILES["hinh"]["tmp_name"][$i];
                    $name = basename($_FILES["hinh"]["name"][$i]);
                    if (! move_uploaded_file($temp, "image/" . $name)) {
                        $err .= "Không thể lưu file " . $name . "<br>";
                    } else {
                        $uploadedFiles[] = $name;
                    }
                }
            }
        }
    }
?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Lab5_3/2 - Upload nhiều hình</title>
</head>

<body>
    <?php
        if ($err != "") {
            echo $err;
        } else {
            if ($gt == "1") {
                echo "Chào Anh: $ten ";
            } else {
                echo "Chào Chị $ten ";
            }

            echo "<hr>";
            // hiển thị tất cả hình đã upload
            foreach ($uploadedFiles as $f) {
                echo '<img src="image/' . htmlspecialchars($f) . '" style="max-width:200px; margin:5px;">';
            }
        }
    ?>
    <p>
        <a href="index.php">Tiếp tục</a>
    </p>
</body>

</html>