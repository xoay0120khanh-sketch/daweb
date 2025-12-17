<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <title>Lab5_2 - Sticky Form</title>
</head>

<body>
    <?php
        echo "<h3>REQUEST:</h3>";
        print_r($_REQUEST);
        echo "<hr><h3>POST:</h3>";
        print_r($_POST);
    ?>
    <hr>
    <a href="?x=1&y=2&z=3">Link 1</a><br>
    <a href="?x[]=1&x[]=2&y=3">Link 2</a><br>
    <a href="?mod=product&ac=detail&id=1">Link 3</a><br>
    <a href="?mod=product&ac=list&name=a&page=2">Link 4</a><br>
    <hr>

    <fieldset>
        <legend>Form 2</legend>
        <form method="post">
            Nhập x1:<input type="text" name="x[]"
                value="<?php echo isset($_POST['x'][0]) ? htmlspecialchars($_POST['x'][0]) : '1'; ?>"><br>
            Nhập x2:<input type="text" name="x[]"
                value="<?php echo isset($_POST['x'][1]) ? htmlspecialchars($_POST['x'][1]) : '2'; ?>"><br>
            Nhập y:<input type="text" name="y"
                value="<?php echo isset($_POST['y']) ? htmlspecialchars($_POST['y']) : '3'; ?>"><br>
            <input type="submit">
        </form>
    </fieldset>

    <fieldset>
        <legend>Form 3</legend>
        <form method="post">
            Nhập tên:<input type="text" name="ten"
                value="<?php echo isset($_POST['ten']) ? htmlspecialchars($_POST['ten']) : ''; ?>"><br>
            Giới tính:
            <input type="radio" name="gt" value="1"
                <?php echo(isset($_POST['gt']) && $_POST['gt'] == '1') ? 'checked' : ''; ?>>Nam
            <input type="radio" name="gt" value="0"
                <?php echo(isset($_POST['gt']) && $_POST['gt'] == '0') ? 'checked' : ''; ?>>Nữ<br>
            Sở Thích:
            <input type="checkbox" name="st[]" value="tt"
                <?php echo(isset($_POST['st']) && in_array('tt', $_POST['st'])) ? 'checked' : ''; ?>>Thể Thao
            <input type="checkbox" name="st[]" value="dl"
                <?php echo(isset($_POST['st']) && in_array('dl', $_POST['st'])) ? 'checked' : ''; ?>>Du Lịch
            <input type="checkbox" name="st[]" value="game"
                <?php echo(isset($_POST['st']) && in_array('game', $_POST['st'])) ? 'checked' : ''; ?>>Game<br>
            <input type="submit">
        </form>
    </fieldset>
</body>

</html>