<fieldset>
    <legend>Cach tim san pham</legend>
    <form action="2.php" method="get">
        <label for="">Ten san pham :</label> <input type="text" name="ten" required><br>
        <label for="">cach tim</label> <input type="radio" name="ctim" value="gan dung" required>Gan dung
        <input type="radio" name="ctim" value="chinh xac">Chinh xac <br>
        <select name="loai" id="lsp">
            <option value="tat ca" selected>Tat ca</option>
            <option value="loai 1">Loai 1</option>
            <option value="Loai 2">Loai 2</option>
            <option value="Loai 1">Loai 3</option>
        </select><br>
        <input type="submit" value="Gui">


    </form>
</fieldset>

<?php

?>